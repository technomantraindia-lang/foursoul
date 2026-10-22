<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AdminRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Prompt 2C (§3): RBAC seeder — single source of truth is AdminRole.
 *
 * Idempotent: uses firstOrCreate so it can be re-run safely on every
 * deploy. Creates all permissions and roles on the dedicated `admin`
 * guard, then syncs each role's permission set. Never touches the
 * customer `web` guard.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'admin';

        // 0) Reset Spatie's permission cache first — without this, a re-run
        //    after code changes could be served stale cached permissions.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1) Permissions — create any missing ones, never delete existing
        //    rows (removals are deliberate code changes, not seed accidents).
        foreach (AdminRole::allPermissions() as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => $guard],
            );
        }

        // 2) Roles + permission sync.
        foreach (AdminRole::cases() as $roleCase) {
            /** @var Role $role */
            $role = Role::firstOrCreate(
                ['name' => $roleCase->value, 'guard_name' => $guard],
            );

            // syncPermissions replaces the role's permission set with the
            // exact definition in AdminRole — keeps DB aligned with code.
            $role->syncPermissions($roleCase->permissions());
        }
    }
}