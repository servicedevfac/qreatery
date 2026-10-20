<?php

namespace Tests\Feature\Api\V1;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use App\RestaurantRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RestaurantControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_401_when_user_is_not_authenticated(): void
    {
        $response = $this->get('/api/v1/restaurants');

        $response
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_returns_only_restaurants_of_authenticated_user(): void
    {
        $user = User::factory()->create();
        $ownRestaurant = Restaurant::factory()->create(['name' => 'Chez Awa']);
        $otherRestaurant = Restaurant::factory()->create(['name' => 'Chez Moussa']);
        RestaurantMembership::factory()->for($ownRestaurant)->for($user)->create();
        RestaurantMembership::factory()->for($otherRestaurant)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/restaurants');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownRestaurant->id)
            ->assertJsonMissing(['id' => $otherRestaurant->id]);
    }

    public function test_valid_payload_creates_restaurant_and_owner_membership_and_returns_201(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/restaurants', [
            'name' => 'Maquis du Plateau',
            'email' => 'contact@maquis.test',
            'currency' => 'XOF',
            'timezone' => 'Africa/Abidjan',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Maquis du Plateau')
            ->assertJsonPath('data.status', 'active');
        $this->assertDatabaseHas('restaurants', [
            'name' => 'Maquis du Plateau',
            'email' => 'contact@maquis.test',
        ]);
        $this->assertDatabaseHas('restaurant_memberships', [
            'restaurant_id' => $response->json('data.id'),
            'user_id' => $user->id,
            'role' => RestaurantRole::Owner->value,
            'is_active' => true,
        ]);
    }

    public function test_returns_422_when_required_name_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/restaurants', [
            'email' => 'contact@restaurant.test',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
        $this->assertDatabaseCount('restaurants', 0);
    }

    public function test_returns_404_when_user_views_another_restaurants_record(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/v1/restaurants/{$restaurant->id}");

        $response->assertNotFound();
    }

    public function test_owner_can_update_restaurant(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['name' => 'Ancien nom']);
        RestaurantMembership::factory()->owner()->for($restaurant)->for($user)->create();

        $response = $this->actingAs($user)->patchJson("/api/v1/restaurants/{$restaurant->id}", [
            'name' => 'Nouveau nom',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Nouveau nom');
        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => 'Nouveau nom',
        ]);
    }

    public function test_returns_403_when_manager_updates_restaurant(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['name' => 'Nom protégé']);
        RestaurantMembership::factory()->manager()->for($restaurant)->for($user)->create();

        $response = $this->actingAs($user)->patchJson("/api/v1/restaurants/{$restaurant->id}", [
            'name' => 'Modification interdite',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => 'Nom protégé',
        ]);
    }

    public function test_returns_404_when_user_updates_another_restaurants_record(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['name' => 'Restaurant privé']);

        $response = $this->actingAs($user)->patchJson("/api/v1/restaurants/{$restaurant->id}", [
            'name' => 'Tentative externe',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => 'Restaurant privé',
        ]);
    }
}
