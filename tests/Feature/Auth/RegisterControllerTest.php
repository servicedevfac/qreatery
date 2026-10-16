<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_payload_creates_authenticated_user_and_returns_201(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Awa Koné',
            'email' => 'AWA@EXAMPLE.COM',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Awa Koné')
            ->assertJsonPath('data.email', 'awa@example.com')
            ->assertJsonMissingPath('data.password');
        $user = User::where('email', 'awa@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('SecretPass123', $user->password));
    }

    public function test_returns_422_when_email_is_already_registered(): void
    {
        User::factory()->create(['email' => 'awa@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Autre Awa',
            'email' => 'AWA@example.com',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Cette adresse e-mail est déjà utilisée.');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_returns_422_when_password_is_too_weak(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Awa Koné',
            'email' => 'awa@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_ignores_unexpected_account_status_field(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Awa Koné',
            'email' => 'awa@example.com',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
            'is_active' => false,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.is_active', true);
        $this->assertDatabaseHas('users', [
            'email' => 'awa@example.com',
            'is_active' => true,
        ]);
    }
}
