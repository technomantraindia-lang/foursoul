<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_accessible(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Reset your password', escape: false);
    }

    public function test_reset_link_is_sent_to_existing_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'john@example.com']);

        $response = $this->post('/forgot-password', ['email' => 'john@example.com']);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_link_request_does_not_reveal_unknown_email(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', ['email' => 'nobody@example.com']);

        // Same generic status to prevent email enumeration.
        $response->assertSessionHas('status');
        Notification::assertNothingSent();
    }

    public function test_reset_link_request_requires_valid_email(): void
    {
        $response = $this->post('/forgot-password', ['email' => 'not-an-email']);

        $response->assertSessionHasErrors('email');
    }

    public function test_reset_password_page_is_accessible_with_token(): void
    {
        $response = $this->get('/reset-password/test-token');

        $response->assertStatus(200);
        $response->assertSee('Choose a new password', escape: false);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'OldPassword123!',
        ]);

        $this->post('/forgot-password', ['email' => 'john@example.com']);

        $token = null;
        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            static function (ResetPassword $notification) use (&$token): bool {
                $token = $notification->token;

                return true;
            }
        );

        $this->assertNotNull($token);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'john@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/login');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
        $this->assertFalse(Hash::check('OldPassword123!', $user->password));
    }

    public function test_password_reset_rejects_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'john@example.com']);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => 'john@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_password_reset_rejects_mismatched_confirmation(): void
    {
        $response = $this->post('/reset-password', [
            'token' => 'some-token',
            'email' => 'john@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'Different123!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_reset_rejects_weak_password(): void
    {
        $user = User::factory()->create(['email' => 'john@example.com']);
        $token = Password::broker('users')->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_reset_rejects_unknown_email(): void
    {
        $response = $this->post('/reset-password', [
            'token' => 'some-token',
            'email' => 'nobody@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_broker_uses_users_provider(): void
    {
        $this->assertSame('users', config('auth.passwords.users.provider'));
        $this->assertSame(Password::class, config('auth.defaults.passwords') === 'users' ? Password::class : Password::class);
    }
}