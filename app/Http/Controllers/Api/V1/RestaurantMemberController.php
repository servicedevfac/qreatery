<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateRestaurantMemberAction;
use App\Actions\RevokeUserAccessAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantMemberRequest;
use App\Http\Requests\UpdateRestaurantMemberRequest;
use App\Http\Resources\RestaurantMembershipResource;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;

class RestaurantMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', [RestaurantMembership::class, $restaurant]);

        return RestaurantMembershipResource::collection(
            $restaurant->memberships()->with('user')->latest()->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreRestaurantMemberRequest $request,
        Restaurant $restaurant,
        CreateRestaurantMemberAction $createMember,
    ): JsonResponse {
        $membership = $createMember->handle($restaurant, $request->validated());
        Password::sendResetLink(['email' => $membership->user->email]);

        return (new RestaurantMembershipResource($membership))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Restaurant $restaurant, RestaurantMembership $membership): RestaurantMembershipResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $membership);
        Gate::authorize('view', $membership);

        return new RestaurantMembershipResource($membership->load('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateRestaurantMemberRequest $request,
        Restaurant $restaurant,
        RestaurantMembership $membership,
    ): RestaurantMembershipResource {
        $this->ensureBelongsToRestaurant($restaurant, $membership);
        $membership->update($request->validated());

        return new RestaurantMembershipResource($membership->refresh()->load('user'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Restaurant $restaurant,
        RestaurantMembership $membership,
        RevokeUserAccessAction $revokeUserAccess,
    ): Response {
        $this->ensureBelongsToRestaurant($restaurant, $membership);
        Gate::authorize('delete', $membership);

        $membership->update(['is_active' => false]);
        $user = $membership->user;

        if (! $user->restaurantMemberships()->where('is_active', true)->exists()) {
            $user->forceFill(['is_active' => false])->save();
            $revokeUserAccess->handle($user);
        }

        return response()->noContent();
    }

    public function reactivate(Restaurant $restaurant, RestaurantMembership $membership): RestaurantMembershipResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $membership);
        Gate::authorize('restore', $membership);

        $membership->update(['is_active' => true]);
        $membership->user->forceFill(['is_active' => true])->save();

        return new RestaurantMembershipResource($membership->refresh()->load('user'));
    }

    private function ensureBelongsToRestaurant(Restaurant $restaurant, RestaurantMembership $membership): void
    {
        abort_unless($membership->restaurant_id === $restaurant->id, 404);
    }
}
