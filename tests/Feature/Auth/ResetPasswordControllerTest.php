<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_token_resets_password_and_revokes_tokens(): void
    {
        $user = User::factory()->create(['email' => 'awa@example.com']);
        $user->createToken('mobile');
        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'AWA@EXAMPLE.COM',
            'token' => $token,
            'password' => 'NewSecretPass123',
            'password_confirmation' => 'NewSecretPass123',
        ])->assertOk();

        $this->assertTrue(Hash::check('NewSecretPass123', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_token_is_rejected(): void
    {
        User::factory()->create(['email' => 'awa@example.com']);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'awa@example.com',
            'token' => 'invalid-token',
            'password' => 'NewSecretPass123',
            'password_confirmation' => 'NewSecretPass123',
        ])->assertUnprocessable();
    }
}
