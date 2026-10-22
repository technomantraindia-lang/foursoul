<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Filament\Pages\Auth\AdminLogin;
use App\Models\Admin;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Prompt 2B (25): Admin authentication feature tests.
 *
 * Tests:
 * - active Admin can authenticate
 * - invalid credentials rejected
 * - suspended Admin denied
 * - disabled Admin denied
 * - Customer User cannot access /admin
 * - Admin logout works
 * - Admin UUID generated
 * - Admin password is hashed
 * - Admin login updates last_login_at
 * - Admin login updates last_login_ip
 * - Admin password reset is isolated from Customer password reset
 */
class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_can_authenticate(): void
    {
        $admin = Admin::factory()->active()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->followingRedirects()
            ->get('/admin');

        $response->assertSuccessful();
    }

    public function test_invalid_credentials_rejected(): void
    {
        Admin::factory()->active()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test(AdminLogin::class)
            ->fillForm([
                'email' => 'admin@example.com',
                'password' => 'wrongpassword',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest('admin');
    }

    public function test_suspended_admin_denied(): void
    {
        $admin = Admin::factory()->suspended()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin');

        $response->assertForbidden();
    }

    public function test_disabled_admin_denied(): void
    {
        $admin = Admin::factory()->disabled()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin');

        $response->assertForbidden();
    }

    public function test_customer_user_cannot_access_admin_panel(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->get('/admin');

        // Customer authenticated but not admin - should be redirected to admin login
        $response->assertStatus(302);
        $this->assertGuest('admin');
    }

    public function test_admin_logout_works(): void
    {
        $admin = Admin::factory()->active()->create();

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/logout');

        $response->assertStatus(302);
        $this->assertGuest('admin');
    }

    public function test_admin_uuid_generated(): void
    {
        $admin = Admin::factory()->create();

        $this->assertNotNull($admin->uuid);
        $this->assertIsString($admin->uuid);
        $this->assertEquals(36, strlen($admin->uuid)); // Standard UUID length
    }

    public function test_admin_password_is_hashed(): void
    {
        $admin = Admin::factory()->create([
            'password' => 'plaintextpassword',
        ]);

        $this->assertNotEquals('plaintextpassword', $admin->password);
        $this->assertTrue(str_starts_with($admin->password, '$2y$')); // bcrypt
    }

    public function test_admin_login_updates_last_login_at(): void
    {
        $admin = Admin::factory()->active()->create([
            'password' => bcrypt('password123'),
            'last_login_at' => null,
            'last_login_ip' => null,
        ]);

        event(new Login('admin', $admin, false));

        $admin->refresh();

        $this->assertNotNull($admin->last_login_at);
        $this->assertNotNull($admin->last_login_ip);
    }

    public function test_admin_login_updates_last_login_ip(): void
    {
        $admin = Admin::factory()->active()->create([
            'password' => bcrypt('password123'),
            'last_login_at' => null,
            'last_login_ip' => null,
        ]);

        event(new Login('admin', $admin, false));

        $admin->refresh();

        $this->assertNotNull($admin->last_login_ip);
        $this->assertIsString($admin->last_login_ip);
    }

    public function test_admin_password_reset_is_isolated_from_customer_password_reset(): void
    {
        Notification::fake();

        Admin::factory()->active()->create([
            'email' => 'shared@example.com',
            'password' => bcrypt('adminpass'),
        ]);

        \App\Models\User::factory()->create([
            'email' => 'shared@example.com',
            'password' => bcrypt('userpass'),
        ]);

        $adminBroker = Password::broker('admins');
        $customerBroker = Password::broker('users');

        $this->assertNotSame($adminBroker, $customerBroker);

        $this->assertSame(Password::RESET_LINK_SENT, $adminBroker->sendResetLink(['email' => 'shared@example.com']));
        $this->assertSame(Password::RESET_LINK_SENT, $customerBroker->sendResetLink(['email' => 'shared@example.com']));

        $this->assertDatabaseHas('admin_password_reset_tokens', [
            'email' => 'shared@example.com',
        ]);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'shared@example.com',
        ]);
    }
}