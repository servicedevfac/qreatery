<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderDelayedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order, private readonly int $delayMinutes) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'order.delayed',
            'restaurant_id' => $this->order->restaurant_id,
            'order_id' => $this->order->id,
            'number' => $this->order->number,
            'status' => $this->order->preparation_status,
            'delay_minutes' => $this->delayMinutes,
        ];
    }
}
