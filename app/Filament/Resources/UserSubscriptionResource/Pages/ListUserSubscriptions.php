<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserSubscriptionResource\Pages;

use App\Filament\Resources\UserSubscriptionResource;
use Filament\Resources\Pages\ListRecords;

final class ListUserSubscriptions extends ListRecords
{
    protected static string $resource = UserSubscriptionResource::class;
}
