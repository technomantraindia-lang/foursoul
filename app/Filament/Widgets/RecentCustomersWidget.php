<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Prompt 2B (13, 14): Recent customer registrations widget for the admin dashboard.
 *
 * Shows the most recent customer registrations with Name, Email, Status, and Joined At.
 * Uses actual database queries - no fake data.
 */
class RecentCustomersWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->select(['id', 'full_name', 'email', 'status', 'created_at'])
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('Email copied'))
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'success' => UserStatus::Active,
                        'warning' => UserStatus::Suspended,
                        'danger' => UserStatus::Blocked,
                    ])
                    ->formatStateUsing(fn (UserStatus $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Joined At'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record): string => $record->created_at->format('Y-m-d H:i:s T')),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->heading(__('Recent students'))
            ->description(__('Latest 10 student accounts'))
            ->emptyStateHeading(__('No students yet'))
            ->emptyStateDescription(__('Student registrations will appear here.'))
            ->emptyStateIcon('heroicon-o-users');
    }
}
