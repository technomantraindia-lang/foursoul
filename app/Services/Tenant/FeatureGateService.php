<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use App\Models\TenantFeature;

class FeatureGateService
{
    public function isEnabled(Tenant $tenant, string $feature): bool
    {
        $flag = TenantFeature::query()
            ->where('tenant_id', $tenant->id)
            ->where('feature', $feature)
            ->first();

        return (bool) ($flag->enabled ?? false);
    }

    public function enabled(Tenant $tenant, string $feature): bool
    {
        return $this->isEnabled($tenant, $feature);
    }

    public function set(Tenant $tenant, string $feature, bool $enabled, ?array $config = null): TenantFeature
    {
        return TenantFeature::updateOrCreate(
            ['tenant_id' => $tenant->id, 'feature' => $feature],
            ['enabled' => $enabled, 'config' => $config]
        );
    }

    public function setEnabled(Tenant $tenant, string $feature, bool $enabled, ?array $config = null): TenantFeature
    {
        return $this->set($tenant, $feature, $enabled, $config);
    }

    public function allFor(Tenant $tenant): array
    {
        return TenantFeature::query()
            ->where('tenant_id', $tenant->id)
            ->pluck('enabled', 'feature')
            ->all();
    }

    /**
     * Catalogue of platform features that can be toggled per tenant.
     *
     * @return list<string>
     */
    public function allFeatures(): array
    {
        return [
            'courses',
            'commerce',
            'live_sessions',
            'certificates',
            'cms',
            'blog',
            'analytics',
            'reports',
            'api',
            'community',
            'forum',
            'assignments',
            'quizzes',
            'notifications',
            'email',
            'whatsapp',
            'push',
        ];
    }

    public function assertEnabled(Tenant $tenant, string $feature): void
    {
        if (! $this->isEnabled($tenant, $feature)) {
            abort(403, "The [{$feature}] feature is not enabled for this workspace.");
        }
    }

    /**
     * True when current usage is strictly below the tenant cap, or the cap is unlimited.
     *
     * @param  string  $key  users|admins|courses|storage|branches|live_sessions
     */
    public function withinLimit(Tenant $tenant, string $key, int $current): bool
    {
        $limit = match ($key) {
            'users' => $tenant->user_limit,
            'admins' => $tenant->admin_limit,
            'courses' => $tenant->course_limit,
            'storage' => $tenant->storage_limit_bytes,
            'branches' => $tenant->branch_limit,
            'live_sessions' => $tenant->live_session_limit,
            default => null,
        };

        if ($limit === null) {
            return true;
        }

        return $current < $limit;
    }
}
