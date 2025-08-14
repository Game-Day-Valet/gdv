<?php

namespace App\Events;

use App\Models\Rental;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RentalStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rental;
    public $oldStatus;
    public $newStatus;
    public $updatedBy;

    public function __construct(Rental $rental, $oldStatus, $newStatus, $updatedBy)
    {
        $this->rental = $rental;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->updatedBy = $updatedBy;

        Log::info('RentalStatusUpdated event constructed', [
            'rental_id' => $rental->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'updated_by' => $updatedBy,
        ]);
    }

    public function broadcastOn()
    {
        $channelName = 'rental-' . $this->rental->id;
        $channel = new Channel($channelName);

        Log::info('Broadcasting RentalStatusUpdated on channel', [
            'channel' => $channel->name,
            'rental_id' => $this->rental->id,
            'new_status' => $this->newStatus,
        ]);

        return $channel;
    }

    public function broadcastAs()
    {
        return 'rental-status-updated';
    }

    public function broadcastWith()
    {
        $data = [
            'rental_id' => $this->rental->id,
            'status' => $this->newStatus,
            'old_status' => $this->oldStatus,
            'updated_at' => now()->toISOString(),
            'updated_by' => $this->updatedBy,
            'timestamp' => now()->toISOString(),
        ];

        Log::info('RentalStatusUpdated broadcastWith data', [
            'data' => $data,
            'rental_id' => $this->rental->id,
        ]);

        return $data;
    }
}
