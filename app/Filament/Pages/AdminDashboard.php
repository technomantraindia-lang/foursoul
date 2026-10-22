<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

class AdminDashboard extends Dashboard
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Dashboard';
    protected static ?int $navigationSort = -2;

    public function getColumns(): int | array
    {
        return ['md' => 2, 'xl' => 4];
    }
}
