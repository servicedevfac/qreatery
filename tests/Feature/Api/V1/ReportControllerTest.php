<?php

namespace Tests\Feature\Api\V1;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_view_filtered_operational_and_payment_metrics(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $dineInOrder = Order::factory()->for($restaurant)->create([
            'mode' => 'dine_in',
            'preparation_status' => 'completed',
            'total_minor' => 10000,
            'subtotal_minor' => 10000,
            'accepted_at' => now()->subMinutes(20),
            'ready_at' => now()->subMinutes(5),
        ]);
        $takeawayOrder = Order::factory()->for($restaurant)->create([
            'mode' => 'takeaway',
            'preparation_status' => 'cancelled',
            'total_minor' => 6000,
            'subtotal_minor' => 6000,
        ]);
        OrderItem::factory()->for($dineInOrder)->create(['product_name' => 'Poulet', 'quantity' => 2, 'line_total_minor' => 10000]);
        OrderItem::factory()->for($takeawayOrder)->create(['product_name' => 'Poisson', 'quantity' => 1, 'line_total_minor' => 6000]);
        $charge = Payment::factory()->for($restaurant)->for($dineInOrder)->create(['type' => 'charge', 'amount_minor' => 10000]);
        Payment::factory()->for($restaurant)->for($dineInOrder)->create([
            'type' => 'refund',
            'original_payment_id' => $charge->id,
            'amount_minor' => 2000,
        ]);

        $response = $this->actingAs($owner)->getJson(
            "/api/v1/restaurants/{$restaurant->id}/reports/overview?date_from=".now()->toDateString().'&date_to='.now()->toDateString()
        );

        $response->assertOk()
            ->assertJsonPath('data.orders.total', 2)
            ->assertJsonPath('data.orders.completed', 1)
            ->assertJsonPath('data.orders.cancelled', 1)
            ->assertJsonPath('data.orders.average_basket_minor', 8000)
            ->assertJsonPath('data.orders.by_mode.dine_in', 1)
            ->assertJsonPath('data.orders.by_mode.takeaway', 1)
            ->assertJsonPath('data.payments.charged_minor', 10000)
            ->assertJsonPath('data.payments.refunded_minor', 2000)
            ->assertJsonPath('data.payments.net_collected_minor', 8000)
            ->assertJsonPath('data.top_products.0.product_name', 'Poulet');
    }

    public function test_staff_without_report_permission_is_forbidden(): void
    {
        $staff = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->for($restaurant)->for($staff)->create();

        $this->actingAs($staff)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/reports/overview")
            ->assertForbidden();
    }
}
