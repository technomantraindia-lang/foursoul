<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\AdminDashboard;
use App\Filament\Pages\AnalyticsDashboard;
use App\Filament\Pages\TestCenter;
use App\Filament\Pages\Auth\AdminLogin;
use App\Filament\Pages\AdminProfile;
use App\Filament\Resources\BlogPostResource;
use App\Filament\Resources\CertificateTemplateResource;
use App\Filament\Resources\CertificateResource;
use App\Filament\Resources\FaqResource;
use App\Filament\Resources\LiveSessionResource;
use App\Filament\Resources\SubscriptionPlanResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\DashboardQuickActionsWidget;
use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\RecentCustomersWidget;
use App\Filament\Widgets\RecentEnrollmentsWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\UpcomingSessionsWidget;
use App\Http\Middleware\EnsureAdminCanAccessPanel;
use App\Models\Admin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession as BaseAuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $pages = [AdminDashboard::class, AnalyticsDashboard::class];
        if (config('features.test_center_enabled')) {
            $pages[] = TestCenter::class;
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->authPasswordBroker('admins')
            ->login(AdminLogin::class)
            ->passwordReset()
            ->profile(AdminProfile::class, isSimple: false)
            ->colors([
                'primary' => Color::Orange,
            ])
            ->brandName(config('settings.defaults.site_name', 'FourSouls'))
            ->favicon(null)
            ->darkMode(true)
            ->databaseNotifications(false)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                BaseAuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                EnsureAdminCanAccessPanel::class,
                Authenticate::class,
                AuthenticateSession::class,
            ])
            ->pages($pages)
            ->resources([
                SubscriptionPlanResource::class,
                LiveSessionResource::class,
                FaqResource::class,
                BlogPostResource::class,
                CertificateResource::class,
                CertificateTemplateResource::class,
                UserResource::class,
                \App\Filament\Resources\UserSubscriptionResource::class,
                ...(config('features.saas_enabled') ? [\App\Filament\Resources\TenantResource::class] : []),
            ])
            ->widgets([
                DashboardStatsWidget::class,
                RecentEnrollmentsWidget::class,
                RecentOrdersWidget::class,
                UpcomingSessionsWidget::class,
                RecentCustomersWidget::class,
                DashboardQuickActionsWidget::class,
            ]);
    }

    public static function make(): static
    {
        return new static();
    }

    public static function canAccessPanel(Admin $admin): bool
    {
        return $admin->canAccessPanel();
    }
}
