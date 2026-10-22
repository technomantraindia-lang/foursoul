<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssignmentResource\Pages;

use App\Filament\Resources\AssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth('admin')->check()) {
            $data['created_by'] ??= auth('admin')->id();
        }

        return $data;
    }
}