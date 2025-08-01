<?php

namespace App\Jobs;

use App\Enums\Role;
use App\Events\ConversationClaimed;
use App\Events\NewMessage;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class AssignConversation implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $conversationId;
    protected $responderId;
    protected $content;

    public function __construct($conversationId, $responderId, $content)
    {
        $this->conversationId = $conversationId;
        $this->responderId = $responderId;
        $this->content = $content;
    }

    public function handle()
    {
        // Use database lock to prevent race conditions
        DB::transaction(function () {
            $conversation = Conversation::where('id', $this->conversationId)
                ->lockForUpdate()
                ->firstOrFail();

            // Check if conversation is already assigned
            if ($conversation->responder_id) {
                if ($conversation->responder_id !== $this->responderId) {
                    event(new ConversationClaimed($conversation, $this->responderId));
                    return;
                }
                // If already assigned to this responder, just send the message
                $message = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $this->responderId,
                    'content' => $this->content,
                ]);
                event(new NewMessage($message));
                return;
            }

            // Assign conversation to the responder
            $conversation->update([
                'responder_id' => $this->responderId,
                'status' => 'assigned',
            ]);

            // Create the reply
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $this->responderId,
                'content' => $this->content,
            ]);

            // Broadcast the message
            event(new NewMessage($message));
        });
    }
}
