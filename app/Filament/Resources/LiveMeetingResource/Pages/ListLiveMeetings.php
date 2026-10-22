<?php

declare(strict_types=1);

namespace App\Filament\Resources\LiveMeetingResource\Pages;

use App\Filament\Resources\LiveMeetingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLiveMeetings extends ListRecords
{
    protected static string $resource = LiveMeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Live Zoom Link')
                ->icon('heroicon-m-plus'),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Plans'),
            'all_members' => Tab::make('🌟 All Members')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('min_plan_tier', 'all')),
            'begin' => Tab::make('🌱 Begin Plan')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('min_plan_tier', 'begin')),
            'commit' => Tab::make('🌿 Commit Plan')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('min_plan_tier', 'commit')),
            'transform' => Tab::make('🌳 Transform Plan')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('min_plan_tier', 'transform')),
            'sadhana' => Tab::make('👑 Sadhana VIP')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('min_plan_tier', 'sadhana')),
        ];
    }
}
