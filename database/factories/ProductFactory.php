<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'restaurant_id' => Restaurant::factory(),
            'category_id' => null,
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->sentence(),
            'price_minor' => fake()->numberBetween(500, 25000),
            'status' => 'active',
            'is_available' => true,
            'preparation_minutes' => fake()->numberBetween(5, 45),
            'position' => 0,
            'availability' => null,
        ];
    }
}
