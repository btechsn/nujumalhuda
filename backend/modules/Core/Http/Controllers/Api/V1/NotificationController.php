<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Resources\NotificationResource;
use Modules\Core\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->when($request->unread !== null, function ($q) use ($request) {
                return $request->boolean('unread') ? $q->unread() : $q->read();
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        return ApiResponse::success(NotificationResource::collection($notifications));
    }

    public function markAsRead(string $id, Request $request): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->markAsRead();

        return ApiResponse::success(
            NotificationResource::make($notification),
            'Notification marquée comme lue'
        );
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return ApiResponse::success(null, 'Toutes les notifications ont été marquées comme lues');
    }
}
