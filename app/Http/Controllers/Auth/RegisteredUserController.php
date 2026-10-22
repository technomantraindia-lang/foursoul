<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\RegistrationService;
use App\Services\UserCurrencyService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

/**
 * Handles GET/POST /register (Prompt 2A §4, §5).
 *
 * Flow: validate → RegistrationService creates the user (UUID, consents,
 * referral code) → fire Registered event (queues verification mail) →
 * log the new user in → persist IP-based currency detection → redirect.
 */
class RegisteredUserController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly RegistrationService $registration,
        private readonly UserCurrencyService $userCurrency,
    ) {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('guest'),
        ];
    }

    public function create(): View
    {
        return view('auth.register');
    }

    protected function guard(): StatefulGuard
    {
        return Auth::guard();
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = $this->registration->register(
            $request->registrationData(),
            'web',
        );

        event(new Registered($user));

        $this->guard()->login($user);

        // Persist geo detection once at registration (system-controlled fields).
        $this->userCurrency->persistDetection($user, $request);

        return redirect()
            ->intended(route('verification.notice'))
            ->with('status', 'Your account has been created. Please verify your email address.');
    }
}