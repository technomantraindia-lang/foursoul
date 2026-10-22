<?php

namespace App\Enums;

enum ReportFrequency: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
        };
    }

    public function interval(): string
    {
        return match ($this) {
            self::Daily => '1 day',
            self::Weekly => '1 week',
            self::Monthly => '1 month',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}