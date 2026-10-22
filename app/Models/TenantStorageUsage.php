<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantStorageUsage extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_storage_usage';

    protected $fillable = [
        'tenant_id',
        'category',
        'bytes_used',
        'file_count',
        'last_calculated_at',
    ];

    protected $casts = [
        'bytes_used' => 'integer',
        'file_count' => 'integer',
        'last_calculated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}