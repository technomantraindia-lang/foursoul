<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Prompt 2C: RBAC foundation tests.
 *
 * Tests:
 * - Seeder creates every role on the dedicated `admin` guard
 * - Seeder creates the complete permission catalogue
 * - Seeding is idempotent (safe to re-run)
 * - Each role holds exactly its enum-defined permissions
 * - Super Admin holds every permission explicitly
 * - Gate::before bypass: Super Admin passes any ability check,
 *   other admins are limited to assigned permissions
 * - Customer users are untouched by the admin Gate bypass
 */
class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure a clean Spatie permission cache per test
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_seeder_creates_all_roles_on_admin_guard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        foreach (AdminRole::cases() as $roleCase) {
            $this->assertTrue(
                Role::where('name', $roleCase->value)->where('guard_name', 'admin')->exists(),
                "Missing role [{$roleCase->value}] on the admin guard."
            );
        }
    }

    public function test_seeder_creates_full_permission_catalogue(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        foreach (AdminRole::allPermissions() as $permission) {
            $this->assertTrue(
                Permission::where('name', $permission)->where('guard_name', 'admin')->exists(),
                "Missing permission [{$permission}]."
            );
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame(count(AdminRole::allPermissions()), Permission::count());
        $this->assertSame(count(AdminRole::cases()), Role::count());
    }

    public function test_each_role_has_exactly_its_enum_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        foreach (AdminRole::cases() as $roleCase) {
            $role = Role::findByName($roleCase->value, 'admin');

            $this->assertEqualsCanonicalizing(
                $roleCase->permissions(),
                $role->permissions->pluck('name')->all(),
                "Role [{$roleCase->value}] permission mismatch."
            );
        }
    }

    public function test_super_admin_holds_every_permission_explicitly(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $superAdmin = Role::findByName(AdminRole::SuperAdmin->value, 'admin');

        $this->assertCount(count(AdminRole::allPermissions()), $superAdmin->permissions);
    }

    public function test_super_admin_passes_any_gate_check_via_before_bypass(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = Admin::factory()->create();
        $admin->assignRole(AdminRole::SuperAdmin->value);

        // Defined permissions pass through normal resolution…
        $this->assertTrue($admin->can('payments.refund'));
        $this->assertTrue($admin->can('admins.manage'));

        // …and even abilities that are not defined permissions at all
        // pass thanks to the Gate::before bypass (Prompt 2C §4).
        $this->assertTrue($admin->can('anything.undefined'));
    }

    public function test_suspended_super_admin_cannot_use_gate_bypass(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = Admin::factory()->suspended()->create();
        $admin->assignRole(AdminRole::SuperAdmin->value);

        // Gate::before denies non-active admins outright - even their
        // explicitly synced permissions never resolve (Prompt 2C §4).
        $this->assertFalse($admin->can('payments.refund'));
        $this->assertFalse($admin->can('admins.manage'));
        $this->assertFalse($admin->can('anything.undefined'));
    }

    public function test_disabled_super_admin_cannot_use_gate_bypass(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = Admin::factory()->disabled()->create();
        $admin->assignRole(AdminRole::SuperAdmin->value);

        $this->assertFalse($admin->can('payments.refund'));
        $this->assertFalse($admin->can('admins.manage'));
        $this->assertFalse($admin->can('anything.undefined'));
    }

    public function test_non_super_admin_is_limited_to_assigned_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $support = Admin::factory()->create();
        $support->assignRole(AdminRole::SupportAdmin->value);

        $this->assertTrue($support->can('customers.manage'));
        $this->assertTrue($support->can('reports.view'));

        $this->assertFalse($support->can('payments.refund'));
        $this->assertFalse($support->can('admins.manage'));
        $this->assertFalse($support->can('anything.undefined'));
    }

    public function test_customer_users_are_not_affected_by_admin_gate_bypass(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();

        // Gate::before returns null for non-Admin models, so customers
        // fall through to normal (denying) resolution.
        $this->assertFalse($user->can('customers.view'));
        $this->assertFalse($user->can('anything.undefined'));
    }

    public function test_no_rbac_rows_leak_onto_the_web_guard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame(0, Role::where('guard_name', 'web')->count());
        $this->assertSame(0, Permission::where('guard_name', 'web')->count());
    }
}