<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantClosure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantClosure>
 */
class RestaurantClosureFactory extends Factory
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
            'starts_at' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'ends_at' => null,
            'reason' => fake()->optional()->sentence(),
        ];
    }
}
