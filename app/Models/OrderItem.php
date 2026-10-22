<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 5 (§6): Order line item. Snapshots are frozen at purchase time.
 */
class OrderItem extends Model
{
    protected $fillable = [
        'uuid',
        'order_id',
        'product_type',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'line_total',
        'currency',
        'snapshot_name',
        'snapshot_description',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $item) {
            $item->uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Resolve the concrete product (Course, SubscriptionPlan, ...).
     */
    public function product(): ?Model
    {
        $class = match ($this->product_type) {
            'course' => Course::class,
            'plan' => SubscriptionPlan::class,
            default => null,
        };

        return $class ? $class::find($this->product_id) : null;
    }
}