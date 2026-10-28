<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\CreateSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends Controller
{
    public function allSubscriptions(Request $request): AnonymousResourceCollection
    {
        $subscriptions = Subscription::query()
            ->with(['plan', 'restaurant', 'payments.recorder', 'statusHistories.actor'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('plan_id'), fn ($query) => $query->where('plan_id', $request->integer('plan_id')))
            ->when($request->filled('restaurant_id'), fn ($query) => $query->where('restaurant_id', $request->integer('restaurant_id')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(function ($q) use ($search): void {
                    $q->whereHas('restaurant', function ($inner) use ($search): void {
                        $inner->where('name', 'like', $search)->orWhere('slug', 'like', $search);
                    })->orWhereHas('plan', function ($inner) use ($search): void {
                        $inner->where('name', 'like', $search);
                    })->orWhere('notes', 'like', $search);
                });
            })
            ->latest('id')
            ->paginate();

        return SubscriptionResource::collection($subscriptions);
    }

    public function createGlobal(
        StoreSubscriptionRequest $request,
        CreateSubscriptionAction $createSubscription,
    ): JsonResponse|RedirectResponse {
        $validated = $request->validated();
        $restaurantId = $validated['restaurant_id'] ?? $request->input('restaurant_id');
        $restaurant = Restaurant::query()->findOrFail($restaurantId);

        $subscription = $createSubscription->handle($restaurant, $request->user(), $validated);

        if (! $request->expectsJson()) {
            return redirect('/?view=admin&section=subscriptions')
                ->with('success', "L'abonnement pour {$restaurant->name} a été activé avec succès.");
        }

        return (new SubscriptionResource($subscription->load(['plan', 'restaurant'])))
            ->response()
            ->setStatusCode(201);
    }

    public function showGlobal(Subscription $subscription): SubscriptionResource
    {
        return new SubscriptionResource(
            $subscription->load(['plan', 'restaurant', 'payments.recorder', 'statusHistories.actor'])
        );
    }

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
