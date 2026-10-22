<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Services\Tenant\FeatureGateService;
use App\Services\Tenant\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FeatureGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_feature_is_rejected_server_side(): void
    {
        $tenant = app(TenantService::class)->create(['company_name' => 'Acme']);
        $gates = app(FeatureGateService::class);

        $this->assertFalse($gates->enabled($tenant, 'commerce'));

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $gates->assertEnabled($tenant, 'commerce');
    }

    public function test_enabled_feature_passes_assert(): void
    {
        $tenant = app(TenantService::class)->create(['company_name' => 'Acme']);
        $gates = app(FeatureGateService::class);
        $gates->setEnabled($tenant, 'courses', true);

        $this->assertTrue($gates->enabled($tenant, 'courses'));
        $gates->assertEnabled($tenant, 'courses');
    }

    public function test_user_admin_course_storage_branch_and_live_session_limits(): void
    {
        $tenant = Tenant::query()->create([
            'company_name' => 'Limited Co',
            'user_limit' => 2,
            'admin_limit' => 1,
            'course_limit' => 3,
            'storage_limit_bytes' => 1000,
            'branch_limit' => 1,
            'live_session_limit' => 4,
        ]);
        $gates = app(FeatureGateService::class);

        $this->assertTrue($gates->withinLimit($tenant, 'users', 1));
        $this->assertFalse($gates->withinLimit($tenant, 'users', 2));
        $this->assertFalse($gates->withinLimit($tenant, 'admins', 1));
        $this->assertTrue($gates->withinLimit($tenant, 'courses', 2));
        $this->assertFalse($gates->withinLimit($tenant, 'storage', 1000));
        $this->assertTrue($gates->withinLimit($tenant, 'storage', 999));
        $this->assertFalse($gates->withinLimit($tenant, 'branches', 1));
        $this->assertTrue($gates->withinLimit($tenant, 'live_sessions', 3));
    }

    public function test_null_limits_are_unlimited(): void
    {
        $tenant = Tenant::query()->create([
            'company_name' => 'Open Co',
            'user_limit' => null,
            'storage_limit_bytes' => null,
        ]);

        $this->assertTrue(app(FeatureGateService::class)->withinLimit($tenant, 'users', 10_000));
        $this->assertTrue(app(FeatureGateService::class)->withinLimit($tenant, 'storage', 99_999_999));
    }
}
