<?php

namespace App\Actions;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionOrderStatusAction
{
    public function __construct(private readonly RecordAuditLogAction $recordAuditLog) {}

    /** @var array<string, array<int, string>> */
    private const TRANSITIONS = [
        'pending' => ['accepted', 'rejected', 'cancelled'],
        'accepted' => ['preparing', 'cancelled'],
        'preparing' => ['ready', 'cancelled'],
        'ready' => ['served', 'completed'],
        'served' => ['completed'],
        'completed' => [],
        'rejected' => [],
        'cancelled' => [],
    ];

    public function handle(Order $order, User $user, string $status, ?string $reason): Order
    {
        $fromStatus = $order->preparation_status;
        $updatedOrder = DB::transaction(function () use ($order, $user, $status, $reason): Order {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $lockedOrder->preparation_status;

            if (! in_array($status, self::TRANSITIONS[$fromStatus] ?? [], true)) {
                throw ValidationException::withMessages(['status' => "La transition de {$fromStatus} vers {$status} n'est pas autorisée."]);
            }

            $timestamps = match ($status) {
                'accepted' => ['accepted_at' => now()],
                'ready' => ['ready_at' => now()],
                'completed' => ['completed_at' => now()],
                'rejected', 'cancelled' => ['cancelled_at' => now()],
                default => [],
            };

            $lockedOrder->update(['preparation_status' => $status, ...$timestamps]);
            $lockedOrder->statusHistories()->create([
                'user_id' => $user->id,
                'from_status' => $fromStatus,
                'to_status' => $status,
                'reason' => $reason,
            ]);

            return $lockedOrder->load(['restaurantTable', 'items.options', 'statusHistories.user']);
        });

        $this->recordAuditLog->handle($user, 'order.status_changed', $updatedOrder, $updatedOrder->restaurant, [
            'from_status' => $fromStatus,
            'to_status' => $status,
            'reason' => $reason,
        ]);
        OrderStatusUpdated::dispatch($updatedOrder, $fromStatus);

        return $updatedOrder;
    }
}
