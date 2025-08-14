<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Events\NewMessage;
use App\Jobs\AssignConversation;
use App\Models\Conversation;
use App\Models\Message;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'conversation_id' => 'nullable|exists:conversations,id',
        ]);

        Log::debug('ChatController sendMessage started', [
            'user_id' => auth()->id(),
            'user_roles' => auth()->user()->getRoleNames()->toArray(),
            'content' => $request->content,
            'conversation_id' => $request->conversation_id,
        ]);

        return DB::transaction(function () use ($request) {
            $user = auth()->user();

            Log::info('ChatController sendMessage processing', [
                'user_id' => $user->id,
                'user_roles' => $user->getRoleNames()->toArray(),
                'content' => $request->content,
                'conversation_id' => $request->conversation_id,
            ]);

            if (!$request->conversation_id) {
                $conversation = Conversation::create([
                    'user_id' => $user->id,
                    'status' => 'open',
                ]);
                Log::info('New conversation created', [
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                ]);
            } else {
                $conversation = Conversation::findOrFail($request->conversation_id);
                if ($conversation->user_id !== $user->id && !$user->hasRole(Role::USER)) {
                    Log::error('Unauthorized access to conversation', [
                        'user_id' => $user->id,
                        'conversation_id' => $request->conversation_id,
                    ]);
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'content' => $request->content,
            ]);

            Log::info('Message created', [
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'content' => $message->content,
            ]);

            try {
                event(new NewMessage($message));
                Log::info('NewMessage event dispatched', [
                    'message_id' => $message->id,
                    'conversation_id' => $conversation->id,
                    'channel' => $conversation->responder_id ? 'conversation.' . $conversation->id : 'support',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch NewMessage event', [
                    'message_id' => $message->id,
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString(),
                ]);
                throw $e;
            }

            return response()->json([
                'message' => new MessageResource($message->load('sender')),
                'conversation' => new ConversationResource($conversation->fresh()),
            ]);
        });
    }

    public function replyToMessage(Request $request, $conversationId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        if (!$user->hasRole([Role::MANAGER, Role::SUPER_ADMIN])) {
            Log::error('Unauthorized reply attempt', [
                'user_id' => $user->id,
                'conversation_id' => $conversationId,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Log::debug('ReplyToMessage started', [
            'user_id' => $user->id,
            'conversation_id' => $conversationId,
            'content' => $request->content,
        ]);

        AssignConversation::dispatch($conversationId, $user->id, $request->content)
            ->onQueue('chat');

        Log::info('ReplyToMessage queued', [
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
        ]);

        return response()->json(['message' => 'Reply queued for processing']);
    }

    public function getConversations()
    {
        $user = auth()->user();

        Log::debug('getConversations called', [
            'user_id' => $user->id,
            'roles' => $user->getRoleNames()->toArray(),
        ]);

        if ($user->hasRole(Role::USER)) {
            $conversations = Conversation::where('user_id', $user->id)
                ->with(['messages.sender', 'responder'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $conversations = Conversation::where('responder_id', $user->id)
                ->orWhereNull('responder_id')
                ->with(['messages.sender', 'user'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        Log::info('Conversations retrieved', [
            'user_id' => $user->id,
            'conversation_count' => $conversations->count(),
        ]);

        return response()->json(ConversationResource::collection($conversations));
    }

    public function getMessages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $user = auth()->user();

        Log::debug('getMessages called', [
            'user_id' => $user->id,
            'conversation_id' => $conversationId,
            'roles' => $user->getRoleNames()->toArray(),
        ]);

        if ($user->hasRole(Role::USER) && $conversation->user_id !== $user->id) {
            Log::error('Unauthorized access to messages', [
                'user_id' => $user->id,
                'conversation_id' => $conversationId,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->hasRole([Role::MANAGER, Role::SUPER_ADMIN])) {
            if ($conversation->responder_id && $conversation->responder_id !== $user->id) {
                Log::error('Unauthorized access to messages by responder', [
                    'user_id' => $user->id,
                    'conversation_id' => $conversationId,
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $messages = $conversation->messages()->with('sender')->orderBy('created_at', 'asc')->get();

        Log::info('Messages retrieved', [
            'conversation_id' => $conversationId,
            'message_count' => $messages->count(),
        ]);

        return response()->json(MessageResource::collection($messages));
    }

    public function markAsRead($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $user = auth()->user();

        Log::debug('markAsRead called', [
            'user_id' => $user->id,
            'conversation_id' => $conversationId,
        ]);

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->update(['is_read' => true]);

        Log::info('Messages marked as read', [
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function closeConversation($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $user = auth()->user();

        Log::debug('closeConversation called', [
            'user_id' => $user->id,
            'conversation_id' => $conversationId,
        ]);

        if (!$user->hasRole([Role::MANAGER, Role::SUPER_ADMIN])) {
            Log::error('Unauthorized attempt to close conversation', [
                'user_id' => $user->id,
                'conversation_id' => $conversationId,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($conversation->responder_id !== $user->id) {
            Log::error('Unauthorized attempt to close conversation by non-responder', [
                'user_id' => $user->id,
                'conversation_id' => $conversationId,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->update(['status' => 'closed']);

        Log::info('Conversation closed', [
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function getUnassignedConversations()
    {
        $user = auth()->user();

        Log::debug('getUnassignedConversations called', [
            'user_id' => $user->id,
            'roles' => $user->getRoleNames()->toArray(),
        ]);

        if (!$user->hasRole([Role::MANAGER, Role::SUPER_ADMIN])) {
            Log::error('Unauthorized access to unassigned conversations', [
                'user_id' => $user->id,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversations = Conversation::with(['user', 'messages.sender'])
            ->whereNull('responder_id')
            ->where('status', 'open')
            ->orderBy('updated_at', 'desc')
            ->get();

        Log::info('Unassigned conversations retrieved', [
            'user_id' => $user->id,
            'conversation_count' => $conversations->count(),
        ]);

        return response()->json(ConversationResource::collection($conversations));
    }

    public function getConversationDetails($conversationId)
    {
        $conversation = Conversation::with(['user', 'responder', 'messages.sender'])
            ->findOrFail($conversationId);

        $user = auth()->user();

        Log::debug('getConversationDetails called', [
            'user_id' => $user->id,
            'conversation_id' => $conversationId,
            'roles' => $user->getRoleNames()->toArray(),
        ]);

        if ($user->hasRole(Role::USER) && $conversation->user_id !== $user->id) {
            Log::error('Unauthorized access to conversation details', [
                'user_id' => $user->id,
                'conversation_id' => $conversationId,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->hasRole([Role::MANAGER, Role::SUPER_ADMIN])) {
            if ($conversation->responder_id && $conversation->responder_id !== $user->id) {
                Log::error('Unauthorized access to conversation details by responder', [
                    'user_id' => $user->id,
                    'conversation_id' => $conversationId,
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        Log::info('Conversation details retrieved', [
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
        ]);

        return response()->json(new ConversationResource($conversation));
    }
}
