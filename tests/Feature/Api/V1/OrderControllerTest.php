<?php

namespace Tests\Feature\Api\V1;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_list_show_and_transition_order_status(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $order = Order::factory()->for($restaurant)->create();

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/orders")
            ->assertOk()->assertJsonCount(1, 'data');

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}")
            ->assertOk()->assertJsonPath('data.number', $order->number);

        $this->actingAs($owner)
            ->patchJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/status", ['status' => 'accepted'])
            ->assertOk()->assertJsonPath('data.preparation_status', 'accepted');

        $this->actingAs($owner)
            ->patchJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/status", ['status' => 'preparing'])
            ->assertOk()->assertJsonPath('data.preparation_status', 'preparing');

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'accepted',
            'to_status' => 'preparing',
            'user_id' => $owner->id,
        ]);
        Event::assertDispatched(OrderStatusUpdated::class, 2);
    }

    public function test_invalid_status_transition_returns_422(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $order = Order::factory()->for($restaurant)->create(['preparation_status' => 'pending']);

        $this->actingAs($owner)
            ->patchJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/status", ['status' => 'ready'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'preparation_status' => 'pending']);
    }

    public function test_order_from_another_restaurant_is_hidden(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $foreignOrder = Order::factory()->create();

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/orders/{$foreignOrder->id}")
            ->assertNotFound();
    }
}
