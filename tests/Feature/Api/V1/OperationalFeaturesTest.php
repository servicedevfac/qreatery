<?php

namespace Tests\Feature\Api\V1;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\QrCode;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class OperationalFeaturesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_healthcheck_endpoint_returns_healthy(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.cache.status', 'ok')
            ->assertJsonPath('checks.storage.status', 'ok');
    }

    public function test_healthcheck_hides_technical_errors_when_debug_is_disabled(): void
    {
        config(['app.debug' => false]);
        Cache::shouldReceive('put')->once()->andThrow(new RuntimeException('secret-cache-host'));

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('checks.cache.status', 'error')
            ->assertJsonPath('checks.cache.message', 'Service de cache indisponible.');
        $this->assertStringNotContainsString('secret-cache-host', $response->getContent());
    }

    public function test_owner_can_download_qr_code_svg_and_pdf(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $table = RestaurantTable::factory()->for($restaurant)->create(['label' => 'Table 10']);
        $qrCode = QrCode::factory()->for($restaurant)->for($table)->create(['type' => 'table', 'status' => 'active']);

        // Download SVG
        $svgResponse = $this->actingAs($owner)
            ->get("/api/v1/restaurants/{$restaurant->id}/qr-codes/{$qrCode->id}/download?format=svg");

        $svgResponse->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');

        // Download PDF
        $pdfResponse = $this->actingAs($owner)
            ->get("/api/v1/restaurants/{$restaurant->id}/qr-codes/{$qrCode->id}/download?format=pdf");

        $pdfResponse->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_owner_can_print_sheet_of_all_table_qr_codes(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $table1 = RestaurantTable::factory()->for($restaurant)->create(['label' => 'T1']);
        $table2 = RestaurantTable::factory()->for($restaurant)->create(['label' => 'T2']);
        QrCode::factory()->for($restaurant)->for($table1)->create(['type' => 'table', 'status' => 'active']);
        QrCode::factory()->for($restaurant)->for($table2)->create(['type' => 'table', 'status' => 'active']);

        $response = $this->actingAs($owner)
            ->get("/api/v1/restaurants/{$restaurant->id}/qr-codes/print-sheet");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_owner_can_download_kitchen_ticket_pdf(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $table = RestaurantTable::factory()->for($restaurant)->create(['label' => 'B4']);
        $product = Product::factory()->for($restaurant)->create(['name' => 'Burger Gourmet']);

        $order = Order::factory()->for($restaurant)->for($table)->create([
            'number' => 'CMD-1042',
            'preparation_status' => 'preparing',
            'notes' => 'Sans oignons s’il vous plaît',
        ]);

        OrderItem::factory()->for($order)->for($product)->create([
            'product_name' => 'Burger Gourmet',
            'quantity' => 2,
            'instructions' => 'Cuisson à point',
        ]);

        $response = $this->actingAs($owner)
            ->get("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/kitchen-ticket");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_owner_can_view_table_active_orders_and_close_table(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $table = RestaurantTable::factory()->for($restaurant)->create(['label' => 'Table 5']);

        $order = Order::factory()->for($restaurant)->for($table)->create([
            'preparation_status' => 'served',
            'payment_status' => 'paid',
        ]);

        // View active orders
        $listResponse = $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/tables/{$table->id}/active-orders");

        $listResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order->id);

        // Close table
        $closeResponse = $this->actingAs($owner)
            ->postJson("/api/v1/restaurants/{$restaurant->id}/tables/{$table->id}/close");

        $closeResponse->assertOk()
            ->assertJsonPath('orders_completed', 1);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'preparation_status' => 'completed',
        ]);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'user_id' => $owner->id,
            'from_status' => 'served',
            'to_status' => 'completed',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'restaurant_id' => $restaurant->id,
            'action' => 'restaurant_table.closed',
            'auditable_type' => RestaurantTable::class,
            'auditable_id' => $table->id,
        ]);
        Event::assertDispatched(OrderStatusUpdated::class, fn (OrderStatusUpdated $event): bool => $event->order->id === $order->id
            && $event->fromStatus === 'served');
    }

    public function test_cannot_close_table_with_unpaid_orders_unless_forced(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $table = RestaurantTable::factory()->for($restaurant)->create(['label' => 'Table 8']);

        Order::factory()->for($restaurant)->for($table)->create([
            'preparation_status' => 'served',
            'payment_status' => 'unpaid',
        ]);

        // Fail without force
        $this->actingAs($owner)
            ->postJson("/api/v1/restaurants/{$restaurant->id}/tables/{$table->id}/close")
            ->assertStatus(422)
            ->assertJsonPath('unpaid_orders_count', 1);

        // Succeed with force
        $this->actingAs($owner)
            ->postJson("/api/v1/restaurants/{$restaurant->id}/tables/{$table->id}/close", ['force' => true])
            ->assertOk()
            ->assertJsonPath('orders_completed', 1);
    }
}
