# Phase 12 — Production Engineering Notes

Covers: DB optimization review (Part 14), performance guardrails (Part 16),
DevOps preparation (Part 17), production checklists (Part 18),
and test architecture organization (Part 19). Runtime verification deferred.

---

## Part 14 — Database Optimization Review

Conventions already established by migrations (`2026_01_01_*`):

- **Indexes**: All foreign keys indexed; status/enum columns used in list
  filters should have composite indexes when a query filters on two or more
  of them together (e.g. `orders(tenant_id, status, created_at)`).
- **Composite indexes**: Recommended additions at first runtime migration:
  - `enrollments(user_id, course_id, status)`
  - `lesson_progress(enrollment_id, lesson_id)` unique
  - `webhook_deliveries(webhook_id, created_at)`
  - `notifications(user_id, status, created_at)`
- **Foreign keys / cascade**: Child rows cascade on parent delete for
  tenant-scoped data; audit logs use `nullOnDelete` to preserve history.
- **Soft deletes**: Applied to CMS content (pages, blog posts, media) and
  commerce records (orders, invoices) — never to progress/ledger tables.
- **Unique constraints**: Enforced on slugs, order numbers, certificate
  serials, API key hashes, tenant domains.
- **UUID strategy**: Public-facing identifiers (certificates, orders) use
  UUIDs/signed tokens; internal PKs remain big integers for join speed.

## Part 16 — Performance Guardrails

- **N+1 prevention**: Controllers must eager-load relations used in views
  (`Course::with('sections.lessons')`); Filament tables declare
  `getEloquentQuery()` overrides with eager loads.
- **Heavy eager loading**: Never load full relation trees for list pages;
  use `withCount` instead of `with` where only counts are displayed.
- **Duplicate queries**: Use `CacheService::remember()` for repeated
  per-request lookups (settings, feature flags, plan resolution).
- **Large exports**: `ExportService` must stream via chunked cursors
  (`chunkById(500)`) and write directly to storage — never build the full
  array in memory.
- **Reports**: Scheduled reports run on queues (`reports` domain), never
  inline in web requests.

## Part 17 — DevOps Preparation

- **Docker-ready**: App is stateless (sessions/cache configurable to Redis);
  no local file dependencies outside `storage/app`.
- **Queue workers**: One supervisor program per queue domain
  (`app/Support/QueueDomains.php`): commerce, emails, whatsapp, push,
  analytics, reports, certificates, media, video, imports, exports,
  retries, dead-letter.
- **Scheduler**: Single `schedule:run` entrypoint; existing commands
  (`ExpireSubscriptions`) plus reminder engine and webhook retry sweeps.
- **Horizon-ready**: Queue domains map 1:1 to Horizon supervisor tiers;
  enable by setting `QUEUE_CONNECTION=redis`.
- **Octane/Vapor-ready**: No request-scoped state stored on singletons;
  services are stateless and resolved via container.

## Part 18 — Production Checklists

### Deployment checklist
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan config:cache && route:cache && view:cache`
- [ ] `php artisan migrate --force`
- [ ] Storage disks provisioned and permissions verified
- [ ] Queue workers + scheduler running under supervisor/systemd

### Environment checklist
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Secrets only via environment/secrets manager (never committed)
- [ ] `CACHE_STORE`, `QUEUE_CONNECTION`, `SESSION_DRIVER` set to redis
- [ ] Mail/WhatsApp/Push provider credentials configured

### Security checklist
- [ ] CSP headers enabled (`SecurityHardeningService`)
- [ ] Trusted proxy ranges configured
- [ ] Rate limit policies active (`RateLimitPolicy`)
- [ ] API keys hashed at rest; rotation procedure documented
- [ ] Signed URLs for private media

### Queue checklist
- [ ] Dead-letter queue monitored with alerting
- [ ] Retry backoff configured per job (`ProductionJob` concern)
- [ ] Failed job pruning scheduled

### Cron checklist
- [ ] Subscription expiry sweep daily
- [ ] Reminder engine hourly
- [ ] Webhook retry sweep every 5 minutes
- [ ] Analytics refresh nightly
- [ ] Search index refresh nightly

### Backup checklist
- [ ] Nightly DB dump to off-site storage
- [ ] Media/storage bucket versioning enabled
- [ ] Backup integrity verified weekly

### Restore / Disaster Recovery checklist
- [ ] Documented restore procedure tested quarterly
- [ ] RPO ≤ 24h, RTO ≤ 4h targets defined
- [ ] Feature kill switches available for degraded-mode operation

## Part 19 — Test Architecture Organization

Existing tests reviewed (no execution):

| Suite | Path | Type |
|---|---|---|
| Admin auth/access/RBAC | `tests/Feature/Admin/*` | Feature |
| Registration | `tests/Feature/Auth/*` | Feature |
| Course management | `tests/Feature/Courses/*` | Feature |
| Subscription access | `tests/Feature/Subscriptions/*` | Feature |
| LMS player | `tests/Feature/Learn/LmsPlayerTest.php` | Feature |
| Live sessions | `tests/Feature/Learn/LiveSessionTest.php` | Feature |
| Assessment engine | `tests/Feature/Assessments/*` | Feature |
| CMS engine | `tests/Feature/Cms/*` | Feature |
| Analytics engine | `tests/Feature/Analytics/*` | Feature |

Recommended structure going forward:
- `tests/Unit/Services/**` — pure service logic (quiz evaluation, coupons)
- `tests/Feature/**` — HTTP + policy behavior (current suites)
- `tests/Integration/**` — multi-service flows (checkout → fulfillment → enrollment)
- `tests/Architecture/**` — dependency-direction rules (services must not import controllers)
- `tests/Performance/**` — query-count assertions on hot endpoints
- `tests/Security/**` — tenant isolation and policy bypass attempts