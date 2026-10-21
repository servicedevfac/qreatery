<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\SubscriptionPaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<SubscriptionPayment> */
class SubscriptionPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'recorded_by' => User::factory(),
            'amount_minor' => fake()->numberBetween(500000, 5000000),
            'currency' => 'XOF',
            'method' => 'bank_transfer',
            'transaction_reference' => fake()->unique()->bothify('SUB-########'),
            'idempotency_key' => Str::uuid()->toString(),
            'status' => SubscriptionPaymentStatus::Succeeded,
            'proof_path' => null,
            'failure_reason' => null,
            'metadata' => null,
            'paid_at' => now(),
        ];
    }
}
