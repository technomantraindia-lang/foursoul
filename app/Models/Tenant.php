<?php

namespace App\Models;

use App\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_name',
        'slug',
        'status',
        'owner_user_id',
        'brand_name',
        'timezone',
        'country_code',
        'currency',
        'language',
        'user_limit',
        'admin_limit',
        'course_limit',
        'storage_limit_bytes',
        'branch_limit',
        'live_session_limit',
        'certificate_limit',
        'email_limit_monthly',
        'storage_used_bytes',
        'metadata',
        'suspended_at',
    ];

    protected $casts = [
        'status' => TenantStatus::class,
        'storage_used_bytes' => 'integer',
        'storage_limit_bytes' => 'integer',
        'user_limit' => 'integer',
        'admin_limit' => 'integer',
        'course_limit' => 'integer',
        'branch_limit' => 'integer',
        'live_session_limit' => 'integer',
        'certificate_limit' => 'integer',
        'email_limit_monthly' => 'integer',
        'metadata' => 'array',
        'suspended_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tenant) {
            $tenant->uuid ??= (string) Str::uuid();
            $tenant->slug ??= Str::slug((string) $tenant->company_name).'-'.Str::lower(Str::random(6));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class)->latestOfMany();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(TenantInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TenantPayment::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(TenantFeature::class);
    }

    public function storageUsage(): HasMany
    {
        return $this->hasMany(TenantStorageUsage::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function primaryDomain()
    {
        return $this->hasOne(TenantDomain::class)->where('is_primary', true);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(TenantApiKey::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(TenantAuditLog::class);
    }

    public function hasFeature(string $feature): bool
    {
        return $this->features()
            ->where('feature', $feature)
            ->where('enabled', true)
            ->exists();
    }

    public function featureConfig(string $feature): ?array
    {
        $featureRow = $this->features()->where('feature', $feature)->first();

        return $featureRow?->config;
    }

    public function isActive(): bool
    {
        return $this->status->canAuthenticate();
    }
}
