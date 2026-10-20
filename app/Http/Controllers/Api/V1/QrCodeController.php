<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\GenerateQrCodeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQrCodeRequest;
use App\Http\Resources\QrCodeResource;
use App\Models\QrCode;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class QrCodeController extends Controller
{
    public function index(Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('view', $restaurant);

        return QrCodeResource::collection(
            $restaurant->qrCodes()->with('restaurantTable')->latest()->paginate()
        );
    }

    public function store(StoreQrCodeRequest $request, Restaurant $restaurant, GenerateQrCodeAction $generateQrCode): JsonResponse
    {
        $data = $request->validated();
        $qrCode = $generateQrCode->handle($restaurant, $data['type'], $data['restaurant_table_id'] ?? null);

        return (new QrCodeResource($qrCode))->response()->setStatusCode(201);
    }

    public function regenerate(Restaurant $restaurant, QrCode $qrCode, GenerateQrCodeAction $generateQrCode): QrCodeResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $qrCode);
        Gate::authorize('manageQrCodes', $restaurant);
        $newQrCode = $generateQrCode->handle($restaurant, $qrCode->type, $qrCode->restaurant_table_id);

        return new QrCodeResource($newQrCode);
    }

    public function destroy(Restaurant $restaurant, QrCode $qrCode): Response
    {
        $this->ensureBelongsToRestaurant($restaurant, $qrCode);
        Gate::authorize('manageQrCodes', $restaurant);
        $qrCode->update(['status' => 'revoked', 'revoked_at' => now()]);

        return response()->noContent();
    }

    private function ensureBelongsToRestaurant(Restaurant $restaurant, QrCode $qrCode): void
    {
        abort_unless($qrCode->restaurant_id === $restaurant->id, 404);
    }
}
