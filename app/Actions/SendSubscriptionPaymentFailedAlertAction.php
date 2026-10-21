<?php

namespace App\Actions;

use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Notifications\SubscriptionPaymentFailedNotification;
use App\SubscriptionPaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SendSubscriptionPaymentFailedAlertAction
{
    public function handle(SubscriptionPayment $payment): bool
    {
        $payment = SubscriptionPayment::query()
            ->with('subscription.restaurant.users')
            ->findOrFail($payment->id);

        if ($payment->status !== SubscriptionPaymentStatus::Failed || $payment->failure_notified_at !== null) {
            return false;
        }

        return DB::transaction(function () use ($payment): bool {
            $payment = SubscriptionPayment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->failure_notified_at !== null) {
                return false;
            }

            $payment->load('subscription.restaurant.users');
            $restaurantUsers = $payment->subscription->restaurant->users
                ->filter(fn (User $user): bool => (bool) $user->pivot->is_active);
            $recipients = $restaurantUsers
                ->merge(User::query()->where('is_platform_admin', true)->where('is_active', true)->get())
                ->unique('id');

            Notification::send($recipients, new SubscriptionPaymentFailedNotification($payment));
            $payment->update(['failure_notified_at' => now()]);

            return true;
        });
    }
}
