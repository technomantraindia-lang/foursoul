<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final readonly class PaymentWebhookEvent
{
    public function __construct(
        public string $provider,
        public string $eventId,
        public string $transactionId,
        public string $orderReference,
        public string $status,
        public string $amount,
        public string $currency,
        public ?CarbonImmutable $occurredAt = null,
        public ?string $rawReference = null,
        public array $safeMetadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        foreach (['provider', 'event_id', 'transaction_id', 'order_reference', 'status', 'amount', 'currency'] as $field) {
            if (blank($data[$field] ?? null)) {
                throw ValidationException::withMessages([$field => 'This webhook field is required.']);
            }
        }
        if (! is_numeric($data['amount']) || (float) $data['amount'] < 0) {
            throw ValidationException::withMessages(['amount' => 'Webhook amount is invalid.']);
        }
        $status = strtolower((string) $data['status']);
        if (! in_array($status, ['pending', 'processing', 'paid', 'success', 'succeeded', 'failed', 'cancelled', 'refunded', 'partially_refunded'], true)) {
            throw ValidationException::withMessages(['status' => 'Webhook status is unsupported.']);
        }
        return new self((string) $data['provider'], (string) $data['event_id'], (string) $data['transaction_id'], (string) $data['order_reference'], $status, number_format((float) $data['amount'], 2, '.', ''), strtoupper((string) $data['currency']), isset($data['occurred_at']) ? CarbonImmutable::parse($data['occurred_at']) : null, $data['raw_reference'] ?? null, is_array($data['safe_metadata'] ?? null) ? $data['safe_metadata'] : []);
    }
}
