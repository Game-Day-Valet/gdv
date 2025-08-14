<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'user_id' => (int) $this->user_id,
            'responder_id' => $this->responder_id ? (int) $this->responder_id : null,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->whenLoaded('user', fn() => [
                'id' => (int) $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'responder' => $this->whenLoaded('responder', fn() => [
                'id' => (int) $this->responder->id,
                'name' => $this->responder->name,
                'email' => $this->responder->email,
            ]),
            'messages' => $this->whenLoaded('messages', fn() => MessageResource::collection($this->messages)),
        ];
    }
}
