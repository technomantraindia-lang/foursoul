<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardQuickActionsWidget extends Widget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.dashboard-quick-actions';
}
