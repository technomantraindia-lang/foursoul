<?php

declare(strict_types=1);

namespace App\Services\Communication\Providers;

use App\Services\Communication\Contracts\PushProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * Default no-op push provider. Logs the notification instead of calling a
 * real vendor API. Swap with FirebaseProvider or OneSignalProvider via
 * config('communication.push.provider') when ready.
 */
class LogPushProvider implements PushProviderInterface
{
    public function send(string $deviceToken, string $title, string $body, array $payload = []): bool
    {
        Log::info('Push notification placeholder (provider not configured).', [
            'provider' => $this->key(),
            'token' => substr($deviceToken, 0, 12).'…',
            'title' => $title,
        ]);

        return true;
    }

    public function key(): string
    {
        return 'log';
    }
}