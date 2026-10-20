<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Product;
use App\Models\Restaurant;
use App\RestaurantStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PublicMenuControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_menu_only_returns_active_available_products(): void
    {
        $restaurant = Restaurant::factory()->create(['slug' => 'chez-aicha']);
        $category = Category::factory()->for($restaurant)->create(['name' => 'Grillades', 'position' => 1]);
        Product::factory()->for($restaurant)->for($category)->create([
            'name' => 'Poulet braisé',
            'price_minor' => 6500,
            'status' => 'active',
            'is_available' => true,
        ]);
        Product::factory()->for($restaurant)->for($category)->create([
            'name' => 'Poisson indisponible',
            'status' => 'active',
            'is_available' => false,
        ]);
        Product::factory()->for($restaurant)->for($category)->create([
            'name' => 'Brouillon',
            'status' => 'draft',
            'is_available' => true,
        ]);

        $response = $this->getJson('/api/v1/public/restaurants/chez-aicha/menu');

        $response->assertOk()
            ->assertJsonPath('data.restaurant.slug', 'chez-aicha')
            ->assertJsonCount(1, 'data.categories')
            ->assertJsonCount(1, 'data.categories.0.products')
            ->assertJsonPath('data.categories.0.products.0.name', 'Poulet braisé');
    }

    public function test_public_menu_search_filters_products(): void
    {
        $restaurant = Restaurant::factory()->create(['slug' => 'maquis-demo']);
        $category = Category::factory()->for($restaurant)->create();
        Product::factory()->for($restaurant)->for($category)->create(['name' => 'Poulet braisé']);
        Product::factory()->for($restaurant)->for($category)->create(['name' => 'Poisson braisé']);

        $this->getJson('/api/v1/public/restaurants/maquis-demo/menu?search=Poulet')
            ->assertOk()
            ->assertJsonCount(1, 'data.categories.0.products')
            ->assertJsonPath('data.categories.0.products.0.name', 'Poulet braisé');
    }

    public function test_suspended_restaurant_menu_returns_404(): void
    {
        $restaurant = Restaurant::factory()->create(['slug' => 'ferme', 'status' => RestaurantStatus::Suspended]);

        $this->getJson("/api/v1/public/restaurants/{$restaurant->slug}/menu")
            ->assertNotFound();
    }
}
