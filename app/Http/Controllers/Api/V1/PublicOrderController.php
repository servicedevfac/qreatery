<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;

class PublicOrderController extends Controller
{
    public function store(StorePublicOrderRequest $request, Restaurant $restaurant, CreateOrderAction $createOrder): JsonResponse
    {
        $order = $createOrder->handle($restaurant, $request->validated());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function show(string $token): OrderResource
    {
        $order = Order::query()
            ->with(['restaurantTable', 'items.options', 'statusHistories.user'])
            ->where('tracking_token', $token)
            ->firstOrFail();

        return new OrderResource($order);
    }
}
