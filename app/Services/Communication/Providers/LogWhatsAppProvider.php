<?php

declare(strict_types=1);

namespace App\Services\Communication\Providers;

use App\Services\Communication\Contracts\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * Default no-op WhatsApp provider. Logs the message instead of calling a
 * real vendor API. Swap with WatiProvider, MetaCloudProvider or
 * TwilioProvider via config('communication.whatsapp.provider') when ready.
 */
class LogWhatsAppProvider implements WhatsAppProviderInterface
{
    public function send(string $toMobile, string $message, array $payload = []): bool
    {
        Log::info('WhatsApp message placeholder (provider not configured).', [
            'provider' => $this->key(),
            'to' => $toMobile,
            'message' => $message,
            'payload' => $payload,
        ]);

        return true;
    }

    public function key(): string
    {
        return 'log';
    }
}