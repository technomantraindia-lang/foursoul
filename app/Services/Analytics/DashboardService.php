<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Role-aware dashboard data assembly.
 */
class DashboardService
{
    public function __construct(
        protected KpiService $kpis,
        protected ReportService $reports,
    ) {}

    /**
     * Build dashboard payload for the given admin based on role.
     */
    public function forAdmin(Admin $admin): array
    {
        if ($admin->hasRole(AdminRole::ContentAdmin->value) || $admin->hasRole(AdminRole::SessionManager->value)) {
            return $this->instructorDashboard($admin);
        }

        $role = $admin->getRoleNames()->first() ?? AdminRole::SuperAdmin->value;

        return [
            'role' => $role,
            'kpis' => $this->kpis->all([]),
            'revenue_by_period' => $this->reports->revenue(['granularity' => 'month'])['by_period'],
            'recent_orders' => DB::table('orders')->latest('created_at')->limit(5)->get(),
            'recent_enrollments' => DB::table('enrollments')->latest('created_at')->limit(5)->get(),
        ];
    }

    /**
     * Student-facing learning dashboard summary.
     */
    public function forStudent(User $user): array
    {
        return [
            'kpis' => [
                'enrollments' => $this->reports->students(['user_id' => $user->id])['enrollments'],
                'completed_courses' => $this->reports->students(['user_id' => $user->id])['completed_courses'],
                'certificates' => $this->reports->students(['user_id' => $user->id])['certificates'],
                'quiz_attempts' => $this->reports->students(['user_id' => $user->id])['quiz_attempts'],
            ],
        ];
    }

    /**
     * @return list<array{period:string,total:mixed,transactions:mixed}>
     */
    public function revenueTrend(array $filters = []): array
    {
        $rows = $this->reports->revenue(array_merge($filters, ['granularity' => 'month']))['by_period'] ?? [];

        return collect($rows)->map(function ($row) {
            return [
                'period' => (string) (data_get($row, 'period') ?? '-'),
                'total' => data_get($row, 'total') ?? 0,
                'transactions' => data_get($row, 'transactions') ?? 0,
            ];
        })->values()->all();
    }

    /**
     * @return list<array{period:string,total:mixed}>
     */
    public function enrollmentTrend(array $filters = []): array
    {
        $query = DB::table('enrollments')
            ->selectRaw($this->periodSelect('enrolled_at', '%Y-%m').', COUNT(*) as total')
            ->groupBy('period')
            ->orderBy('period');

        if (! empty($filters['from'])) {
            $query->where('enrolled_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->where('enrolled_at', '<=', $filters['to']);
        }

        return $query->get()->map(fn ($row) => [
            'period' => (string) $row->period,
            'total' => $row->total,
        ])->all();
    }

    protected function instructorDashboard(Admin $admin): array
    {
        return [
            'role' => 'instructor',
            'courses' => $this->reports->instructors(['instructor_id' => $admin->id]),
            'revenue_by_period' => $this->reports->revenue(['granularity' => 'month'])['by_period'],
        ];
    }

    protected function periodSelect(string $column, string $mysqlFormat): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $sqlite = match ($mysqlFormat) {
                '%Y-%m-%d' => '%Y-%m-%d',
                '%Y' => '%Y',
                default => '%Y-%m',
            };

            return "strftime('{$sqlite}', {$column}) as period";
        }

        return "DATE_FORMAT({$column}, '{$mysqlFormat}') as period";
    }
}