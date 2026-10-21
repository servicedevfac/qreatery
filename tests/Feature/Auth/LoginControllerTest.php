<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_credentials_authenticate_user_and_return_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'awa@example.com',
            'password' => 'SecretPass123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => ' AWA@EXAMPLE.COM ',
            'password' => 'SecretPass123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'awa@example.com');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->refresh()->last_login_at);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'action' => 'auth.login',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);
    }

    public function test_returns_422_for_invalid_credentials_without_revealing_account_state(): void
    {
        User::factory()->create([
            'email' => 'awa@example.com',
            'password' => 'SecretPass123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'awa@example.com',
            'password' => 'WrongPassword123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Les identifiants fournis sont incorrects.');
        $this->assertGuest();
    }

    public function test_returns_422_for_disabled_user(): void
    {
        User::factory()->disabled()->create([
            'email' => 'awa@example.com',
            'password' => 'SecretPass123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'awa@example.com',
            'password' => 'SecretPass123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Les identifiants fournis sont incorrects.');
        $this->assertGuest();
    }

    public function test_returns_429_after_five_failed_attempts(): void
    {
        User::factory()->create([
            'email' => 'limited@example.com',
            'password' => 'SecretPass123',
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'limited@example.com',
                'password' => 'WrongPassword123',
            ])->assertUnprocessable();
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'limited@example.com',
            'password' => 'WrongPassword123',
        ]);

        $response->assertTooManyRequests();
    }
}
