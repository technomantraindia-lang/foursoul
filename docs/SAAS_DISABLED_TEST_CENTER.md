# FourSouls Demo Mode

## Current switches

The demo defaults are:

```env
SAAS_ENABLED=false
TEST_CENTER_ENABLED=false
```

`SAAS_ENABLED=false` preserves all SaaS source code, migrations, models,
relationships, and tables, but does not register the platform Tenant resource
in the active Filament panel. Customer subscriptions, plans, orders, payments,
enrollments, and course access remain available.

`TEST_CENTER_ENABLED=false` keeps the Test Center unavailable, including direct
URLs. For local or staging QA, set it to `true`, then run:

```bash
php artisan optimize:clear
```

Only an active `super_admin` can access `/admin/test-center`.

## Test Center

The Test Center calls the existing idempotent `LocalDemoSeeder` through
`TestDataService`, then runs structured non-destructive application checks via
`SystemTestService`. Results are shown in the Filament page without exposing
stack traces. Reset targets only the deterministic local demo records and is
never a database wipe.

No gateway, mail, WhatsApp, webhook, push, or external payment call is made by
the system checks.

## Re-enable SaaS

Set `SAAS_ENABLED=true`, clear the cached configuration, and reload the panel:

```bash
php artisan optimize:clear
```

No source restoration or database migration reversal is required.

## Verification

- Tenant resource is absent from the default demo route inventory.
- Dashboard, analytics, learning, commerce, assessment, and CMS routes remain registered.
- New PHP classes pass syntax validation.
- Existing focused admin tests remain the regression gate.
