<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Phase 5 (§8): Payment method, gateway-independent.
 * Gateway integrations map their own identifiers onto these values.
 */
enum PaymentMethod: string
{
    case Card = 'card';
    case Upi = 'upi';
    case NetBanking = 'net_banking';
    case Wallet = 'wallet';
    case Paypal = 'paypal';
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Card => 'Card',
            self::Upi => 'UPI',
            self::NetBanking => 'Net Banking',
            self::Wallet => 'Wallet',
            self::Paypal => 'PayPal',
            self::Cash => 'Cash',
            self::BankTransfer => 'Bank Transfer',
        };
    }
}