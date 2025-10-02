<?php


namespace App\Http\Resources;

use App\Models\Favorite;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TournamentResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'sport' => $this->whenLoaded('sport', fn() => [
                'id' => (int) $this->sport->id,
                'name' => $this->sport->name,
                'description' => $this->sport->description,
                'status' => $this->sport->status,
            ]),
            'name' => $this->name,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'location' => $this->location,
            'description' => $this->description,
            'status' => $this->status->value,
            'tax_rate' => $this->tax_rate,
            'is_favorite' => Auth::check() ? Favorite::where('user_id', Auth::id())->where('tournament_id', $this->id)->exists() : false,
        ];
    }
}
