<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('restaurants.'.$this->order->restaurant_id)];
    }

    public function broadcastAs(): string
    {
        return 'orders.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'number' => $this->order->number,
            'mode' => $this->order->mode,
            'preparation_status' => $this->order->preparation_status,
            'total_minor' => $this->order->total_minor,
            'currency' => $this->order->currency,
            'created_at' => $this->order->created_at?->toIso8601String(),
        ];
    }
}
