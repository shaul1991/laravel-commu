<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\NotFoundException;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\NotificationModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $query = NotificationModel::byUser($user->id)
            ->orderBy('created_at', 'desc');

        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'data' => $notifications->map(fn ($notification) => $this->formatNotification($notification)),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }

    public function markAsRead(Request $request, int $notificationId): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $notification = NotificationModel::find($notificationId);

        if (! $notification) {
            throw new NotFoundException('Notification not found');
        }

        if ($notification->user_id !== $user->id) {
            throw new ForbiddenException('You are not authorized to access this notification');
        }

        $notification->update(['is_read' => true]);
        $notification->refresh();

        return response()->json([
            'data' => $this->formatNotification($notification),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        NotificationModel::byUser($user->id)
            ->unread()
            ->update(['is_read' => true]);

        return response()->json([
            'message' => '모든 알림을 읽음 처리했습니다.',
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        $count = NotificationModel::byUser($user->id)
            ->unread()
            ->count();

        return response()->json([
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    private function formatNotification(NotificationModel $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'message' => $notification->message,
            'data' => $notification->data,
            'is_read' => $notification->is_read,
            'created_at' => $notification->created_at->toIso8601String(),
        ];
    }
}
