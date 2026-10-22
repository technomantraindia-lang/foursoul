<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Central enforcement of UserStatus (Prompt 2A §9).
 *
 * Applied to every authenticated route group so status logic is never
 * duplicated in controllers:
 *   - active:    request proceeds normally.
 *   - suspended: redirected to the account-status page (read-only).
 *   - blocked:   logged out and redirected to login with a generic error.
 *   - closed:    logged out and redirected to login with a generic error.
 *
 * Blocked/closed users therefore cannot bypass restrictions with direct
 * URLs: the middleware runs on every authenticated request, including
 * /account/* and /dashboard.
 */
class EnsureUserAccountIsAccessible
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $status = $user->status;

        // Active users proceed; suspended users may only view the status page.
        if ($status === UserStatus::Active) {
            return $next($request);
        }

        if ($status === UserStatus::Suspended) {
            $statusRoute = route('account.status');

            // Allow the user to reach the read-only status page itself.
            if ($request->routeIs('account.status') || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->to($statusRoute);
        }

        // Blocked or closed: terminate the session immediately.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('account_denied', true)
            ->withInput($request->only('email'));
    }
}