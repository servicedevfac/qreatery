<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Order $order, public readonly string $fromStatus) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('restaurants.'.$this->order->restaurant_id),
            new Channel('orders.'.$this->order->tracking_token),
        ];
    }

    public function broadcastAs(): string
    {
        return 'orders.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'number' => $this->order->number,
            'from_status' => $this->fromStatus,
            'to_status' => $this->order->preparation_status,
            'accepted_at' => $this->order->accepted_at?->toIso8601String(),
            'ready_at' => $this->order->ready_at?->toIso8601String(),
            'completed_at' => $this->order->completed_at?->toIso8601String(),
            'cancelled_at' => $this->order->cancelled_at?->toIso8601String(),
            'updated_at' => $this->order->updated_at?->toIso8601String(),
        ];
    }
}
