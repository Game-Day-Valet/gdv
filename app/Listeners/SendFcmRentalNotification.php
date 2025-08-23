<?php

namespace App\Listeners;

use App\Events\RentalStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendFcmRentalNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RentalStatusUpdated $event)
    {
        $user = $event->rental->user;

        if (!$user || !$user->fcm_token || $user->fcm_notification === false) {
            Log::warning('FCM notification skipped: No user or FCM token for rental ID ' . $event->rental->id);
            return;
        }

        // Prevent duplicate notifications using cache lock
        $cacheKey = 'fcm_notification_rental_' . $event->rental->id . '_' . $event->newStatus;
        if (Cache::has($cacheKey)) {
            Log::warning('Duplicate FCM notification prevented for rental ID ' . $event->rental->id, [
                'status' => $event->newStatus,
                'user_id' => $user->id,
            ]);
            return;
        }

        // Set cache lock for 10 seconds to prevent duplicate processing
        Cache::put($cacheKey, true, now()->addSeconds(10));

        $messaging = app('firebase.messaging');

        $statusLabel = ucfirst(str_replace('_', ' ', $event->newStatus));
        $title = 'Rental Status Updated';
        $body = "Your rental #{$event->rental->id} is now {$statusLabel}.";

        if ($event->newStatus === 'confirmed' && $event->rental->estimated_delivery_time) {
            $formattedTime = \Carbon\Carbon::parse($event->rental->estimated_delivery_time)->format('d M Y H:i');
            $body .= " Estimated delivery: {$formattedTime}.";
        }

        $notification = Notification::create($title, $body);

        $message = CloudMessage::withTarget('token', $user->fcm_token)
            ->withNotification($notification)
            ->withData([
                'rental_id' => (string) $event->rental->id,
                'new_status' => $event->newStatus,
                'updated_at' => $event->rental->updated_at->toIso8601String(),
            ]);

        try {
            $messaging->send($message);
            Log::info('FCM notification sent for rental ID ' . $event->rental->id, [
                'user_id' => $user->id,
                'fcm_token' => $user->fcm_token,
                'status' => $event->newStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('FCM notification failed for rental ID ' . $event->rental->id, [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }
}