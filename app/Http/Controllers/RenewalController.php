<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserSubscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class RenewalController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    public function show(Request $request): View
    {
        $subscription = UserSubscription::query()->forUser($request->user())->with('plan')->latest()->first();
        $eligible = $subscription !== null && (! $subscription->grantsAccess() || ($subscription->expires_at && $subscription->expires_at->isFuture() && $subscription->expires_at->diffInDays(now()) <= 30));

        return view('subscription.renew', compact('subscription', 'eligible'));
    }

    public function renew(Request $request): RedirectResponse
    {
        $subscription = UserSubscription::query()->forUser($request->user())->with('plan')->latest()->firstOrFail();
        abort_unless(! $subscription->grantsAccess() || ($subscription->expires_at && $subscription->expires_at->isFuture() && $subscription->expires_at->diffInDays(now()) <= 30), 403);
        $this->subscriptions->renew($subscription);

        return redirect()->route('subscription.show')->with('success', 'Access renewed. Your progress and course history are preserved.');
    }
}
