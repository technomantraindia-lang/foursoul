<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Password;

/**
 * Forgot password (Prompt 2A §11).
 *
 * Sends a secure reset link via Laravel's Password broker. Always returns
 * the same user-facing message so the endpoint cannot be used to enumerate
 * registered email addresses.
 */
class PasswordResetLinkController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('guest'),
        ];
    }

    public function create(): \Illuminate\View\View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Enter a valid email address.',
        ]);

        // We will send the password reset link to this user. Once we have
        // attempted to send the link, we will examine the response then
        // view the message we need to show to the user. Finally, we'll
        // send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // §11: identical response regardless of broker status — prevents
        // account enumeration via the forgot-password form.
        return back()->with('status', __($status));
    }
}