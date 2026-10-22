<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Currency;
use Illuminate\Http\Request;

/**
 * Resolves which stored regional price currency to display by default.
 *
 * Business rules (Prompt 1):
 *   AE → AED
 *   IN → INR
 *   Other countries → USD
 *   Unknown location → USD
 *
 * IMPORTANT:
 * - This resolver NEVER converts amounts. Admin enters AED/USD/INR prices
 *   independently; the resolver only picks WHICH stored price is shown.
 * - Country detection comes exclusively from GeoLocationService, which only
 *   trusts verified sources (Cloudflare edge header / server-side GeoIP).
 * - An explicit user preference (e.g. a manual currency switcher) always
 *   wins over geo detection when provided.
 */
class PricingCurrencyResolver
{
    public function __construct(
        private readonly GeoLocationService $geoLocation,
    ) {
    }

    /**
     * Resolve the display currency for the current request.
     *
     * @param  Request  $request  Current HTTP request.
     * @param  string|null  $explicitPreference  Manually selected currency code
     *                                       (session/user preference), if any.
     */
    public function resolve(Request $request, ?string $explicitPreference = null): Currency
    {
        // 1. Explicit manual choice wins (validated against supported currencies).
        $manual = Currency::tryFromCode($explicitPreference);
        if ($manual !== null) {
            return $manual;
        }

        // 2. Geo-based default from trusted sources.
        $country = $this->geoLocation->resolve($request);

        return self::currencyForCountry($country);
    }

    /**
     * Map an ISO country code to its default display currency.
     *
     * AE → AED · IN → INR · anything else/unknown → USD
     */
    public static function currencyForCountry(?string $country): Currency
    {
        return match ($country === null ? null : strtoupper(trim($country))) {
            'AE' => Currency::AED,
            'IN' => Currency::INR,
            default => Currency::USD,
        };
    }
}