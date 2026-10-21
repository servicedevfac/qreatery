<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Subscription $subscription, private readonly string $fromStatus) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'subscription.status_changed',
            'restaurant_id' => $this->subscription->restaurant_id,
            'subscription_id' => $this->subscription->id,
            'from_status' => $this->fromStatus,
            'to_status' => $this->subscription->status->value,
            'ends_at' => $this->subscription->ends_at,
            'grace_ends_at' => $this->subscription->grace_ends_at,
        ];
    }
}
