<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\LiveSession;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingSessionsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = ['md' => 1, 'xl' => 2];

    public function table(Table $table): Table
    {
        return $table->query(LiveSession::query()->with('course')->upcoming()->orderBy('scheduled_start_at')->limit(6))
            ->columns([
                Tables\Columns\TextColumn::make('title')->limit(30),
                Tables\Columns\TextColumn::make('course.title')->label('Course')->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('scheduled_start_at')->label('Starts')->dateTime(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])->paginated(false)->heading('Upcoming live sessions')->description('Scheduled sessions requiring attention.');
    }
}
