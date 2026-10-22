<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SubscriptionController extends Controller
{
    public function show(Request $request): View
    {
        $subscription = UserSubscription::query()->forUser($request->user())->with('plan')->latest()->first();

        return view('account.subscription', compact('subscription'));
    }
}
