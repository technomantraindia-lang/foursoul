<?php

declare(strict_types=1);

namespace App\Filament\Resources\LiveSessionResource\Pages;

use App\Filament\Resources\LiveSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLiveSessions extends ListRecords
{
    protected static string $resource = LiveSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add content')
                ->icon('heroicon-m-plus'),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All content'),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'scheduled')),
            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'draft')),
            'meetings' => Tab::make('Meeting links')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('video_type', 'live_meeting')),
            'videos' => Tab::make('Videos')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('video_type', '!=', 'live_meeting')),
        ];
    }
}
