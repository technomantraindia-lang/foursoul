<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDomain extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'domain',
        'is_primary',
        'ssl_status',
        'dns_status',
        'verified_at',
        'verification_token',
        'redirect_to_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'redirect_to_primary' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isVerified(): bool
    {
        return $this->dns_status === 'verified' && $this->verified_at !== null;
    }

    public function hasValidSsl(): bool
    {
        return $this->ssl_status === 'issued';
    }
}