<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Log;

class ConversationClaimed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $conversation;
    public $responderId;

    public function __construct(Conversation $conversation, $responderId)
    {
        $this->conversation = $conversation;
        $this->responderId = $responderId;
    }

    public function broadcastOn()
    {
        return new Channel('support');
    }

    public function broadcastAs()
    {
        return 'conversation-claimed';
    }

    public function broadcastWith()
    {
        $data = [
            'conversation' => $this->conversation->load(['user', 'responder']),
            'responder_id' => $this->responderId,
        ];
        
        return $data;
    }
}