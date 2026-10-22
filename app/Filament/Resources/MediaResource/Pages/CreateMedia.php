<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['file_path']) && is_string($data['file_path'])) {
            $fullPath = storage_path('app/public/' . $data['file_path']);

            if (is_file($fullPath)) {
                $data['mime_type'] = mime_content_type($fullPath);
                $data['size'] = filesize($fullPath);
            }
        }

        return $data;
    }
}