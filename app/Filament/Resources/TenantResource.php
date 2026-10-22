<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers;
use Filament\Forms;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Illuminate\Support\Str;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string | \UnitEnum | null $navigationGroup = 'SaaS Platform';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            Section::make('Tenant')->schema([
                Forms\Components\TextInput::make('company_name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?Tenant $record, Set $set, ?string $state) {
                        if (! $record) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->alphaDash()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options(collect(TenantStatus::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->default(TenantStatus::default()->value)
                    ->required(),
                Forms\Components\Select::make('owner_user_id')
                    ->relationship('owner', 'full_name')
                    ->searchable()
                    ->preload(),
            ])->columns(2),

            Section::make('Localization & Branding')->schema([
                Forms\Components\TextInput::make('brand_name')->maxLength(255),
                Forms\Components\TextInput::make('timezone')->default('UTC')->maxLength(64),
                Forms\Components\TextInput::make('country_code')->length(2),
                Forms\Components\TextInput::make('currency')->length(3)->default('USD'),
                Forms\Components\TextInput::make('language')->maxLength(8)->default('en'),
            ])->columns(2),

            Section::make('Limits')->schema([
                Forms\Components\TextInput::make('storage_limit_bytes')->numeric()->minValue(0),
                Forms\Components\TextInput::make('user_limit')->numeric()->minValue(1),
                Forms\Components\TextInput::make('admin_limit')->numeric()->minValue(1),
                Forms\Components\TextInput::make('course_limit')->numeric()->minValue(1),
                Forms\Components\TextInput::make('branch_limit')->numeric()->minValue(1),
                Forms\Components\TextInput::make('live_session_limit')->numeric()->minValue(1),
            ])->columns(3),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->badge()->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof TenantStatus ? $state->label() : $state)
                    ->color(fn ($state) => match (true) {
                        $state === TenantStatus::Active => 'success',
                        $state === TenantStatus::Trialing => 'info',
                        $state === TenantStatus::Suspended => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('owner.full_name')->label('Owner'),
                Tables\Columns\TextColumn::make('user_limit')->label('User Limit'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(TenantStatus::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    )),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FeaturesRelationManager::class,
            RelationManagers\DomainsRelationManager::class,
            RelationManagers\BranchesRelationManager::class,
            RelationManagers\SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}


