<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\GenerateQrCodeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantTableRequest;
use App\Http\Requests\UpdateRestaurantTableRequest;
use App\Http\Resources\RestaurantTableResource;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class RestaurantTableController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('view', $restaurant);

        return RestaurantTableResource::collection(
            $restaurant->restaurantTables()
                ->when(! $request->boolean('with_archived'), fn ($query) => $query->whereNull('archived_at'))
                ->orderBy('label')
                ->paginate()
        );
    }

    public function store(StoreRestaurantTableRequest $request, Restaurant $restaurant, GenerateQrCodeAction $generateQrCode): JsonResponse
    {
        $restaurantTable = $restaurant->restaurantTables()->create($request->validated());
        $generateQrCode->handle($restaurant, 'table', $restaurantTable->id);

        return (new RestaurantTableResource($restaurantTable->load('qrCodes')))->response()->setStatusCode(201);
    }

    public function show(Restaurant $restaurant, RestaurantTable $restaurantTable): RestaurantTableResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $restaurantTable);
        Gate::authorize('view', $restaurant);

        return new RestaurantTableResource($restaurantTable);
    }

    public function update(UpdateRestaurantTableRequest $request, Restaurant $restaurant, RestaurantTable $restaurantTable): RestaurantTableResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $restaurantTable);
        $restaurantTable->update($request->validated());

        return new RestaurantTableResource($restaurantTable->refresh());
    }

    public function destroy(Restaurant $restaurant, RestaurantTable $restaurantTable): Response
    {
        $this->ensureBelongsToRestaurant($restaurant, $restaurantTable);
        Gate::authorize('manageTables', $restaurant);
        $restaurantTable->update(['is_active' => false, 'archived_at' => now()]);
        $restaurantTable->qrCodes()->where('status', 'active')->update(['status' => 'revoked', 'revoked_at' => now()]);

        return response()->noContent();
    }

    private function ensureBelongsToRestaurant(Restaurant $restaurant, RestaurantTable $restaurantTable): void
    {
        abort_unless($restaurantTable->restaurant_id === $restaurant->id, 404);
    }
}
