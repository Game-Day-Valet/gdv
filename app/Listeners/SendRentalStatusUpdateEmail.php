<?php

namespace App\Listeners;

use App\Events\RentalStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\EmailLog;
use App\Models\SettingNotification;

class SendRentalStatusUpdateEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RentalStatusUpdated $event): void
    {
        try {
            $rental = $event->rental->load(['user', 'tournament', 'tournament.sport']);

            $status = (string) $event->newStatus;
            $statusLabel = ucfirst(str_replace('_', ' ', $status));

            // Skip sending emails for confirmed and out_for_delivery per requirement
            if (in_array($status, ['confirmed', 'out_for_delivery'], true)) {
                Log::info('Skipping status email per rule', [
                    'rental_id' => $rental->id,
                    'status' => $status,
                ]);
                return;
            }

            // De-dupe: avoid sending twice for same rental/status in a short window
            $cacheKey = 'email_status_rental_' . $rental->id . '_' . $status . '_' . optional($rental->updated_at)->timestamp;
            if (!Cache::add($cacheKey, true, now()->addMinutes(2))) {
                Log::warning('Duplicate status email prevented', [
                    'rental_id' => $rental->id,
                    'status' => $status,
                ]);
                return;
            }

            // Use the same BookingOption types as SMS for dynamic text (admin-managed)
            $typeMap = [
                'confirmed' => 'sms_status_confirmed',
                'out_for_delivery' => 'sms_status_out_for_delivery',
                'delivered' => 'sms_status_delivered',
                'cancelled' => 'sms_status_cancelled',
            ];
            $templateType = $typeMap[$status] ?? null;
            $dynamicContent = '';
            if ($templateType) {
                $dynamicContent = (string) (\App\Models\BookingOption::where('type', $templateType)->value('description') ?? '');
            }

            // Fallback content if admin hasn't configured a message
            $fallbackByStatus = [
                'confirmed' => "Great news! Your rental booking has been confirmed successfully.",
                'out_for_delivery' => "Good news! Your rental is out for delivery and on its way.",
                'delivered' => "Your rental has been delivered. We hope everything is perfect!",
                'cancelled' => "Your rental has been cancelled. If this was a mistake, please contact support.",
            ];
            $fallback = $fallbackByStatus[$status] ?? 'Your rental status has been updated.';

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

            // Build email data payload for the single Blade template (title + dynamic content)
            $emailData = [
                'rental' => $rental,
                'user' => $rental->user,
                'tournament' => $rental->tournament,
                'sport' => $rental->tournament->sport ?? null,
                'email_content' => (trim($dynamicContent) !== '' ? $dynamicContent : $fallback),
                'itemNames' => $itemNames,
                'bundleNames' => $bundleNames,
                'title' => [
                    'confirmed' => 'Booking Confirmed',
                    'out_for_delivery' => 'Out For Delivery',
                    'delivered' => 'Delivered Successfully',
                    'cancelled' => 'Booking Cancelled',
                ][$status] ?? 'Rental Status Update',
            ];

            $toEmail = $rental->user->email;
            $toName = optional($rental->user)->name ?? 'Customer';

            // Respect user's email notification preference when user exists
            $canEmail = true;
            if ($rental->user) {
                $canEmail = $rental->user->email_notification !== false;
            }

            if ($canEmail && !empty($toEmail)) {
                if (!SettingNotification::current()->email_enabled) {
                    Log::info('Global email disabled; skipping status email', ['rental_id' => $rental->id, 'status' => $status]);
                    return;
                }
                // Always use the single booking template; only title/content/subject differ
                $view = 'emails.rental-booking';

                // Better, status-specific subject lines
                $subjectMap = [
                    'confirmed' => 'Your rental booking is confirmed',
                    'out_for_delivery' => 'Your rental is out for delivery',
                    'delivered' => 'Your rental has been delivered',
                    'cancelled' => 'Your rental booking has been cancelled',
                ];
                $subject = $subjectMap[$status] ?? ('Rental status updated: ' . $statusLabel);

                $emailLog = EmailLog::create([
                    'to_email' => $toEmail,
                    'subject' => $subject,
                    'status' => 'queued',
                    'body_preview' => (string) ($dynamicContent ?? ''),
                    'meta' => ['context' => 'status_update', 'rental_id' => $rental->id, 'status' => $status],
                ]);

                $smtpReady = (bool) (config('mail.mailers.smtp.host') && config('mail.mailers.smtp.username') && config('mail.mailers.smtp.password') && config('mail.from.address'));
                if (!$smtpReady) {
                    $emailLog->update(['status' => 'failed', 'error_reason' => 'Email not sent: SMTP configuration incomplete.']);
                    Log::warning('Status email skipped due to incomplete SMTP config', ['rental_id' => $rental->id, 'status' => $status, 'to' => $toEmail]);
                    return;
                }
                try {
                    Mail::send($view, $emailData, function ($message) use ($rental, $toEmail, $toName, $subject) {
                        $message->to($toEmail, $toName)
                            ->subject($subject);
                    });
                    $emailLog->update(['status' => 'sent', 'sent_at' => now()]);
                } catch (\Throwable $mailErr) {
                    $short = collect(preg_split("/\r?\n/", (string) $mailErr->getMessage()))->filter()->take(3)->implode(" \n");
                    $emailLog->update(['status' => 'failed', 'error_reason' => $short]);
                    Log::error('Status update email send failed', [
                        'rental_id' => $rental->id,
                        'status' => $status,
                        'to' => $toEmail,
                        'error' => $mailErr->getMessage(),
                    ]);
                    return; // swallow transport errors to avoid breaking flow
                }
                Log::info('Rental status update email sent', [
                    'rental_id' => $rental->id,
                    'status' => $status,
                    'to' => $toEmail,
                ]);
            } else {
                Log::warning('Rental status update email skipped (no recipient email)', [
                    'rental_id' => $rental->id,
                    'status' => $status,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('SendRentalStatusUpdateEmail failed', [
                'rental_id' => $event->rental->id ?? null,
                'status' => $event->newStatus ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}


