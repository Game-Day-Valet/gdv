<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatManagementController extends Controller
{

    public function unassignedList()
    {
        $conversations = Conversation::with(['user', 'messages'])
            ->withCount('unreadMessages')
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'user' => $conversation->user ? [
                        'id' => $conversation->user->id,
                        'name' => $conversation->user->name,
                    ] : null,
                    'status' => $conversation->status,
                    'messages' => $conversation->messages->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'content' => $message->content,
                            'created_at' => $message->created_at->toDateTimeString(),
                        ];
                    })->toArray(),
                    'unread_count' => $conversation->unread_messages_count,
                    'updated_at' => $conversation->updated_at->toDateTimeString(),
                ];
            });

        return response()->json($conversations);
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole([Role::MANAGER, Role::SUPER_ADMIN])) {
            $conversations = Conversation::with(['user', 'responder', 'messages.sender'])
                ->where(function ($query) use ($user) {
                    $query->where('responder_id', $user->id)
                        ->orWhereNull('responder_id');
                })
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $conversations = Conversation::where('user_id', $user->id)
                ->with(['user', 'responder', 'messages.sender'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return view('chat_management.index', compact('conversations'));
    }

    public function show($id)
    {
        $conversation = Conversation::with(['user', 'responder', 'messages.sender'])
            ->findOrFail($id);

        $user = Auth::user();

        Log::debug('ChatManagementController show called', [
            'user_id' => $user->id,
            'conversation_id' => $id,
            'roles' => $user->getRoleNames()->toArray(),
        ]);

        if ($user->hasRole(Role::USER) && $conversation->user_id !== $user->id) {
            Log::error('Unauthorized access to conversation', [
                'user_id' => $user->id,
                'conversation_id' => $id,
            ]);
            abort(403);
        }

        if ($user->hasRole([Role::MANAGER, Role::SUPER_ADMIN])) {
            if ($conversation->responder_id && $conversation->responder_id !== $user->id) {
                Log::error('Unauthorized access to conversation by responder', [
                    'user_id' => $user->id,
                    'conversation_id' => $id,
                ]);
                abort(403);
            }
        }

        Log::info('Conversation details retrieved', [
            'conversation_id' => $id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'id' => $conversation->id,
            'user' => $conversation->user ? [
                'id' => $conversation->user->id,
                'name' => $conversation->user->name ?? 'Unknown User',
            ] : ['id' => null, 'name' => 'Unknown User'],
            'responder' => $conversation->responder ? [
                'id' => $conversation->responder->id,
                'name' => $conversation->responder->name ?? 'Unknown Responder',
            ] : null,
            'status' => $conversation->status,
            'responder_id' => $conversation->responder_id,
            'created_at' => $conversation->created_at->toISOString(),
            'updated_at' => $conversation->updated_at->toISOString(),
        ]);
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $conversation = Conversation::findOrFail($conversationId);
        $user = Auth::user();

        Log::debug('ChatManagementController sendMessage called', [
            'user_id' => $user->id,
            'conversation_id' => $conversationId,
            'content' => $request->content,
            'roles' => $user->getRoleNames()->toArray(),
        ]);

        if ($user->hasRole(Role::USER) && $conversation->user_id !== $user->id) {
            Log::error('Unauthorized access to send message', [
                'user_id' => $user->id,
                'conversation_id' => $conversationId,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->hasRole([Role::MANAGER, Role::SUPER_ADMIN])) {
            if ($conversation->responder_id && $conversation->responder_id !== $user->id) {
                Log::error('Unauthorized access to send message by responder', [
                    'user_id' => $user->id,
                    'conversation_id' => $conversationId,
                ]);
                return response()->json(['error' => 'Conversation already assigned to another admin'], 403);
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

        if ($user->hasRole([Role::MANAGER, Role::SUPER_ADMIN]) && !$conversation->responder_id) {
            $conversation->update([
                'responder_id' => $user->id,
                'status' => 'assigned',
            ]);
            Log::info('Conversation assigned to responder', [
                'conversation_id' => $conversation->id,
                'responder_id' => $user->id,
            ]);
        }

        try {
            event(new \App\Events\NewMessage($message));
            Log::info('NewMessage event dispatched', [
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch NewMessage event', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return response()->json([
            'message' => $message->load('sender'),
            'conversation' => $conversation->fresh(),
        ]);
    }

    public function getMessages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $user = Auth::user();

        Log::debug('ChatManagementController getMessages called', [
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

        return response()->json($messages);
    }

    public function markAsRead($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $user = Auth::user();

        Log::debug('ChatManagementController markAsRead called', [
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
        $user = Auth::user();

        Log::debug('ChatManagementController closeConversation called', [
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
        $user = Auth::user();

        Log::debug('ChatManagementController getUnassignedConversations called', [
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

        return response()->json($conversations);
    }
}
