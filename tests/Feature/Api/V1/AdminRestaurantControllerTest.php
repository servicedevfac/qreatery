<?php

namespace Tests\Feature\Api\V1;

use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\User;
use App\RestaurantRole;
use App\SubscriptionStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminRestaurantControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_regular_user_cannot_create_restaurant_via_admin_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/admin/restaurants', [
            'name' => 'Tentative Non Autorisée',
        ]);

        $response->assertForbidden();
    }

    public function test_platform_admin_can_create_restaurant_with_auto_slug(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/restaurants', [
            'name' => 'L\'Oasis Tropicale',
            'phone' => '+225 07 11 22 33',
            'email' => 'contact@oasis.test',
            'currency' => 'XOF',
            'timezone' => 'Africa/Abidjan',
            'status' => 'active',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'L\'Oasis Tropicale')
            ->assertJsonPath('data.currency', 'XOF')
            ->assertJsonPath('data.status', 'active');

        $restaurant = Restaurant::query()->where('name', 'L\'Oasis Tropicale')->firstOrFail();
        $this->assertStringStartsWith('loasis-tropicale', $restaurant->slug);

        // Le Super-Admin est rattaché en tant que propriétaire par défaut
        $this->assertTrue($restaurant->memberships()->where('user_id', $admin->id)->where('role', RestaurantRole::Owner)->exists());

        // L'audit log a été enregistré
        $this->assertTrue(
            AuditLog::query()
                ->where('action', 'restaurant.created')
                ->where('restaurant_id', $restaurant->id)
                ->where('actor_id', $admin->id)
                ->exists()
        );
    }

    public function test_platform_admin_can_create_restaurant_with_custom_owner_and_slug(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/restaurants', [
            'name' => 'Le Gourmet Abidjan',
            'slug' => 'le-gourmet-abidjan',
            'owner_name' => 'Patrice Danho',
            'owner_email' => 'patrice@gourmet.test',
            'owner_password' => 'secret123',
            'address' => 'Cocody Rue des Jardins',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'le-gourmet-abidjan')
            ->assertJsonPath('data.address', 'Cocody Rue des Jardins');

        $restaurant = Restaurant::query()->where('slug', 'le-gourmet-abidjan')->firstOrFail();

        // Le nouvel utilisateur propriétaire a bien été créé
        $owner = User::query()->where('email', 'patrice@gourmet.test')->firstOrFail();
        $this->assertSame('Patrice Danho', $owner->name);

        // Il a le rôle Owner
        $this->assertTrue($restaurant->memberships()->where('user_id', $owner->id)->where('role', RestaurantRole::Owner)->exists());
    }

    public function test_platform_admin_can_create_restaurant_with_initial_plan(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $plan = Plan::factory()->create([
            'name' => 'Formule Starter',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/restaurants', [
            'name' => 'Bistrot Express',
            'plan_id' => $plan->id,
        ]);

        $response->assertCreated();

        $restaurant = Restaurant::query()->where('name', 'Bistrot Express')->firstOrFail();
        $this->assertTrue(
            $restaurant->subscriptions()
                ->where('plan_id', $plan->id)
                ->where('status', SubscriptionStatus::Active)
                ->exists()
        );
    }

    public function test_validation_prevents_duplicate_slug_or_short_name(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        Restaurant::factory()->create(['slug' => 'resto-existant']);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/restaurants', [
            'name' => 'A', // Trop court
            'slug' => 'resto-existant', // Déjà pris
            'email' => 'email-invalide',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'slug', 'email']);
    }
}
