<?php

declare(strict_types=1);

namespace App\Filament\Resources\CourseSectionResource\Pages;

use App\Filament\Resources\CourseSectionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

/**
 * Phase 2 — fallback listing for sections.
 *
 * Sections are normally managed from the CourseResource relation
 * manager; this page only exists so the hidden resource has a valid
 * index route (e.g. for breadcrumbs and redirects).
 */
class ListCourseSections extends ListRecords
{
    protected static string $resource = CourseSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'with_lessons' => Tab::make('With lessons')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->has('lessons')),
        ];
    }
}
