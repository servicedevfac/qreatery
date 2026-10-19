<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantClosureRequest;
use App\Http\Resources\RestaurantClosureResource;
use App\Models\Restaurant;
use App\Models\RestaurantClosure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class RestaurantClosureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('view', $restaurant);

        return RestaurantClosureResource::collection(
            $restaurant->closures()->orderBy('starts_at')->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRestaurantClosureRequest $request, Restaurant $restaurant): JsonResponse
    {
        $closure = $restaurant->closures()->create($request->validated());

        return (new RestaurantClosureResource($closure))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function destroy(Restaurant $restaurant, RestaurantClosure $closure): Response
    {
        abort_unless($closure->restaurant_id === $restaurant->id, 404);
        Gate::authorize('update', $restaurant);
        $closure->delete();

        return response()->noContent();
    }
}
