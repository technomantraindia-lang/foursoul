<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\PaymentStatus;
use App\Enums\ReportFormat;
use App\Enums\SubscriptionStatus;
use App\Models\Admin;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\Analytics\DashboardService;
use App\Services\Analytics\ExportService;
use App\Services\Analytics\KpiService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnalyticsDashboard extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.pages.analytics-dashboard';

    protected static string | \UnitEnum | null $navigationGroup = 'Analytics';

    protected static ?string $slug = 'analytics';

    protected static ?string $title = 'Analytics & Revenue';

    public array $kpis = [];

    public array $revenueTrend = [];

    public array $enrollmentTrend = [];

    public array $currencyRevenues = [];

    public array $planBreakdown = [];

    public array $recentTransactions = [];

    public int $activeMembersCount = 0;

    public int $totalCustomersCount = 0;

    public string $report = 'revenue';

    public ?string $from = null;

    public ?string $to = null;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        $filters = array_filter([
            'from' => $this->from,
            'to' => $this->to,
            'instructor_id' => $admin?->hasRole('super_admin') || $admin?->hasRole('admin') ? null : $admin?->id,
        ]);

        $this->kpis = app(KpiService::class)->all($filters);
        $this->revenueTrend = app(DashboardService::class)->revenueTrend($filters);
        $this->enrollmentTrend = app(DashboardService::class)->enrollmentTrend($filters);

        $this->activeMembersCount = UserSubscription::where('status', SubscriptionStatus::Active)->count();
        $this->totalCustomersCount = User::count();

        $this->currencyRevenues = Payment::where('status', PaymentStatus::Succeeded->value)
            ->selectRaw('currency, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('currency')
            ->get()
            ->toArray();

        $this->planBreakdown = SubscriptionPlan::withCount(['subscriptions' => function ($q) {
            $q->where('status', SubscriptionStatus::Active);
        }])->get()->map(fn ($p) => [
            'name' => $p->name,
            'slug' => $p->slug,
            'active_count' => $p->subscriptions_count,
            'price_inr' => $p->price_inr ?? 0,
            'price_aed' => $p->price_aed ?? 0,
            'price_usd' => $p->price_usd ?? 0,
            'billing_type' => $p->billing_type?->value ?? 'monthly',
        ])->toArray();

        $this->recentTransactions = Payment::with('user:id,full_name,email')
            ->where('status', PaymentStatus::Succeeded->value)
            ->latest('paid_at')
            ->limit(7)
            ->get()
            ->map(fn ($pm) => [
                'user_name' => $pm->user?->full_name ?? 'Customer',
                'user_email' => $pm->user?->email ?? '-',
                'currency' => $pm->currency ?? 'INR',
                'amount' => (float) $pm->amount,
                'gateway' => $pm->gateway ?? 'online',
                'paid_at' => $pm->paid_at ? $pm->paid_at->format('d M Y, H:i') : '-',
            ])
            ->toArray();
    }

    public function export()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        try {
            $export = app(ExportService::class)->export(
                $this->report,
                ReportFormat::Csv,
                array_filter(['from' => $this->from, 'to' => $this->to]),
                $admin,
            );
        } catch (\Throwable $e) {
            Halt::make();
        }

        $path = Storage::disk('local')->path((string) $export->file_path);

        return response()->download($path, basename($path), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ])->deleteFileAfterSend(false);
    }
}
