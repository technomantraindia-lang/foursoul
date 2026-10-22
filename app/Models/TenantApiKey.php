<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantApiKey extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'key_prefix',
        'key_hash',
        'scopes',
        'rate_limit_per_minute',
        'last_used_at',
        'revoked_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isActive(): bool
    {
        return ! $this->isRevoked();
    }

    public function hasScope(string $scope): bool
    {
        if (in_array('*', (array) $this->scopes, true)) {
            return true;
        }

        return in_array($scope, (array) $this->scopes, true);
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
    }

    public function rotate(string $keyHash): void
    {
        $this->forceFill([
            'key_hash' => $keyHash,
            'revoked_at' => null,
            'last_used_at' => null,
        ])->save();
    }

    public function matchesPlaintext(string $plaintext): bool
    {
        if ($this->isRevoked()) {
            return false;
        }

        return hash_equals($this->key_hash, hash('sha256', $plaintext));
    }
}