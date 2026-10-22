<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\LiveSession;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $currencyTotals = Payment::query()->where('status', PaymentStatus::Succeeded->value)
            ->select('currency', DB::raw('SUM(amount) as total'))->groupBy('currency')->orderBy('currency')->get()
            ->map(fn ($row): string => strtoupper((string) $row->currency) . ' ' . number_format((float) $row->total, 2))->implode('  ·  ');

        return [
            Stat::make('Registered Members', User::count())
                ->description(User::where('status', 'active')->count() . ' active accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Membership Plans', SubscriptionPlan::query()->active()->count())
                ->description('Active membership tiers')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success'),

            Stat::make('Live Classes & Videos', LiveSession::count())
                ->description('Scheduled & published videos')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('info'),

            Stat::make('Successful Payments', $currencyTotals !== '' ? $currencyTotals : 'No payments yet')
                ->description('Recorded revenue')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
