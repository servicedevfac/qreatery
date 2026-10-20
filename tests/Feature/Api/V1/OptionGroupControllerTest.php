<?php

namespace Tests\Feature\Api\V1;

use App\Models\OptionGroup;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OptionGroupControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_create_option_group_with_options(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();

        $response = $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/option-groups", [
            'name' => 'Accompagnements',
            'minimum_selections' => 1,
            'maximum_selections' => 2,
            'options' => [
                ['name' => 'Attiéké', 'price_delta_minor' => 0],
                ['name' => 'Frites', 'price_delta_minor' => 500],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.is_required', true)
            ->assertJsonCount(2, 'data.options');
        $this->assertDatabaseHas('option_groups', ['restaurant_id' => $restaurant->id, 'name' => 'Accompagnements']);
        $this->assertDatabaseCount('options', 2);
    }

    public function test_rejects_minimum_greater_than_maximum_with_422(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();

        $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/option-groups", [
            'name' => 'Sauces',
            'minimum_selections' => 3,
            'maximum_selections' => 2,
        ])->assertUnprocessable()->assertJsonValidationErrors('minimum_selections');
    }

    public function test_option_group_from_another_restaurant_is_hidden(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $foreignOptionGroup = OptionGroup::factory()->create();

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/option-groups/{$foreignOptionGroup->id}")
            ->assertNotFound();
    }
}
