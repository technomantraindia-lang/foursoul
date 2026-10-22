<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

/**
 * Phase 12 — Part 10: Security hardening architecture.
 *
 * Centralizes password policy, signed URL issuance, file upload
 * validation, API key rotation helpers, and security event logging.
 * CSRF/CSP/trusted proxies are enforced via framework config + middleware;
 * this service is the single application-level seam for the rest.
 */
class SecurityHardeningService
{
    /** Password policy shared by registration, reset, and admin forms. */
    public function passwordRules(): Password
    {
        return Password::min(10)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
    }

    /**
     * Allowed upload MIME types per media category.
     *
     * @return array<string, array<int, string>>
     */
    public function allowedMimeTypes(): array
    {
        return [
            'image' => ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif'],
            'video' => ['video/mp4', 'video/webm'],
            'document' => ['application/pdf', 'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv'],
        ];
    }

    /** Validate an uploaded file against a category policy. */
    public function validateUpload(string $category, $uploadedFile): bool
    {
        $allowed = $this->allowedMimeTypes()[$category] ?? [];

        return in_array($uploadedFile->getMimeType(), $allowed, true)
            && $uploadedFile->isValid();
    }

    /** Issue a time-limited signed URL (downloads, verification links). */
    public function signedUrl(string $route, array $parameters = [], int $ttlMinutes = 30): string
    {
        return URL::temporarySignedRoute($route, now()->addMinutes($ttlMinutes), $parameters);
    }

    /** Verify a signed URL request. */
    public function verifySignedUrl(\Illuminate\Http\Request $request): bool
    {
        return $request->hasValidSignature();
    }

    /**
     * Rotate an API key: revoke current secret, issue replacement.
     * The model keeps plaintext out of storage — only hash is persisted.
     */
    public function rotateApiKey(\App\Models\TenantApiKey $apiKey): array
    {
        $plain = bin2hex(random_bytes(32));

        $apiKey->rotate(hash('sha256', $plain));

        $this->logEvent('api_key.rotated', [
            'key_id' => $apiKey->id,
            'tenant_id' => $apiKey->tenant_id,
        ]);

        // Plaintext is returned exactly once to the caller.
        return ['secret' => $plain];
    }

    /** Structured security event log — correlation-ID ready. */
    public function logEvent(string $event, array $context = []): void
    {
        Log::channel('security')->info($event, $context + [
            'correlation_id' => request()?->header('X-Correlation-Id') ?? (string) \Illuminate\Support\Str::uuid(),
            'ip' => request()?->ip(),
        ]);
    }
}