<?php

namespace Database\Factories;

use App\Models\OptionGroup;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OptionGroup>
 */
class OptionGroupFactory extends Factory
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
            'name' => fake()->unique()->words(2, true),
            'minimum_selections' => 0,
            'maximum_selections' => 1,
            'is_active' => true,
            'position' => 0,
        ];
    }
}
