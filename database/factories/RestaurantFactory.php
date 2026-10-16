<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\RestaurantStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->optional()->sentence(),
            'address' => fake()->optional()->address(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->companyEmail(),
            'currency' => 'XOF',
            'timezone' => 'Africa/Abidjan',
            'status' => RestaurantStatus::Active,
            'settings' => null,
        ];
    }
}
