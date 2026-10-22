<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserSubscriptionResource\Pages;

use App\Filament\Resources\UserSubscriptionResource;
use Filament\Resources\Pages\EditRecord;

final class EditUserSubscription extends EditRecord
{
    protected static string $resource = UserSubscriptionResource::class;
}
