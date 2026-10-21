<?php

namespace Tests\Feature\Api\V1;

use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminSubscriptionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_create_subscription_with_computed_expiry_and_history(): void
    {
        CarbonImmutable::setTestNow('2026-10-20 12:00:00');
        $admin = User::factory()->platformAdmin()->create();
        $restaurant = Restaurant::factory()->create();
        $plan = Plan::factory()->create(['billing_period' => 'month', 'billing_interval' => 1]);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/restaurants/{$restaurant->id}/subscriptions", [
            'plan_id' => $plan->id,
            'starts_at' => '2026-11-01 00:00:00',
            'grace_days' => 7,
            'notes' => 'Abonnement pilote',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.restaurant.id', $restaurant->id)
            ->assertJsonPath('data.plan.id', $plan->id)
            ->assertJsonPath('data.status', 'active');
        $this->assertDatabaseHas('subscriptions', [
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'notes' => 'Abonnement pilote',
        ]);
        $this->assertDatabaseHas('subscription_status_histories', [
            'actor_id' => $admin->id,
            'from_status' => null,
            'to_status' => 'active',
        ]);

        $subscription = Subscription::query()->firstOrFail();
        $this->assertSame('2026-12-01', $subscription->ends_at->toDateString());
        $this->assertSame('2026-12-08', $subscription->grace_ends_at->toDateString());
    }

    public function test_overlapping_active_subscription_is_rejected(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $restaurant = Restaurant::factory()->create();
        $plan = Plan::factory()->create();
        Subscription::factory()->for($restaurant)->for($plan)->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/restaurants/{$restaurant->id}/subscriptions", [
                'plan_id' => $plan->id,
                'starts_at' => now()->toDateTimeString(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('starts_at');
    }

    public function test_status_transition_is_validated_and_historized(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $subscription = Subscription::factory()->create(['status' => 'active']);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/subscriptions/{$subscription->id}/status", [
                'status' => 'suspended',
                'reason' => 'Paiement en attente',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'suspended');

        $this->assertDatabaseHas('subscription_status_histories', [
            'subscription_id' => $subscription->id,
            'from_status' => 'active',
            'to_status' => 'suspended',
            'reason' => 'Paiement en attente',
        ]);
    }

    public function test_cancelled_subscription_cannot_be_reactivated(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $subscription = Subscription::factory()->create(['status' => 'cancelled']);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/subscriptions/{$subscription->id}/status", [
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_subscription_from_another_restaurant_is_hidden(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $restaurant = Restaurant::factory()->create();
        $subscription = Subscription::factory()->create();

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/restaurants/{$restaurant->id}/subscriptions/{$subscription->id}")
            ->assertNotFound();
    }

    public function test_admin_can_list_and_show_restaurant_subscriptions(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $restaurant = Restaurant::factory()->create();
        $activeSubscription = Subscription::factory()->for($restaurant)->create(['status' => 'active']);
        Subscription::factory()->for($restaurant)->create(['status' => 'expired']);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/restaurants/{$restaurant->id}/subscriptions?status=active")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $activeSubscription->id);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/restaurants/{$restaurant->id}/subscriptions/{$activeSubscription->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $activeSubscription->id)
            ->assertJsonPath('data.restaurant.id', $restaurant->id)
            ->assertJsonPath('data.status', 'active');
    }
}
