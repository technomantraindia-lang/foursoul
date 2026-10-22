<?php

declare(strict_types=1);

namespace App\Filament\Resources\CourseSectionResource\RelationManagers;

use App\Enums\LessonType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Phase 2 — manage the lessons of one course section.
 *
 * Rows are drag-reordered within the section only (position is
 * rewritten per section). Form fields adapt to the lesson type:
 * video lessons show a URL field, text/quiz lessons show content.
 */
class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->options(LessonType::class)
                    ->default(LessonType::Video->value)
                    ->required()
                    ->live(),

                TextInput::make('video_url')
                    ->label('Video URL')
                    ->url()
                    ->maxLength(2048)
                    ->visible(fn (callable $get): bool => $get('type') === LessonType::Video->value),

                Textarea::make('content')
                    ->rows(6)
                    ->columnSpanFull()
                    ->visible(fn (callable $get): bool => in_array(
                        $get('type'),
                        [LessonType::Text->value, LessonType::Quiz->value],
                        true,
                    )),

                TextInput::make('duration_minutes')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(600)
                    ->nullable()
                    ->helperText('Minutes; leave empty if not applicable.'),

                Toggle::make('is_free_preview')
                    ->label('Free preview')
                    ->helperText('Allow non-enrolled visitors to watch this lesson.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),

                TextColumn::make('type')
                    ->badge(),

                IconColumn::make('is_free_preview')
                    ->boolean()
                    ->label('Free preview'),

                TextColumn::make('duration_minutes')
                    ->numeric()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('position')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->reorderable('position')
            ->defaultSort('position');
    }
}
