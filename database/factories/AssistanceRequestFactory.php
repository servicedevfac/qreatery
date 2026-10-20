<?php

namespace Database\Factories;

use App\Models\AssistanceRequest;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssistanceRequest>
 */
class AssistanceRequestFactory extends Factory
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
            'order_id' => null,
            'handled_by' => null,
            'type' => 'waiter',
            'status' => 'pending',
            'message' => fake()->optional()->sentence(),
            'handled_at' => null,
            'resolved_at' => null,
        ];
    }
}
