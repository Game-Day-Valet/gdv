<?php


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
{
    public function toArray($request)
    {
        $totalItems = null;
        try {
            $parts = [];
            // Items: stored as array of {item_id, quantity}
            if (is_array($this->items)) {
                foreach ($this->items as $entry) {
                    if (is_array($entry) && isset($entry['item_id'], $entry['quantity'])) {
                        $quantity = (int) $entry['quantity'];
                        if ($quantity > 0) {
                            $itemModel = \App\Models\Item::find($entry['item_id']);
                            $name = $itemModel?->name ?? 'Item';
                            $parts[] = $quantity . ' ' . $name;
                        }
                    }
                }
            }
            // Bundles: stored as array of ids
            if (is_array($this->bundles)) {
                foreach ($this->bundles as $bundleId) {
                    if (is_numeric($bundleId)) {
                        $bundleModel = \App\Models\Bundle::find($bundleId);
                        if ($bundleModel) {
                            $parts[] = '1 ' . $bundleModel->name;
                        }
                    }
                }
            }
            if (!empty($parts)) {
                $totalItems = implode(', ', $parts);
            }
        } catch (\Throwable $e) {
            $totalItems = null;
        }
        return [
            'id' => $this->id,
            'user_id' => (int) $this->user_id,
            'tournament_id' => (int) $this->tournament_id,
            'tournament_name' => $this->whenLoaded('tournament', fn() => $this->tournament->name),
            'team_name_with_age_group' => $this->team_name_with_age_group,
            'coach_name' => $this->coach_name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'field_number' => $this->field_number,
            'items' => $this->items,
            'bundles' => $this->bundles,
            'total_items' => $totalItems,
            'instructions' => $this->instructions,
            'drop_off_time' => $this->drop_off_time,
            'promo_code' => $this->promo_code,
            'insurance_option' => $this->insurance_option,
            'damage_waiver' => $this->damage_waiver,
            // 'rental_date' => \Carbon\Carbon::parse($this->rental_date)->format('d M Y H:i'),
            'created_at' => \Carbon\Carbon::parse($this->created_at)->format('d M Y H:i'),
            'rental_date' => $this->rental_date,
            'delivery_assigned_to' => $this->delivery_assigned_to,
            'booking_days' => $this->booking_days ? (int) $this->booking_days : null,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'return_instruction' => $this->return_instruction,
            'status_logs' => $this->statusLogs,
            'photos' => $this->whenLoaded('photos', fn() => $this->photos->pluck('photo_path')),
            'reviews' => $this->whenLoaded('reviews', fn() => RentalReviewResource::collection($this->reviews)),
            'estimated_delivery_time' => $this->estimated_delivery_time
        ];
    }
}
