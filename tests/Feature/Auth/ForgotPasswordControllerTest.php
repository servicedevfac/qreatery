<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_sends_a_reset_link_to_an_existing_user(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'awa@example.com']);

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'AWA@EXAMPLE.COM'])
            ->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_receives_the_same_generic_response(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'unknown@example.com'])
            ->assertOk()
            ->assertJsonPath('message', 'Si cette adresse est enregistrée, un lien de réinitialisation a été envoyé.');

        Notification::assertNothingSent();
    }
}
