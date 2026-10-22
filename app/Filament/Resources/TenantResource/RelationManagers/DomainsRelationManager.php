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

class DomainsRelationManager extends RelationManager
{
    protected static string $relationship = 'domains';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('domain')
                ->required()
                ->maxLength(255)
                ->unique(ignorable: fn ($record) => $record),
            Forms\Components\Toggle::make('is_primary')->default(false),
            Forms\Components\Select::make('ssl_status')
                ->options([
                    'pending' => 'Pending',
                    'issued' => 'Issued',
                    'failed' => 'Failed',
                ])
                ->default('pending'),
            Forms\Components\Select::make('dns_status')
                ->options([
                    'pending' => 'Pending',
                    'verified' => 'Verified',
                    'failed' => 'Failed',
                ])
                ->default('pending'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')->searchable(),
                Tables\Columns\IconColumn::make('is_primary')->boolean()->label('Primary'),
                Tables\Columns\TextColumn::make('ssl_status')->badge(),
                Tables\Columns\TextColumn::make('dns_status')->badge(),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
