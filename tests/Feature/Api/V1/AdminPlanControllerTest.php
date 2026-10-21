<?php

namespace Tests\Feature\Api\V1;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminPlanControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_regular_user_cannot_access_platform_plans(): void
    {
        $response = $this->actingAs(User::factory()->create())->getJson('/api/v1/admin/plans');

        $response->assertForbidden();
    }

    public function test_platform_admin_can_create_plan(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/plans', [
            'name' => 'Formule Pro',
            'price_minor' => 2500000,
            'currency' => 'xof',
            'billing_period' => 'month',
            'billing_interval' => 1,
            'limits' => ['employees' => 20],
            'features' => ['orders', 'reports', 'qr_codes'],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Formule Pro')
            ->assertJsonPath('data.slug', 'formule-pro')
            ->assertJsonPath('data.currency', 'XOF')
            ->assertJsonPath('data.features.1', 'reports');
        $this->assertDatabaseHas('plans', [
            'slug' => 'formule-pro',
            'price_minor' => 2500000,
        ]);
    }

    public function test_platform_admin_can_update_and_filter_plans(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $active = Plan::factory()->create(['name' => 'Essentiel', 'is_active' => true]);
        Plan::factory()->create(['name' => 'Ancien plan', 'is_active' => false]);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/plans/{$active->id}", ['price_minor' => 1800000])
            ->assertOk()
            ->assertJsonPath('data.price_minor', 1800000);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/plans?active=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id);
    }

    public function test_platform_admin_can_show_a_plan(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $plan = Plan::factory()->create([
            'name' => 'Formule Premium',
            'features' => ['orders', 'reports'],
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/plans/{$plan->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $plan->id)
            ->assertJsonPath('data.name', 'Formule Premium')
            ->assertJsonPath('data.features.1', 'reports');
    }
}
