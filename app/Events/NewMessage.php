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

    public function __construct(Message $message)
    {
        $this->message = $message;
        Log::debug('NewMessage event constructed', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'responder_id' => $message->conversation->responder_id,
        ]);
    }

    public function broadcastOn()
    {
        $conversation = $this->message->conversation;
        $channelName = $conversation->responder_id ? 'conversation.' . $this->message->conversation_id : 'support';
        $channel = new Channel($channelName);

        Log::info('Broadcasting NewMessage on channel', [
            'channel' => $channel->name,
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'responder_id' => $conversation->responder_id,
        ]);

        return $channel;
    }

    public function broadcastAs()
    {
        Log::debug('Broadcasting as new-message', [
            'message_id' => $this->message->id,
        ]);
        return 'new-message';
    }

    public function broadcastWith()
    {
        if (!$this->message) {
            Log::error('NewMessage broadcastWith: message is null', [
                'conversation_id' => $this->message->conversation_id ?? 'unknown',
            ]);
            return [];
        }

        $this->message->load(['sender', 'conversation.user', 'conversation.responder']);

        $data = [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'sender_id' => $this->message->sender_id,
                'content' => $this->message->content,
                'created_at' => $this->message->created_at->toISOString(),
                'sender' => $this->message->sender ? [
                    'id' => $this->message->sender->id,
                    'name' => $this->message->sender->name,
                ] : null,
                'conversation' => [
                    'user' => $this->message->conversation->user ? [
                        'id' => $this->message->conversation->user->id,
                        'name' => $this->message->conversation->user->name,
                    ] : null,
                    'responder' => $this->message->conversation->responder ? [
                        'id' => $this->message->conversation->responder->id,
                        'name' => $this->message->conversation->responder->name,
                    ] : null,
                ],
            ],
        ];

        Log::info('NewMessage broadcastWith data', [
            'data' => $data,
            'message_id' => $this->message->id,
        ]);

        return $data;
    }
}