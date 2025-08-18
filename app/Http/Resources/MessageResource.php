<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'conversation_id' => (int) $this->conversation_id,
            'sender_id' => (int) $this->sender_id,
            'content' => $this->content,
            'is_read' => (int) $this->is_read,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'sender' => $this->whenLoaded('sender', fn() => [
                'id' => (int) $this->sender->id,
                'name' => $this->sender->name,
                'email' => $this->sender->email,
            ]),
        ];
    }
}
