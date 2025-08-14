<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => (int) $this->user_id,
            'tournament' => new TournamentResource($this->whenLoaded('tournament')),
            'created_at' => $this->created_at,
        ];
    }
}
