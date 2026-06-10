<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Bell-and-dropdown endpoints for the in-app notification feed.
 *
 * The notifications themselves are written by Laravel's Notifiable trait
 * (see `notifications` table + `App\Notifications\*`); this controller is
 * pure CRUD on top of the user's notification stream.
 */
class NotificationApiController extends Controller
{
    /**
     * GET /api/notifications
     * Paginated list (default 20). Use `?filter=unread` to skip read rows.
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = $request->query('filter') === 'unread'
            ? $user->unreadNotifications()
            : $user->notifications();

        $page = $query->paginate(20);

        return response()->json([
            'data' => $page->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'data'       => $n->data,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at,
            ]),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    /**
     * GET /api/notifications/unread-count
     * Just the integer — used by the bell badge to avoid pulling the full list.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * PUT /api/notifications/{id}/read
     * Mark one notification as read. Idempotent — calling twice is fine.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();
        if (! $notification) {
            return response()->json(['message' => 'Notification introuvable.'], 404);
        }
        $notification->markAsRead();

        return response()->json(['message' => 'OK']);
    }

    /**
     * PUT /api/notifications/read-all
     * Mark every unread notification as read. Used by the "tout marquer comme
     * lu" button.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'OK']);
    }

    /**
     * DELETE /api/notifications/{id}
     * Permanently dismiss a notification (e.g., user clicks the X on a row).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $deleted = $request->user()->notifications()->where('id', $id)->delete();

        return response()->json(['deleted' => $deleted]);
    }
}
