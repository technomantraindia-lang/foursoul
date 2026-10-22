<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Configuration for optional third-party integrations. Each block maps
    | environment variables to the keys consumed by application services.
    |
    */

    'geoip' => [
        /*
         |----------------------------------------------------------------------
         | GeoIP Lookup Driver
         |----------------------------------------------------------------------
         |
         | Pluggable server-side IP geolocation driver. Supported values:
         | "none" (default, lookups disabled), or a provider slug such as
         | "maxmind" / "ip-api". No provider SDK is bundled by default;
         | GeoLocationService resolves lookups behind this configuration.
         |
         */
        'provider' => env('GEOIP_DRIVER', 'none'),

        /*
         |----------------------------------------------------------------------
         | GeoIP Provider Credentials
         |----------------------------------------------------------------------
         |
         | API key used by the configured GeoIP provider, when required.
         |
         */
        'api_key' => env('GEOIP_PROVIDER_API_KEY'),

        /*
         |----------------------------------------------------------------------
         | Cloudflare Client IP Trust
         |----------------------------------------------------------------------
         |
         | When enabled, the CF-Connecting-IP header is trusted as the real
         | client address for geolocation resolution. Only enable this when
         | traffic is guaranteed to pass through Cloudflare.
         |
         */
        'cloudflare_enabled' => filter_var(env('GEOIP_TRUST_CF_HEADER', false), FILTER_VALIDATE_BOOL),
    ],

];