<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Prompt 2B (13, 14): Customer statistics widget for the admin dashboard.
 *
 * Shows real counts from the users table using the UserStatus enum from Prompt 2A.
 * No fake numbers - actual database queries.
 */
class CustomerStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $total = User::count();
        $active = User::where('status', UserStatus::Active)->count();
        $suspended = User::where('status', UserStatus::Suspended)->count();
        $blocked = User::where('status', UserStatus::Blocked)->count();

        return [
            Stat::make(__('Total Customers'), $total)
                ->description(__('All registered customers'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('Active Customers'), $active)
                ->description(__('Can authenticate and use protected features'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('Suspended Customers'), $suspended)
                ->description(__('Temporarily suspended'))
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('warning'),

            Stat::make(__('Blocked Customers'), $blocked)
                ->description(__('Permanently blocked'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}