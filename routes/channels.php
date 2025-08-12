<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

// Private channel authorization removed - now using public channels
// Broadcast::channel('conversation.{id}', function ($user, $id) {
//     $conversation = Conversation::findOrFail($id);

//     if ($user->hasRole(\App\Enums\Role::USER)) {
//         return $conversation->user_id === $user->id;
//     }

//     if ($user->hasRole([\App\Enums\Role::MANAGER, \App\Enums\Role::SUPER_ADMIN])) {
//         return !$conversation->responder_id || $conversation->responder_id === $user->id;
//     }

//     return false;
// });

// Broadcast::channel('support', function ($user) {
//     return $user->hasRole([\App\Enums\Role::MANAGER, \App\Enums\Role::SUPER_ADMIN]);
// });
