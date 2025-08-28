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

        // Prevent duplicate notifications using atomic lock or cache-add
        $cacheKey = 'fcm_notification_rental_' . $event->rental->id . '_' . $event->newStatus . '_' . $user->id;
        try {
            $lock = Cache::lock($cacheKey . '_lock', 10);
            if (!$lock->get()) {
                Log::warning('Duplicate FCM notification prevented by lock for rental ID ' . $event->rental->id, [
                    'status' => $event->newStatus,
                    'user_id' => $user->id,
                ]);
                return;
            }
        } catch (\Throwable $e) {
            if (!Cache::add($cacheKey, true, now()->addSeconds(30))) {
                Log::warning('Duplicate FCM notification prevented by cache-add for rental ID ' . $event->rental->id, [
                    'status' => $event->newStatus,
                    'user_id' => $user->id,
                ]);
                return;
            }
        }

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

        // Also send Twilio SMS for status updates if enabled and phone number exists
        $to = $event->rental->phone_number;
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');
        $enabled = (bool) config('services.twilio.enabled', true);
        if ($enabled && $to && $sid && $token && $from) {
            try {
                $twilio = new \Twilio\Rest\Client($sid, $token);
                $typeMap = [
                    'confirmed' => 'sms_status_confirmed',
                    'out_for_delivery' => 'sms_status_out_for_delivery',
                    'delivered' => 'sms_status_delivered',
                    'cancelled' => 'sms_status_cancelled',
                ];
                $templateType = $typeMap[$event->newStatus] ?? null;
                if ($templateType) {
                    $body = (string) (\App\Models\BookingOption::where('type', $templateType)->value('description') ?? '');
                    if ($body !== '') {
                        $twilio->messages->create($to, ['from' => $from, 'body' => $body]);
                        Log::info('Twilio SMS sent for rental status update', [
                            'rental_id' => $event->rental->id,
                            'status' => $event->newStatus,
                            'to' => $to,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Twilio SMS failed for rental status update', [
                    'rental_id' => $event->rental->id,
                    'status' => $event->newStatus,
                    'to' => $to,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}