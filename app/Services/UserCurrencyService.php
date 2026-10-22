<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Support\Currency;
use Illuminate\Http\Request;

/**
 * Resolves the effective display currency for a user.
 *
 * Priority (Prompt 2A §21):
 *   1. Valid preferred/manual currency stored on the user record.
 *   2. IP-detected currency (persisted detection fields).
 *   3. USD fallback.
 *
 * This service NEVER converts amounts; it only selects WHICH stored
 * regional price currency applies. Detection fields are system-controlled
 * (not fillable) and are only written through persistDetection().
 */
class UserCurrencyService
{
    public function __construct(
        private readonly GeoLocationService $geoLocation,
    ) {
    }

    /**
     * Resolve the currency for a logged-in user on the current request.
     */
    public function resolve(User $user, Request $request): Currency
    {
        // 1. Valid preferred/manual currency wins.
        $preferred = Currency::tryFromCode($user->preferred_currency);
        if ($preferred !== null) {
            return $preferred;
        }

        // 2. Previously persisted IP-detected currency (only for active users).
        if ($user->status === UserStatus::Active) {
            $detected = Currency::tryFromCode($user->detected_currency);
            if ($detected !== null) {
                return $detected;
            }
        }

        // 3. Fresh geo detection for this request, then USD fallback.
        $country = $this->geoLocation->resolve($request);

        return PricingCurrencyResolver::currencyForCountry($country);
    }

    /**
     * Persist IP-based detection results. Called from trusted middleware
     * after a successful login — never from arbitrary profile requests.
     * Detection data is throttled: only rewritten when the country or IP
     * actually changed.
     */
    public function persistDetection(User $user, Request $request): void
    {
        $country = $this->geoLocation->resolve($request);
        $ip = (string) $request->ip();

        if (
            $user->detected_country_code === $country
            && $user->last_detected_ip === $ip
            && $user->detected_currency !== null
        ) {
            return; // Nothing changed — skip the write.
        }

        $currency = PricingCurrencyResolver::currencyForCountry($country);

        $user->forceFill([
            'detected_country_code' => $country,
            'detected_currency' => $currency->value,
            'last_detected_ip' => $ip,
        ])->save();
    }
}