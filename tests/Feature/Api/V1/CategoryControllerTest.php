<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_401_when_user_is_not_authenticated(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->getJson("/api/v1/restaurants/{$restaurant->id}/categories")
            ->assertUnauthorized();
    }

    public function test_owner_can_create_update_list_and_archive_category(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();

        $create = $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/categories", [
            'name' => 'Plats chauds',
            'description' => 'Les spécialités de la maison',
            'position' => 2,
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.name', 'Plats chauds')
            ->assertJsonPath('data.slug', 'plats-chauds');
        $categoryId = $create->json('data.id');
        $this->assertDatabaseHas('categories', ['id' => $categoryId, 'restaurant_id' => $restaurant->id]);

        $this->actingAs($owner)
            ->patchJson("/api/v1/restaurants/{$restaurant->id}/categories/{$categoryId}", ['name' => 'Nos plats chauds'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nos plats chauds');

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/categories")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($owner)
            ->deleteJson("/api/v1/restaurants/{$restaurant->id}/categories/{$categoryId}")
            ->assertNoContent();
        $this->assertDatabaseHas('categories', ['id' => $categoryId, 'is_active' => false]);
    }

    public function test_category_from_another_restaurant_is_hidden(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $foreignCategory = Category::factory()->create();

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/categories/{$foreignCategory->id}")
            ->assertNotFound();
    }
}
