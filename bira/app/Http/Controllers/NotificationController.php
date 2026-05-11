<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Return the current user's notifications as JSON (used by AJAX popup).
     * Limited to the 20 most recent notifications.
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'type'       => $n->type,
                    'title'      => $n->title,
                    'message'    => $n->message,
                    'link'       => $n->link,
                    'is_read'    => $n->is_read,
                    'created_at' => $n->created_at->toIso8601String(),
                    'time_ago'   => $n->created_at->diffForHumans(),
                ];
            });

        if (request()->wantsJson()) {
            return response()->json($notifications);
        }

        $query = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at');

        if (request()->query('unread')) {
            $query->where('is_read', false);
        }

        $allNotifications = $query->paginate(30)->withQueryString();

        return view('notifications.index', compact('allNotifications'));
    }

    /**
     * Mark a single notification as read and redirect to its link.
     */
    public function markAsRead(Notification $notification)
    {
        abort_unless((int) $notification->user_id === Auth::id(), 403);

        $notification->update(['is_read' => true]);

        if (request()->has('stay')) {
            return back();
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back();
    }

    /**
     * Mark all of the current user's notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
