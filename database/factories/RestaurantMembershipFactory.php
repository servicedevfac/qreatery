<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use App\RestaurantRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantMembership>
 */
class RestaurantMembershipFactory extends Factory
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
            'user_id' => User::factory(),
            'role' => RestaurantRole::Staff,
            'permissions' => null,
            'is_active' => true,
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (): array => ['role' => RestaurantRole::Owner]);
    }

    public function admin(): static
    {
        return $this->state(fn (): array => ['role' => RestaurantRole::Admin]);
    }

    public function manager(): static
    {
        return $this->state(fn (): array => ['role' => RestaurantRole::Manager]);
    }
}
