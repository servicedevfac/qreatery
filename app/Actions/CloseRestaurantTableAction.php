<?php

namespace App\Actions;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CloseRestaurantTableAction
{
    public function __construct(private readonly RecordAuditLogAction $recordAuditLog) {}

    public function handle(RestaurantTable $restaurantTable, User $actor, bool $forced): int
    {
        /** @var Collection<int, array{order: Order, from_status: string}> $transitions */
        $transitions = DB::transaction(function () use ($restaurantTable, $actor, $forced): Collection {
            $orders = $restaurantTable->orders()
                ->whereIn('preparation_status', ['pending', 'accepted', 'preparing', 'ready', 'served'])
                ->lockForUpdate()
                ->get();

            $transitions = $orders->map(function (Order $order) use ($actor, $forced): array {
                $fromStatus = $order->preparation_status;
                $reason = $forced
                    ? 'Clôture forcée de la table.'
                    : 'Clôture de la table après règlement.';

                $order->update([
                    'preparation_status' => 'completed',
                    'completed_at' => now(),
                ]);
                $order->statusHistories()->create([
                    'user_id' => $actor->id,
                    'from_status' => $fromStatus,
                    'to_status' => 'completed',
                    'reason' => $reason,
                ]);

                $this->recordAuditLog->handle(
                    $actor,
                    'order.status_changed',
                    $order,
                    $order->restaurant,
                    [
                        'from_status' => $fromStatus,
                        'to_status' => 'completed',
                        'reason' => 'table.closed',
                        'forced' => $forced,
                    ],
                );

                return [
                    'order' => $order->refresh()->load(['restaurantTable', 'items.options', 'statusHistories.user']),
                    'from_status' => $fromStatus,
                ];
            });

            $restaurantTable->assistanceRequests()
                ->whereIn('status', ['pending', 'in_progress'])
                ->update([
                    'status' => 'resolved',
                    'handled_by' => $actor->id,
                    'handled_at' => now(),
                    'resolved_at' => now(),
                ]);

            $this->recordAuditLog->handle(
                $actor,
                'restaurant_table.closed',
                $restaurantTable,
                $restaurantTable->restaurant,
                [
                    'orders_completed' => $transitions->count(),
                    'forced' => $forced,
                ],
            );

            return $transitions;
        });

        foreach ($transitions as $transition) {
            OrderStatusUpdated::dispatch($transition['order'], $transition['from_status']);
        }

        return $transitions->count();
    }
}
