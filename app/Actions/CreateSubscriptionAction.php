<?php

namespace App\Actions;

use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use App\SubscriptionStatus;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateSubscriptionAction
{
    public function __construct(private readonly RecordAuditLogAction $recordAuditLog) {}

    /** @param array<string, mixed> $data */
    public function handle(Restaurant $restaurant, User $actor, array $data): Subscription
    {
        return DB::transaction(function () use ($restaurant, $actor, $data): Subscription {
            $plan = Plan::query()->lockForUpdate()->findOrFail($data['plan_id']);
            $startsAt = isset($data['starts_at'])
                ? CarbonImmutable::parse($data['starts_at'])
                : CarbonImmutable::now();

            $hasOverlappingSubscription = $restaurant->subscriptions()
                ->whereIn('status', [
                    SubscriptionStatus::Trialing->value,
                    SubscriptionStatus::Active->value,
                    SubscriptionStatus::GracePeriod->value,
                    SubscriptionStatus::Suspended->value,
                ])
                ->where('ends_at', '>=', $startsAt)
                ->lockForUpdate()
                ->exists();

            if ($hasOverlappingSubscription) {
                throw ValidationException::withMessages([
                    'starts_at' => ['Ce restaurant possède déjà un abonnement actif sur cette période.'],
                ]);
            }

            $endsAt = match ($plan->billing_period) {
                'day' => $startsAt->addDays($plan->billing_interval),
                'week' => $startsAt->addWeeks($plan->billing_interval),
                'year' => $startsAt->addYearsNoOverflow($plan->billing_interval),
                default => $startsAt->addMonthsNoOverflow($plan->billing_interval),
            };
            $status = SubscriptionStatus::from($data['status'] ?? SubscriptionStatus::Active->value);
            $graceDays = (int) ($data['grace_days'] ?? 0);

            $subscription = $restaurant->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => $status,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'grace_ends_at' => $graceDays > 0 ? $endsAt->addDays($graceDays) : null,
                'notes' => $data['notes'] ?? null,
            ]);

            $subscription->statusHistories()->create([
                'actor_id' => $actor->id,
                'from_status' => null,
                'to_status' => $status,
                'reason' => 'Création de l’abonnement.',
            ]);

            $this->recordAuditLog->handle($actor, 'subscription.created', $subscription, $restaurant, [
                'plan_id' => $plan->id,
                'status' => $status->value,
                'starts_at' => $startsAt->toIso8601String(),
                'ends_at' => $endsAt->toIso8601String(),
            ]);

            return $subscription->load(['plan', 'restaurant', 'statusHistories.actor']);
        });
    }
}
