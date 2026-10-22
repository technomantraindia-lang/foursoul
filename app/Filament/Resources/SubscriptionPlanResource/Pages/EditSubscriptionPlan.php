<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriptionPlanResource\Pages;

use App\Filament\Resources\SubscriptionPlanResource;
use App\Models\Admin;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSubscriptionPlan extends EditRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $admin = Auth::guard('admin')->user();
        if ($admin instanceof Admin) {
            $data['updated_by'] = $admin->id;
        }

        return $data;
    }
}