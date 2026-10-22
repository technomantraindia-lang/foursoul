<?php

namespace App\Services\Tenant;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantService
{
    public function __construct(
        protected FeatureGateService $features,
    ) {}

    public function create(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'company_name' => $data['company_name'],
                'status' => $data['status'] ?? TenantStatus::default(),
                'owner_user_id' => $data['owner_user_id'] ?? $data['owner_id'] ?? null,
                'brand_name' => $data['brand_name'] ?? $data['company_name'],
                'timezone' => $data['timezone'] ?? 'UTC',
                'country_code' => $data['country_code'] ?? null,
                'currency' => $data['currency'] ?? $data['currency_code'] ?? 'USD',
                'language' => $data['language'] ?? $data['locale'] ?? 'en',
                'storage_limit_bytes' => $data['storage_limit_bytes'] ?? 5368709120,
                'user_limit' => $data['user_limit'] ?? 25,
                'admin_limit' => $data['admin_limit'] ?? 5,
                'course_limit' => $data['course_limit'] ?? 50,
                'branch_limit' => $data['branch_limit'] ?? 1,
                'live_session_limit' => $data['live_session_limit'] ?? 10,
                'metadata' => $data['metadata'] ?? null,
            ]);

            TenantSetting::create([
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'group' => 'brand',
                'key' => 'brand_name',
                'value' => $tenant->brand_name,
                'type' => 'string',
            ]);

            foreach ($this->features->allFeatures() as $feature) {
                $this->features->setEnabled($tenant, $feature, false);
            }

            $this->audit($tenant, 'system', null, 'tenant_created');

            return $tenant;
        });
    }

    public function suspend(Tenant $tenant): void
    {
        $tenant->forceFill([
            'status' => TenantStatus::Suspended,
            'suspended_at' => now(),
        ])->save();

        $this->audit($tenant, 'system', null, 'tenant_suspended');
    }

    public function activate(Tenant $tenant): void
    {
        $tenant->forceFill([
            'status' => TenantStatus::Active,
            'suspended_at' => null,
        ])->save();

        $this->audit($tenant, 'system', null, 'tenant_activated');
    }

    public function close(Tenant $tenant): void
    {
        $tenant->forceFill(['status' => TenantStatus::Cancelled])->save();
        $this->audit($tenant, 'system', null, 'tenant_cancelled');
    }

    public function hasReachedUserLimit(Tenant $tenant, int $currentUsers): bool
    {
        return ! $this->features->withinLimit($tenant, 'users', $currentUsers);
    }

    public function hasReachedBranchLimit(Tenant $tenant, int $currentBranches): bool
    {
        return ! $this->features->withinLimit($tenant, 'branches', $currentBranches);
    }

    public function addStorageUsage(Tenant $tenant, string $category, int $bytes): void
    {
        $usage = $tenant->storageUsage()->firstOrCreate(
            ['category' => $category],
            ['bytes_used' => 0]
        );

        $usage->increment('bytes_used', $bytes);
        $tenant->increment('storage_used_bytes', $bytes);
    }

    public function reduceStorageUsage(Tenant $tenant, string $category, int $bytes): void
    {
        $usage = $tenant->storageUsage()->where('category', $category)->first();

        if ($usage) {
            $usage->decrement('bytes_used', min($bytes, $usage->bytes_used));
        }

        $tenant->decrement('storage_used_bytes', min($bytes, $tenant->storage_used_bytes));
    }

    public function hasStorageAvailable(Tenant $tenant, int $bytes): bool
    {
        return $this->features->withinLimit($tenant, 'storage', $tenant->storage_used_bytes + $bytes);
    }

    public function settingsFor(Tenant $tenant): TenantSetting
    {
        return TenantSetting::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'group' => 'brand',
                'key' => 'brand_name',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'value' => $tenant->brand_name,
                'type' => 'string',
            ]
        );
    }

    public function updateSettings(Tenant $tenant, array $attributes): TenantSetting
    {
        $settings = $this->settingsFor($tenant);

        if (array_key_exists('brand_name', $attributes)) {
            $settings->value = $attributes['brand_name'];
            $settings->save();
        }

        foreach ($attributes as $key => $value) {
            if ($key === 'brand_name') {
                continue;
            }

            TenantSetting::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'group' => 'brand',
                    'key' => $key,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                    'type' => is_array($value) ? 'json' : 'string',
                ]
            );
        }

        return $settings->refresh();
    }

    public function audit(Tenant $tenant, string $actorType, ?int $actorId, string $action, array $context = []): void
    {
        $tenant->auditLogs()->create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $action,
            'metadata' => $context,
        ]);
    }
}
