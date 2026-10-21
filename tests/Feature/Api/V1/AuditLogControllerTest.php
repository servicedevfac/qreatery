<?php

namespace Tests\Feature\Api\V1;

use App\Actions\RecordAuditLogAction;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_list_restaurant_audit_logs(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        app(RecordAuditLogAction::class)->handle($owner, 'restaurant.updated', $restaurant, $restaurant, [
            'field' => 'name',
        ]);

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/audit-logs?action=restaurant.updated")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.action', 'restaurant.updated')
            ->assertJsonPath('data.0.actor.id', $owner->id)
            ->assertJsonPath('data.0.metadata.field', 'name');
    }

    public function test_unrelated_user_cannot_view_restaurant_audit_logs(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->actingAs(User::factory()->create())
            ->getJson("/api/v1/restaurants/{$restaurant->id}/audit-logs")
            ->assertNotFound();
    }

    public function test_platform_admin_can_list_all_audit_logs(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $restaurant = Restaurant::factory()->create();
        app(RecordAuditLogAction::class)->handle($admin, 'restaurant.status_changed', $restaurant, $restaurant);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/audit-logs?restaurant_id={$restaurant->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.action', 'restaurant.status_changed');
    }
}
