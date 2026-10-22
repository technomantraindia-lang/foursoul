<?php

declare(strict_types=1);

namespace App\Filament\Resources\CourseSectionResource\Pages;

use App\Filament\Resources\CourseSectionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

/**
 * Phase 2 — edit a single course section and manage its lessons.
 *
 * Reached from the SectionsRelationManager "Lessons" action. The
 * header action returns to the parent course's edit page.
 */
class EditCourseSection extends EditRecord
{
    protected static string $resource = CourseSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToCourse')
                ->label('Back to course')
                ->icon('heroicon-m-arrow-left')
                ->url(fn (): string => \App\Filament\Resources\CourseResource::getUrl(
                    'edit',
                    ['record' => $this->getRecord()->course_id],
                )),
        ];
    }
}