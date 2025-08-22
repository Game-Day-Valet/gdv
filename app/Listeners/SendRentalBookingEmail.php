<?php

namespace App\Listeners;

use App\Events\RentalBookingCreated;
use App\Jobs\SendRentalBookingEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendRentalBookingEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RentalBookingCreated $event): void
    {
        $rentalId = $event->rental->id;
        $cacheKey = "rental_email_sent_{$rentalId}";
        
        // Check if email was already sent for this rental using cache
        if (Cache::has($cacheKey)) {
            Log::warning('Email already sent for rental, skipping duplicate', [
                'rental_id' => $rentalId,
                'user_id' => $event->rental->user_id,
                'timestamp' => now()->toISOString()
            ]);
            return;
        }


        try {
            // Mark that email is being sent for this rental
            Cache::put($cacheKey, true, now()->addHours(24));
            
            // Dispatch the job to send the email
            SendRentalBookingEmailJob::dispatch($event->rental);
            
        } catch (\Exception $e) {
            // Remove the cache key if job dispatch failed
            Cache::forget($cacheKey);
            
            Log::error('Failed to dispatch SendRentalBookingEmailJob', [
                'rental_id' => $rentalId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toISOString()
            ]);
            
            throw $e;
        }
    }
} 