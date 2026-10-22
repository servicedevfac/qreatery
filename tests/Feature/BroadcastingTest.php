<?php

namespace Tests\Feature;

use App\Events\AssistanceRequested;
use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Models\AssistanceRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastingTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['broadcasting.default' => 'reverb']);
        require base_path('routes/channels.php');
    }

    public function test_active_restaurant_member_can_authorize_on_private_restaurant_channel(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($user)->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/broadcasting/auth', [
            'channel_name' => 'private-restaurants.'.$restaurant->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['auth']);
    }

    public function test_user_from_another_restaurant_is_forbidden_on_private_restaurant_channel(): void
    {
        $user = User::factory()->create();
        $restaurant1 = Restaurant::factory()->create();
        $restaurant2 = Restaurant::factory()->create();

        RestaurantMembership::factory()->owner()->for($restaurant1)->for($user)->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/broadcasting/auth', [
            'channel_name' => 'private-restaurants.'.$restaurant2->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertForbidden();
    }

    public function test_inactive_or_revoked_member_is_forbidden_on_private_restaurant_channel(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        RestaurantMembership::factory()->for($restaurant)->for($user)->create([
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->post('/broadcasting/auth', [
            'channel_name' => 'private-restaurants.'.$restaurant->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertForbidden();
    }

    public function test_guest_cannot_authorize_on_private_restaurant_channel(): void
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post('/broadcasting/auth', [
            'channel_name' => 'private-restaurants.'.$restaurant->id,
            'socket_id' => '1234.5678',
        ]);

        // Web middleware with Authenticate will either redirect or return 403
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403], true));
    }

    public function test_order_created_event_channels_name_and_payload(): void
    {
        $restaurant = Restaurant::factory()->create();
        $order = Order::factory()->for($restaurant)->create([
            'number' => 'CMD-2026-TEST',
            'mode' => 'dine_in',
            'preparation_status' => 'pending',
            'total_minor' => 2450,
            'currency' => 'EUR',
        ]);

        $event = new OrderCreated($order);

        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-restaurants.'.$restaurant->id, $channels[0]->name);

        $this->assertSame('orders.created', $event->broadcastAs());

        $payload = $event->broadcastWith();
        $this->assertSame($order->id, $payload['id']);
        $this->assertSame('CMD-2026-TEST', $payload['number']);
        $this->assertSame('dine_in', $payload['mode']);
        $this->assertSame('pending', $payload['preparation_status']);
        $this->assertSame(2450, $payload['total_minor']);
        $this->assertSame('EUR', $payload['currency']);
        $this->assertArrayHasKey('created_at', $payload);
    }

    public function test_order_status_updated_event_channels_name_and_payload(): void
    {
        $restaurant = Restaurant::factory()->create();
        $order = Order::factory()->for($restaurant)->create([
            'number' => 'CMD-STATUS-TEST',
            'preparation_status' => 'accepted',
            'tracking_token' => str_repeat('a', 64),
        ]);

        $event = new OrderStatusUpdated($order, 'pending');

        $channels = $event->broadcastOn();
        $this->assertCount(2, $channels);

        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-restaurants.'.$restaurant->id, $channels[0]->name);

        $this->assertInstanceOf(Channel::class, $channels[1]);
        $this->assertSame('orders.'.str_repeat('a', 64), $channels[1]->name);

        $this->assertSame('orders.status.updated', $event->broadcastAs());

        $payload = $event->broadcastWith();
        $this->assertSame($order->id, $payload['id']);
        $this->assertSame('CMD-STATUS-TEST', $payload['number']);
        $this->assertSame('pending', $payload['from_status']);
        $this->assertSame('accepted', $payload['to_status']);
        $this->assertArrayHasKey('accepted_at', $payload);
        $this->assertArrayHasKey('ready_at', $payload);
        $this->assertArrayHasKey('completed_at', $payload);
        $this->assertArrayHasKey('cancelled_at', $payload);
        $this->assertArrayHasKey('updated_at', $payload);
    }

    public function test_assistance_requested_event_channels_name_and_payload(): void
    {
        $restaurant = Restaurant::factory()->create();
        $table = RestaurantTable::factory()->for($restaurant)->create();
        $assistanceRequest = AssistanceRequest::factory()->for($restaurant)->for($table)->create([
            'type' => 'waiter',
            'status' => 'pending',
        ]);

        $event = new AssistanceRequested($assistanceRequest);

        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-restaurants.'.$restaurant->id, $channels[0]->name);

        $this->assertSame('assistance.requested', $event->broadcastAs());

        $payload = $event->broadcastWith();
        $this->assertSame($assistanceRequest->id, $payload['id']);
        $this->assertSame('waiter', $payload['type']);
        $this->assertSame('pending', $payload['status']);
        $this->assertSame($table->id, $payload['restaurant_table_id']);
        $this->assertArrayHasKey('created_at', $payload);
    }

    public function test_order_status_updated_dispatched_on_successful_transition(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $order = Order::factory()->for($restaurant)->create(['preparation_status' => 'pending']);

        $this->actingAs($owner)
            ->patchJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/status", [
                'status' => 'accepted',
            ])
            ->assertOk();

        Event::assertDispatched(OrderStatusUpdated::class, function (OrderStatusUpdated $event) use ($order): bool {
            return $event->order->id === $order->id && $event->fromStatus === 'pending' && $event->order->preparation_status === 'accepted';
        });
    }

    public function test_order_status_updated_not_dispatched_on_invalid_transition(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $order = Order::factory()->for($restaurant)->create(['preparation_status' => 'pending']);

        $this->actingAs($owner)
            ->patchJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/status", [
                'status' => 'ready', // Invalid transition from pending
            ])
            ->assertUnprocessable();

        Event::assertNotDispatched(OrderStatusUpdated::class);
    }

    public function test_order_created_dispatched_on_order_creation(): void
    {
        Event::fake([OrderCreated::class]);

        $restaurant = Restaurant::factory()->create([
            'status' => 'active',
            'slug' => 'test-restau-broadcast',
            'settings' => ['order_modes' => ['dine_in', 'takeaway']],
        ]);
        $category = Category::factory()->for($restaurant)->create();
        $product = Product::factory()->for($restaurant)->for($category)->create([
            'is_available' => true,
            'price_minor' => 1200,
        ]);

        $response = $this->postJson('/api/v1/public/restaurants/test-restau-broadcast/orders', [
            'mode' => 'takeaway',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertCreated();

        Event::assertDispatched(OrderCreated::class, function (OrderCreated $event) use ($restaurant): bool {
            return $event->order->restaurant_id === $restaurant->id && $event->order->mode === 'takeaway';
        });
    }
}
