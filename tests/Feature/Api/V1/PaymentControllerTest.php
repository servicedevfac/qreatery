<?php

namespace Tests\Feature\Api\V1;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_payments_are_idempotent_and_update_order_balance(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $order = Order::factory()->for($restaurant)->create(['total_minor' => 10000, 'subtotal_minor' => 10000]);

        $payload = [
            'amount_minor' => 4000,
            'method' => 'cash',
            'idempotency_key' => 'cash-payment-001',
        ];

        $first = $this->actingAs($owner)
            ->postJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/payments", $payload);
        $first->assertCreated()->assertJsonPath('data.amount_minor', 4000);

        $second = $this->actingAs($owner)
            ->postJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/payments", $payload);
        $second->assertCreated()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'partially_paid']);

        $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/payments", [
            'amount_minor' => 6000,
            'method' => 'mobile_money',
            'idempotency_key' => 'mobile-payment-002',
        ])->assertCreated();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_overpayment_returns_422_without_creating_payment(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $order = Order::factory()->for($restaurant)->create(['total_minor' => 5000, 'subtotal_minor' => 5000]);

        $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/payments", [
            'amount_minor' => 6000,
            'method' => 'cash',
            'idempotency_key' => 'overpayment-001',
        ])->assertUnprocessable()->assertJsonValidationErrors('amount_minor');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_owner_can_partially_refund_payment_once(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $order = Order::factory()->for($restaurant)->create(['total_minor' => 5000, 'subtotal_minor' => 5000]);
        $paymentResponse = $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/payments", [
            'amount_minor' => 5000,
            'method' => 'cash',
            'idempotency_key' => 'charge-refund-test',
        ]);
        $paymentId = $paymentResponse->json('data.id');

        $payload = ['amount_minor' => 2000, 'idempotency_key' => 'refund-test-001', 'reason' => 'Article annulé'];
        $first = $this->actingAs($owner)
            ->postJson("/api/v1/restaurants/{$restaurant->id}/payments/{$paymentId}/refunds", $payload);
        $first->assertCreated()->assertJsonPath('data.type', 'refund');

        $this->actingAs($owner)
            ->postJson("/api/v1/restaurants/{$restaurant->id}/payments/{$paymentId}/refunds", $payload)
            ->assertCreated()->assertJsonPath('data.id', $first->json('data.id'));

        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'partially_refunded']);
    }

    public function test_order_from_another_restaurant_is_hidden_on_payment(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $foreignOrder = Order::factory()->create();

        $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/orders/{$foreignOrder->id}/payments", [
            'amount_minor' => 1000,
            'method' => 'cash',
            'idempotency_key' => 'foreign-order-payment',
        ])->assertNotFound();
    }
}
