<?php

namespace Tests\Feature\Api\V1;

use App\Models\Restaurant;
use App\Models\RestaurantClosure;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RestaurantClosureControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_create_list_and_delete_an_exceptional_closure(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();

        $create = $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/closures", [
            'starts_at' => '2026-12-25 00:00:00',
            'ends_at' => '2026-12-26 00:00:00',
            'reason' => 'Jour férié',
        ]);

        $create->assertCreated()->assertJsonPath('data.reason', 'Jour férié');
        $closureId = $create->json('data.id');
        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/closures")
            ->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($owner)
            ->deleteJson("/api/v1/restaurants/{$restaurant->id}/closures/{$closureId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('restaurant_closures', ['id' => $closureId]);
    }

    public function test_closure_from_another_restaurant_is_hidden(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $foreignClosure = RestaurantClosure::factory()->create();

        $this->actingAs($owner)
            ->deleteJson("/api/v1/restaurants/{$restaurant->id}/closures/{$foreignClosure->id}")
            ->assertNotFound();
    }
}
