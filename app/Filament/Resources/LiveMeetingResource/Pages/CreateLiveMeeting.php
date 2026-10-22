<?php

declare(strict_types=1);

namespace App\Filament\Resources\LiveMeetingResource\Pages;

use App\Filament\Resources\LiveMeetingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveMeeting extends CreateRecord
{
    protected static string $resource = LiveMeetingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['video_type'] = 'live_meeting';
        return $data;
    }
}
