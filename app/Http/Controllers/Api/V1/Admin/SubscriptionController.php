<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\CreateSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): AnonymousResourceCollection
    {
        $subscriptions = $restaurant->subscriptions()
            ->with(['plan', 'restaurant'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest('starts_at')
            ->paginate();

        return SubscriptionResource::collection($subscriptions);
    }

    public function store(StoreSubscriptionRequest $request, Restaurant $restaurant, CreateSubscriptionAction $createSubscription): JsonResponse
    {
        $subscription = $createSubscription->handle($restaurant, $request->user(), $request->validated());

        return (new SubscriptionResource($subscription))->response()->setStatusCode(201);
    }

    public function show(Restaurant $restaurant, Subscription $subscription): SubscriptionResource
    {
        abort_unless($subscription->restaurant_id === $restaurant->id, 404);

        return new SubscriptionResource(
            $subscription->load(['plan', 'restaurant', 'payments.recorder', 'statusHistories.actor'])
        );
    }
}
