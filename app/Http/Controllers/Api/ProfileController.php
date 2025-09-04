<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fcm_notification' => 'nullable|boolean',
            'email_notification' => 'nullable|boolean',
            'text_notification' => 'nullable|boolean',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('contact_number')) {
            $user->contact_number = $request->contact_number;
        }

        if ($request->has('address')) {
            $user->address = $request->address;
        }

        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('users', 'public');
            $user->profile_image = $imagePath;
        }

        if ($request->has('fcm_notification')) {
            $user->fcm_notification = (bool) $request->boolean('fcm_notification');
        }
        if ($request->has('email_notification')) {
            $user->email_notification = (bool) $request->boolean('email_notification');
        }
        if ($request->has('text_notification')) {
            $user->text_notification = (bool) $request->boolean('text_notification');
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user),
        ]);
    }
}
