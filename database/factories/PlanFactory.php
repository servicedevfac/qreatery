<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Plan> */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'price_minor' => fake()->numberBetween(500000, 5000000),
            'currency' => 'XOF',
            'billing_period' => 'month',
            'billing_interval' => 1,
            'limits' => ['restaurants' => 1, 'employees' => 10],
            'features' => ['orders', 'reports'],
            'terms' => null,
            'is_active' => true,
        ];
    }
}
