<?php

namespace App\Notifications;

use App\Models\SubscriptionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionPaymentFailedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly SubscriptionPayment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'subscription.payment_failed',
            'restaurant_id' => $this->payment->subscription->restaurant_id,
            'subscription_id' => $this->payment->subscription_id,
            'payment_id' => $this->payment->id,
            'amount_minor' => $this->payment->amount_minor,
            'currency' => $this->payment->currency,
            'failure_reason' => $this->payment->failure_reason,
        ];
    }
}
