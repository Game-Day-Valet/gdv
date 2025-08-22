<?php

namespace App\Events;

use App\Models\Rental;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RentalBookingCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rental;

    /**
     * Create a new event instance.
     */
    public function __construct(Rental $rental)
    {
        $this->rental = $rental;
        
        // Get the backtrace to see where this event was created from
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $caller = '';
        foreach ($backtrace as $trace) {
            if (isset($trace['class']) && strpos($trace['class'], 'Controller') !== false) {
                $caller = $trace['class'] . '::' . $trace['function'];
                break;
            }
        }
        
        // Log event creation with caller information
        Log::info('RentalBookingCreated event created', [
            'rental_id' => $rental->id,
            'user_id' => $rental->user_id,
            'tournament_id' => $rental->tournament_id,
            'total_amount' => $rental->total_amount,
            'caller' => $caller,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
} 