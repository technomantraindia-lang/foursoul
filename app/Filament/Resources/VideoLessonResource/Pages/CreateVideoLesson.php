<?php

declare(strict_types=1);

namespace App\Filament\Resources\VideoLessonResource\Pages;

use App\Filament\Resources\VideoLessonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVideoLesson extends CreateRecord
{
    protected static string $resource = VideoLessonResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['video_type'])) {
            $data['video_type'] = !empty($data['video_file']) ? 'uploaded_video' : 'youtube';
        }
        return $data;
    }
}
