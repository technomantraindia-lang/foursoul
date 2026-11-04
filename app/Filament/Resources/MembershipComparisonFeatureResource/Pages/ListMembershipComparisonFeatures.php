<?php

declare(strict_types=1);

namespace App\Filament\Resources\MembershipComparisonFeatureResource\Pages;

use App\Filament\Resources\MembershipComparisonFeatureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMembershipComparisonFeatures extends ListRecords
{
    protected static string $resource = MembershipComparisonFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Add Comparison Row')];
    }
}
