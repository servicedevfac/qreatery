<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_create_product_with_variants_and_option_groups(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $category = Category::factory()->for($restaurant)->create();
        $optionGroup = OptionGroup::factory()->for($restaurant)->create();

        $response = $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/products", [
            'category_id' => $category->id,
            'name' => 'Poulet braisé',
            'description' => 'Poulet braisé avec accompagnement',
            'price_minor' => 6500,
            'preparation_minutes' => 25,
            'variants' => [
                ['name' => 'Demi', 'price_delta_minor' => 0, 'is_default' => true],
                ['name' => 'Entier', 'price_delta_minor' => 5000],
            ],
            'option_group_ids' => [$optionGroup->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'poulet-braise')
            ->assertJsonPath('data.price_minor', 6500)
            ->assertJsonCount(2, 'data.variants')
            ->assertJsonCount(1, 'data.option_groups');
        $productId = $response->json('data.id');
        $this->assertDatabaseHas('products', ['id' => $productId, 'restaurant_id' => $restaurant->id]);
        $this->assertDatabaseCount('product_variants', 2);
        $this->assertDatabaseHas('option_group_product', ['product_id' => $productId, 'option_group_id' => $optionGroup->id]);
    }

    public function test_rejects_category_from_another_restaurant_with_422(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $foreignCategory = Category::factory()->create();

        $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/products", [
            'category_id' => $foreignCategory->id,
            'name' => 'Produit test',
            'price_minor' => 1000,
        ])->assertUnprocessable()->assertJsonValidationErrors('category_id');

        $this->assertDatabaseMissing('products', ['restaurant_id' => $restaurant->id, 'name' => 'Produit test']);
    }

    public function test_product_from_another_restaurant_is_hidden(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $foreignProduct = Product::factory()->create();

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/products/{$foreignProduct->id}")
            ->assertNotFound();
    }
}
