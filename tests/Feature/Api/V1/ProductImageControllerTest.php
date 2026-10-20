<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_upload_product_image(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $product = Product::factory()->for($restaurant)->create();

        $response = $this->actingAs($owner)->post(
            "/api/v1/restaurants/{$restaurant->id}/products/{$product->id}/image",
            ['image' => $this->fakePng('plat.png')]
        );

        $response->assertOk();
        $product->refresh();
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_product_image_from_another_restaurant_is_hidden(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $foreignProduct = Product::factory()->create();

        $this->actingAs($owner)->post(
            "/api/v1/restaurants/{$restaurant->id}/products/{$foreignProduct->id}/image",
            ['image' => $this->fakePng('plat.png')]
        )->assertNotFound();
    }

    private function fakePng(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true)
        );
    }
}
