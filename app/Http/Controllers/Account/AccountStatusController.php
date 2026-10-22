<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Read-only account status screen (§10).
 *
 * Reached only by suspended users via the account.access middleware.
 * Displays safe information only — no internal admin notes/reasons
 * are exposed unless explicitly approved in a later prompt.
 */
final class AccountStatusController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return view('account.status', [
            'user' => $user,
            'status' => $user->status,
        ]);
    }
}