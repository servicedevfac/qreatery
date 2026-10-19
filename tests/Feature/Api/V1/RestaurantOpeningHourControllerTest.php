<?php

namespace Tests\Feature\Api\V1;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RestaurantOpeningHourControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_replace_the_seven_opening_hours(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $hours = collect(range(0, 6))->map(fn (int $day): array => [
            'day_of_week' => $day,
            'is_closed' => $day === 0,
            'opens_at' => $day === 0 ? null : '08:00',
            'closes_at' => $day === 0 ? null : '22:00',
        ])->all();

        $this->actingAs($owner)
            ->putJson("/api/v1/restaurants/{$restaurant->id}/opening-hours", ['hours' => $hours])
            ->assertOk()->assertJsonCount(7, 'data');

        $this->assertDatabaseCount('restaurant_opening_hours', 7);
        $this->assertDatabaseHas('restaurant_opening_hours', [
            'restaurant_id' => $restaurant->id,
            'day_of_week' => 0,
            'is_closed' => true,
            'opens_at' => null,
        ]);
    }

    public function test_open_day_requires_valid_hours(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $hours = collect(range(0, 6))->map(fn (int $day): array => [
            'day_of_week' => $day,
            'is_closed' => false,
            'opens_at' => null,
            'closes_at' => null,
        ])->all();

        $this->actingAs($owner)
            ->putJson("/api/v1/restaurants/{$restaurant->id}/opening-hours", ['hours' => $hours])
            ->assertUnprocessable()->assertJsonValidationErrors(['hours.0.opens_at']);
    }
}
