<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use App\RestaurantRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CentralLoginPortalTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_access_central_login_portal(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Connexion à votre espace')
            ->assertSee('Adresse Email')
            ->assertSee('Mot de passe')
            ->assertSee('Se connecter');
    }

    public function test_super_admin_is_redirected_to_super_admin_portal(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@qreatery.test',
            'password' => 'SecretPass123!',
        ]);
        $admin->forceFill(['is_platform_admin' => true])->save();

        $response = $this->post('/login', [
            'email' => 'admin@qreatery.test',
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect('/?view=admin');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_restaurant_owner_is_redirected_to_restaurant_management(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@qreatery.test',
            'password' => 'SecretPass123!',
        ]);
        $restaurant = Restaurant::factory()->create(['slug' => 'le-palais-gourmand']);
        RestaurantMembership::create([
            'user_id' => $owner->id,
            'restaurant_id' => $restaurant->id,
            'role' => RestaurantRole::Owner,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'owner@qreatery.test',
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect('/?view=restaurant&restaurant=le-palais-gourmand');
        $this->assertAuthenticatedAs($owner);
    }

    public function test_cashier_is_redirected_to_pos(): void
    {
        $cashier = User::factory()->create([
            'email' => 'cashier@qreatery.test',
            'password' => 'SecretPass123!',
        ]);
        $restaurant = Restaurant::factory()->create(['slug' => 'pos-bistrot']);
        RestaurantMembership::create([
            'user_id' => $cashier->id,
            'restaurant_id' => $restaurant->id,
            'role' => RestaurantRole::Cashier,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'cashier@qreatery.test',
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect('/?view=pos&restaurant=pos-bistrot');
        $this->assertAuthenticatedAs($cashier);
    }

    public function test_cook_is_redirected_to_kds(): void
    {
        $cook = User::factory()->create([
            'email' => 'cook@qreatery.test',
            'password' => 'SecretPass123!',
        ]);
        $restaurant = Restaurant::factory()->create(['slug' => 'kds-resto']);
        RestaurantMembership::create([
            'user_id' => $cook->id,
            'restaurant_id' => $restaurant->id,
            'role' => RestaurantRole::Staff,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'cook@qreatery.test',
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect('/?view=kds&restaurant=kds-resto');
        $this->assertAuthenticatedAs($cook);
    }

    public function test_waiter_is_redirected_to_orders(): void
    {
        $waiter = User::factory()->create([
            'email' => 'waiter@qreatery.test',
            'password' => 'SecretPass123!',
        ]);
        $restaurant = Restaurant::factory()->create(['slug' => 'service-resto']);
        RestaurantMembership::create([
            'user_id' => $waiter->id,
            'restaurant_id' => $restaurant->id,
            'role' => RestaurantRole::Staff,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'waiter@qreatery.test',
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect('/?view=restaurant&section=orders&restaurant=service-resto');
        $this->assertAuthenticatedAs($waiter);
    }

    public function test_json_login_returns_correct_role_redirect_url(): void
    {
        $cook = User::factory()->create([
            'email' => 'cook-test@test.com',
            'password' => 'Pass12345!',
        ]);
        $restaurant = Restaurant::factory()->create(['slug' => 'mon-resto']);
        RestaurantMembership::create([
            'user_id' => $cook->id,
            'restaurant_id' => $restaurant->id,
            'role' => RestaurantRole::Staff,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'cook-test@test.com',
            'password' => 'Pass12345!',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.redirect_url', '/?view=kds&restaurant=mon-resto');
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
