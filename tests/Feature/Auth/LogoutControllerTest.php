<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_session_is_terminated_and_returns_204(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/auth/logout');

        $response->assertNoContent();
        $this->assertGuest('web');
    }

    public function test_returns_401_when_user_is_not_authenticated(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertUnauthorized();
    }

    public function test_bearer_token_is_revoked_and_returns_204(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device');

        $response = $this
            ->withToken($token->plainTextToken)
            ->postJson('/api/v1/auth/logout');

        $response->assertNoContent();
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}
