<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Services\UserCurrencyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlanController extends Controller
{
    public function __construct(private readonly UserCurrencyService $currencyService) {}

    public function index(Request $request): View
    {
        $currency = $this->currencyService->resolve($request->user(), $request);
        $plans = SubscriptionPlan::query()->purchasable()->ordered()->withCount('courses')->get();
        $currentPlanId = $request->user()->subscriptions()->active()->value('subscription_plan_id');

        return view('plans.index', compact('plans', 'currency', 'currentPlanId'));
    }
}
