<?php

declare(strict_types=1);

namespace App\Services\Communication\Contracts;

/**
 * Provider abstraction for WhatsApp delivery.
 *
 * Implementations: WATI, Meta Cloud API, Twilio, or any future provider.
 * No vendor lock-in: the communication engine depends only on this contract.
 */
interface WhatsAppProviderInterface
{
    /**
     * Send a text/template message to the given mobile number (E.164).
     *
     * @param  array<string, mixed>  $payload  Template name, variables, media, etc.
     * @return bool True when the provider accepted the message.
     */
    public function send(string $toMobile, string $message, array $payload = []): bool;

    /** Human-readable provider key used in config and audit metadata. */
    public function key(): string;
}