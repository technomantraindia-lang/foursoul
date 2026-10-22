<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Currency;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Prompt 1 (Step 16): Currency enum foundation tests.
 *
 * Pure-PHP tests (no Laravel bootstrap required) so they remain runnable
 * on any machine with PHP 8.4+ alone.
 */
final class CurrencyTest extends TestCase
{
    public function test_enum_contains_exactly_the_three_supported_currencies(): void
    {
        $this->assertSame(['AED', 'USD', 'INR'], array_map(
            static fn (Currency $currency): string => $currency->value,
            Currency::cases(),
        ));
    }

    public function test_symbols_match_business_specification(): void
    {
        $this->assertSame('AED', Currency::AED->symbol());
        $this->assertSame('$', Currency::USD->symbol());
        $this->assertSame('₹', Currency::INR->symbol());
    }

    public function test_labels_are_human_readable(): void
    {
        $this->assertSame('UAE Dirham', Currency::AED->label());
        $this->assertNotSame('', Currency::USD->label());
        $this->assertNotSame('', Currency::INR->label());
    }

    public function test_codes_list_matches_supported_currencies(): void
    {
        $this->assertSame(['AED', 'USD', 'INR'], Currency::codes());
    }

    public function test_try_from_code_accepts_supported_codes_case_insensitively(): void
    {
        $this->assertSame(Currency::AED, Currency::tryFromCode('AED'));
        $this->assertSame(Currency::AED, Currency::tryFromCode('aed'));
        $this->assertSame(Currency::USD, Currency::tryFromCode('Usd'));
        $this->assertSame(Currency::INR, Currency::tryFromCode('inr'));
    }

    public function test_try_from_code_returns_null_for_unsupported_or_missing_codes(): void
    {
        // "IND" is explicitly forbidden — correct ISO code is INR.
        $this->assertNull(Currency::tryFromCode('IND'));
        $this->assertNull(Currency::tryFromCode('EUR'));
        $this->assertNull(Currency::tryFromCode(null));
        $this->assertNull(Currency::tryFromCode(''));
        $this->assertNull(Currency::tryFromCode('  '));
    }

    public function test_from_code_returns_currency_for_supported_code(): void
    {
        $this->assertSame(Currency::AED, Currency::fromCode('AED'));
        $this->assertSame(Currency::INR, Currency::fromCode('inr'));
    }

    public function test_from_code_rejects_unsupported_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported currency [IND]');

        Currency::fromCode('IND');
    }
}