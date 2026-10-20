<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\TransitionOrderStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Restaurant;

class OrderStatusController extends Controller
{
    public function __invoke(UpdateOrderStatusRequest $request, Restaurant $restaurant, Order $order, TransitionOrderStatusAction $transitionOrderStatus): OrderResource
    {
        abort_unless($order->restaurant_id === $restaurant->id, 404);
        $data = $request->validated();
        $order = $transitionOrderStatus->handle($order, $request->user(), $data['status'], $data['reason'] ?? null);

        return new OrderResource($order);
    }
}
