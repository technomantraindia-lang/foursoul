<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_user_sees_status_page_on_dashboard(): void
    {
        $user = User::factory()->suspended()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('account.status'));

        $response = $this->actingAs($user)->get(route('account.status'));

        $response->assertStatus(200);
        $response->assertSee('Account suspended', escape: false);
        // Internal admin notes must never be exposed.
        $response->assertDontSee('Internal note');
    }

    public function test_blocked_user_is_logged_out_and_denied_direct_url_access(): void
    {
        $user = User::factory()->blocked()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_closed_user_is_logged_out_and_denied_direct_url_access(): void
    {
        $user = User::factory()->closed()->create();

        $response = $this->actingAs($user)->get('/account/profile');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_blocked_user_cannot_access_any_protected_route(): void
    {
        $user = User::factory()->blocked()->create();

        foreach (['/dashboard', '/account/profile', '/account/security', '/account/preferences'] as $uri) {
            $response = $this->actingAs($user)->get($uri);

            $response->assertRedirect('/login');
            $this->assertGuest();
        }
    }

    public function test_active_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create();

        foreach (['/dashboard', '/account/profile', '/account/security', '/account/preferences'] as $uri) {
            $response = $this->actingAs($user)->get($uri);

            $response->assertStatus(200);
        }
    }

    public function test_suspended_user_cannot_update_profile(): void
    {
        $user = User::factory()->suspended()->create();

        $response = $this->actingAs($user)->put('/account/profile', [
            'full_name' => 'Updated Name',
            'preferred_language' => 'en',
            'timezone' => 'Asia/Dubai',
        ]);

        $response->assertRedirect(route('account.status'));
        $this->assertNotSame('Updated Name', $user->fresh()->full_name);
    }

    public function test_suspended_user_cannot_change_password(): void
    {
        $user = User::factory()->suspended()->create([
            'password' => 'CurrentPass123!',
        ]);

        $response = $this->actingAs($user)->put('/account/security/password', [
            'current_password' => 'CurrentPass123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('account.status'));
    }

    public function test_guest_cannot_view_account_status_page(): void
    {
        $response = $this->get(route('account.status'));

        $response->assertRedirect('/login');
    }

    public function test_status_labels_are_human_readable(): void
    {
        $this->assertSame('Active', UserStatus::Active->label());
        $this->assertSame('Suspended', UserStatus::Suspended->label());
        $this->assertSame('Blocked', UserStatus::Blocked->label());
        $this->assertSame('Closed', UserStatus::Closed->label());
    }
}