<?php


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'tournament_id' => $this->tournament_id,
            'tournament_name' => $this->whenLoaded('tournament', fn() => $this->tournament->name),
            'team_name' => $this->team_name,
            'coach_name' => $this->coach_name,
            'field_number' => $this->field_number,
            'items' => $this->items,
            'bundles' => $this->bundles,
            'instructions' => $this->instructions,
            'drop_off_time' => $this->drop_off_time,
            'promo_code' => $this->promo_code,
            'insurance_option' => $this->insurance_option,
            'damage_waiver' => $this->damage_waiver,
            'rental_date' => $this->rental_date,
            'delivery_assigned_to' => $this->delivery_assigned_to,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'return_instruction' => $this->return_instruction,
            'status_logs' => $this->statusLogs,
            'photos' => $this->whenLoaded('photos', fn() => $this->photos->pluck('photo_path')),
            'reviews' => $this->whenLoaded('reviews', fn() => RentalReviewResource::collection($this->reviews)),
        ];
    }
}
