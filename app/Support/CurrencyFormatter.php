<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Centralized currency formatter for the LMS.
 *
 * All money display formatting must go through this class so that
 * formatting logic is never duplicated inside individual Blade files.
 *
 * Examples:
 *   AED 699.00
 *   $199.00
 *   ₹12,999.00
 *
 * This formatter never converts between currencies; amounts are assumed
 * to already be stored in the given currency.
 */
final class CurrencyFormatter
{
    private const DECIMALS = 2;
    private const THOUSANDS_SEPARATOR = ',';
    private const DECIMAL_SEPARATOR = '.';

    /**
     * Format an amount for display.
     *
     * @param  Currency  $currency  Stored/display currency of the amount.
     * @param  int|float|string  $amount  Numeric amount (string accepted for DB decimal values).
     */
    public static function format(Currency $currency, int|float|string $amount): string
    {
        // AED uses its ISO code as prefix ("AED 699.00") and therefore takes a
        // separating space; symbol currencies ($199.00, ₹12,999.00) do not.
        $separator = $currency === Currency::AED ? ' ' : '';

        return sprintf(
            '%s%s%s',
            $currency->symbol(),
            $separator,
            self::formatNumber($amount),
        );
    }

    public static function formatAed(int|float|string $amount): string
    {
        return self::format(Currency::AED, $amount);
    }

    public static function formatUsd(int|float|string $amount): string
    {
        return self::format(Currency::USD, $amount);
    }

    public static function formatInr(int|float|string $amount): string
    {
        return self::format(Currency::INR, $amount);
    }

    /**
     * Format using a raw ISO code ("aed", "usd", "inr").
     * Unsupported codes fall back to USD per business rule (unknown → USD).
     */
    public static function formatCode(string $code, int|float|string $amount): string
    {
        return self::format(Currency::tryFromCode($code) ?? Currency::USD, $amount);
    }

    private static function formatNumber(int|float|string $amount): string
    {
        return number_format(
            (float) $amount,
            self::DECIMALS,
            self::DECIMAL_SEPARATOR,
            self::THOUSANDS_SEPARATOR,
        );
    }
}