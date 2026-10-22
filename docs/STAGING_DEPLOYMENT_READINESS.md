# FourSouls Staging Deployment Readiness

## Status

- Target: Hostinger staging/client demo.
- Local PHP: 8.3.33; project requires PHP `^8.3`.
- Application timezone: `Asia/Dubai` by default through `BUSINESS_TIMEZONE`.
- Local migration status: all 41 migrations applied; no pending local migrations.
- Database reset required: no.
- Full regression baseline: 193/193 passing, 556 assertions, 1 risky test.

## Frontend and assets

The local Vite build passes. Current manifest assets are:

- `build/assets/app-c0XbyJ04.css` (89,302 bytes)
- `build/assets/app-BVfFs1lK.js` (1,600 bytes)

Root copies `build/`, `css/`, `js/`, and `fonts/` have been prepared from the
corresponding `public/` directories for the existing Hostinger document-root
workaround. Filament assets are present under `public/css`, `public/js`, and
`public/fonts`. Do not add a generic Vite theme to Filament.

## Environment safety

The local `.env` remains local (`APP_ENV=local`, `APP_DEBUG=true`, local URL) and
must not be uploaded. Configure staging separately with `APP_ENV=staging`,
`APP_DEBUG=false`, the Hostinger URL, `BUSINESS_TIMEZONE=Asia/Dubai`, and the
staging database credentials. `SAAS_ENABLED` defaults to disabled. Live UAE
payment is not configured and is acceptable for the client demo.

## Runtime dependencies

The app uses `QUEUE_CONNECTION=database`; a queue worker is required for queued
login activity and notification jobs. Without a worker, those asynchronous
notifications remain queued while synchronous page flows continue. The
scheduler has no critical demo-only schedule in `routes/console.php`; scheduled
expiry/reminder operations should be configured before production use.

`MAIL_MAILER=log` is demo-safe but does not deliver real password-reset,
verification, or notification email. Storage is linked locally; preserve staging
uploads and the existing link. No static versioned Livewire fallback is referenced
by the current customer layouts.

## Deployment commands

See [STAGING_DEPLOYMENT_MANIFEST.md](STAGING_DEPLOYMENT_MANIFEST.md) for the
safe upload classification and exact `/usr/bin/php8.3 artisan` command sequence.
Use only safe migrations after a staging backup; never reset the database.

## Security review

No secrets were found in the handbook/deployment documentation. Local runtime
backups and SQL dumps exist in the project workspace and must be excluded from
the upload package. Do not expose `.env`, `storage/`, SQL dumps, credential JSON,
or debug routes through the web root. This is a deployment-readiness review, not
a penetration test.

## Verification

- `php artisan route:cache`: passed locally.
- `npm.cmd run build`: passed locally.
- `php artisan migrate:status`: all local migrations ran.
- `php artisan storage:link`: existing link present.
- Root `.htaccess` and `public/index.php`: preserve existing Hostinger copies.
- Browser/staging smoke test: not run from this workstation.
