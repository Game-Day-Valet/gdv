<?php

namespace App\Listeners;

use App\Events\RentalStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\RentalStatusLog;
// use Illuminate\Support\Facades\Mail; // email sending moved to SendRentalStatusUpdateEmail listener
use App\Models\SettingNotification;

class SendFcmRentalNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RentalStatusUpdated $event)
    {
        $user = $event->rental->user;

        // Idempotency: suppress duplicate notifications for same rental+status
        $dedupeKey = 'rental_status_notified_' . $event->rental->id . '_' . $event->newStatus;
        if (!Cache::add($dedupeKey, now()->toIso8601String(), now()->addMinutes(15))) {
            Log::warning('Duplicate status notification suppressed', [
                'rental_id' => $event->rental->id,
                'status' => $event->newStatus,
            ]);
            return;
        }

        $globalFcm = (bool) \App\Models\SettingNotification::current()->fcm_enabled;
        $hasFcm = $globalFcm && $user && $user->fcm_token && $user->fcm_notification !== false;

        // Prevent duplicate notifications using atomic lock or cache-add
        $cacheKey = 'fcm_notification_rental_' . $event->rental->id . '_' . $event->newStatus;
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

        if ($hasFcm) { $messaging = app('firebase.messaging'); }

        $statusLabel = ucfirst(str_replace('_', ' ', $event->newStatus));
        $title = 'Rental Status Updated';
        $body = "Your rental #{$event->rental->id} is now {$statusLabel}.";

        // Estimated delivery time messaging disabled for now

        if ($hasFcm) {
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
        } else {
            Log::info('FCM skipped (no token); proceeding with SMS for rental ID ' . $event->rental->id);
        }

        // Always attempt SMS regardless of FCM, but honor user text preference
        // Original destination pulled from rental record (temporarily overridden per request)
        $originalTo = $event->rental->phone_number;
        // $to = '+18777804236';
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');
        $globalSms = (bool) \App\Models\SettingNotification::current()->sms_enabled;
        $enabled = $globalSms && (bool) config('services.twilio.enabled', true);
        // if ($enabled && $to && $sid && $token && $from) {
        $canText = true;
        // Per requirement: do NOT send SMS for confirmed or out_for_delivery
        if (in_array($event->newStatus, ['confirmed', 'out_for_delivery'], true)) {
            $canText = false;
        }
        if ($user) {
            $canText = $user->text_notification !== false;
        }
        if ($canText && $enabled && $originalTo && $sid && $token && $from) {
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
                    $rawBody = (string) (\App\Models\BookingOption::where('type', $templateType)->value('description') ?? '');
                    $body = trim($rawBody);
                    if ($body === '') {
                        $fallbackByStatus = [
                            'confirmed' => 'Your rental booking has been confirmed successfully.',
                            'out_for_delivery' => 'Your rental is out for delivery and on its way.',
                            'delivered' => 'Your rental has been delivered. Thank you for choosing us.',
                            'cancelled' => 'Your rental has been cancelled. Contact support if this is unexpected.',
                        ];
                        $body = $fallbackByStatus[$event->newStatus] ?? 'Your rental status has been updated.';
                    }
                    // If delivered, include delivery photos as MMS media
                    $messageParams = ['from' => $from, 'body' => $body];
                    if ($event->newStatus === 'delivered') {
                        try {
                            $latestLog = RentalStatusLog::where('rental_id', $event->rental->id)
                                ->where('status', 'delivered')
                                ->orderBy('created_at', 'desc')
                                ->first();
                            $paths = $latestLog && is_array($latestLog->image_paths) ? $latestLog->image_paths : [];
                            Log::info('Preparing MMS media for delivered status', [
                                'rental_id' => $event->rental->id,
                                'log_id' => optional($latestLog)->id,
                                'paths' => $paths,
                            ]);
                            $mediaUrls = [];
                            foreach ($paths as $p) {
                                $p = ltrim($p, '/');
                                $isPublic = Storage::disk('public')->exists($p);
                                if ($isPublic) {
                                    $url = asset('storage/' . $p);
                                    // Ensure https for Twilio
                                    if (strpos($url, 'http://') === 0) { $url = preg_replace('#^http://#', 'https://', $url); }
                                    $mediaUrls[] = $url;
                                    Log::info('Added MMS media URL', [ 'url' => $url ]);
                                }
                                if (count($mediaUrls) >= 10) break; // Twilio max 10
                            }
                            if (!empty($mediaUrls)) {
                                $messageParams['mediaUrl'] = $mediaUrls;
                                Log::info('Final MMS media list prepared', [ 'count' => count($mediaUrls), 'urls' => $mediaUrls ]);
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Failed preparing MMS media for delivered status', [
                                'rental_id' => $event->rental->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                    $result = $twilio->messages->create($originalTo, $messageParams);
                    Log::info('Twilio SMS sent for rental status update', [
                        'rental_id' => $event->rental->id,
                        'status' => $event->newStatus,
                        'to' => $originalTo,
                        'original_to' => $originalTo,
                        'used_fallback' => $rawBody === null || trim($rawBody) === '',
                        'twilio_sid' => method_exists($result ?? null, 'sid') ? $result->sid : null,
                        'has_media' => isset($messageParams['mediaUrl']) ? count((array)$messageParams['mediaUrl']) : 0,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Twilio SMS failed for rental status update', [
                    'rental_id' => $event->rental->id,
                    'status' => $event->newStatus,
                    'to' => $originalTo,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}