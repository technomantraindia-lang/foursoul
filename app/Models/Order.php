<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Phase 5 (§4): Customer order. Totals are always server-calculated.
 */
class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'currency',
        'subtotal',
        'discount',
        'tax',
        'total',
        'status',
        'payment_status',
        'coupon_id',
        'billing_information',
        'source',
        'metadata',
    ];

    protected $attributes = [
        'status' => OrderStatus::Pending->value,
        'payment_status' => PaymentStatus::Pending->value,
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'billing_information' => 'array',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            $order->uuid ??= (string) \Illuminate\Support\Str::uuid();
            $order->order_number ??= static::generateOrderNumber();
        });
    }

    /**
     * Unique, human-readable order number: FS-YYYYMMDD-XXXXXX.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = sprintf(
                'FS-%s-%s',
                now()->format('Ymd'),
                strtoupper(bin2hex(random_bytes(3)))
            );
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function successfulPayments(): HasMany
    {
        return $this->payments()->where('status', PaymentStatus::Succeeded);
    }

    public function amountPaid(): string
    {
        return (string) $this->successfulPayments()->sum('amount');
    }

    public function isFullyPaid(): bool
    {
        return bccomp($this->amountPaid(), (string) $this->total, 2) >= 0
            && $this->status === OrderStatus::Paid;
    }
}