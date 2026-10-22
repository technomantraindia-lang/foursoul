<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Signed email verification link handler (Prompt 2A §13).
 *
 * Uses Laravel's markEmailAsVerified() — signed URL, expiry and hash
 * validation come from the framework. No fake success states.
 * The authenticated user must match the signed `id` (no IDOR).
 */
class VerifyEmailController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('throttle:6,1'),
        ];
    }

    public function __invoke(Request $request): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('verification.notice')
                ->with('error', 'This verification link is invalid or has expired.');
        }

        /** @var User $authenticated */
        $authenticated = $request->user();
        $targetId = (int) $request->route('id');

        if ((int) $authenticated->getKey() !== $targetId) {
            throw new AuthorizationException('Invalid verification link.');
        }

        if (! hash_equals(
            sha1($authenticated->getEmailForVerification()),
            (string) $request->route('hash')
        )) {
            throw new AuthorizationException('Invalid verification link.');
        }

        if ($authenticated->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($authenticated->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($authenticated));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1')
            ->with('status', 'Your email address has been verified.');
    }
}
