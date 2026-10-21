<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Subscription $subscription, private readonly int $daysRemaining) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'subscription.expiring',
            'restaurant_id' => $this->subscription->restaurant_id,
            'subscription_id' => $this->subscription->id,
            'status' => $this->subscription->status->value,
            'days_remaining' => $this->daysRemaining,
            'ends_at' => $this->subscription->ends_at,
            'grace_ends_at' => $this->subscription->grace_ends_at,
        ];
    }
}
