<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 5 (§12): Refund foundation (no gateway integration yet).
 */
class Refund extends Model
{
    protected $fillable = [
        'uuid',
        'order_id',
        'payment_id',
        'user_id',
        'amount',
        'currency',
        'status',
        'reason',
        'processed_by_admin_id',
        'processed_at',
        'metadata',
    ];

    protected $attributes = [
        'status' => RefundStatus::Requested->value,
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'status' => RefundStatus::class,
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $refund) {
            $refund->uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}