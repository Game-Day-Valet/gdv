<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function setFcm(Request $request)
    {
        $request->validate(['enabled' => 'required|boolean']);

        $user = Auth::user();
        $user->fcm_notification = $request->boolean('enabled');
        $user->save();

        return response()->json([
            'message' => 'FCM notifications updated',
            'enabled' => (bool) $user->fcm_notification,
        ]);
    }

    public function toggleFcm()
    {
        $user = Auth::user();
    
        $user->fcm_notification = !(bool) $user->fcm_notification;
        $user->save();
    
        $status = $user->fcm_notification ? 'enabled' : 'disabled';
    
        return response()->json([
            'message' => "Notifications {$status}.",
            'enabled' => (bool) $user->fcm_notification,
        ]);
    }
    
}


