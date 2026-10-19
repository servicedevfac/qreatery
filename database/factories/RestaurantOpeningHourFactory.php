<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantOpeningHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantOpeningHour>
 */
class RestaurantOpeningHourFactory extends Factory
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
            'day_of_week' => fake()->numberBetween(0, 6),
            'opens_at' => '08:00',
            'closes_at' => '22:00',
            'is_closed' => false,
        ];
    }
}
