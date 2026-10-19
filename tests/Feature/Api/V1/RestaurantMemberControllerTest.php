<?php

namespace Tests\Feature\Api\V1;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use App\RestaurantRole;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RestaurantMemberControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_invite_update_deactivate_and_reactivate_a_member(): void
    {
        Notification::fake();
        [$owner, $restaurant] = $this->ownerContext();

        $create = $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/members", [
            'name' => 'Moussa Diallo',
            'email' => 'MOUSSA@EXAMPLE.COM',
            'role' => RestaurantRole::Manager->value,
            'permissions' => ['orders.view'],
        ]);

        $create->assertCreated()->assertJsonPath('data.user.email', 'moussa@example.com');
        $member = RestaurantMembership::findOrFail($create->json('data.id'));
        Notification::assertSentTo($member->user, ResetPassword::class);

        $this->actingAs($owner)->patchJson("/api/v1/restaurants/{$restaurant->id}/members/{$member->id}", [
            'role' => RestaurantRole::Cashier->value,
            'permissions' => ['payments.manage'],
        ])->assertOk()->assertJsonPath('data.role', 'cashier');

        $this->actingAs($owner)
            ->deleteJson("/api/v1/restaurants/{$restaurant->id}/members/{$member->id}")
            ->assertNoContent();
        $this->assertFalse($member->fresh()->is_active);
        $this->assertFalse($member->user->fresh()->is_active);

        $this->actingAs($owner)
            ->postJson("/api/v1/restaurants/{$restaurant->id}/members/{$member->id}/reactivation")
            ->assertOk()->assertJsonPath('data.is_active', true);
    }

    public function test_manager_without_permission_cannot_manage_members(): void
    {
        $manager = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->manager()->for($restaurant)->for($manager)->create();

        $this->actingAs($manager)
            ->postJson("/api/v1/restaurants/{$restaurant->id}/members", [
                'name' => 'Employé',
                'email' => 'employee@example.com',
                'role' => 'staff',
            ])->assertForbidden();
    }

    public function test_member_from_another_restaurant_is_hidden(): void
    {
        [$owner, $restaurant] = $this->ownerContext();
        $foreignMembership = RestaurantMembership::factory()->create();

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/members/{$foreignMembership->id}")
            ->assertNotFound();
    }

    /** @return array{User, Restaurant} */
    private function ownerContext(): array
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();

        return [$owner, $restaurant];
    }
}
