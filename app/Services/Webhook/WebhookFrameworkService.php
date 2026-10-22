<?php

namespace App\Services\Webhook;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Phase 12 — Part 8: Webhook framework.
 *
 * Signed outbound delivery with HMAC signature, timestamp-based replay
 * protection, exponential-backoff retry, dead-letter handling, and full
 * delivery history persisted on webhook_deliveries.
 */
class WebhookFrameworkService
{
    public const SIGNATURE_HEADER = 'X-Foursouls-Signature';
    public const TIMESTAMP_HEADER = 'X-Foursouls-Timestamp';
    public const REPLAY_TOLERANCE_SECONDS = 300;

    public const MAX_ATTEMPTS = 5;

    /** Deliver a payload to a subscribed webhook endpoint. */
    public function deliver(Webhook $webhook, string $event, array $payload): WebhookDelivery
    {
        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $payload,
            'attempts' => 0,
            'status' => 'pending',
        ]);

        return $this->attempt($delivery);
    }

    /** Execute one delivery attempt with signing + replay-safe headers. */
    public function attempt(WebhookDelivery $delivery): WebhookDelivery
    {
        $webhook = $delivery->webhook;
        $delivery->increment('attempts');
        $delivery->refresh();
        $timestamp = (string) time();
        $body = json_encode([
            'id' => $delivery->id,
            'event' => $delivery->event,
            'payload' => $delivery->payload,
            'timestamp' => $timestamp,
        ]);

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->signedHeaders($webhook->secret, $body, $timestamp))
                ->withBody($body, 'application/json')
                ->post($webhook->url);

            if ($response->successful()) {
                $delivery->markSuccessful($response->status());
            } else {
                $this->scheduleRetryOrFail($delivery, "HTTP {$response->status()}");
            }
        } catch (\Throwable $e) {
            $this->scheduleRetryOrFail($delivery, $e->getMessage());
        }

        return $delivery->refresh();
    }

    /**
     * Verify an inbound webhook signature (replay-protected).
     * Returns true only when the HMAC matches and the timestamp is fresh.
     */
    public function verifyInbound(string $secret, string $body, string $signatureHeader, string $timestampHeader): bool
    {
        $expected = hash_hmac('sha256', $timestampHeader.'.'.$body, $secret);

        if (! hash_equals($expected, $signatureHeader)) {
            return false;
        }

        return abs(time() - (int) $timestampHeader) <= self::REPLAY_TOLERANCE_SECONDS;
    }

    protected function signedHeaders(string $secret, string $body, string $timestamp): array
    {
        return [
            self::SIGNATURE_HEADER => hash_hmac('sha256', $timestamp.'.'.$body, $secret),
            self::TIMESTAMP_HEADER => $timestamp,
        ];
    }

    protected function scheduleRetryOrFail(WebhookDelivery $delivery, string $error): void
    {
        if ($delivery->attempts >= self::MAX_ATTEMPTS) {
            $delivery->markDeadLetter($error);
            Log::channel('production')->warning('webhook.dead_letter', [
                'delivery_id' => $delivery->id,
                'event' => $delivery->event,
                'error' => $error,
            ]);

            return;
        }

        // Exponential backoff: 30s, 2m, 8m, 32m.
        $delaySeconds = 30 * (4 ** max(0, $delivery->attempts - 1));
        $delivery->markPendingRetry($error, now()->addSeconds($delaySeconds));

        self::class.':retry' === '' ?: dispatch(function () use ($delivery): void {
            app(self::class)->attempt($delivery);
        })->delay(now()->addSeconds($delaySeconds))->onQueue(\App\Support\QueueDomains::RETRIES);
    }
}