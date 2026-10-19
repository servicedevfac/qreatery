<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_change_password_and_tokens_are_revoked(): void
    {
        $user = User::factory()->create(['password' => 'OldSecretPass123']);
        $user->createToken('mobile');

        $this->actingAs($user)->patchJson('/api/v1/auth/password', [
            'current_password' => 'OldSecretPass123',
            'password' => 'NewSecretPass123',
            'password_confirmation' => 'NewSecretPass123',
        ])->assertNoContent();

        $this->assertTrue(Hash::check('NewSecretPass123', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'OldSecretPass123']);

        $this->actingAs($user)->patchJson('/api/v1/auth/password', [
            'current_password' => 'WrongSecretPass123',
            'password' => 'NewSecretPass123',
            'password_confirmation' => 'NewSecretPass123',
        ])->assertUnprocessable()->assertJsonValidationErrors(['current_password']);
    }
}
