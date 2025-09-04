<?php

namespace App\Jobs;

use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

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
                $toEmail = $rental->user->email;
                $toName = optional($rental->user)->name ?? 'Customer';
                if (!empty($toEmail)) {
                    Mail::send('emails.rental-booking', $emailData, function ($message) use ($toEmail, $toName) {
                        $message->to($toEmail, $toName)
                                ->subject('Booking Created Successfully.');
                    });
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