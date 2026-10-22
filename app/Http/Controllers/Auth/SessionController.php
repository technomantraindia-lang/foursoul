<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Services\UserCurrencyService;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Login + logout (Prompt 2A §7, §9, §14).
 *
 * Uses Laravel's standard attempt()/logout() — no custom auth logic.
 * Blocked/closed users are rejected at login; suspended users may log in
 * but are restricted by EnsureUserAccountIsAccessible middleware.
 */
class SessionController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly UserCurrencyService $userCurrency,
    ) {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['destroy']),
        ];
    }

    protected function guard(): StatefulGuard
    {
        return Auth::guard();
    }

    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ]);

        $remember = $request->boolean('remember');

        if (! $this->guard()->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = $this->guard()->user();

        // §9: blocked/closed accounts must not authenticate. Log out
        // immediately and show a generic failure (no account-state leak).
        if ($user !== null && ! $user->status->canAuthenticate()) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        // §14/§21: persist IP-based currency detection once per login
        // (throttled inside the service; login tracking is event-driven).
        if ($user !== null) {
            $this->userCurrency->persistDetection($user, $request);
        }

        // Suspended users land on their status page (§10); active users go on.
        if ($user !== null && $user->status === UserStatus::Suspended) {
            return redirect()->route('account.status');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'You have been logged out.');
    }
}