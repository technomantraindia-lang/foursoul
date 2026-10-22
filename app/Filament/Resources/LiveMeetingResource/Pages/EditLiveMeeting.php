<?php

declare(strict_types=1);

namespace App\Filament\Resources\LiveMeetingResource\Pages;

use App\Filament\Resources\LiveMeetingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLiveMeeting extends EditRecord
{
    protected static string $resource = LiveMeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
