<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'order.created',
            'restaurant_id' => $this->order->restaurant_id,
            'order_id' => $this->order->id,
            'number' => $this->order->number,
            'mode' => $this->order->mode,
            'total_minor' => $this->order->total_minor,
            'currency' => $this->order->currency,
        ];
    }
}
