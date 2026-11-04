<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MembershipComparisonFeatureResource\Pages;
use App\Models\MembershipComparisonFeature;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MembershipComparisonFeatureResource extends Resource
{
    protected static ?string $model = MembershipComparisonFeature::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';

    protected static string | \UnitEnum | null $navigationGroup = 'Subscriptions';

    protected static ?string $navigationLabel = 'Comparison Features';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Feature Row')
                ->description('Add, rename, reorder, publish, or remove a row from the public membership comparison table.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Feature name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('begin')
                        ->label('BEGIN value')
                        ->placeholder('Yes, —, 0 Days, or custom text')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TextInput::make('commit')
                        ->label('COMMIT value')
                        ->placeholder('Yes, —, or custom text')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TextInput::make('transform')
                        ->label('TRANSFORM value')
                        ->placeholder('Yes, —, or custom text')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TextInput::make('sadhana')
                        ->label('SADHANA value')
                        ->placeholder('Yes, VIP, or custom text')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Display order')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->helperText('Lower numbers appear first.'),

                    Forms\Components\Toggle::make('is_published')
                        ->label('Visible on website')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Feature')
                    ->searchable()
                    ->wrap()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('begin')->label('BEGIN'),
                Tables\Columns\TextColumn::make('commit')->label('COMMIT'),
                Tables\Columns\TextColumn::make('transform')->label('TRANSFORM'),
                Tables\Columns\TextColumn::make('sadhana')->label('SADHANA'),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Visible')
                    ->boolean(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembershipComparisonFeatures::route('/'),
            'create' => Pages\CreateMembershipComparisonFeature::route('/create'),
            'edit' => Pages\EditMembershipComparisonFeature::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::guard('admin')->check();
    }
}
