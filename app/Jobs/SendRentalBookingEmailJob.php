<?php

namespace App\Jobs;

use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Str;
use App\Models\EmailLog;
use App\Models\SettingNotification;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Cache;

class SendRentalBookingEmailJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $rental;

    /**
     * Create a new job instance.
     */
    public function __construct(Rental $rental)
    {
        $this->rental = $rental;
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId()
    {
        return 'rental_email_' . $this->rental->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $rental = $this->rental->load(['user', 'tournament', 'tournament.sport']);

            // Prepare email data
            $dynamicContent = \App\Models\BookingOption::where('type', 'email_content')->value('description');

            // Build name maps for items and bundles displayed in the email
            $itemNames = [];
            $bundleNames = [];
            try {
                if (!empty($rental->items) && is_array($rental->items)) {
                    $ids = collect($rental->items)->pluck('item_id')->filter()->unique()->values()->all();
                    if (!empty($ids)) {
                        $itemNames = \App\Models\Item::whereIn('id', $ids)->pluck('name', 'id')->toArray();
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }
            try {
                if (!empty($rental->bundles) && is_array($rental->bundles)) {
                    $bids = [];
                    foreach ($rental->bundles as $b) {
                        if (is_array($b) && isset($b['bundle_id'])) { $bids[] = $b['bundle_id']; }
                        elseif (is_numeric($b)) { $bids[] = $b; }
                    }
                    $bids = array_values(array_unique($bids));
                    if (!empty($bids)) {
                        $bundleNames = \App\Models\Bundle::whereIn('id', $bids)->pluck('name', 'id')->toArray();
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }

            $emailData = [
                'rental' => $rental,
                'user' => $rental->user,
                'tournament' => $rental->tournament,
                'sport' => $rental->tournament->sport ?? null,
                'email_content' => $dynamicContent,
                'itemNames' => $itemNames,
                'bundleNames' => $bundleNames,
            ];
            
            // Respect user's email notification preference if a user exists
            $canEmail = true;
            if ($rental->user) {
                $canEmail = $rental->user->email_notification !== false;
            }
            
            if ($canEmail) {
                // Global email toggle
                if (!SettingNotification::current()->email_enabled) {
                    Log::info('Global email disabled; skipping booking email', ['rental_id' => $rental->id]);
                    return;
                }
                $toEmail = $rental->user->email;
                $toName = optional($rental->user)->name ?? 'Customer';
                if (!empty($toEmail)) {
                    $subject = 'Booking Created Successfully.';
                    

                    $emailLog = EmailLog::create([
                        'to_email' => $toEmail,
                        'subject' => $subject,
                        'body_preview' => (string) ($dynamicContent ?? ''),
                        'status' => 'queued',
                        'meta' => ['context' => 'booking_confirmation', 'rental_id' => $rental->id],
                    ]);

                    $smtpReady = (bool) (config('mail.mailers.smtp.host') && config('mail.mailers.smtp.username') && config('mail.mailers.smtp.password') && config('mail.from.address'));
                    if (!$smtpReady) {
                        $emailLog->update(['status' => 'failed', 'error_reason' => 'Email not sent: SMTP configuration incomplete.']);
                        Log::warning('Booking email skipped due to incomplete SMTP config', ['rental_id' => $rental->id, 'to' => $toEmail]);
                        return;
                    }
                    try {
                        // Generate PDF invoice
                        $invoiceService = new InvoiceService();
                        $invoicePath = $invoiceService->generateInvoice($rental);
                        
                        Mail::send('emails.rental-booking', $emailData, function ($message) use ($toEmail, $toName, $subject, $invoicePath) {
                            $message->to($toEmail, $toName)
                                    ->subject($subject)
                                    ->attach($invoicePath, [
                                        'as' => 'Invoice-GDV-' . str_pad($this->rental->id, 6, '0', STR_PAD_LEFT) . '.pdf',
                                        'mime' => 'application/pdf',
                                    ]);
                        });
                        $emailLog->update(['status' => 'sent', 'sent_at' => now()]);
                    } catch (\Throwable $mailErr) {
                        $short = collect(preg_split("/\r?\n/", (string) $mailErr->getMessage()))->filter()->take(3)->implode(" \n");
                        $emailLog->update(['status' => 'failed', 'error_reason' => $short]);
                        Log::error('Booking email send failed', [
                            'rental_id' => $rental->id,
                            'to' => $toEmail,
                            'error' => $mailErr->getMessage(),
                        ]);
                        return; // do not bubble up transport errors
                    }
                }
            }

            // Send SMS via Twilio if phone number is present and service is enabled
            // Original destination pulled from rental record (temporarily overridden per request)
            $originalTo = $rental->phone_number;
            // $to = '+18777804236';
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');
            $enabled = (bool) config('services.twilio.enabled', true);
            // Respect user's text notification preference if a user exists
            $canText = true;
            if ($rental->user) {
                $canText = $rental->user->text_notification !== false;
            }

            if ($canText && $enabled && !empty($originalTo) && $sid && $token && $from) {
                try {
                    $twilio = new TwilioClient($sid, $token);
                    $rawBody = (string) (\App\Models\BookingOption::where('type', 'sms_booking_confirmation')->value('description') ?? '');
                    $body = trim($rawBody);
                    if ($body === '') {
                        $body = 'Your rental booking has been received. We will notify you with updates.';
                    }
                    // Respect user SMS preference if such a flag is ever added; for now SMS independent of email flag
                    $twilio->messages->create($originalTo, ['from' => $from, 'body' => $body]);
                    Log::info('Twilio SMS sent for rental booking', [
                        'rental_id' => $rental->id,
                        'to' => $originalTo,
                        'original_to' => $originalTo,
                        'used_fallback' => trim($rawBody) === '',
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Twilio SMS failed', [
                        'rental_id' => $rental->id,
                        'to' => $originalTo,
                        'original_to' => $originalTo,
                        'error' => $e->getMessage(),
                        'hint' => 'Ensure server has internet/DNS, correct Twilio credentials, phone in E.164 format.'
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('SendRentalBookingEmailJob failed', [
                'rental_id' => $this->rental->id,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toISOString()
            ]);
            
            throw $e;
        }

        // Admin notification email (new booking)
        try {
            if (SettingNotification::current()->email_enabled) {
                $adminEmail = trim((string) env('ADMIN_NOTIFY_EMAIL', config('mail.from.address')));
                if (!empty($adminEmail)) {
                    // Throttle to avoid SMTP rate limits (e.g., Mailtrap: 1 msg/sec on free tier)
                    $lockKey = 'admin_notify_throttle';
                    if (!Cache::add($lockKey, true, now()->addSeconds(2))) {
                        // If locked, delay send a bit via re-dispatch
                        self::dispatch($this->rental)->delay(now()->addSeconds(3));
                        return;
                    }
                    $subject = 'New Rental Booking Received';
                    $adminData = [
                        'rental' => $this->rental->load(['user','tournament','tournament.sport']),
                        'user' => $this->rental->user,
                        'tournament' => $this->rental->tournament,
                        'sport' => optional($this->rental->tournament)->sport,
                        'email_content' => 'A new booking has been placed. View it in the admin panel: ' . route('rental-management.index'),
                        'itemNames' => [],
                        'bundleNames' => [],
                        'title' => 'New Booking Notification',
                    ];

                    $log = EmailLog::create([
                        'to_email' => $adminEmail,
                        'subject' => $subject,
                        'body_preview' => (string) $adminData['email_content'],
                        'status' => 'queued',
                        'meta' => ['context' => 'admin_booking_notification', 'rental_id' => $this->rental->id],
                    ]);

                    $smtpReady = (bool) (config('mail.mailers.smtp.host') && config('mail.mailers.smtp.username') && config('mail.mailers.smtp.password') && config('mail.from.address'));
                    if ($smtpReady) {
                        // Generate PDF invoice for admin notification
                        $invoiceService = new InvoiceService();
                        $invoicePath = $invoiceService->generateInvoice($this->rental);
                        
                        Mail::send('emails.rental-booking', $adminData, function ($message) use ($adminEmail, $subject, $invoicePath) {
                            $message->to($adminEmail)
                                    ->subject($subject)
                                    ->attach($invoicePath, [
                                        'as' => 'Invoice-GDV-' . str_pad($this->rental->id, 6, '0', STR_PAD_LEFT) . '.pdf',
                                        'mime' => 'application/pdf',
                                    ]);
                        });
                        $log->update(['status' => 'sent', 'sent_at' => now()]);
                    } else {
                        $log->update(['status' => 'failed', 'error_reason' => 'SMTP configuration incomplete']);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Admin booking email failed', ['rental_id' => $this->rental->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendRentalBookingEmailJob failed permanently', [
            'rental_id' => $this->rental->id,
            'error' => $exception->getMessage(),
            'error_class' => get_class($exception),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => now()->toISOString()
        ]);
    }
} 