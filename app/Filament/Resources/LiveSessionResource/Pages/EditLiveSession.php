<?php

declare(strict_types=1);

namespace App\Filament\Resources\LiveSessionResource\Pages;

use App\Filament\Resources\LiveSessionResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveSession extends EditRecord
{
    protected static string $resource = LiveSessionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $contentKind = $data['metadata']['content_kind'] ?? null;
        if ($contentKind !== 'meeting') {
            $data['scheduled_end_at'] = null;
        }

        return $data;
    }
}
