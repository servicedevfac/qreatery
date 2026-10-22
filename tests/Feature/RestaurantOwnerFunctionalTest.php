<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use App\RestaurantPermission;
use App\RestaurantRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantOwnerFunctionalTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create([
            'slug' => 'resto-owner-test',
            'status' => 'active',
            'currency' => 'XOF',
            'settings' => [
                'tax_rate' => 18,
                'service_fee_minor' => 50000,
                'estimated_preparation_minutes' => 20,
            ],
        ]);

        $this->owner = User::factory()->create([
            'email' => 'owner-functional@qreatery.test',
            'is_active' => true,
        ]);

        RestaurantMembership::create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->owner->id,
            'role' => RestaurantRole::Owner,
            'permissions' => array_map(fn ($p) => $p->value, RestaurantPermission::cases()),
            'is_active' => true,
        ]);
    }

    public function test_owner_can_create_edit_and_delete_a_product(): void
    {
        $category = Category::factory()->create(['restaurant_id' => $this->restaurant->id]);

        // 1. Create Product
        $createResponse = $this->actingAs($this->owner)
            ->postJson("/api/v1/restaurants/{$this->restaurant->id}/products", [
                'name' => 'Poulet Yassa Signature',
                'category_id' => $category->id,
                'price_minor' => 450000,
                'preparation_minutes' => 25,
                'description' => 'Délicieux poulet mariné aux oignons et citron.',
                'is_available' => true,
                'status' => 'active',
            ]);

        $createResponse->assertStatus(201);
        $productId = $createResponse->json('data.id');

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'name' => 'Poulet Yassa Signature',
            'price_minor' => 450000,
        ]);

        // 2. Update Product
        $updateResponse = $this->actingAs($this->owner)
            ->putJson("/api/v1/restaurants/{$this->restaurant->id}/products/{$productId}", [
                'name' => 'Poulet Yassa Royal',
                'category_id' => $category->id,
                'price_minor' => 500000,
                'preparation_minutes' => 20,
                'description' => 'Recette enrichie du chef.',
                'is_available' => true,
            ]);

        $updateResponse->assertOk();
        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'name' => 'Poulet Yassa Royal',
            'price_minor' => 500000,
        ]);

        // 3. Delete / Archive Product
        $deleteResponse = $this->actingAs($this->owner)
            ->deleteJson("/api/v1/restaurants/{$this->restaurant->id}/products/{$productId}");

        $deleteResponse->assertNoContent();
        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'status' => 'archived',
            'is_available' => false,
        ]);
    }

    public function test_owner_can_create_category_and_table(): void
    {
        // 1. Create Category
        $catResponse = $this->actingAs($this->owner)
            ->postJson("/api/v1/restaurants/{$this->restaurant->id}/categories", [
                'name' => 'Desserts Gourmands',
                'description' => 'Pâtisseries et glaces artisanales',
                'is_active' => true,
            ]);

        $catResponse->assertStatus(201);
        $this->assertDatabaseHas('categories', [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Desserts Gourmands',
        ]);

        // 2. Create Table
        $tableResponse = $this->actingAs($this->owner)
            ->postJson("/api/v1/restaurants/{$this->restaurant->id}/tables", [
                'label' => 'Terrasse T09',
                'capacity' => 6,
                'is_active' => true,
            ]);

        $tableResponse->assertStatus(201);
        $tableId = $tableResponse->json('data.id');

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $tableId,
            'label' => 'Terrasse T09',
            'capacity' => 6,
        ]);
    }

    public function test_owner_can_transition_order_status(): void
    {
        $order = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'preparation_status' => 'pending',
            'mode' => 'takeaway',
            'total_minor' => 300000,
            'currency' => 'XOF',
        ]);

        $response = $this->actingAs($this->owner)
            ->patchJson("/api/v1/restaurants/{$this->restaurant->id}/orders/{$order->id}/status", [
                'status' => 'accepted',
            ]);

        $response->assertOk();
        $this->assertEquals('accepted', $order->fresh()->preparation_status);
    }

    public function test_owner_can_update_configuration(): void
    {
        $response = $this->actingAs($this->owner)
            ->patchJson("/api/v1/restaurants/{$this->restaurant->id}/configuration", [
                'tax_rate' => 20,
                'service_fee_minor' => 75000,
                'estimated_preparation_minutes' => 30,
            ]);

        $response->assertOk();
        $freshSettings = $this->restaurant->fresh()->settings;
        $this->assertEquals(20, $freshSettings['tax_rate']);
        $this->assertEquals(75000, $freshSettings['service_fee_minor']);
        $this->assertEquals(30, $freshSettings['estimated_preparation_minutes']);
    }
}
