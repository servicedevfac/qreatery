<?php

namespace App\Actions;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionStatusChangedNotification;
use App\SubscriptionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class TransitionSubscriptionStatusAction
{
    public function __construct(private readonly RecordAuditLogAction $recordAuditLog) {}

    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'trialing' => ['active', 'suspended', 'cancelled', 'expired'],
        'active' => ['grace_period', 'suspended', 'cancelled', 'expired'],
        'grace_period' => ['active', 'suspended', 'cancelled', 'expired'],
        'suspended' => ['active', 'cancelled', 'expired'],
        'expired' => ['active', 'cancelled'],
        'cancelled' => [],
    ];

    public function handle(Subscription $subscription, User $actor, SubscriptionStatus $toStatus, ?string $reason): Subscription
    {
        $fromStatusValue = $subscription->status->value;
        $updatedSubscription = DB::transaction(function () use ($subscription, $actor, $toStatus, $reason): Subscription {
            $subscription = Subscription::query()->lockForUpdate()->findOrFail($subscription->id);
            $fromStatus = $subscription->status;

            if ($fromStatus === $toStatus) {
                return $subscription->load(['plan', 'restaurant', 'payments', 'statusHistories.actor']);
            }

            if (! in_array($toStatus->value, self::TRANSITIONS[$fromStatus->value] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => ["La transition de {$fromStatus->value} vers {$toStatus->value} n’est pas autorisée."],
                ]);
            }

            $subscription->update([
                'status' => $toStatus,
                'cancelled_at' => $toStatus === SubscriptionStatus::Cancelled ? now() : null,
            ]);

            $subscription->statusHistories()->create([
                'actor_id' => $actor->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'reason' => $reason,
            ]);

            $this->recordAuditLog->handle($actor, 'subscription.status_changed', $subscription, $subscription->restaurant, [
                'from_status' => $fromStatus->value,
                'to_status' => $toStatus->value,
                'reason' => $reason,
            ]);

            return $subscription->refresh()->load(['plan', 'restaurant', 'payments', 'statusHistories.actor']);
        });

        if ($updatedSubscription->status->value !== $fromStatusValue) {
            Notification::send(
                $updatedSubscription->restaurant->users()->wherePivot('is_active', true)->get(),
                new SubscriptionStatusChangedNotification($updatedSubscription, $fromStatusValue),
            );
        }

        return $updatedSubscription;
    }
}
