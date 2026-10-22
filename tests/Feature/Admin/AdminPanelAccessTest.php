<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prompt 2B (26): Admin panel access tests.
 *
 * Tests:
 * - /admin resolves correctly
 * - active Admin reaches dashboard
 * - unauthenticated request reaches Admin login
 * - Customer session does not grant Admin panel access
 */
class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_route_resolves_correctly(): void
    {
        $response = $this->get('/admin');

        // Should redirect to admin login
        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    public function test_active_admin_reaches_dashboard(): void
    {
        $admin = Admin::factory()->active()->create();

        $response = $this->actingAs($admin, 'admin')
            ->followingRedirects()
            ->get('/admin');

        $response->assertSuccessful();
    }

    public function test_unauthenticated_request_reaches_admin_login(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Administration Portal', escape: false);
    }

    public function test_customer_session_does_not_grant_admin_panel_access(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->get('/admin');

        // Customer session uses the web guard and must not authenticate as admin.
        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_admin_login_page_accessible_without_authentication(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    public function test_suspended_admin_cannot_access_dashboard(): void
    {
        $admin = Admin::factory()->suspended()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin');

        $response->assertForbidden();
    }

    public function test_disabled_admin_cannot_access_dashboard(): void
    {
        $admin = Admin::factory()->disabled()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin');

        $response->assertForbidden();
    }

    public function test_deleted_admin_cannot_access_dashboard(): void
    {
        $admin = Admin::factory()->active()->create();
        $admin->delete();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin');

        $response->assertForbidden();
    }
}