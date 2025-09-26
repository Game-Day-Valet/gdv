<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    private $userId;

    public function __construct(Message $message, $userId = null)
    {
        $this->message = $message;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        $conversation = $this->message->conversation;

        // If userId is provided in constructor, use it for channel name
        if ($this->userId) {
            $channelName = 'conversation.' . $this->userId;
        } else {
            // Fall back to existing logic
            $channelName = $conversation->responder_id ? 'conversation.' . $this->message->conversation_id : 'support';
        }

        $channel = new Channel($channelName);

        return $channel;
    }

    public function broadcastAs()
    {
        return 'new-message';
    }

    public function broadcastWith()
    {
        if (!$this->message) {
            return [];
        }

        $this->message->load(['sender', 'conversation']);

        // For broadcast payload: sender should see is_read = 1, recipients see is_read = 0.
        // We can't personalize a single broadcast for different subscribers on a public channel,
        // so we default to is_read = 0 and let sender UI force it to 1 locally, OR broadcast two events if userId provided.
        $isReadBroadcast = 0;
        if ($this->userId && (int)$this->userId === (int)$this->message->sender_id) {
            // When targeting a specific user channel (like in ChatManagementController), show read=1 to the sender
            $isReadBroadcast = 1;
        }

        $data = [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => (int) $this->message->conversation_id,
                'sender_id' => (int) $this->message->sender_id,
                'content' => $this->message->content,
                'is_read' => $isReadBroadcast,
                'created_at' => $this->message->created_at->toISOString(),
                'updated_at' => $this->message->updated_at->toISOString(),
                'sender' => $this->message->sender ? [
                    'id' => (int) $this->message->sender->id,
                    'name' => $this->message->sender->name,
                    'email' => $this->message->sender->email,
                ] : null,
            ],
            'conversation' => [
                'id' => (int) $this->message->conversation->id,
                'user_id' => (int) $this->message->conversation->user_id,
                'responder_id' => (int) $this->message->conversation->responder_id,
                'status' => $this->message->conversation->status,
                'created_at' => $this->message->conversation->created_at->toISOString(),
                'updated_at' => $this->message->conversation->updated_at->toISOString(),
            ],
        ];


        return $data;
    }
}
