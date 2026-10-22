<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Currency;
use App\Support\CurrencyFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Prompt 1 (Step 16): Centralized currency formatter tests.
 *
 * Pure-PHP tests (no Laravel bootstrap required).
 */
final class CurrencyFormatterTest extends TestCase
{
    public function test_formats_aed_with_code_prefix(): void
    {
        $this->assertSame('AED 699.00', CurrencyFormatter::formatAed(699));
        $this->assertSame('AED 699.00', CurrencyFormatter::format(Currency::AED, '699'));
    }

    public function test_formats_usd_with_dollar_symbol(): void
    {
        $this->assertSame('$199.00', CurrencyFormatter::formatUsd(199));
    }

    public function test_formats_inr_with_rupee_symbol_and_thousands_separator(): void
    {
        $this->assertSame('₹12,999.00', CurrencyFormatter::formatInr(12999));
    }

    public function test_formats_decimal_amounts_to_two_places(): void
    {
        $this->assertSame('$49.50', CurrencyFormatter::formatUsd(49.5));
        $this->assertSame('AED 0.10', CurrencyFormatter::formatAed('0.1'));
    }

    public function test_format_code_uses_enum_lookup(): void
    {
        $this->assertSame('AED 100.00', CurrencyFormatter::formatCode('AED', 100));
        $this->assertSame('₹1,000.00', CurrencyFormatter::formatCode('inr', 1000));
    }

    public function test_format_code_falls_back_to_usd_for_unsupported_codes(): void
    {
        // Business rule: unknown → USD.
        $this->assertSame('$100.00', CurrencyFormatter::formatCode('EUR', 100));
        $this->assertSame('$100.00', CurrencyFormatter::formatCode('', 100));
    }
}