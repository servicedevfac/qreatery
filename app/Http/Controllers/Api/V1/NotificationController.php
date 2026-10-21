<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->boolean('unread_only')
            ? $request->user()->unreadNotifications()
            : $request->user()->notifications();

        return NotificationResource::collection($notifications->latest()->paginate());
    }

    public function markRead(Request $request, string $notification): NotificationResource
    {
        /** @var DatabaseNotification $databaseNotification */
        $databaseNotification = $request->user()->notifications()->findOrFail($notification);
        $databaseNotification->markAsRead();

        return new NotificationResource($databaseNotification);
    }

    public function markAllRead(Request $request): Response
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->noContent();
    }
}
