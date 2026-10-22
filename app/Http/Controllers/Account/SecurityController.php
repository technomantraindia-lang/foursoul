<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateSecurityPasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Security / password management (§19).
 *
 * Requires the correct current password before allowing a change.
 * Uses Laravel's secure password hashing — the current password is
 * never revealed (§19).
 */
final class SecurityController extends Controller
{
    public function edit(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return view('account.security', [
            'user' => $user,
        ]);
    }

    public function updatePassword(UpdateSecurityPasswordRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validated();

        // Verify the current password before allowing a change (§19).
        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('The provided password does not match your current password.'),
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('account.security.edit')
            ->with('status', 'Password changed.');
    }
}