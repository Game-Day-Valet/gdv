<?php

namespace App\Jobs;

use App\Models\BookingOption;
use App\Models\EmailLog;
use App\Models\SettingNotification;
use App\Models\User;
use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTournamentEndReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Rental $rental;

    public function __construct(Rental $rental)
    {
        // Pass minimal payload and re-fetch later
        $this->rental = $rental;
    }

    public function handle(): void
    {
        $rental = Rental::with(['user', 'tournament', 'tournament.sport'])->find($this->rental->id);
        if (!$rental) return;

        $user = $rental->user;
        if (!$user) return;

        $notif = SettingNotification::current();

        // Dynamic content (admin managed)
        $emailContent = (string) (BookingOption::where('type', 'email_pre_end_reminder')->value('description') ?? '');
        $smsContent = (string) (BookingOption::where('type', 'sms_pre_end_reminder')->value('description') ?? '');

        $eventName = trim((string) optional($rental->tournament)->name) ?: 'your tournament';

        // Build fallback content if not configured
        if (trim($emailContent) === '') {
            $emailContent = "Friendly reminder: {$eventName} wraps up tomorrow. Please plan returns per instructions.";
        } else {
            // Admin content is static; inject tournament name automatically
            if (str_contains($emailContent, '{{tournament_name}}')) {
                $emailContent = str_replace('{{tournament_name}}', $eventName, $emailContent);
            } else {
                $emailContent = "{$eventName}: " . $emailContent;
            }
        }
        if (trim($smsContent) === '') {
            $smsContent = "Reminder: {$eventName} ends tomorrow. Please prepare your rental returns.";
        } else {
            if (str_contains($smsContent, '{{tournament_name}}')) {
                $smsContent = str_replace('{{tournament_name}}', $eventName, $smsContent);
            } else {
                $smsContent = "{$eventName}: " . $smsContent;
            }
        }

        // Email
        try {
            $recipientEmail = $rental->email ?: ($user->email ?? null);
            $canEmail = $notif->email_enabled && ($user->email_notification !== false) && !empty($recipientEmail);
            if ($canEmail) {
                $view = 'emails.rental-booking';
                $payload = [
                    'rental' => $rental,
                    'user' => $user,
                    'tournament' => $rental->tournament,
                    'sport' => $rental->tournament->sport ?? null,
                    'email_content' => $emailContent,
                    'itemNames' => [],
                    'bundleNames' => [],
                    'title' => 'Tournament Reminder',
                ];

                // Log create (queued)
                $log = EmailLog::create([
                    'to_email' => $recipientEmail,
                    'subject' => 'Tournament Reminder',
                    'body_preview' => (string) $emailContent,
                    'status' => 'queued',
                    'meta' => ['type' => 'pre_end_reminder', 'rental_id' => $rental->id],
                ]);

                // SMTP pre-check
                $host = config('mail.mailers.smtp.host');
                $username = config('mail.mailers.smtp.username');
                $password = config('mail.mailers.smtp.password');
                $from = config('mail.from.address');
                if (!$host || !$username || !$password || !$from) {
                    $log->update(['status' => 'failed', 'error_reason' => 'SMTP configuration incomplete']);
                    Log::warning('Pre-end reminder email skipped due to SMTP config', ['rental_id' => $rental->id, 'user_id' => $user->id]);
                } else {
                    Log::info('Sending pre-end reminder email', ['rental_id' => $rental->id, 'user_id' => $user->id, 'to' => $recipientEmail]);
                    Mail::send($view, $payload, function ($message) use ($recipientEmail, $user) {
                        $message->to($recipientEmail, $user->name ?? 'Customer')
                            ->subject('Tournament Reminder');
                    });
                    $log->update(['status' => 'sent', 'sent_at' => now()]);
                    Log::info('Pre-end reminder email sent', ['rental_id' => $rental->id, 'user_id' => $user->id]);
                }
            }
        } catch (\Throwable $e) {
            try {
                EmailLog::create([
                    'to_email' => $recipientEmail ?? '',
                    'subject' => 'Tournament Reminder',
                    'body_preview' => (string) $emailContent,
                    'status' => 'failed',
                    'error_reason' => collect(explode("\n", (string)$e->getMessage()))->filter()->take(3)->implode(' | '),
                    'meta' => ['type' => 'pre_end_reminder', 'rental_id' => $rental->id],
                ]);
            } catch (\Throwable $ignored) {}
            Log::error('Pre-end reminder email failed', ['rental_id' => $rental->id, 'user_id' => optional($user)->id, 'error' => $e->getMessage()]);
        }

        // SMS via Twilio
        try {
            $canSms = $notif->sms_enabled && ($user->text_notification !== false);
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');
            $enabled = (bool) config('services.twilio.enabled', true);
            $to = $rental->phone_number ?: ($user->contact_number ?? null);
            if ($canSms && $enabled && $sid && $token && $from && $to) {
                Log::info('Sending pre-end reminder SMS', ['rental_id' => $rental->id, 'user_id' => $user->id, 'to' => $to]);
                $client = new \Twilio\Rest\Client($sid, $token);
                $client->messages->create($to, [
                    'from' => $from,
                    'body' => $smsContent,
                ]);
                Log::info('Pre-end reminder SMS sent', ['rental_id' => $rental->id, 'user_id' => $user->id]);
            } else {
                Log::info('Pre-end reminder SMS skipped', [
                    'rental_id' => $rental->id,
                    'user_id' => optional($user)->id,
                    'canSms' => $canSms,
                    'enabled' => $enabled,
                    'hasSid' => !empty($sid),
                    'hasToken' => !empty($token),
                    'hasFrom' => !empty($from),
                    'hasTo' => !empty($to),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send pre-end SMS', ['rental_id' => $rental->id, 'error' => $e->getMessage()]);
        }
    }
}


