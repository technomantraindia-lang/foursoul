<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 5 (§9): Invoice generated on successful payment.
 */
class Invoice extends Model
{
    protected $fillable = [
        'uuid',
        'invoice_number',
        'order_id',
        'user_id',
        'payment_id',
        'currency',
        'subtotal',
        'discount',
        'tax',
        'total',
        'issued_at',
        'billing_information',
        'line_items',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'issued_at' => 'datetime',
            'billing_information' => 'array',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $invoice) {
            $invoice->uuid ??= (string) \Illuminate\Support\Str::uuid();
            $invoice->invoice_number ??= static::generateInvoiceNumber();
        });
    }

    public static function generateInvoiceNumber(): string
    {
        do {
            $number = sprintf(
                'INV-%s-%s',
                now()->format('Ymd'),
                strtoupper(bin2hex(random_bytes(3)))
            );
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}