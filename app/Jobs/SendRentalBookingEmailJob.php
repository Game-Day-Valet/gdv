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

            $emailData = [
                'rental' => $rental,
                'user' => $rental->user,
                'tournament' => $rental->tournament,
                'sport' => $rental->tournament->sport ?? null,
                'email_content' => $dynamicContent,
            ];

            Mail::send('emails.rental-booking', $emailData, function ($message) use ($rental) {
                $message->to($rental->user->email, $rental->user->name)
                        ->subject('Booking Confirmation - Rental #' . $rental->id);
            });

            // Send SMS via Twilio if phone number is present and service is enabled
            $to = $rental->phone_number;
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');
            $enabled = (bool) config('services.twilio.enabled', true);
            if ($enabled && !empty($to) && $sid && $token && $from) {
                try {
                    $twilio = new TwilioClient($sid, $token);
                    $body = (string) (\App\Models\BookingOption::where('type', 'sms_booking_confirmation')->value('description') ?? '');
                    if ($body === '') {
                        // If admin left blank, skip silently
                        Log::info('SMS template empty for booking confirmation; skipping Twilio send.', ['rental_id' => $rental->id]);
                    } else {
                        $twilio->messages->create($to, ['from' => $from, 'body' => $body]);
                    }
                    $twilio->messages->create($to, ['from' => $from, 'body' => $body]);
                    Log::info('Twilio SMS sent for rental booking', [
                        'rental_id' => $rental->id,
                        'to' => $to,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Twilio SMS failed', [
                        'rental_id' => $rental->id,
                        'to' => $to,
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