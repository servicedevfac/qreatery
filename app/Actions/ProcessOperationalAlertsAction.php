<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Notifications\OrderDelayedNotification;
use App\Notifications\SubscriptionExpiringNotification;
use App\Notifications\SubscriptionStatusChangedNotification;
use App\SubscriptionPaymentStatus;
use App\SubscriptionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ProcessOperationalAlertsAction
{
    public function __construct(
        private readonly SendSubscriptionPaymentFailedAlertAction $sendPaymentFailedAlert,
    ) {}

    /** @return array{delayed_orders: int, expiring_subscriptions: int, transitioned_subscriptions: int, failed_payments: int} */
    public function handle(): array
    {
        return [
            'delayed_orders' => $this->processDelayedOrders(),
            'expiring_subscriptions' => $this->processExpiringSubscriptions(),
            'transitioned_subscriptions' => $this->processExpiredSubscriptions(),
            'failed_payments' => $this->processFailedPayments(),
        ];
    }

    private function processDelayedOrders(): int
    {
        $count = 0;

        Order::query()
            ->with('restaurant.users')
            ->whereNull('late_notified_at')
            ->whereIn('preparation_status', ['pending', 'accepted', 'preparing'])
            ->eachById(function (Order $order) use (&$count): void {
                $threshold = (int) ($order->restaurant->settings['order_delay_minutes'] ?? 45);
                $startedAt = $order->accepted_at ?? $order->created_at;

                if ($startedAt->gt(now()->subMinutes($threshold))) {
                    return;
                }

                $delayMinutes = (int) floor($startedAt->diffInMinutes(now()));
                $sent = DB::transaction(function () use ($order, $delayMinutes): bool {
                    $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
                    if ($lockedOrder->late_notified_at !== null) {
                        return false;
                    }

                    $recipients = $order->restaurant->users
                        ->filter(fn (User $user): bool => (bool) $user->pivot->is_active);
                    Notification::send($recipients, new OrderDelayedNotification($lockedOrder, $delayMinutes));
                    $lockedOrder->update(['late_notified_at' => now()]);

                    return true;
                });

                $count += (int) $sent;
            });

        return $count;
    }

    private function processExpiringSubscriptions(): int
    {
        $count = 0;

        Subscription::query()
            ->with('restaurant.users')
            ->whereNull('expiry_notified_at')
            ->whereIn('status', [SubscriptionStatus::Trialing, SubscriptionStatus::Active, SubscriptionStatus::GracePeriod])
            ->eachById(function (Subscription $subscription) use (&$count): void {
                $expiresAt = $subscription->status === SubscriptionStatus::GracePeriod
                    ? ($subscription->grace_ends_at ?? $subscription->ends_at)
                    : $subscription->ends_at;

                if ($expiresAt->isPast() || $expiresAt->gt(now()->addDays(7))) {
                    return;
                }

                $daysRemaining = max(0, (int) ceil(now()->diffInDays($expiresAt, false)));
                $sent = DB::transaction(function () use ($subscription, $daysRemaining): bool {
                    $lockedSubscription = Subscription::query()->lockForUpdate()->findOrFail($subscription->id);
                    if ($lockedSubscription->expiry_notified_at !== null) {
                        return false;
                    }

                    $restaurantUsers = $subscription->restaurant->users
                        ->filter(fn (User $user): bool => (bool) $user->pivot->is_active);
                    $recipients = $restaurantUsers
                        ->merge(User::query()->where('is_platform_admin', true)->where('is_active', true)->get())
                        ->unique('id');
                    Notification::send($recipients, new SubscriptionExpiringNotification($lockedSubscription, $daysRemaining));
                    $lockedSubscription->update(['expiry_notified_at' => now()]);

                    return true;
                });

                $count += (int) $sent;
            });

        return $count;
    }

    private function processExpiredSubscriptions(): int
    {
        $count = 0;

        Subscription::query()
            ->with('restaurant.users')
            ->whereIn('status', [SubscriptionStatus::Trialing, SubscriptionStatus::Active, SubscriptionStatus::GracePeriod])
            ->eachById(function (Subscription $subscription) use (&$count): void {
                $targetStatus = null;

                if ($subscription->status === SubscriptionStatus::GracePeriod && ($subscription->grace_ends_at ?? $subscription->ends_at)->isPast()) {
                    $targetStatus = SubscriptionStatus::Expired;
                } elseif (in_array($subscription->status, [SubscriptionStatus::Trialing, SubscriptionStatus::Active], true) && $subscription->ends_at->isPast()) {
                    $targetStatus = $subscription->grace_ends_at?->isFuture()
                        ? SubscriptionStatus::GracePeriod
                        : SubscriptionStatus::Expired;
                }

                if ($targetStatus === null) {
                    return;
                }

                $fromStatus = $subscription->status;
                $changed = DB::transaction(function () use ($subscription, $fromStatus, $targetStatus): bool {
                    $lockedSubscription = Subscription::query()->lockForUpdate()->findOrFail($subscription->id);
                    if ($lockedSubscription->status !== $fromStatus) {
                        return false;
                    }

                    $lockedSubscription->update(['status' => $targetStatus]);
                    $lockedSubscription->statusHistories()->create([
                        'actor_id' => null,
                        'from_status' => $fromStatus,
                        'to_status' => $targetStatus,
                        'reason' => 'Transition automatique à échéance.',
                    ]);

                    return true;
                });

                if ($changed) {
                    $subscription->refresh();
                    Notification::send(
                        $subscription->restaurant->users->filter(fn (User $user): bool => (bool) $user->pivot->is_active),
                        new SubscriptionStatusChangedNotification($subscription, $fromStatus->value),
                    );
                    $count++;
                }
            });

        return $count;
    }

    private function processFailedPayments(): int
    {
        $count = 0;

        SubscriptionPayment::query()
            ->where('status', SubscriptionPaymentStatus::Failed)
            ->whereNull('failure_notified_at')
            ->eachById(function (SubscriptionPayment $payment) use (&$count): void {
                $count += (int) $this->sendPaymentFailedAlert->handle($payment);
            });

        return $count;
    }
}
