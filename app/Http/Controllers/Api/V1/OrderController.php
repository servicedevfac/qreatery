<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('viewOrders', $restaurant);

        $orders = $restaurant->orders()
            ->with(['restaurantTable', 'items.options'])
            ->when($request->filled('status'), fn ($query) => $query->where('preparation_status', $request->string('status')->toString()))
            ->when($request->filled('mode'), fn ($query) => $query->where('mode', $request->string('mode')->toString()))
            ->when($request->filled('table_id'), fn ($query) => $query->where('restaurant_table_id', $request->integer('table_id')))
            ->when($request->filled('number'), fn ($query) => $query->where('number', $request->string('number')->toString()))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest('id')
            ->paginate();

        return OrderResource::collection($orders);
    }

    public function show(Restaurant $restaurant, Order $order): OrderResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $order);
        Gate::authorize('viewOrders', $restaurant);

        return new OrderResource($order->load(['restaurantTable', 'items.options', 'statusHistories.user']));
    }

    private function ensureBelongsToRestaurant(Restaurant $restaurant, Order $order): void
    {
        abort_unless($order->restaurant_id === $restaurant->id, 404);
    }
}
