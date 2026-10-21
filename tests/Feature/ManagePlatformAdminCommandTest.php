<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ManagePlatformAdminCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_grants_and_revokes_platform_admin_access(): void
    {
        $user = User::factory()->create(['email' => 'admin@qreatery.test']);

        $this->artisan('platform:admin', ['email' => $user->email])
            ->assertSuccessful();
        $this->assertTrue($user->refresh()->is_platform_admin);

        $this->artisan('platform:admin', ['email' => $user->email, '--revoke' => true])
            ->assertSuccessful();
        $this->assertFalse($user->refresh()->is_platform_admin);
    }

    public function test_it_fails_for_unknown_user(): void
    {
        $this->artisan('platform:admin', ['email' => 'unknown@qreatery.test'])
            ->assertFailed();
    }
}
