<?php

namespace App\Filament\Resources;

use App\Enums\MediaType;
use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static string | \UnitEnum | null $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 1;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            Forms\Components\FileUpload::make('file_path')
                ->label('File')
                ->disk('public')
                ->directory('media')
                ->required()
                ->maxSize(20480)
                ->afterStateUpdated(function ($state, Set $set) {
                    if (is_string($state)) {
                        $set('name', pathinfo($state, PATHINFO_FILENAME));
                        $ext = strtolower(pathinfo($state, PATHINFO_EXTENSION));
                        $set('type', in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']) ? MediaType::Image->value : MediaType::Document->value);
                    }
                }),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\Select::make('type')
                ->options(collect(MediaType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->getLabel()]))
                ->default(MediaType::Image->value)
                ->required(),
            Forms\Components\Select::make('folder_id')
                ->relationship('folder', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('alt_text')->maxLength(255),
            Forms\Components\TextInput::make('title')->maxLength(255),
            Forms\Components\Textarea::make('caption')->columnSpanFull(),
            Forms\Components\TagsInput::make('tags'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('folder.name')->placeholder('???'),
                Tables\Columns\TextColumn::make('size_human')->label('Size'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(collect(MediaType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->getLabel()])),
                Tables\Filters\SelectFilter::make('folder')
                    ->relationship('folder', 'name'),
            ])
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
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}


