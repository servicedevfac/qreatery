<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CloseRestaurantTableAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class RestaurantTableSessionController extends Controller
{
    public function activeOrders(Restaurant $restaurant, RestaurantTable $restaurantTable): AnonymousResourceCollection
    {
        abort_unless($restaurantTable->restaurant_id === $restaurant->id, 404);
        Gate::authorize('viewOrders', $restaurant);

        $activeOrders = $restaurantTable->orders()
            ->where(function ($query): void {
                $query->whereNotIn('preparation_status', ['completed', 'cancelled', 'rejected'])
                    ->orWhere(function ($query): void {
                        $query->where('payment_status', '!=', 'paid')
                            ->whereNotIn('preparation_status', ['cancelled', 'rejected']);
                    });
            })
            ->with(['items.options', 'payments', 'restaurantTable'])
            ->latest()
            ->get();

        return OrderResource::collection($activeOrders);
    }

    public function close(
        Request $request,
        Restaurant $restaurant,
        RestaurantTable $restaurantTable,
        CloseRestaurantTableAction $closeTable,
    ): JsonResponse {
        abort_unless($restaurantTable->restaurant_id === $restaurant->id, 404);
        Gate::authorize('manageOrders', $restaurant);

        $request->validate(['force' => ['sometimes', 'boolean']]);
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

        $closedCount = $closeTable->handle($restaurantTable, $request->user(), $force);

        return response()->json([
            'message' => 'Table '.$restaurantTable->label.' clôturée avec succès.',
            'table_id' => $restaurantTable->id,
            'table_label' => $restaurantTable->label,
            'orders_completed' => $closedCount,
            'closed_at' => now()->toIso8601String(),
        ]);
    }
}
