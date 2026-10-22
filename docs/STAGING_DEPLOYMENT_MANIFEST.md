# FourSouls Staging Deployment Manifest

Target: `https://blueviolet-stingray-292410.hostingersite.com/`  
Project path: `/home/u253609925/websites/TfS6LP2Ra/public_html`

## Upload / overwrite

Upload the current application overlay from `app/`, `bootstrap/`, `config/`,
`database/migrations/`, `lang/`, `public/`, `resources/`, and `routes/`, plus
the current `composer.json`/`composer.lock` only when dependency state is being
updated. Include `docs/` for internal handover.

## Preserve

Never overwrite or delete `.env`, `vendor/`, `storage/`, the existing root
`index.php`, root `.htaccess`, database data, uploads, or runtime logs. Never run
`migrate:fresh` or `db:wipe` on staging.

## Root asset copies

Because Hostinger serves `public_html` as the document root, the package must
contain these root copies alongside the Laravel source:

- `build/` copied from `public/build/` (manifest currently references `assets/app-c0XbyJ04.css` and `assets/app-BVfFs1lK.js`).
- `css/` copied from `public/css/` (Filament assets).
- `js/` copied from `public/js/` (Filament assets).
- `fonts/` copied from `public/fonts/` (Filament fonts).

No versioned `livewire-*` static fallback is currently referenced by the
customer Blade layouts. Preserve the existing Hostinger Livewire arrangement if
present; do not introduce an outdated fallback.

## Optional / local-only

Exclude `node_modules/`, `.git/`, `runtime_backups/`, local logs, SQL dumps,
debugbar runtime data, and any local `.env` copy. The local database dump files
under `database/sql/generated/` are not deployment assets.

## Safe Hostinger commands

```sh
cd /home/u253609925/websites/TfS6LP2Ra/public_html
/usr/bin/php8.3 artisan optimize:clear
/usr/bin/php8.3 artisan migrate --force
/usr/bin/php8.3 artisan storage:link
/usr/bin/php8.3 artisan config:cache
/usr/bin/php8.3 artisan route:cache
/usr/bin/php8.3 artisan view:cache
```

Run `migrate --force` only after reviewing the migration status and only when
the staging backup is confirmed. Do not run npm on Hostinger.
