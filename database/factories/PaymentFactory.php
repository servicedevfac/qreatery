<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'order_id' => Order::factory(),
            'operator_id' => null,
            'original_payment_id' => null,
            'type' => 'charge',
            'amount_minor' => 5000,
            'currency' => 'XOF',
            'method' => 'cash',
            'idempotency_key' => Str::uuid()->toString(),
            'status' => 'succeeded',
            'processed_at' => now(),
        ];
    }
}
