<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\RegistrationService;
use App\Services\UserCurrencyService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registration,
        private readonly UserCurrencyService $userCurrency,
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        if (empty($request->input('email')) && $request->getContent()) {
            $json = json_decode($request->getContent(), true);
            if (is_array($json)) {
                $request->merge($json);
            }
        }

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $guard = Auth::guard('web');

        if (! $guard->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = $guard->user();

        if ($user === null || ! $user->status->canAuthenticate()) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();
        $this->userCurrency->persistDetection($user, $request);

        return response()->json([
            'message' => 'Signed in successfully.',
            'redirect' => $user->status === UserStatus::Suspended ? '/account/status' : '/member',
            'user' => $this->userPayload($user),
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->registration->register($request->registrationData(), 'web');

        event(new Registered($user));
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $this->userCurrency->persistDetection($user, $request);

        return response()->json([
            'message' => 'Your account has been created. Please verify your email address.',
            'redirect' => '/member',
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Signed out successfully.']);
    }

    public function purchaseSubscription(Request $request): JsonResponse
    {
        $user = Auth::guard('web')->user();
        if (!$user && $request->input('email')) {
            $user = \App\Models\User::where('email', $request->input('email'))->first();
            if ($user) {
                Auth::guard('web')->login($user);
            }
        }

        if (!$user) {
            return response()->json(['message' => 'Please sign in to select a membership plan.'], 401);
        }

        $planSlug = strtolower((string) ($request->input('plan_slug') ?: $request->input('plan_id', 'begin')));
        $plan = \App\Models\SubscriptionPlan::where('slug', $planSlug)->first()
            ?? \App\Models\SubscriptionPlan::active()->first();

        if (!$plan) {
            return response()->json(['message' => 'Selected plan not found.'], 404);
        }

        $days = match (strtolower((string) $plan->slug)) {
            'sadhana' => 365,
            'transform' => 180,
            'commit' => 90,
            'begin' => 30,
            default => (int) ($plan->duration_days ?: 30),
        };

        // Deactivate any prior active subscriptions for this user
        \App\Models\UserSubscription::where('user_id', $user->id)
            ->whereIn('status', [\App\Enums\SubscriptionStatus::Active, \App\Enums\SubscriptionStatus::Trialing])
            ->update([
                'status' => \App\Enums\SubscriptionStatus::Cancelled,
                'ended_at' => now(),
            ]);

        // Create fresh active subscription with explicit expires_at
        \App\Models\UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => \App\Enums\SubscriptionStatus::Active,
            'currency' => strtoupper((string) ($request->input('currency', 'INR'))),
            'unit_price' => $plan->price_inr ?? 1399,
            'total_amount' => $plan->price_inr ?? 1399,
            'started_at' => now(),
            'current_period_start' => now(),
            'current_period_end' => now()->addDays($days),
            'expires_at' => now()->addDays($days),
            'activation_source' => 'web_portal',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Welcome to FourSouls! Your {$plan->name} membership is now active.",
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    /** @return array<string, mixed> */
    public function userPayload(object $user): array
    {
        $hasActiveMembership = false;
        $membershipData = null;

        try {
            if (method_exists($user, 'subscriptions')) {
                $sub = $user->subscriptions()
                    ->with('plan')
                    ->whereIn('status', [\App\Enums\SubscriptionStatus::Active, \App\Enums\SubscriptionStatus::Trialing])
                    ->latest('current_period_end')
                    ->first();

                if ($sub) {
                    $now = now();
                    $expiry = $sub->expires_at ?? $sub->current_period_end;
                    $isExpired = $expiry && $now->gt($expiry);

                    if (!$isExpired) {
                        $hasActiveMembership = true;
                        $daysLeft = $expiry ? max(0, (int) $now->diffInDays($expiry, false)) : 30;
                        $membershipData = [
                            'plan_id' => $sub->subscription_plan_id,
                            'plan_slug' => strtolower((string) ($sub->plan?->slug ?? '')),
                            'plan_name' => $sub->plan?->name ?? 'Active Plan',
                            'expires_at' => $expiry ? $expiry->format('d M Y') : 'Active',
                            'expires_at_iso' => $expiry ? $expiry->toIso8601String() : null,
                            'days_remaining' => $daysLeft,
                            'status' => 'active',
                            'is_expired' => false,
                        ];
                    } else {
                        $sub->update(['status' => \App\Enums\SubscriptionStatus::Expired]);
                        $membershipData = [
                            'plan_id' => $sub->subscription_plan_id,
                            'plan_slug' => strtolower((string) ($sub->plan?->slug ?? '')),
                            'plan_name' => $sub->plan?->name ?? 'Expired Plan',
                            'expires_at' => $expiry ? $expiry->format('d M Y') : 'Expired',
                            'status' => 'expired',
                            'is_expired' => true,
                            'days_remaining' => 0,
                        ];
                    }
                } else {
                    $pastSub = $user->subscriptions()
                        ->with('plan')
                        ->where('status', \App\Enums\SubscriptionStatus::Expired)
                        ->latest('current_period_end')
                        ->first();
                    if ($pastSub && ($pastSub->expires_at || $pastSub->current_period_end)) {
                        $expiry = $pastSub->expires_at ?? $pastSub->current_period_end;
                        $membershipData = [
                            'plan_id' => $pastSub->subscription_plan_id,
                            'plan_slug' => strtolower((string) ($pastSub->plan?->slug ?? '')),
                            'plan_name' => $pastSub->plan?->name ?? 'Plan',
                            'expires_at' => $expiry->format('d M Y'),
                            'status' => 'expired',
                            'is_expired' => true,
                            'days_remaining' => 0,
                        ];
                    }
                }
            }
        } catch (\Throwable) {
            $hasActiveMembership = false;
        }

        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'status' => $user->status->value,
            'email_verified' => $user->hasVerifiedEmail(),
            'has_active_membership' => $hasActiveMembership,
            'membership' => $membershipData,
        ];
    }
}
