<?php

namespace App\Notifications;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Illuminate\Support\Facades\Cache;
use App\Models\SettingNotification;

class RentalBookingConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $rental;
    protected $user;

    public function __construct(Rental $rental, User $user)
    {
        $this->rental = $rental;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        $this->toFcm($notifiable);

        return [
            'rental_id' => $this->rental->id,
            'user_id' => $this->rental->user_id,
            'tournament_name' => $this->rental->tournament->name ?? 'N/A',
            'total_amount' => $this->rental->total_amount,
            'status' => $this->rental->status,
            'message' => 'Your rental booking #' . $this->rental->id . ' has been confirmed.',
            'timestamp' => now(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'rental_id' => $this->rental->id,
            'user_id' => $this->rental->user_id,
            'tournament_name' => $this->rental->tournament->name ?? 'N/A',
            'total_amount' => $this->rental->total_amount,
            'status' => $this->rental->status,
            'message' => 'Your rental booking #' . $this->rental->id . ' has been confirmed.',
            'timestamp' => now(),
        ]);
    }

    public function toFcm($notifiable)
    {
        // Respect global switch
        if (!SettingNotification::current()->fcm_enabled) {
            Log::info('Global FCM disabled, skipping FCM notification', [
                'rental_id' => $this->rental->id,
                'user_id' => $notifiable->id,
            ]);
            return null;
        }

        if (!$notifiable->fcm_token || $notifiable->fcm_notification === false) {
            Log::info('User has no FCM token, skipping FCM notification', [
                'rental_id' => $this->rental->id,
                'user_id' => $notifiable->id,
                'fcm_notification' => $notifiable->fcm_notification,
                'timestamp' => now()->toISOString()
            ]);
            return null;
        }

        // Idempotency guard to prevent duplicate FCM sends
        $cacheKey = 'fcm_sent_booking_confirmation_' . $this->rental->id . '_' . $notifiable->id;
        try {
            // Try atomic lock first (Redis/memcached/file drivers support lock)
            $lock = Cache::lock($cacheKey . '_lock', 10);
            if (!$lock->get()) {
                Log::warning('Duplicate FCM prevented by lock for booking confirmation', [
                    'rental_id' => $this->rental->id,
                    'user_id' => $notifiable->id,
                ]);
                return null;
            }
        } catch (\Throwable $e) {
            // Fallback to Cache::add for drivers without locks
            if (!Cache::add($cacheKey, true, now()->addMinutes(10))) {
                Log::warning('Duplicate FCM prevented by cache-add for booking confirmation', [
                    'rental_id' => $this->rental->id,
                    'user_id' => $notifiable->id,
                ]);
                return null;
            }
        }

        $credentials = config('firebase.projects.app.credentials');
        $projectId = config('firebase.projects.app.project_id');

        if (!$credentials || !file_exists($credentials)) {
            Log::error('FCM configuration error: Firebase credentials file not found', [
                'user_id' => $notifiable->id,
                'credentials_path' => $credentials,
                'file_exists' => file_exists($credentials) ? 'yes' : 'no',
            ]);
            return null;
        }

        try {
            $factory = (new Factory)->withServiceAccount($credentials);
            $messaging = $factory->createMessaging();

            $notification = FcmNotification::create(
                'Rental Booking Confirmed! 🎉',
                'Your rental booking #' . $this->rental->id . ' has been confirmed successfully.'
            );

            $message = CloudMessage::withTarget('token', $notifiable->fcm_token)
                ->withNotification($notification)
                ->withData([
                    'notification' => json_encode([
                        'title' => $notification->title(),
                        'body' => $notification->body(),
                    ]),
                    'android' => json_encode(['priority' => 'high', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']),
                    'apns' => json_encode(['headers' => ['apns-priority' => '10']]),
                    'rental_id' => (string) $this->rental->id,
                    'user_id' => (string) $this->rental->user_id,
                    'tournament_name' => $this->rental->tournament->name ?? 'N/A',
                    'total_amount' => (string) $this->rental->total_amount,
                ]);

            $response = $messaging->send($message);

            Log::info('FCM notification sent successfully', [
                'rental_id' => $this->rental->id,
                'user_id' => $notifiable->id,
                'fcm_result' => $response,
                'timestamp' => now()->toISOString()
            ]);

            return $response;
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            Log::error('FCM notification failed', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            $msg = strtolower($e->getMessage() ?? '');
            if (
                str_contains($msg, 'invalid') ||
                str_contains($msg, 'not registered') ||
                str_contains($msg, 'requested entity was not found') ||
                str_contains($msg, 'requested entity not found')
            ) {
                $notifiable->update(['fcm_token' => null]);
                Log::info('Cleared invalid FCM token for user', ['user_id' => $notifiable->id]);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Unexpected FCM error', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}