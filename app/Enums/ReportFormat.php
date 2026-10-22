<?php

namespace App\Enums;

enum ReportFormat: string
{
    case Csv = 'csv';
    case Excel = 'excel';
    case Pdf = 'pdf';

    public function label(): string
    {
        return match ($this) {
            self::Csv => 'CSV',
            self::Excel => 'Excel',
            self::Pdf => 'PDF',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}