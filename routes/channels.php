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

// Rental status update channel - public channel for mobile app consumption
Broadcast::channel('rental.{id}', function ($user, $id) {
    // Allow access to rental status updates for authenticated users
    // This channel will be used for real-time status updates in mobile app
    return true; // Public channel for now, can be restricted later if needed
});
