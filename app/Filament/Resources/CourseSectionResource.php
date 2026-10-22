<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CourseSectionResource\Pages;
use App\Filament\Resources\CourseSectionResource\RelationManagers;
use App\Models\CourseSection;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;

/**
 * Phase 2 ??? hidden host resource for per-section lesson management.
 *
 * Filament does not nest relation managers, so sections are edited on
 * the CourseResource and each section row links here (via the
 * SectionsRelationManager "Lessons" action) to manage its lessons.
 * Hidden from navigation; access is gated by CourseSectionPolicy.
 */
class CourseSectionResource extends Resource
{
    protected static ?string $model = CourseSection::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\LessonsRelationManager::class,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseSections::route('/'),
            'edit' => Pages\EditCourseSection::route('/{record}/edit'),
        ];
    }
}
