<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\AdminStatus;
use App\Models\Admin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Prompt 2B (13, 14): Admin statistics widget for the admin dashboard.
 *
 * Shows real counts from the admins table using the AdminStatus enum.
 * No fake numbers - actual database queries.
 */
class AdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $total = Admin::count();
        $active = Admin::where('status', AdminStatus::Active)->count();

        return [
            Stat::make(__('Total Admins'), $total)
                ->description(__('All admin accounts'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make(__('Active Admins'), $active)
                ->description(__('Can access the administration panel'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}