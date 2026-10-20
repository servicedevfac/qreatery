<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantTable>
 */
class RestaurantTableFactory extends Factory
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
            'label' => 'Table '.fake()->unique()->numberBetween(1, 500),
            'capacity' => fake()->numberBetween(1, 12),
            'is_active' => true,
            'archived_at' => null,
        ];
    }
}
