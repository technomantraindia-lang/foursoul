<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserSubscriptionResource\Pages;

use App\Filament\Resources\UserSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUserSubscription extends CreateRecord
{
    protected static string $resource = UserSubscriptionResource::class;
}
