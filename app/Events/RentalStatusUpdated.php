<?php

namespace App\Events;

use App\Models\Rental;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\User;

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
        // Prepare optional fields safely
        $status = $this->newStatus;
        $statusLabel = $status ? ucfirst(str_replace('_', ' ', $status)) : null;

        $statusLogId = null;
        $statusNotes = null;
        $imagePaths = null;
        $imageUrls = null;
        $firstImageUrl = null;
        $estimatedDeliveryTime = $this->rental->estimated_delivery_time ?? null;
        $formattedEstimatedDeliveryTime = $estimatedDeliveryTime ? \Carbon\Carbon::parse($estimatedDeliveryTime)->format('d M Y H:i') : null;

        // If latest status log is available, enrich payload
        $latestUpdatedByName = null;
        try {
            $latestLog = $this->rental->statusLogs()->latest()->first();
            if ($latestLog) {
                $statusLogId = $latestLog->id;
                $statusNotes = $latestLog->notes;
                $imagePaths = $latestLog->image_paths ?: null;
                if (is_array($imagePaths)) {
                    $imageUrls = array_map(function ($p) { return $p ? asset('storage/' . ltrim($p, '/')) : null; }, $imagePaths);
                    $firstImageUrl = $imageUrls ? ($imageUrls[0] ?? null) : null;
                }
                $latestUpdatedByName = optional($latestLog->updatedBy)->name;
            }
        } catch (\Throwable $e) {
            // ignore enrichment errors; send nulls
        }

        // Resolve updated_by name from provided value or fallback to latest log
        $updatedByName = null;
        try {
            if (is_object($this->updatedBy)) {
                $updatedByName = $this->updatedBy->name ?? null;
            } elseif (is_array($this->updatedBy)) {
                $updatedByName = $this->updatedBy['name'] ?? null;
            } elseif (is_numeric($this->updatedBy)) {
                $user = User::find($this->updatedBy);
                $updatedByName = $user ? $user->name : null;
            } elseif (is_string($this->updatedBy)) {
                // If a plain string is passed, assume it's already a name
                $updatedByName = $this->updatedBy;
            }
        } catch (\Throwable $e) {
            $updatedByName = null;
        }
        if (!$updatedByName) {
            $updatedByName = $latestUpdatedByName;
        }

        $data = [
            'id' => (int) ($statusLogId ?? 0),
            'rental_id' => (int) ($this->rental->id ?? 0),
            'status' => $status,
            'status_label' => $statusLabel,
            'notes' => $statusNotes,
            'image_paths' => $imagePaths,
            'image_urls' => $imageUrls,
            'first_image_url' => $firstImageUrl,
            'updated_by' => $updatedByName,
            'estimated_delivery_time' => $estimatedDeliveryTime,
            'formatted_estimated_delivery_time' => $formattedEstimatedDeliveryTime,
            'created_at' => $this->rental->created_at ?? null,
            'updated_at' => $this->rental->updated_at ?? null,
            'formatted_created_at' => ($this->rental->created_at ?? null) ? $this->rental->created_at->format('d M Y H:i') : null,
            'formatted_updated_at' => ($this->rental->updated_at ?? null) ? $this->rental->updated_at->format('d M Y H:i') : null,
        ];

        Log::info('RentalStatusUpdated broadcastWith data', [
            'data' => $data,
            'rental_id' => $this->rental->id,
        ]);

        return $data;
    }
}
