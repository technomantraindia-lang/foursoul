<?php

declare(strict_types=1);

namespace App\Filament\Resources\VideoLessonResource\Pages;

use App\Filament\Resources\VideoLessonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVideoLesson extends EditRecord
{
    protected static string $resource = VideoLessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
