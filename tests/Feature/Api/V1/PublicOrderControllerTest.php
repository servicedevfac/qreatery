<?php

namespace Tests\Feature\Api\V1;

use App\Events\OrderCreated;
use App\Models\Option;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\QrCode;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PublicOrderControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_order_recalculates_totals_and_preserves_item_snapshot(): void
    {
        Event::fake([OrderCreated::class]);
        $restaurant = Restaurant::factory()->create([
            'slug' => 'maquis-order',
            'settings' => ['order_modes' => ['dine_in'], 'tax_rate' => 10, 'service_fee_minor' => 500],
        ]);
        $restaurantTable = RestaurantTable::factory()->for($restaurant)->create();
        $qrCode = QrCode::factory()->for($restaurant)->create([
            'restaurant_table_id' => $restaurantTable->id,
            'type' => 'table',
        ]);
        $product = Product::factory()->for($restaurant)->create(['name' => 'Poulet braisé', 'price_minor' => 5000]);
        $variant = ProductVariant::factory()->for($product)->create(['name' => 'Grand', 'price_delta_minor' => 1000, 'is_active' => true]);
        $optionGroup = OptionGroup::factory()->for($restaurant)->create(['minimum_selections' => 1, 'maximum_selections' => 1]);
        $option = Option::factory()->for($optionGroup)->create(['name' => 'Frites', 'price_delta_minor' => 500]);
        $product->optionGroups()->attach($optionGroup);

        $response = $this->postJson('/api/v1/public/restaurants/maquis-order/orders', [
            'mode' => 'dine_in',
            'qr_token' => $qrCode->token,
            'total_minor' => 1,
            'items' => [[
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'option_ids' => [$option->id],
                'quantity' => 2,
                'instructions' => 'Sans piment',
            ]],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.subtotal_minor', 13000)
            ->assertJsonPath('data.tax_minor', 1300)
            ->assertJsonPath('data.service_fee_minor', 500)
            ->assertJsonPath('data.total_minor', 14800)
            ->assertJsonPath('data.items.0.product_name', 'Poulet braisé')
            ->assertJsonPath('data.items.0.variant_name', 'Grand')
            ->assertJsonPath('data.items.0.options.0.option_name', 'Frites');

        $trackingToken = $response->json('data.tracking_token');
        $this->assertSame(64, strlen($trackingToken));
        $this->assertDatabaseHas('orders', ['restaurant_id' => $restaurant->id, 'total_minor' => 14800]);
        $this->assertDatabaseHas('order_items', ['product_name' => 'Poulet braisé', 'unit_price_minor' => 6500, 'quantity' => 2]);
        $this->assertDatabaseHas('order_status_histories', ['from_status' => null, 'to_status' => 'pending']);
        Event::assertDispatched(OrderCreated::class, fn (OrderCreated $event): bool => $event->order->id === $response->json('data.id'));

        $product->update(['name' => 'Nouveau nom', 'price_minor' => 9999]);
        $this->getJson("/api/v1/public/orders/{$trackingToken}")
            ->assertOk()
            ->assertJsonPath('data.items.0.product_name', 'Poulet braisé')
            ->assertJsonPath('data.items.0.unit_price_minor', 6500);
    }

    public function test_unavailable_product_returns_422_without_creating_order(): void
    {
        $restaurant = Restaurant::factory()->create(['slug' => 'indisponible']);
        $product = Product::factory()->for($restaurant)->create(['is_available' => false]);

        $this->postJson('/api/v1/public/restaurants/indisponible/orders', [
            'mode' => 'takeaway',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.product_id');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_product_from_another_restaurant_returns_422(): void
    {
        $restaurant = Restaurant::factory()->create(['slug' => 'restaurant-a']);
        $foreignProduct = Product::factory()->create();

        $this->postJson('/api/v1/public/restaurants/restaurant-a/orders', [
            'mode' => 'takeaway',
            'items' => [['product_id' => $foreignProduct->id, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items');
    }
}
