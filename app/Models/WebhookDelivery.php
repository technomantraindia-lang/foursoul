<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_status',
        'attempts',
        'status',
        'next_retry_at',
        'delivered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'response_status' => 'integer',
        'delivered_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'delivered';
    }

    public function isPendingRetry(): bool
    {
        return $this->status === 'pending'
            && ($this->next_retry_at === null || $this->next_retry_at->isPast());
    }

    public function markSuccessful(int $responseStatus): void
    {
        $this->forceFill([
            'status' => 'delivered',
            'response_status' => $responseStatus,
            'delivered_at' => now(),
            'next_retry_at' => null,
        ])->save();
    }

    public function markPendingRetry(string $error, $nextRetryAt): void
    {
        $this->forceFill([
            'status' => 'pending',
            'next_retry_at' => $nextRetryAt,
            'attempts' => $this->attempts + 1,
        ])->save();
    }

    public function markDeadLetter(string $error): void
    {
        $this->forceFill([
            'status' => 'failed',
            'next_retry_at' => null,
            'attempts' => $this->attempts + 1,
        ])->save();
    }
}
