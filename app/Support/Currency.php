<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Supported customer currencies for the LMS.
 *
 * Business rules (Prompt 1):
 * - Base business currency: AED.
 * - Only AED, USD and INR are supported. No other codes may be added here
 *   without an explicit product decision.
 * - Regional prices are entered independently by Admin; this enum never converts.
 */
enum Currency: string
{
    case AED = 'AED';
    case USD = 'USD';
    case INR = 'INR';

    /**
     * Display symbol/prefix used by the centralized formatter.
     * AED is shown as the ISO code ("AED 699.00"), USD as "$", INR as "₹".
     */
    public function symbol(): string
    {
        return match ($this) {
            self::AED => 'AED',
            self::USD => '$',
            self::INR => '₹',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::AED => 'UAE Dirham',
            self::USD => 'US Dollar',
            self::INR => 'Indian Rupee',
        };
    }

    /** @return list<string> */
    public static function codes(): array
    {
        return array_map(static fn (self $currency): string => $currency->value, self::cases());
    }

    /**
     * Normalize and resolve a currency code safely.
     * Invalid or unsupported codes (e.g. "IND", "EUR") yield null instead of throwing.
     */
    public static function tryFromCode(self|string|null $code): ?self
    {
        if ($code instanceof self) {
            return $code;
        }

        if ($code === null) {
            return null;
        }

        $normalized = strtoupper(trim($code));

        return self::tryFrom($normalized);
    }

    /**
     * Strictly resolve a currency code, throwing on unsupported values.
     *
     * @throws \InvalidArgumentException
     */
    public static function fromCode(string $code): self
    {
        return self::tryFromCode($code)
            ?? throw new \InvalidArgumentException(sprintf(
                'Unsupported currency [%s]. Supported currencies: %s.',
                $code,
                implode(', ', self::codes()),
            ));
    }
}