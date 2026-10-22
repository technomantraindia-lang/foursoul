<?php

declare(strict_types=1);

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-slug when the admin left it blank.
        if (blank($data['slug'] ?? null)) {
            $data['slug'] = Str::slug($data['title'] ?? '');
        }

        // Stamp authorship with the signed-in admin.
        $data['created_by'] = auth('admin')->id();

        return $data;
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label(__('filament-actions.create.label'));
    }
}