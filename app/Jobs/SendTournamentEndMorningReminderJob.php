<?php

namespace App\Jobs;

use App\Models\BookingOption;
use App\Models\EmailLog;
use App\Models\SettingNotification;
use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTournamentEndMorningReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Rental $rental;

    public function __construct(Rental $rental)
    {
        $this->rental = $rental;
    }

    public function handle(): void
    {
        $rental = Rental::with(['user', 'tournament', 'tournament.sport'])->find($this->rental->id);
        if (!$rental || !$rental->user) return;

        $user = $rental->user;
        $notif = SettingNotification::current();

        $emailContent = (string) (BookingOption::where('type', 'email_end_day_morning')->value('description') ?? '');
        $smsContent = (string) (BookingOption::where('type', 'sms_end_day_morning')->value('description') ?? '');

        $eventName = trim((string) optional($rental->tournament)->name) ?: 'your tournament';

        if (trim($emailContent) === '') {
            $emailContent = "Reminder: {$eventName} ends today. Please follow return instructions and timelines.";
        } else {
            $emailContent = str_contains($emailContent, '{{tournament_name}}')
                ? str_replace('{{tournament_name}}', $eventName, $emailContent)
                : ($eventName . ': ' . $emailContent);
        }

        if (trim($smsContent) === '') {
            $smsContent = "Reminder: {$eventName} ends today. Please prepare rental returns.";
        } else {
            $smsContent = str_contains($smsContent, '{{tournament_name}}')
                ? str_replace('{{tournament_name}}', $eventName, $smsContent)
                : ($eventName . ': ' . $smsContent);
        }

        // Email
        try {
            $canEmail = $notif->email_enabled && ($user->email_notification !== false) && !empty($user->email);
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
                    'title' => 'Tournament Ends Today',
                ];

                $log = EmailLog::create([
                    'to_email' => $user->email,
                    'subject' => 'Tournament Ends Today',
                    'body_preview' => (string) $emailContent,
                    'status' => 'queued',
                    'meta' => ['type' => 'end_day_morning', 'rental_id' => $rental->id],
                ]);

                $host = config('mail.mailers.smtp.host');
                $username = config('mail.mailers.smtp.username');
                $password = config('mail.mailers.smtp.password');
                $from = config('mail.from.address');
                if (!$host || !$username || !$password || !$from) {
                    $log->update(['status' => 'failed', 'error_reason' => 'SMTP configuration incomplete']);
                    Log::warning('End-day morning email skipped due to SMTP config', ['rental_id' => $rental->id, 'user_id' => $user->id]);
                } else {
                    Log::info('Sending end-day morning email', ['rental_id' => $rental->id, 'user_id' => $user->id, 'to' => $user->email]);
                    Mail::send($view, $payload, function ($message) use ($user) {
                        $message->to($user->email, $user->name ?? 'Customer')
                            ->subject('Tournament Ends Today');
                    });
                    $log->update(['status' => 'sent', 'sent_at' => now()]);
                    Log::info('End-day morning email sent', ['rental_id' => $rental->id, 'user_id' => $user->id]);
                }
            }
        } catch (\Throwable $e) {
            try {
                EmailLog::create([
                    'to_email' => $user->email ?? '',
                    'subject' => 'Tournament Ends Today',
                    'body_preview' => (string) $emailContent,
                    'status' => 'failed',
                    'error_reason' => collect(explode("\n", (string)$e->getMessage()))->filter()->take(3)->implode(' | '),
                    'meta' => ['type' => 'end_day_morning', 'rental_id' => $rental->id],
                ]);
            } catch (\Throwable $ignored) {}
            Log::error('End-day morning email failed', ['rental_id' => $rental->id, 'user_id' => optional($user)->id, 'error' => $e->getMessage()]);
        }

        // SMS
        try {
            $canSms = $notif->sms_enabled && ($user->text_notification !== false);
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');
            $enabled = (bool) config('services.twilio.enabled', true);
            $to = $rental->phone_number ?: ($user->contact_number ?? null);
            if ($canSms && $enabled && $sid && $token && $from && $to) {
                Log::info('Sending end-day morning SMS', ['rental_id' => $rental->id, 'user_id' => $user->id, 'to' => $to]);
                $client = new \Twilio\Rest\Client($sid, $token);
                $client->messages->create($to, [
                    'from' => $from,
                    'body' => $smsContent,
                ]);
                Log::info('End-day morning SMS sent', ['rental_id' => $rental->id, 'user_id' => $user->id]);
            } else {
                Log::info('End-day morning SMS skipped', [
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
            Log::error('Failed to send end-day morning SMS', ['rental_id' => $rental->id, 'error' => $e->getMessage()]);
        }
    }
}


