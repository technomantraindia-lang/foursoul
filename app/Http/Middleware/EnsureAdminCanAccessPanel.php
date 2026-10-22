<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out suspended, disabled, or soft-deleted admins before Filament
 * would otherwise keep the session and return 403.
 *
 * Uses the admin guard directly so this runs even before the Filament
 * panel instance is bound on the request.
 */
final class EnsureAdminCanAccessPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();

        if (! $admin instanceof Admin) {
            return $next($request);
        }

        if (! $admin->trashed() && $admin->status->canAccessPanel()) {
            return $next($request);
        }

        auth('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->guest(url('/admin/login'));
    }
}
