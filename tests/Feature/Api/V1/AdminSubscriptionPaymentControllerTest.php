<?php

namespace Tests\Feature\Api\V1;

use App\Models\RestaurantMembership;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Notifications\SubscriptionPaymentFailedNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminSubscriptionPaymentControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_record_successful_subscription_payment(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $subscription = Subscription::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/subscriptions/{$subscription->id}/payments", [
            'amount_minor' => 2500000,
            'currency' => 'xof',
            'method' => 'mobile_money',
            'transaction_reference' => 'OM-2026-0001',
            'idempotency_key' => 'subscription-payment-0001',
            'status' => 'succeeded',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.amount_minor', 2500000)
            ->assertJsonPath('data.currency', 'XOF')
            ->assertJsonPath('data.status', 'succeeded')
            ->assertJsonPath('data.recorder.id', $admin->id);
        $this->assertNotNull(SubscriptionPayment::query()->firstOrFail()->paid_at);
    }

    public function test_idempotency_key_does_not_create_duplicate_payment(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $subscription = Subscription::factory()->create();
        $payload = [
            'amount_minor' => 1500000,
            'currency' => 'XOF',
            'method' => 'bank_transfer',
            'transaction_reference' => 'BANK-0002',
            'idempotency_key' => 'subscription-payment-0002',
            'status' => 'succeeded',
        ];

        $first = $this->actingAs($admin)->postJson("/api/v1/admin/subscriptions/{$subscription->id}/payments", $payload);
        $second = $this->actingAs($admin)->postJson("/api/v1/admin/subscriptions/{$subscription->id}/payments", $payload);

        $first->assertCreated();
        $second->assertCreated()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertDatabaseCount('subscription_payments', 1);
    }

    public function test_failed_payment_requires_failure_reason(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $subscription = Subscription::factory()->create();

        $this->actingAs($admin)->postJson("/api/v1/admin/subscriptions/{$subscription->id}/payments", [
            'amount_minor' => 1500000,
            'currency' => 'XOF',
            'method' => 'mobile_money',
            'idempotency_key' => 'subscription-payment-0003',
            'status' => 'failed',
        ])->assertUnprocessable()->assertJsonValidationErrors('failure_reason');
    }

    public function test_admin_can_list_subscription_payments(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $subscription = Subscription::factory()->create();
        $payment = SubscriptionPayment::factory()->for($subscription)->create([
            'amount_minor' => 3500000,
            'status' => 'succeeded',
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/subscriptions/{$subscription->id}/payments")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $payment->id)
            ->assertJsonPath('data.0.amount_minor', 3500000);
    }

    public function test_failed_payment_immediately_alerts_restaurant_and_platform_admins(): void
    {
        Notification::fake();
        $admin = User::factory()->platformAdmin()->create();
        $owner = User::factory()->create();
        $subscription = Subscription::factory()->create();
        RestaurantMembership::factory()->owner()->for($subscription->restaurant)->for($owner)->create();

        $this->actingAs($admin)->postJson("/api/v1/admin/subscriptions/{$subscription->id}/payments", [
            'amount_minor' => 1500000,
            'currency' => 'XOF',
            'method' => 'mobile_money',
            'idempotency_key' => 'subscription-payment-failed-alert',
            'status' => 'failed',
            'failure_reason' => 'Solde insuffisant',
        ])->assertCreated();

        Notification::assertSentTo([$owner, $admin], SubscriptionPaymentFailedNotification::class);
        $this->assertNotNull(SubscriptionPayment::query()->firstOrFail()->failure_notified_at);
    }
}
