<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
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
            'restaurant_table_id' => null,
            'number' => 'ORD-'.fake()->unique()->numerify('##########'),
            'tracking_token' => Str::random(64),
            'mode' => 'takeaway',
            'preparation_status' => 'pending',
            'payment_status' => 'unpaid',
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'subtotal_minor' => 5000,
            'tax_minor' => 0,
            'service_fee_minor' => 0,
            'total_minor' => 5000,
            'currency' => 'XOF',
        ];
    }
}
