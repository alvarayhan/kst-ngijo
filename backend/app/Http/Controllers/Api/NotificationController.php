<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::where('user_id', auth('api')->id());
        
        if ($request->filled('unread_only')) {
            $query->whereNull('read_at');
        }
        
        $notifications = $query->latest()->paginate($request->per_page ?? 20);
        
        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ]
        ]);
    }
    
    public function show(string $id)
    {
        $notification = Notification::where('user_id', auth('api')->id())->find($id);
        
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }
        
        return response()->json(['success' => true, 'data' => $notification]);
    }
    
    public function markAsRead(string $id)
    {
        $notification = Notification::where('user_id', auth('api')->id())->find($id);
        
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        
        $notification->update(['read_at' => now()]);
        
        return response()->json(['success' => true, 'data' => $notification]);
    }
    
    public function markAllAsRead()
    {
        Notification::where('user_id', auth('api')->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
    }
    
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth('api')->id())
            ->whereNull('read_at')
            ->count();
        
        return response()->json(['success' => true, 'unread_count' => $count]);
    }
}
