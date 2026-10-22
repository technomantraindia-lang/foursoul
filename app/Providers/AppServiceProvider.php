<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\AdminRole;
use App\Enums\AdminStatus;
use App\Http\Middleware\EnsureUserAccountIsAccessible;
use App\Listeners\Communication\CommunicationEventSubscriber;
use App\Listeners\UpdateAdminLastLoginActivity;
use App\Listeners\UpdateLastLoginActivity;
use App\Models\Admin;
use App\Services\AvatarService;
use App\Services\Communication\Contracts\PushProviderInterface;
use App\Services\Communication\Contracts\WhatsAppProviderInterface;
use App\Services\Communication\Providers\LogPushProvider;
use App\Services\Communication\Providers\LogWhatsAppProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Application service provider.
 *
 * Responsibilities:
 *  - register the login-tracking listeners (Prompt 2A §14, Prompt 2B §9)
 *  - register the EnsureUserAccountIsAccessible route middleware alias (Prompt 2A §9)
 *  - register the Super Admin Gate::before bypass (Prompt 2C §4)
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WhatsAppProviderInterface::class, LogWhatsAppProvider::class);
        $this->app->singleton(PushProviderInterface::class, LogPushProvider::class);

        $this->app->singleton(AvatarService::class, function ($app) {
            return new AvatarService(
                disk: $app->make(FilesystemFactory::class)->disk('public'),
            );
        });
    }

    public function boot(): void
    {
        Event::listen(Login::class, UpdateLastLoginActivity::class);
        Event::listen(Login::class, UpdateAdminLastLoginActivity::class);
        Event::subscribe(CommunicationEventSubscriber::class);

        Route::aliasMiddleware('account.access', EnsureUserAccountIsAccessible::class);

        // Register after Spatie so this callback is unshifted to the front
        // of Gate::before and can deny inactive Super Admins before Spatie
        // grants their synced permissions.
        $this->app->booted(function (): void {
            Gate::before(static function (Authorizable $user, string $ability): ?bool {
                if (! $user instanceof Admin) {
                    return null;
                }

                if ($user->trashed() || $user->status !== AdminStatus::Active) {
                    return false;
                }

                return $user->hasRole(AdminRole::SuperAdmin->value) ? true : null;
            });
        });
    }
}
