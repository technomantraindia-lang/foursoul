<?php

declare(strict_types=1);

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Filament\Resources\CourseSectionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Phase 2 — manage a course's sections from the course edit page.
 *
 * Rows can be drag-reordered (position is rewritten per course only).
 * The "Lessons" action opens the section in the hidden
 * CourseSectionResource, which hosts the Lessons relation manager.
 */
class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),

                TextColumn::make('lessons_count')
                    ->counts('lessons')
                    ->label('Lessons'),

                TextColumn::make('position')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),

                Action::make('lessons')
                    ->label('Lessons')
                    ->icon('heroicon-m-play-circle')
                    ->url(fn ($record): string => CourseSectionResource::getUrl('edit', ['record' => $record]))
                    ->visible(static fn (): bool => CourseSectionResource::canViewAny()),

                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->reorderable('position')
            ->defaultSort('position');
    }
}
