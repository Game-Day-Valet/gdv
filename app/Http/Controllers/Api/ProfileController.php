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

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user),
        ]);
    }
}
