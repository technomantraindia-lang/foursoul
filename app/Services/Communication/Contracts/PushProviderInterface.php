<?php

declare(strict_types=1);

namespace App\Services\Communication\Contracts;

/**
 * Provider abstraction for push notification delivery.
 *
 * Implementations: Firebase (FCM), OneSignal, or any future provider.
 * No vendor lock-in: the communication engine depends only on this contract.
 */
interface PushProviderInterface
{
    /**
     * Send a push notification to a device token.
     *
     * @param  array<string, mixed>  $payload  Title, body, deep link, data bag, etc.
     * @return bool True when the provider accepted the message.
     */
    public function send(string $deviceToken, string $title, string $body, array $payload = []): bool;

    /** Human-readable provider key used in config and audit metadata. */
    public function key(): string;
}