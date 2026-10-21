<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RestaurantTableSessionController extends Controller
{
    public function activeOrders(Restaurant $restaurant, RestaurantTable $restaurantTable): AnonymousResourceCollection
    {
        abort_unless($restaurantTable->restaurant_id === $restaurant->id, 404);
        Gate::authorize('viewOrders', $restaurant);

        $activeOrders = $restaurantTable->orders()
            ->whereNotIn('preparation_status', ['completed', 'cancelled', 'rejected'])
            ->orWhere(function ($query) use ($restaurantTable): void {
                $query->where('restaurant_table_id', $restaurantTable->id)
                    ->where('payment_status', '!=', 'paid')
                    ->whereNotIn('preparation_status', ['cancelled', 'rejected']);
            })
            ->with(['items.options', 'payments', 'restaurantTable'])
            ->latest()
            ->get();

        return OrderResource::collection($activeOrders);
    }

    public function close(Request $request, Restaurant $restaurant, RestaurantTable $restaurantTable): JsonResponse
    {
        abort_unless($restaurantTable->restaurant_id === $restaurant->id, 404);
        Gate::authorize('manageOrders', $restaurant);

        $force = $request->boolean('force');

        $unpaidOrders = $restaurantTable->orders()
            ->where('payment_status', '!=', 'paid')
            ->whereNotIn('preparation_status', ['cancelled', 'rejected'])
            ->get();

        if ($unpaidOrders->isNotEmpty() && ! $force) {
            return response()->json([
                'message' => 'Impossible de clôturer la table : des commandes ne sont pas encore réglées.',
                'unpaid_orders_count' => $unpaidOrders->count(),
                'unpaid_orders' => $unpaidOrders->pluck('number'),
            ], 422);
        }

        $closedCount = DB::transaction(function () use ($restaurantTable): int {
            // Mark open active orders as completed
            $affected = Order::where('restaurant_table_id', $restaurantTable->id)
                ->whereIn('preparation_status', ['pending', 'accepted', 'preparing', 'ready', 'served'])
                ->update([
                    'preparation_status' => 'completed',
                    'completed_at' => now(),
                ]);

            // Resolve any open assistance requests for this table
            $restaurantTable->assistanceRequests()
                ->whereIn('status', ['pending', 'in_progress'])
                ->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                ]);

            return $affected;
        });

        return response()->json([
            'message' => 'Table '.$restaurantTable->label.' clôturée avec succès.',
            'table_id' => $restaurantTable->id,
            'table_label' => $restaurantTable->label,
            'orders_completed' => $closedCount,
            'closed_at' => now()->toIso8601String(),
        ]);
    }
}
