<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Services\AvatarService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Profile management (§17–18).
 *
 * Email is read-only here — secure email-change verification is not
 * implemented in this prompt. Avatar uploads are validated and stored
 * through the AvatarService (§18).
 */
final class ProfileController extends Controller
{
    public function __construct(private readonly AvatarService $avatars)
    {
    }

    public function edit(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return view('account.profile', [
            'user' => $user,
        ]);
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->avatars->replace($user, $request->file('avatar'));

        return redirect()->route('account.profile.edit')
            ->with('status', 'Avatar updated.');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Avatar handled separately through the storage abstraction (§18).
        if ($request->hasFile('avatar')) {
            $this->avatars->replace($user, $request->file('avatar'));
        }

        // Only explicitly allowed fields are filled — mass-assignment
        // protection for status, email_verified_at, detected fields, etc. (§30).
        $payload = collect($request->validated())->except('avatar')->all();
        $user->fill($payload)->save();

        return redirect()->route('account.profile.edit')
            ->with('status', 'Profile updated.');
    }
}