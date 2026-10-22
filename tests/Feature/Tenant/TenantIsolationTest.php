<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantApiKey;
use App\Models\TenantDomain;
use App\Models\Webhook;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_a_cannot_read_tenant_b_branches_domains_keys_or_webhooks(): void
    {
        $tenantA = $this->makeTenant('Tenant A');
        $tenantB = $this->makeTenant('Tenant B');

        $branchB = Branch::query()->create([
            'tenant_id' => $tenantB->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'B HQ',
        ]);
        $domainB = TenantDomain::query()->create([
            'tenant_id' => $tenantB->id,
            'domain' => 'b.example.test',
        ]);
        $keyB = TenantApiKey::query()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'B key',
            'key_prefix' => 'fs_b_',
            'key_hash' => hash('sha256', 'secret-b'),
            'scopes' => ['courses.read'],
        ]);
        $hookB = Webhook::query()->create([
            'tenant_id' => $tenantB->id,
            'url' => 'https://b.example.test/hook',
            'events' => ['order.paid'],
            'secret' => 'hook-secret-b',
            'is_active' => true,
        ]);

        TenantContext::set($tenantA->id);

        $this->assertNull(Branch::query()->find($branchB->id));
        $this->assertNull(TenantDomain::query()->find($domainB->id));
        $this->assertNull(TenantApiKey::query()->find($keyB->id));
        $this->assertNull(Webhook::query()->find($hookB->id));

        $this->assertSame(0, Branch::query()->whereKey($branchB->id)->update(['name' => 'Hijacked']));
        $this->assertSame('B HQ', $branchB->fresh()->name);
    }

    public function test_request_supplied_tenant_id_is_overwritten_by_context(): void
    {
        $tenantA = $this->makeTenant('Tenant A');
        $tenantB = $this->makeTenant('Tenant B');

        TenantContext::set($tenantA->id);

        $branch = Branch::query()->create([
            'tenant_id' => $tenantB->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Should belong to A',
        ]);

        $this->assertSame($tenantA->id, (int) $branch->tenant_id);
        $this->assertSame(0, Branch::query()->where('tenant_id', $tenantB->id)->count());
    }

    public function test_revoked_api_key_does_not_match_plaintext(): void
    {
        $tenant = $this->makeTenant('Tenant A');
        $plain = 'super-secret-key';
        $key = TenantApiKey::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Live',
            'key_prefix' => 'fs_a_',
            'key_hash' => hash('sha256', $plain),
            'scopes' => ['courses.read'],
        ]);

        $this->assertTrue($key->matchesPlaintext($plain));
        $this->assertTrue($key->hasScope('courses.read'));
        $this->assertFalse($key->hasScope('users.write'));

        $key->revoke();
        $this->assertFalse($key->fresh()->matchesPlaintext($plain));
    }

    private function makeTenant(string $name): Tenant
    {
        return Tenant::query()->create([
            'company_name' => $name,
            'brand_name' => $name,
        ]);
    }
}
