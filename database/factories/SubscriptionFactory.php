<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subscription> */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = now()->startOfDay();

        return [
            'restaurant_id' => Restaurant::factory(),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::Active,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMonth(),
            'grace_ends_at' => $startsAt->copy()->addMonth()->addDays(7),
            'cancelled_at' => null,
            'notes' => null,
        ];
    }
}
