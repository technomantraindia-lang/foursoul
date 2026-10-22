<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Central analytics engine: audit trail + shared date-range helpers
 * used by KpiService, ReportService and DashboardService.
 */
class AnalyticsService
{
    public function logAction(Model $actor, string $action, string $subject, array $parameters = []): void
    {
        DB::table('analytics_audit_logs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'actor_type' => $actor instanceof Admin ? 'admin' : ($actor instanceof User ? 'user' : $actor->getMorphClass()),
            'actor_id' => $actor->getKey(),
            'action' => $action,
            'subject' => $subject,
            'parameters' => json_encode($parameters),
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Normalize a filters array into a bounded date range.
     *
     * @return array{0: \Carbon\CarbonInterface, 1: \Carbon\CarbonInterface}
     */
    public function resolveDateRange(array $filters = []): array
    {
        $from = isset($filters['from']) ? \Illuminate\Support\Carbon::parse($filters['from'])->startOfDay() : now()->subDays(29)->startOfDay();
        $to = isset($filters['to']) ? \Illuminate\Support\Carbon::parse($filters['to'])->endOfDay() : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    public function applyDateRange($query, string $column, array $filters = [])
    {
        [$from, $to] = $this->resolveDateRange($filters);

        return $query->whereBetween($column, [$from, $to]);
    }
}