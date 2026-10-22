<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdatePreferencesRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Preferences management (§20–21).
 *
 * Currency options come from the existing Currency enum (AED/USD/INR only).
 * Resolution priority for logged-in users: preferred → detected → USD (§21).
 */
final class PreferencesController extends Controller
{
    public function edit(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return view('account.preferences', [
            'user' => $user,
        ]);
    }

    public function update(UpdatePreferencesRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Only explicitly allowed fields are filled — mass-assignment
        // protection for status, email_verified_at, detected fields, etc. (§30).
        $user->fill($request->validated())->save();

        return redirect()->route('account.preferences.edit')
            ->with('status', 'Preferences updated.');
    }
}