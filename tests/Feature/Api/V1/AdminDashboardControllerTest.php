<?php

namespace Tests\Feature\Api\V1;

use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminDashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_returns_global_saas_metrics(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $activeRestaurant = Restaurant::factory()->create(['status' => 'active']);
        $suspendedRestaurant = Restaurant::factory()->create(['status' => 'suspended']);
        $activeSubscription = Subscription::factory()->for($activeRestaurant)->create(['status' => 'active']);
        $expiredSubscription = Subscription::factory()->for($suspendedRestaurant)->create(['status' => 'expired']);
        SubscriptionPayment::factory()->for($activeSubscription)->create(['status' => 'succeeded', 'amount_minor' => 2000000, 'paid_at' => now()]);
        SubscriptionPayment::factory()->for($expiredSubscription)->create(['status' => 'failed', 'amount_minor' => 9999999, 'paid_at' => null]);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.restaurants.total', 2)
            ->assertJsonPath('data.restaurants.active', 1)
            ->assertJsonPath('data.restaurants.suspended', 1)
            ->assertJsonPath('data.subscriptions.active', 1)
            ->assertJsonPath('data.subscriptions.expired', 1)
            ->assertJsonPath('data.subscription_revenue.total_minor', 2000000);
    }

    public function test_admin_can_suspend_and_reactivate_restaurant(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $restaurant = Restaurant::factory()->create(['status' => 'active']);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/restaurants/{$restaurant->id}/status", [
                'status' => 'suspended',
                'reason' => 'Abonnement expiré',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'suspended');

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'restaurant_id' => $restaurant->id,
            'action' => 'restaurant.status_changed',
        ]);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/restaurants/{$restaurant->id}/status", [
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'active');
    }

    public function test_admin_can_filter_restaurants(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $restaurant = Restaurant::factory()->create([
            'name' => 'Qreatery Abidjan',
            'status' => 'active',
        ]);
        Restaurant::factory()->create([
            'name' => 'Restaurant suspendu',
            'status' => 'suspended',
        ]);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/restaurants?status=active&search=Qreatery')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $restaurant->id)
            ->assertJsonPath('data.0.name', 'Qreatery Abidjan');
    }
}
