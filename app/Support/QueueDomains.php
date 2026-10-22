<?php

namespace App\Support;

/**
 * Phase 12 — Part 2: Queue architecture.
 *
 * Canonical queue names for every async domain. Workers are configured per
 * queue so heavy work (video/media/imports) never starves latency-sensitive
 * traffic (commerce/email). Retries use `retries`; exhausted jobs fall to
 * `dead_letter` via failed-job handling (see FailedJobHandler).
 */
final class QueueDomains
{
    public const COMMERCE = 'commerce';
    public const EMAILS = 'emails';
    public const WHATSAPP = 'whatsapp';
    public const PUSH = 'push';
    public const ANALYTICS = 'analytics';
    public const REPORTS = 'reports';
    public const CERTIFICATES = 'certificates';
    public const MEDIA = 'media';
    public const VIDEO_PROCESSING = 'video-processing';
    public const IMPORTS = 'imports';
    public const EXPORTS = 'exports';
    public const RETRIES = 'retries';
    public const DEAD_LETTER = 'dead-letter';

    /** All managed queues in dispatch-priority order. */
    public static function all(): array
    {
        return [
            self::COMMERCE,
            self::EMAILS,
            self::WHATSAPP,
            self::PUSH,
            self::ANALYTICS,
            self::REPORTS,
            self::CERTIFICATES,
            self::MEDIA,
            self::VIDEO_PROCESSING,
            self::IMPORTS,
            self::EXPORTS,
            self::RETRIES,
            self::DEAD_LETTER,
        ];
    }
}