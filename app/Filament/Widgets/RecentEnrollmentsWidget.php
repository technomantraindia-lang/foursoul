<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\UserSubscription;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentEnrollmentsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = ['md' => 1, 'xl' => 2];

    public function table(Table $table): Table
    {
        return $table
            ->query(UserSubscription::query()->with(['user', 'plan'])->latest('created_at')->limit(8))
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Member')
                    ->placeholder('Unknown')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Membership Tier')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Membership'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state, $record) => strtoupper((string) ($record->currency ?? 'INR')) . ' ' . number_format((float) $state, 2)),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Subscribed')
                    ->since(),
            ])
            ->paginated(false)
            ->heading('Recent member subscriptions')
            ->description('Latest membership purchases & activations.');
    }
}
