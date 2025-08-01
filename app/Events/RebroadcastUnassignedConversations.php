<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Log;

class RebroadcastUnassignedConversations implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function broadcastOn()
    {
        return new Channel('support');
    }

    public function broadcastAs()
    {
        return 'unassigned-conversation';
    }

    public function broadcastWith()
    {
        $data = [
            'conversation' => $this->conversation->load(['user', 'messages']),
        ];
        
        Log::info('RebroadcastUnassignedConversations broadcast data', $data);
        
        return $data;
    }
} 