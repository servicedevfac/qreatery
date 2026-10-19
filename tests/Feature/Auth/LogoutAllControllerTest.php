<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LogoutAllControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_logout_all_revokes_tokens_and_ends_current_session(): void
    {
        $user = User::factory()->create(['password' => 'SecretPass123']);
        $user->createToken('mobile');
        $user->createToken('tablet');

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'SecretPass123',
        ])->assertOk();
        $this->postJson('/api/v1/auth/logout-all')->assertNoContent();

        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
