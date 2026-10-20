<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\RecordPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function index(Restaurant $restaurant, Order $order): AnonymousResourceCollection
    {
        $this->ensureBelongsToRestaurant($restaurant, $order);
        Gate::authorize('viewOrders', $restaurant);

        return PaymentResource::collection(
            $order->payments()->with('operator')->oldest('id')->paginate()
        );
    }

    public function store(StorePaymentRequest $request, Restaurant $restaurant, Order $order, RecordPaymentAction $recordPayment): JsonResponse
    {
        $this->ensureBelongsToRestaurant($restaurant, $order);
        $payment = $recordPayment->handle($restaurant, $order, $request->user(), $request->validated());

        return (new PaymentResource($payment))->response()->setStatusCode(201);
    }

    private function ensureBelongsToRestaurant(Restaurant $restaurant, Order $order): void
    {
        abort_unless($order->restaurant_id === $restaurant->id, 404);
    }
}
