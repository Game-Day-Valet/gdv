<?php

namespace App\Listeners;

use App\Events\RentalStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendRentalStatusUpdateEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RentalStatusUpdated $event): void
    {
        try {
            $rental = $event->rental->load(['user', 'tournament', 'tournament.sport']);

            $status = (string) $event->newStatus;
            $statusLabel = ucfirst(str_replace('_', ' ', $status));

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
                    $bids = collect($rental->bundles)->filter('is_numeric')->unique()->values()->all();
                    if (!empty($bids)) {
                        $bundleNames = \App\Models\Bundle::whereIn('id', $bids)->pluck('name', 'id')->toArray();
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }

            // Build email data payload for the Blade template (first paragraph is dynamic)
            $emailData = [
                'rental' => $rental,
                'user' => $rental->user,
                'tournament' => $rental->tournament,
                'sport' => $rental->tournament->sport ?? null,
                'email_content' => (trim($dynamicContent) !== '' ? $dynamicContent : $fallback),
                'itemNames' => $itemNames,
                'bundleNames' => $bundleNames,
            ];

            $toEmail = $rental->email ?? optional($rental->user)->email;
            $toName = optional($rental->user)->name ?? 'Customer';

            if (!empty($toEmail)) {
                // Use the existing full booking email template; only first paragraph is dynamic
                Mail::send('emails.rental-booking', $emailData, function ($message) use ($rental, $toEmail, $toName, $statusLabel) {
                    $message->to($toEmail, $toName)
                        ->subject('Rental Status: ' . $statusLabel);
                });
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


