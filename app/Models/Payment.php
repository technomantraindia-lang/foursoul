<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 5 (§7): Gateway-independent payment record.
 */
class Payment extends Model
{
    protected $fillable = [
        'uuid',
        'order_id',
        'user_id',
        'gateway',
        'gateway_reference',
        'status',
        'currency',
        'amount',
        'paid_at',
        'failure_reason',
        'metadata',
    ];

    protected $attributes = [
        'status' => PaymentStatus::Pending->value,
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $payment) {
            $payment->uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}