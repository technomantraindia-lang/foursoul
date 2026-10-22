<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Services\UserCurrencyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * User dashboard (§15).
 *
 * Shows only real account data — no fake course/subscription/payment
 * content. Future prompts extend this view rather than replace it.
 */
final class DashboardController extends Controller
{
    public function __construct(private readonly UserCurrencyService $currencyService)
    {
    }

    public function __invoke(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return view('account.dashboard', [
            'user' => $user,
            'status' => $user->status,
            'activeCurrency' => $this->currencyService->resolve($user, $request),
        ]);
    }
}