<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RentalStatusLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'rental_id' => (int) $this->rental_id,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'notes' => $this->notes,
            'image_paths' => $this->image_paths,
            'image_urls' => $this->image_urls,
            'first_image_url' => $this->first_image_url,
            // 'updated_by' => $this->when($this->updatedBy, [
            //     'id' => (int) $this->updatedBy->id,
            //     'name' => $this->updatedBy->name,
            //     'email' => $this->updatedBy->email,
            // ]),
            'updated_by' => $this->updatedBy->name ?? null,
            'estimated_delivery_time' => $this->rental->estimated_delivery_time ?? null,
            'formatted_estimated_delivery_time' => $this->rental->estimated_delivery_time ? \Carbon\Carbon::parse($this->rental->estimated_delivery_time)->format('d M Y H:i') : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'formatted_created_at' => $this->created_at ? $this->created_at->format('d M Y H:i') : null,
            'formatted_updated_at' => $this->updated_at ? $this->updated_at->format('d M Y H:i') : null,
        ];
    }
}
