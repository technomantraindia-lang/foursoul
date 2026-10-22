<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('subscription_plan_id')
                ->relationship('plan', 'name')
                ->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'trialing' => 'Trialing',
                    'active' => 'Active',
                    'past_due' => 'Past Due',
                    'expired' => 'Expired',
                    'cancelled' => 'Cancelled',
                ])
                ->default('trialing')
                ->required(),
            Forms\Components\DateTimePicker::make('trial_ends_at'),
            Forms\Components\DateTimePicker::make('current_period_start')->default(now()),
            Forms\Components\DateTimePicker::make('current_period_end'),
            Forms\Components\DateTimePicker::make('expires_at'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')->label('Plan'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('current_period_start')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('expires_at')->dateTime()->sortable(),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
