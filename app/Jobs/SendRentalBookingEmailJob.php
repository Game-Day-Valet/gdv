<?php

namespace App\Jobs;

use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
            $emailData = [
                'rental' => $rental,
                'user' => $rental->user,
                'tournament' => $rental->tournament,
                'sport' => $rental->tournament->sport ?? null,
            ];

            Mail::send('emails.rental-booking', $emailData, function ($message) use ($rental) {
                $message->to($rental->user->email, $rental->user->name)
                        ->subject('Booking Confirmation - Rental #' . $rental->id);
            });

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