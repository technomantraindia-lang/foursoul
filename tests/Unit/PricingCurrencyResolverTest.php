<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GeoLocationService;
use App\Services\PricingCurrencyResolver;
use App\Support\Currency;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Prompt 1, Step 16 — regional currency resolution contract.
 *
 *   AE  → AED
 *   IN  → INR
 *   US  → USD
 *   GB  → USD
 *   unknown → USD
 *
 * Also verifies the security rule: client-supplied country headers are only
 * honoured when the trusted source (Cloudflare) is explicitly enabled.
 */
class PricingCurrencyResolverTest extends TestCase
{
    private PricingCurrencyResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        // Default test state: no trusted source configured → clean USD fallback.
        config()->set('services.geoip.cloudflare_enabled', false);
        config()->set('services.geoip.provider', null);

        $this->resolver = new PricingCurrencyResolver(new GeoLocationService());
    }

    private function requestWithCountry(?string $country): Request
    {
        $request = Request::create('/', 'GET');

        if ($country !== null) {
            $request->headers->set('CF-IPCountry', $country);
        }

        return $request;
    }

    public function test_ae_resolves_to_aed(): void
    {
        config()->set('services.geoip.cloudflare_enabled', true);

        $this->assertSame(
            Currency::AED,
            $this->resolver->resolve($this->requestWithCountry('AE')),
        );
    }

    public function test_in_resolves_to_inr(): void
    {
        config()->set('services.geoip.cloudflare_enabled', true);

        $this->assertSame(
            Currency::INR,
            $this->resolver->resolve($this->requestWithCountry('IN')),
        );
    }

    public function test_us_resolves_to_usd(): void
    {
        config()->set('services.geoip.cloudflare_enabled', true);

        $this->assertSame(
            Currency::USD,
            $this->resolver->resolve($this->requestWithCountry('US')),
        );
    }

    public function test_gb_resolves_to_usd(): void
    {
        config()->set('services.geoip.cloudflare_enabled', true);

        $this->assertSame(
            Currency::USD,
            $this->resolver->resolve($this->requestWithCountry('GB')),
        );
    }

    public function test_unknown_country_resolves_to_usd(): void
    {
        config()->set('services.geoip.cloudflare_enabled', true);

        $this->assertSame(
            Currency::USD,
            $this->resolver->resolve($this->requestWithCountry(null)),
        );
    }

    public function test_lowercase_country_code_is_normalized(): void
    {
        config()->set('services.geoip.cloudflare_enabled', true);

        $this->assertSame(
            Currency::AED,
            $this->resolver->resolve($this->requestWithCountry('ae')),
        );
    }

    public function test_spoofable_header_is_ignored_when_cloudflare_disabled(): void
    {
        // Security rule: without a verified trusted source, an arbitrary
        // client-supplied country header must NOT influence pricing.
        config()->set('services.geoip.cloudflare_enabled', false);

        $this->assertSame(
            Currency::USD,
            $this->resolver->resolve($this->requestWithCountry('AE')),
        );
    }

    public function test_explicit_preference_wins_over_geo_detection(): void
    {
        config()->set('services.geoip.cloudflare_enabled', true);

        $this->assertSame(
            Currency::INR,
            $this->resolver->resolve($this->requestWithCountry('AE'), 'inr'),
        );
    }

    public function test_invalid_explicit_preference_falls_back_to_geo(): void
    {
        config()->set('services.geoip.cloudflare_enabled', true);

        $this->assertSame(
            Currency::AED,
            $this->resolver->resolve($this->requestWithCountry('AE'), 'GBP'),
        );
    }

    public function test_static_country_mapping_matches_business_rules(): void
    {
        $this->assertSame(Currency::AED, PricingCurrencyResolver::currencyForCountry('AE'));
        $this->assertSame(Currency::INR, PricingCurrencyResolver::currencyForCountry('IN'));
        $this->assertSame(Currency::USD, PricingCurrencyResolver::currencyForCountry('US'));
        $this->assertSame(Currency::USD, PricingCurrencyResolver::currencyForCountry('GB'));
        $this->assertSame(Currency::USD, PricingCurrencyResolver::currencyForCountry(null));
        $this->assertSame(Currency::USD, PricingCurrencyResolver::currencyForCountry('XX'));
    }
}