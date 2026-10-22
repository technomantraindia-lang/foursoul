<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Resolves the visitor's ISO 3166-1 alpha-2 country code from TRUSTED sources only.
 *
 * Security rules (Prompt 1, Step 7):
 * - Arbitrary browser/client-supplied headers (e.g. a spoofable "X-Country")
 *   must NEVER be trusted.
 * - Trusted sources, in priority order:
 *     1. Cloudflare's "CF-IPCountry" header — only when the request is verified
 *        as coming through Cloudflare (config: services.geoip.cloudflare_enabled).
 *     2. A server-side GeoIP provider (e.g. MaxMind, ip-api) — pluggable via
 *        config("services.geoip.provider"). No provider is bundled or
 *        hard-coded in Prompt 1; the hook is intentionally left as an
 *        extension point.
 * - When no trusted source is available (typical local development),
 *   resolve() returns null and callers must fall back to the default
 *   currency (USD) per business rules.
 */
class GeoLocationService
{
    /**
     * Resolve the visitor's country code, or null when unknown/untrusted.
     */
    public function resolve(Request $request): ?string
    {
        return $this->fromCloudflare($request)
            ?? $this->fromGeoIpProvider($request);
    }

    /**
     * Cloudflare country header, accepted only when Cloudflare integration
     * is explicitly enabled in configuration. Cloudflare sets CF-IPCountry
     * at the edge; it cannot be spoofed when the site is actually proxied
     * through Cloudflare.
     */
    private function fromCloudflare(Request $request): ?string
    {
        if (! config('services.geoip.cloudflare_enabled', false)) {
            return null;
        }

        $country = $request->header('CF-IPCountry');

        if (! is_string($country) || $country === '' || strtoupper($country) === 'XX') {
            return null;
        }

        return $this->normalize($country);
    }

    /**
     * Extension point for a server-side GeoIP provider (MaxMind, etc.).
     *
     * Prompt 1 intentionally does NOT bundle, purchase, or hard-code any
     * provider or credentials. When a provider is added later, implement
     * the lookup here behind config("services.geoip.provider").
     */
    private function fromGeoIpProvider(Request $request): ?string
    {
        $provider = config('services.geoip.provider');

        if (empty($provider)) {
            return null; // No provider configured — clean local-dev fallback.
        }

        // Future: dispatch to the configured provider using $request->ip().
        return null;
    }

    /**
     * Normalize to a two-letter uppercase ISO code, or null when invalid.
     */
    private function normalize(?string $country): ?string
    {
        if ($country === null) {
            return null;
        }

        $country = strtoupper(trim($country));

        return preg_match('/^[A-Z]{2}$/', $country) === 1 ? $country : null;
    }
}