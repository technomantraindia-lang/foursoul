<?php

declare(strict_types=1);

namespace Tests\Feature\Analytics;

use App\Enums\AdminRole;
use App\Enums\AdminStatus;
use App\Enums\ReportFormat;
use App\Models\Admin;
use App\Models\ReportExport;
use App\Services\Analytics\DashboardService;
use App\Services\Analytics\ExportService;
use App\Services\Analytics\KpiService;
use App\Services\Analytics\ReportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnalyticsEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(AdminRole $role = AdminRole::SuperAdmin): Admin
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = Admin::factory()->create([
            'status' => AdminStatus::Active,
        ]);
        $admin->assignRole($role->value);

        return $admin;
    }

    public function test_kpi_service_returns_core_metrics(): void
    {
        $kpis = app(KpiService::class)->all();

        $this->assertArrayHasKey('revenue', $kpis);
        $this->assertArrayHasKey('orders', $kpis);
        $this->assertArrayHasKey('active_students', $kpis);
        $this->assertArrayHasKey('active_courses', $kpis);
    }

    public function test_report_service_revenue_by_period(): void
    {
        $result = app(ReportService::class)->revenue(['granularity' => 'month']);

        $this->assertArrayHasKey('by_period', $result);
        $this->assertIsArray($result['by_period']);
    }

    public function test_dashboard_is_role_aware_for_instructor(): void
    {
        $instructor = $this->makeAdmin(AdminRole::ContentAdmin);
        $payload = app(DashboardService::class)->forAdmin($instructor);

        $this->assertSame('instructor', $payload['role']);
        $this->assertArrayHasKey('courses', $payload);
    }

    public function test_dashboard_shows_global_metrics_for_super_admin(): void
    {
        $admin = $this->makeAdmin(AdminRole::SuperAdmin);
        $payload = app(DashboardService::class)->forAdmin($admin);

        $this->assertSame(AdminRole::SuperAdmin->value, $payload['role']);
        $this->assertArrayHasKey('kpis', $payload);
        $this->assertArrayHasKey('recent_orders', $payload);
    }

    public function test_export_service_produces_csv(): void
    {
        Storage::fake('local');

        $admin = $this->makeAdmin();
        $export = app(ExportService::class)->export(
            'revenue',
            ReportFormat::Csv,
            ['granularity' => 'month'],
            $admin,
        );

        $this->assertInstanceOf(ReportExport::class, $export);
        $this->assertSame('revenue', $export->report_key);
        Storage::disk('local')->assertExists((string) $export->file_path);
        $this->assertStringContainsString('period', Storage::disk('local')->get($export->file_path));
    }
}
