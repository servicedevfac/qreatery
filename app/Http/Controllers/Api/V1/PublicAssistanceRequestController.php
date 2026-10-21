<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\RecordAuditLogAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicAssistanceRequest;
use App\Http\Resources\AssistanceRequestResource;
use App\Models\Order;
use App\Models\QrCode;
use App\Models\Restaurant;
use App\Notifications\AssistanceRequestedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class PublicAssistanceRequestController extends Controller
{
    public function __invoke(
        StorePublicAssistanceRequest $request,
        Restaurant $restaurant,
        RecordAuditLogAction $recordAuditLog,
    ): JsonResponse {
        abort_unless($restaurant->status->value === 'active', 404);
        $data = $request->validated();
        $order = null;
        $restaurantTableId = null;

        if (! empty($data['tracking_token'])) {
            $order = $restaurant->orders()->where('tracking_token', $data['tracking_token'])->first();
            if (! $order instanceof Order) {
                throw ValidationException::withMessages(['tracking_token' => 'Ce lien de suivi est invalide.']);
            }
            $restaurantTableId = $order->restaurant_table_id;
        }

        if (! empty($data['qr_token'])) {
            $qrCode = $restaurant->qrCodes()->where('token', $data['qr_token'])->where('status', 'active')->first();
            if (! $qrCode instanceof QrCode) {
                throw ValidationException::withMessages(['qr_token' => 'Ce QR Code est invalide ou révoqué.']);
            }
            $restaurantTableId = $qrCode->restaurant_table_id;
        }

        $assistanceRequest = $restaurant->assistanceRequests()->create([
            'restaurant_table_id' => $restaurantTableId,
            'order_id' => $order?->id,
            'type' => $data['type'],
            'message' => $data['message'] ?? null,
        ])->load(['restaurantTable', 'order']);

        $recordAuditLog->handle(null, 'assistance.requested', $assistanceRequest, $restaurant, [
            'type' => $assistanceRequest->type,
            'restaurant_table_id' => $assistanceRequest->restaurant_table_id,
            'order_id' => $assistanceRequest->order_id,
        ]);
        Notification::send(
            $restaurant->users()->wherePivot('is_active', true)->get(),
            new AssistanceRequestedNotification($assistanceRequest),
        );

        return (new AssistanceRequestResource($assistanceRequest))->response()->setStatusCode(201);
    }
}
