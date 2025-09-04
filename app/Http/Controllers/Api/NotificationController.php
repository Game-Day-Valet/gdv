<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function list(Request $request)
    {
        $user = Auth::user();
    
        $perPage = (int) max(1, min(100, (int) $request->query('per_page', 5)));
    
        $page = (int) max(1, (int) $request->query('page', 1));
    
        $unreadOnly = (bool) $request->boolean('unread_only', false);
    
        $query = $unreadOnly ? $user->unreadNotifications() : $user->notifications();
    
        $notifications = $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    
        $items = $notifications->getCollection()->map(function ($n) {
            $payload = $n->data;
    
            if (is_array($payload) && array_key_exists('user_id', $payload)) {
                $payload['user_id'] = (int) $payload['user_id'];
            }
    
            if (is_array($payload) && array_key_exists('timestamp', $payload) && !empty($payload['timestamp'])) {
                try {
                    $payload['formatted_timestamp'] = \Carbon\Carbon::parse($payload['timestamp'])
                        ->format('d M Y H:i');
                } catch (\Throwable $e) {
                    $payload['formatted_timestamp'] = $payload['timestamp'];
                }
            }
    
            return [
                'id' => $n->id,
                'type' => $n->type,
                'data' => $payload,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
                'updated_at' => $n->updated_at,
            ];
        })->values();
    
        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page'   => $notifications->currentPage(),
                'per_page'       => $notifications->perPage(),
                'total'          => $notifications->total(),
                'last_page'      => $notifications->lastPage(),
                'next_page_url'  => $notifications->nextPageUrl(),
                'prev_page_url'  => $notifications->previousPageUrl(),
            ],
        ]);
    }
    
    

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
    
    public function setEmail(Request $request)
    {
        $request->validate(['enabled' => 'required|boolean']);

        $user = Auth::user();
        $user->email_notification = $request->boolean('enabled');
        $user->save();

        return response()->json([
            'message' => 'Email notifications updated',
            'enabled' => (bool) $user->email_notification,
        ]);
    }

    public function toggleEmail()
    {
        $user = Auth::user();

        $user->email_notification = !(bool) $user->email_notification;
        $user->save();

        $status = $user->email_notification ? 'enabled' : 'disabled';

        return response()->json([
            'message' => "Email notifications {$status}.",
            'enabled' => (bool) $user->email_notification,
        ]);
    }

    public function setText(Request $request)
    {
        $request->validate(['enabled' => 'required|boolean']);

        $user = Auth::user();
        $user->text_notification = $request->boolean('enabled');
        $user->save();

        return response()->json([
            'message' => 'SMS notifications updated',
            'enabled' => (bool) $user->text_notification,
        ]);
    }

    public function toggleText()
    {
        $user = Auth::user();

        $user->text_notification = !(bool) $user->text_notification;
        $user->save();

        $status = $user->text_notification ? 'enabled' : 'disabled';

        return response()->json([
            'message' => "SMS notifications {$status}.",
            'enabled' => (bool) $user->text_notification,
        ]);
    }
    
}


