<?php

declare(strict_types=1);

namespace App\Filament\Resources\LiveSessionResource\Pages;

use App\Filament\Resources\LiveSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveSession extends CreateRecord
{
    protected static string $resource = LiveSessionResource::class;

    protected \Filament\Support\Enums\Width|string|null $maxContentWidth = 'full';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $contentKind = $data['metadata']['content_kind'] ?? null;
        if ($contentKind === 'timetable') {
            $data['video_type'] = 'member_timetable';
            $data['metadata']['member_content'] = true;
            $data['allowed_plans'] = [];
            $data['min_plan_tier'] = 'all';
            $data['is_locked_for_non_members'] = false;
            $data['scheduled_end_at'] = null;
        } elseif ($contentKind !== 'meeting') {
            $data['scheduled_end_at'] = null;
        }

        return $data;
    }
}
