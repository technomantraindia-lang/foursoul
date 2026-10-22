# LMS Implementation Status

> Last updated: 2026-08-28 (Local Functional QA)
> Status values used: COMPLETE · PARTIAL · NOT STARTED · BLOCKED · IN PROGRESS

## Project Information

| Item | Status |
|---|---|
| Laravel | NOT INSTALLED — `composer` is not available on this machine; Laravel 13 skeleton could not be created. Foundation PHP files are written and ready for `composer create-project` / `composer install`. |
| PHP | NOT DETECTED — no `php` binary on PATH; not present in XAMPP/Laragon/WAMP/C:\php locations. Target: PHP 8.4+. |
| Filament | NOT STARTED (target: Filament 5). Not installed — installation deferred until Composer is available. |
| Livewire | NOT STARTED (target: Livewire 4). |
| Database | MySQL 8 target. No MySQL server listening on 3306 on this machine. No migrations executed. |
| Redis | Not running locally (port 6379 not listening). `.env.example` contains Redis placeholders; safe dev fallback = file/array cache, sync/database queue. Production recommendation: Redis for cache, queue, sessions. |
| Queue | Target: Redis in production, `database` or `sync` locally. Configured via `.env.example` placeholders only. |
| Frontend | Node/npm NOT DETECTED on this machine. Target: Tailwind CSS 4 + Alpine.js. No `package.json` build executed. |

## Existing Modules

### Authentication
Status: CODE COMPLETE (runtime verification deferred)
Notes: Full user auth stack written without a Laravel skeleton (Composer unavailable). All code targets Laravel 13 conventions and is ready for `composer create-project` / `composer install`:
- **User model** (`app/Models/User.php`) — UUID auto-generation, SoftDeletes, strict `$fillable`, hidden fields, casts (UserStatus enum, Currency enum, datetimes), `referredBy()`/`referrals()` relations, status helper methods. Mass assignment protected; `status`, verification timestamps and detected-geo fields are never fillable from requests.
- **UserStatus enum** (`app/Enums/UserStatus.php`) — `active`, `suspended`, `blocked`, `closed`; default `active`; `canAuthenticate()` and `canUseProtectedFeatures()` rules.
- **Migrations** — additive: `000002_create_users_table` (all required columns + unique uuid/email/referral_code, indexes on mobile_number/status), `000003_create_password_reset_tokens`.
- **Registration** — `/register` GET+POST via `RegisteredUserController` → `RegisterRequest` (form-request validation) → `RegistrationService` (transactional, server-generated UUID + referral code, terms/privacy/marketing consent timestamps + versions). Never accepts client-supplied UUID.
- **Login/Logout** — `/login` GET+POST via `SessionController` using Laravel's secure Auth::attempt architecture (no custom crypto). Remember-me supported.
- **Status enforcement** — `EnsureUserAccountIsAccessible` middleware registered globally in `AppServiceProvider`; blocked/closed users are logged out centrally; suspended users land on `/account/status`. Status logic is not duplicated per controller.
- **Password reset** — `/forgot-password` + `/reset-password/{token}` via `PasswordResetLinkController` / `NewPasswordController` on Laravel's broker/token architecture.
- **Email verification** — `/verify-email` prompt + resend + signed `/verify-email/{id}/{hash}` link via Laravel MustVerifyEmail architecture.
- **Login tracking** — `UpdateLastLoginActivity` listener on Laravel's Login event sets `last_login_at`/`last_login_ip`; throttled `TrackLastActivity` middleware updates `last_activity_at` at most every 5 minutes. No passwords/tokens logged.
- **Views** — premium responsive auth pages: register, login, forgot-password, reset-password, verify-email (mobile-first, show/hide password, accessible focus states, no fake social buttons).

### User Account UI
Status: CODE COMPLETE (runtime verification deferred)
Notes:
- **Dashboard** `/dashboard` — real data only (welcome name, account status, email/mobile verification, preferred currency/language, member since) + quick actions (Profile, Security, Preferences). Empty-state cards ready for future prompts; no fake course/payment data.
- **Account layout** — desktop sidebar navigation + mobile responsive nav; Profile / Security / Preferences only (no fake Billing/Notifications).
- **Profile** `/account/profile` — full name, email (read-only until secure email-change flow exists), mobile country code + number selector, preferred language, timezone; displays verification/account status and member since.
- **Avatar** — POST `/account/profile/avatar` via `AvatarService`: MIME + extension validation, 2 MB limit, Laravel Storage abstraction (`public` disk), old avatar deleted on replace, executable uploads rejected.
- **Security** `/account/security` — change password requiring correct current password, new ≠ current, confirmation match, Laravel Hash.
- **Preferences** `/account/preferences` — language (Locales support), currency (existing `Currency` enum only: AED/USD/INR), timezone (Timezones support). Resolution priority preserved: valid preferred currency → IP-detected currency → USD fallback (via existing `PricingCurrencyResolver`/`SettingsService`).
- **Header/Footer components** — guest state (Logo, Login, Register, Currency Selector), authenticated state (Logo, Dashboard, Currency Selector, Account Menu, Logout); footer shows company/support/copyright only (no broken legal/CMS links). Reusable flash-message component for success/error feedback.
- **Authorization** — all account routes operate exclusively on `Auth::user()`; route-model binding of foreign user IDs is not used anywhere, so direct URL manipulation cannot edit another user.

### Admin
Status: CODE COMPLETE (runtime verification deferred)
Notes: Full Admin authentication + Filament Admin panel foundation written without a Laravel skeleton (Composer unavailable). All code targets Laravel 13 / Filament 5 conventions and is ready for `composer create-project` / `composer install`:
- **AdminStatus enum** (`app/Enums/AdminStatus.php`) — `active`, `suspended`, `disabled`; default `active`; `canAuthenticate()`, `canAccessPanel()`, `label()` helpers.
- **Admin model** (`app/Models/Admin.php`) — Authenticatable, Filament-compatible, SoftDeletes, UUID auto-generation, secure password handling/casts, AdminStatus cast, datetime casts, strict `$fillable` / guarded security, avatar support field, login activity fields.
- **Migrations** — additive: `000004_create_admins_table` (all required columns + unique uuid/email, indexes on status/last_login_at), `000005_create_admin_password_reset_tokens_table` (isolated admin password reset broker).
- **Auth config** — dedicated `admin` guard + `admins` provider + isolated `admin` password broker in `config/auth.php`; never shares Customer `web` guard.
- **Filament Panel** (`app/Providers/Filament/AdminPanelProvider.php`) — path `/admin`, custom `AdminLogin` page, `AdminProfile` page, branded Administration Portal login, server-side access gate via `AdminPanelProvider::canAccessPanel()` (only `status = active` allowed).
- **Admin Login** (`app/Filament/Pages/Auth/AdminLogin.php`) — guard pinned to `admin`, status gate (suspended/disabled/deleted denied), professional branded login view (`resources/views/filament/pages/auth/admin-login.blade.php`).
- **Admin Profile** (`app/Filament/Pages/AdminProfile.php`) — name, avatar, email (read-only), status, last login display; password change section; avatar validation via existing `AvatarService`.
- **Login Tracking** — `UpdateAdminLastLoginActivity` listener on Laravel's Login event (admin guard) sets `last_login_at`/`last_login_ip`; no passwords/tokens logged.
- **Dashboard Widgets** — `CustomerStatsWidget` (total/active/suspended/blocked customers), `AdminStatsWidget` (total/active admins), `RecentCustomersWidget` (name, email, status, joined at).
- **Create Admin Command** — `php artisan app:create-admin` (interactive: name, email, role choice, password, confirmation; validates email uniqueness, hashes password, generates UUID, sets active status, assigns the chosen RBAC role via the idempotent seeder — see Roles & Permissions).
- **Admin Factory** — `database/factories/AdminFactory.php` with `active`, `suspended`, `disabled` states.
- **Tests Written** — `tests/Feature/Admin/AdminAuthenticationTest.php` (active admin authenticates, invalid credentials rejected, suspended/disabled denied, customer cannot access /admin, logout works, UUID generated, password hashed, login updates last_login_at/ip, admin password reset isolated from customer). `tests/Feature/Admin/AdminPanelAccessTest.php` (/admin resolves, active admin reaches dashboard, unauthenticated reaches login, customer session does not grant admin access).

### Roles & Permissions
Status: CODE COMPLETE (runtime verification deferred)
Notes: Full RBAC foundation written without a Laravel skeleton (Composer unavailable). All code targets Laravel 13 / Spatie Laravel Permission ^6 conventions and is ready for `composer create-project` / `composer install`:
- **AdminRole enum** (`app/Enums/AdminRole.php`) — six roles: Super Admin, Support Admin, Session Manager, Finance Admin, Marketing Admin, Content Admin. Single source of truth for the complete permission catalogue (`{domain}.{action}` naming, e.g. `customers.view`, `payments.refund`) and each role's exact permission set.
- **Composer** — `spatie/laravel-permission: ^6.0` added to the `require` block of `composer.json`.
- **Migration** — additive: `000006_create_permission_tables` (Spatie standard: permissions, roles, model_has_permissions, model_has_roles, role_has_permissions).
- **Seeder** (`database/seeders/RolesAndPermissionsSeeder.php`) — idempotent (`firstOrCreate` + `syncPermissions`); creates every permission and role exclusively on the dedicated `admin` guard; never touches the customer `web` guard. Wired into `DatabaseSeeder` alongside `SettingsSeeder`.
- **Super Admin bypass** — `Gate::before` registered in `AppServiceProvider`: grants any ability only to **active** Admin models holding the Super Admin role; suspended/disabled/deleted admins are denied outright; every other model falls through to normal resolution (customer users unaffected).
- **Create Admin command** — `app:create-admin` now prompts for a role choice (validated against `AdminRole::slugs()`, default Super Admin), runs the idempotent seeder, then assigns the chosen role.
- **Tests Written** — `tests/Feature/Admin/RolesAndPermissionsTest.php` (all roles created on admin guard, full permission catalogue, seeder idempotency, per-role exact permissions, Super Admin explicit catalogue + Gate bypass, non-Super-Admin limited to assigned permissions, customers unaffected by bypass, zero RBAC rows leak onto web guard).

### Currency
Status: PARTIAL
Notes: Foundation code created and reviewed, but not runtime-verified (no PHP on machine):
- `app/Support/Currency.php` — PHP enum, values strictly `AED`, `USD`, `INR`; symbol helpers (AED→AED, USD→$, INR→₹); rejects unsupported values by design (enum type system).
- `app/Support/CurrencyFormatter.php` — centralized formatter: `AED 699.00`, `$199.00`, `₹12,999.00`. Single reusable service; no per-Blade formatting logic.
- No automatic currency conversion anywhere — prices are independently entered per currency (business rule).

### Courses / Sections / Lessons
Status: CODE COMPLETE (runtime verification deferred)
Notes: Course management layer written for Laravel 13 / Filament 5 — Course, CourseSection, CourseLesson models with UUIDs, slugs, soft deletes, ordering scopes; CourseStatus/LessonType enums; Filament resources with section/lesson relation managers; policies; factories; `tests/Feature/Courses/CourseManagementTest.php`.

### Plans
Status: CODE COMPLETE (runtime verification deferred)
Notes:
- **SubscriptionPlan model** (`app/Models/SubscriptionPlan.php`) — UUID auto-generation, SoftDeletes, strict `$fillable`, casts (PlanStatus, PlanBillingType, PlanCourseAccessType, datetimes, decimals, json metadata), `courses()` belongsToMany, effective-price logic centralized in the model.
- **Enums** — `PlanStatus` (draft/active/inactive/archived; only active purchasable), `PlanBillingType` (fixed_duration/monthly/quarterly/half_yearly/yearly/lifetime), `PlanCourseAccessType` (all_courses/selected_courses).
- **Multi-currency pricing** — independent AED/USD/INR price + sale_price columns; sale price valid only within configured sale window; no FX conversion anywhere.
- **Migration** — `000011_create_subscription_plans_table`; pivot `000012_create_subscription_plan_courses_table` (unique plan+course).
- **Filament resource** — `SubscriptionPlanResource` + List/Create/Edit pages (admin panel).

### Subscriptions
Status: CODE COMPLETE (runtime verification deferred)
Notes:
- **UserSubscription model** (`app/Models/UserSubscription.php`) — table `subscriptions` (migration `000013`), UUID, strict fillable, SubscriptionStatus cast, period/expiry/cancellation fields, activation source/metadata. History never hard-deleted.
- **SubscriptionService** (`app/Services/SubscriptionService.php`) — transactional `activate()` (generates Entitlements per plan course-access rules, creates/reactivates Enrollments without resetting progress), `renew()`, `expire()`, and `userHasCourseAccess(User, Course)` as the single backend authorization source of truth.
- **Expiry command** — `php artisan subscriptions:expire` (`app/Console/Commands/ExpireSubscriptions.php`) locks expired subscriptions; preserves enrollment/history rows.

### Entitlements & Enrollments
Status: CODE COMPLETE (runtime verification deferred)
Notes:
- **Entitlement model** (`app/Models/Entitlement.php`) — polymorphic `entitlementable` (migration `000014`), EntitlementStatus cast; expire marks EXPIRED, never deleted.
- **Enrollment model** (`app/Models/Enrollment.php`) — migration `000015`, EnrollmentStatus cast; expiry sets EXPIRED while preserving history and lesson progress; renewal reactivates without resetting progress.
- **PurchaseMode enum** (`app/Enums/PurchaseMode.php`) — subscription_only/standalone_only/both; added to courses via additive migration `000010_add_purchase_mode_to_courses_table`. standalone_only courses are never granted through plans.
- **CourseAccessPolicy** (`app/Policies/CourseAccessPolicy.php`) — delegates paid-course access checks exclusively to `SubscriptionService::userHasCourseAccess`.
- **Tests Written** — `tests/Feature/Subscriptions/SubscriptionAccessTest.php` (no-subscription denied, activate grants entitlement+enrollment+access, expiry locks access but preserves history, renewal restores access without duplicating/resetting enrollments, standalone_only not granted by plan).

### Assessment Engine (Phase 6 — Quizzes + Assignments + Certificates)
Status: CODE COMPLETE (runtime verification deferred)
Notes: Full assessment layer written without a Laravel skeleton. All additive, no previous modules rebuilt:
- **Migrations** — additive: `000024_create_question_bank_tables` (question_categories, questions, question_options), `000025_create_quizzes_and_attempts_tables` (quizzes, quiz_questions pivot, quiz_attempts, quiz_answers), `000026_create_assignments_tables` (assignments, assignment_submissions), `000027_create_certificates_tables` (certificate_templates, certificates).
- **Enums** — QuestionType (single_choice/multiple_choice/true_false/numeric/short_answer/paragraph/file_upload), QuestionDifficulty, QuestionStatus, QuizStatus, AttemptStatus (not_started/in_progress/submitted/completed/expired), AssignmentStatus, SubmissionStatus.
- **Models** — QuestionCategory, Question (+options relation), QuestionOption, Quiz (+questions belongsToMany with sort_order/pivot marks), QuizAttempt (+answers), QuizAnswer, Assignment, AssignmentSubmission, CertificateTemplate, Certificate (unique serial_number).
- **QuizService** (`app/Services/QuizService.php`) — start/resume attempt, access validation via existing SubscriptionService/CourseAccessPolicy, attempt-number enforcement and duplicate-attempt prevention, submit → QuizEvaluationService scoring, pass/fail determination, attempt locking on expiry.
- **QuizEvaluationService** (`app/Services/QuizEvaluationService.php`) — auto-evaluates single_choice/multiple_choice/true_false/numeric; paragraph/file_upload marked manual-review-required; negative-marks support; percentage + passing-marks logic centralized.
- **AssignmentService** (`app/Services/AssignmentService.php`) — submit, replace submission, file type/size validation, max-attempt enforcement, teacher review (marks + feedback), completion timestamps.
- **CertificateService** (`app/Services/CertificateService.php`) — issue certificate from default or explicit template, unique serial numbers, issued_at stamping, verify-by-serial for public verification.
- **Policies** — QuizPolicy, AssignmentPolicy (admin-guarded management actions).
- **Student routes/controllers** — authenticated: GET /learn/courses/{course}/quizzes/{quiz}, POST .../attempts (start), POST .../submit, GET result; GET /learn/courses/{course}/assignments/{assignment}, POST submission; GET /account/certificates, GET /account/certificates/{certificate}. Public: GET /verify/certificates/{serial} → CertificateController@verify.
- **Views** — learn/quiz.blade.php, learn/quiz-take.blade.php, learn/quiz-result.blade.php, learn/assignment.blade.php, learn/certificates.blade.php, learn/certificate.blade.php, learn/certificate-verify.blade.php.
- **Filament resources** — QuestionResource, QuizResource, AssignmentResource, CertificateTemplateResource (each with List/Create/Edit pages) registered in AdminPanelProvider.
- **Tests Written** — tests/Feature/Assessments/AssessmentEngineTest.php (quiz attempt lifecycle + duplicate prevention, assignment submit/review flow, certificate issuance with unique serial + verify-by-serial, public verification page).

### Checkout
Status: NOT STARTED

### Payments
Status: NOT STARTED
Notes: `.env.example` has `PAYMENT_GATEWAY` placeholder only. No credentials, no fake keys.

### Live Sessions (Phase 7 — Live Learning Engine)
Status: CODE COMPLETE (runtime verification deferred)
Notes: Full live learning layer written without a Laravel skeleton. All additive, no previous modules rebuilt:
- **Migrations** — additive: `000028_create_live_sessions_table`, `000029_create_session_attendance_table`.
- **Enums** — `LiveSessionStatus` (draft/scheduled/live/completed/cancelled/expired), `SessionProvider` (zoom/google_meet/microsoft_teams/jitsi/custom — architecture only, NO API integrations), `SessionType` (live_class/workshop/q_and_a/webinar/office_hours).
- **Models** — `LiveSession` (UUID, course/lesson/instructor relations, meeting metadata, timezone-aware scheduled/actual timestamps, max participants, waiting room + recording + replay flags, status cast, metadata JSON), `SessionAttendance` (UUID, session/enrollment/user, joined/left at, duration minutes, attendance percentage, present flag, metadata).
- **LiveSessionService** (`app/Services/LiveSessionService.php`) — schedule/start/end/cancel/reschedule lifecycle, access validation via existing SubscriptionService/CourseAccessPolicy, join-state generation with configurable join window (15 min before start default, configurable grace after start), replay availability only when recording exists + session completed + replay enabled + user still has valid entitlement. Replay URLs are never exposed publicly.
- **AttendanceService** (`app/Services/AttendanceService.php`) — join (prevents duplicate joins), leave, duration calculation, attendance percentage, mark present.
- **CalendarService** (`app/Services/CalendarService.php`) — instructor and student calendars, upcoming/past sessions, timezone aware.
- **Student routes/controllers** — authenticated: GET /learn/live-sessions (calendar), GET /learn/live-sessions/{session} (detail), POST .../join (attendance recorded server-side; ownership always verified server-side — never trusts request IDs).
- **Views** — learn/live-sessions.blade.php, learn/live-session.blade.php.
- **Tests Written** — tests/Feature/Learn/LiveSessionTest.php (enrolled-only calendar visibility, join blocked without course access, attendance recorded with duplicate prevention, replay URL never exposed to users without access).

### Notifications
Status: NOT STARTED
Notes: `.env.example` has `MAIL_*` and `FIREBASE_*` placeholders.

### WATI
Status: NOT STARTED
Notes: `.env.example` has `WATI_*` placeholders. No credentials created.

### CMS Engine (Phase 9 — Website Builder + Landing Pages + Blog + SEO + Media Library)
Status: CODE COMPLETE (runtime verification deferred)
Notes: Full CMS layer written without a Laravel skeleton. All additive, no previous modules rebuilt:
- **Migrations** — additive: `000033_create_media_library_tables` (media_folders, media), `000034_create_pages_tables` (pages with UUID/slug/SEO/layout/visibility/status/published_at/content JSON/version/metadata), `000035_create_blog_tables` (blog_categories, blog_tags, blog_posts, blog_comments), `000036_create_cms_tables` (menus, menu_items nested, banners scheduled, testimonials, faq_categories, faq_items, announcements global/per-course).
- **Enums** — PageStatus (draft/review/scheduled/published/archived), MediaType.
- **Models** — Media (+folder relation, alt/title/caption, usage tracking fields, responsive variants JSON, CDN-ready path), MediaFolder, Page, BlogCategory, BlogTag, BlogPost (+categories/tags belongsToMany, reading time, SEO fields), BlogComment (moderation foundation), Menu, MenuItem (nested parent/children, external links, permissions), Banner (types: homepage/popup/announcement_bar/campaign; start/end schedule), Testimonial (featured, rating, display order, status), FaqCategory, FaqItem (ordering, visibility), Announcement (global + per-course, schedule/expiry).
- **SeoService** (`app/Services/SeoService.php`) — centralized meta title/description/keywords/canonical/OpenGraph/Twitter Cards/schema foundation; sitemap + robots foundations.
- **CmsService** (`app/Services/CmsService.php`) — published page resolution by slug, active menus/banners/testimonials/faqs/announcements queries (schedule-aware).
- **Policies** — PagePolicy, BlogPostPolicy, MediaPolicy (admin-guarded management actions).
- **Filament resources** — MediaResource, PageResource, BlogPostResource (each with List/Create/Edit pages) registered in AdminPanelProvider under the CMS navigation group.
- **Public routes/controllers** — GET /pages/{slug} → PageController@show (published only); GET /blog → BlogController@index; GET /blog/{slug} → BlogController@show. Static pages served from the pages table.
- **Views** — cms/page.blade.php, cms/blog/index.blade.php, cms/blog/show.blade.php.
- **Settings** — branding keys added to `config/settings.php` defaults + admin-editable groups: brand_logo, brand_favicon, brand_primary_color, brand_font_family, brand_social_links, footer_about_text, contact_email, contact_phone, copyright_text, analytics_ga_id, analytics_meta_pixel_id.
- **Factories** — Page, BlogCategory/BlogPost/BlogTag, Media/MediaFolder, Menu/MenuItem, Banner, Testimonial, FaqCategory/FaqItem, Announcement factories for tests/seeding.
- **Tests Written** — tests/Feature/Cms/CmsEngineTest.php (page publish/slug resolution, blog listing/detail, draft content not public, menu/banner/testimonial/faq/announcement retrieval rules).

### Reports
Status: NOT STARTED

## Phase Status

### Phase 1 — Foundation
Status: PARTIAL
Explanation: All foundation source files (currency enum, formatter, geo resolver, pricing resolver, settings system, `.env.example`, tests) are created. Runtime verification (`composer validate`, `php artisan about`, `migrate:status`, `route:list`, `php artisan test`, `npm run build`) is BLOCKED because PHP, Composer, Node and npm are not installed on this machine. No MySQL/Redis service is running locally. Phase 1 becomes COMPLETE after tooling is installed and the verification suite passes.

### Phase 2 — Course Structure
Status: NOT STARTED

### Phase 3 — Plans & Entitlements
Status: CODE COMPLETE (runtime verification deferred)
Explanation: Full PLAN → SUBSCRIPTION → ENTITLEMENT → ENROLLMENT → COURSE ACCESS layer written at source level (models, enums, migrations, service, policy, Filament resource, expiry command, feature tests). Payment gateways/checkout intentionally excluded (later phase). Runtime verification remains blocked on PHP/Composer/MySQL availability.

### Phase 4 — User LMS (LMS Player + Lesson Progress + Course Progress)
Status: CODE COMPLETE (runtime verification deferred)
Explanation: Full learning experience written at source level — authenticated LMS routes, controllers, services, progress models/enums/migrations, YouTube player views, free preview, sequential unlocking, expired-access handling, and feature tests (`tests/Feature/Learn/LmsPlayerTest.php`). Runtime verification remains blocked on PHP/Composer/MySQL availability.

### Phase 6 — Assessments (Quizzes + Assignments + Certificates)
Status: CODE COMPLETE (runtime verification deferred)
Explanation: Full assessment engine written at source level — question bank/categories/options, quizzes + attempts, evaluation engine, assignments + submissions, certificates + templates + public verification, Filament admin resources, student-facing views/routes, and feature tests (`tests/Feature/Assessments/AssessmentEngineTest.php`). Runtime verification remains blocked on PHP/Composer/MySQL availability.

### Phase 7 — Checkout
Status: NOT STARTED

### Phase 7 — Subscription Lifecycle
Status: NOT STARTED

### Phase 8 — Live Learning Engine (Live Sessions + Attendance + Calendar + Replays)
Status: CODE COMPLETE (runtime verification deferred)
Explanation: Full live learning engine written at source level — live sessions model/enums/migrations, provider architecture (no API integrations by design), attendance service with duplicate-join prevention, join rules/window enforcement, calendar service (instructor/student, timezone aware), replay gating behind valid entitlements, student views/routes, and feature tests (`tests/Feature/Learn/LiveSessionTest.php`). Runtime verification remains blocked on PHP/Composer/MySQL availability.

### Phase 9 — Notifications
Status: NOT STARTED

### Phase 10 — CMS Engine (Website Builder + Landing Pages + Blog + SEO + Media Library)
Status: CODE COMPLETE (runtime verification deferred)
Explanation: Full CMS engine written at source level — media library with folders/usage tracking, page builder model (pages with sections/content JSON/versioning), blog with categories/tags/comments foundation, centralized SeoService, nested menus, scheduled banners, testimonials, FAQs, per-course/global announcements, brand/site settings keys, Filament CMS resources under a CMS navigation group, public page/blog routes/views, and feature tests (`tests/Feature/Cms/CmsEngineTest.php`). Runtime verification remains blocked on PHP/Composer/MySQL availability.

### Phase 11 — Reports
Status: NOT STARTED

### Phase 12 — QA & Deployment
Status: NOT STARTED

## Existing Database Tables

None executed yet (no PHP/MySQL available). Migration files prepared (all additive):

- `settings` — key/value settings table (migration: `2026_01_01_000001_create_settings_table.php`), seeded via `SettingsSeeder` with currency defaults:
  - `base_currency = AED`
  - `uae_currency = AED`
  - `india_currency = INR`
  - `other_currency = USD`
  - `allow_manual_currency_switch = true`
  - `geoip_enabled = true`
- `users` — full user table (migration: `2026_01_01_000002_create_users_table.php`): id, uuid (unique), full_name, first_name/last_name nullable, email (unique) + email_verified_at, mobile_country_code + mobile_number (indexed) + mobile_verified_at, password, avatar_path, preferred_language/preferred_currency, detected_country_code/detected_currency/last_detected_ip, timezone, marketing_consent (default false) + marketing_consent_at, terms_accepted_at/terms_version, privacy_accepted_at/privacy_version, status (indexed, default active), registration_source, referral_code (unique nullable) + referred_by_user_id FK, last_login_at/last_login_ip/last_activity_at, remember_token, timestamps, soft deletes.
- `password_reset_tokens` — Laravel-standard token table (migration: `2026_01_01_000003_create_password_reset_tokens_table.php`).
- `admins` — full admin table (migration: `2026_01_01_000004_create_admins_table.php`): id, uuid (unique), name, email (unique) + email_verified_at, password, avatar_path, status (indexed, default active), last_login_at (indexed)/last_login_ip, two_factor_enabled (default false), two_factor_secret, remember_token, timestamps, soft deletes.
- `admin_password_reset_tokens` — isolated admin password reset token table (migration: `2026_01_01_000005_create_admin_password_reset_tokens_table.php`).
- `permissions` / `roles` / `model_has_permissions` / `model_has_roles` / `role_has_permissions` — Spatie Laravel Permission tables, used exclusively by the `admin` guard (migration: `2026_01_01_000006_create_permission_tables.php`).
- `courses` (+ `purchase_mode` via `2026_01_01_000010`), `course_sections`, `course_lessons` — course structure tables.
- `subscription_plans` (migration: `2026_01_01_000011_create_subscription_plans_table.php`) — uuid, name/slug, descriptions, status, billing_type, duration_days, trial fields, course_access_type, per-currency price/sale_price (AED/USD/INR), sale window, featured/sort, limits, live/community flags, metadata, published_at, audit columns, soft deletes.
- `subscription_plan_courses` (migration: `2026_01_01_000012`) — unique subscription_plan_id + course_id pivot.
- `subscriptions` (migration: `2026_01_01_000013`) — user_id, subscription_plan_id, status, currency, unit_price/discount/tax/total, started_at/trial_ends_at/current_period_start/end/expires_at, cancelled_at/cancel_at_period_end/ended_at, activation_source/source_reference, metadata.
- `entitlements` (migration: `2026_01_01_000014`) — polymorphic entitlementable, user_id, status, granted/expires timestamps.
- `enrollments` (migration: `2026_01_01_000015`) — user_id, course_id, status, source subscription reference, activated/completed timestamps.
- `lesson_progress` (migration: `2026_01_01_000016`) — uuid, user_id, course_id, lesson_id, enrollment_id nullable, status (LessonProgressStatus), started_at/last_accessed_at/completed_at, watch_seconds/duration_seconds/progress_percentage/last_position_seconds, completion_source, metadata; unique user+lesson+enrollment.
- `course_progress` (migration: `2026_01_01_000017`) — uuid, user_id, course_id, enrollment_id nullable, status (CourseProgressStatus), total_lessons/completed_lessons/progress_percentage, started_at/last_accessed_at/completed_at, metadata; unique user+course+enrollment.
- `course_lessons` extended additively (migration: `2026_01_01_000018`) — is_free_preview, youtube_video_id, release_type, release_at, requires_previous_completion, completion_requirement, duration_seconds.
- `media_folders`, `media` (migration: `2026_01_01_000033_create_media_library_tables.php`) — folders (name/slug/parent nesting) and media (uuid, folder_id, name, file_path CDN-ready, type, mime, size, width/height, alt/title/caption, tags, usage tracking counts, responsive variants JSON, sort, timestamps, soft deletes).
- `pages` (migration: `2026_01_01_000034_create_pages_tables.php`) — uuid, title/slug (unique), seo_title/seo_description/og_image/canonical_url, layout, visibility, status (PageStatus), published_at, content JSON (sections/blocks), version, metadata, audit columns, soft deletes.
- `blog_categories`, `blog_tags`, `blog_posts`, `blog_comments` (migration: `2026_01_01_000035_create_blog_tables.php`) — posts carry uuid/slug/excerpt/content/featured image/author (admin)/status/published_at/SEO/reading time; comments are moderation-ready.
- `menus`, `menu_items`, `banners`, `testimonials`, `faq_categories`, `faq_items`, `announcements` (migration: `2026_01_01_000036_create_cms_tables.php`) — nested menus with external links and permission gating; banners typed + scheduled (starts_at/ends_at); testimonials featured/rating/order/status; FAQ categories/items ordered + visible; announcements global or per-course with schedule/expiry.

No legacy tables exist — this is a fresh foundation; no historical migrations to preserve.

## Important Existing Routes

Defined in `routes/web.php` (not yet loaded — no Laravel skeleton installed; no routes were removed or altered):

Guest/auth routes:
- `GET /register` → RegisteredUserController@create · `POST /register` → RegisteredUserController@store
- `GET /login` → SessionController@create · `POST /login` → SessionController@store · `POST /logout` → SessionController@destroy
- `GET /forgot-password` → PasswordResetLinkController@create · `POST /forgot-password` → PasswordResetLinkController@store
- `GET /reset-password/{token}` → NewPasswordController@create · `POST /reset-password` → NewPasswordController@store
- `GET /verify-email` → EmailVerificationPromptController · `POST /email/verification-notification` → EmailVerificationNotificationController@store · `GET /verify-email/{id}/{hash}` → VerifyEmailController (signed)

Authenticated LMS routes (auth + EnsureUserAccountIsAccessible):
- `GET /learn` → LearningDashboardController (enrollments list, active/expired/completed states, continue-learning card)
- `GET /learn/courses/{course:slug}` → CourseLearningController@show (curriculum, locked/unlocked lessons, expired state)
- `GET /learn/courses/{course:slug}/lessons/{lesson:uuid}` → LessonPlayerController@show (access verified server-side)
- `POST /learn/courses/{course:slug}/lessons/{lesson:uuid}/progress` → LessonProgressController@updateProgress
- `POST /learn/courses/{course:slug}/lessons/{lesson:uuid}/complete` → LessonProgressController@complete

Public free preview route:
- `GET /courses/{course:slug}/preview/{lesson:uuid}` → PublicPreviewController (free-preview lessons only; paid lessons return 404)

Public CMS routes:
- `GET /pages/{slug}` → Cms\PageController@show (published pages only)
- `GET /blog` → Cms\BlogController@index · `GET /blog/{slug}` → Cms\BlogController@show

Authenticated account routes (auth + EnsureUserAccountIsAccessible):
- `GET /dashboard` → DashboardController
- `GET /account/status` → AccountStatusController (suspended/blocked/closed screens)
- `GET /account/profile` → ProfileController@edit · `PUT /account/profile` → ProfileController@update · `POST /account/profile/avatar` → ProfileController@updateAvatar
- `GET /account/security` → SecurityController@edit · `PUT /account/security/password` → SecurityController@updatePassword
- `GET /account/preferences` → PreferencesController@edit · `PUT /account/preferences` → PreferencesController@update

Admin routes (Filament panel, not in web.php — registered via PanelProvider):
- `GET /admin` → Admin panel dashboard (requires active Admin)
- `GET /admin/login` → Admin login page
- `POST /admin/login` → Admin login handler
- `POST /admin/logout` → Admin logout
- `GET /admin/password/reset` → Admin forgot password
- `POST /admin/password/reset` → Admin password reset link
- `GET /admin/password/reset/{token}` → Admin reset password form
- `POST /admin/password/reset/{token}` → Admin password reset
- `GET /admin/profile` → Admin profile page

## Known Problems

1. **PHP not installed** — `php` is not recognized; not found in XAMPP (`C:\xampp\php`), Laragon (`C:\laragon\bin`), WAMP (`C:\wamp64\bin\php`), `C:\php`, `C:\Program Files\php`, or `C:\tools\php`.
2. **Composer not installed** — `composer` not on PATH. Laravel skeleton and all Composer packages (Filament, Livewire, Sanctum, Socialite, Spatie Permission) cannot be installed.
3. **Node/npm not installed** — frontend build cannot run; Tailwind 4 + Alpine.js tooling deferred.
4. **MySQL not running** — port 3306 not listening; `migrate:status` cannot run.
5. **Redis not running** — port 6379 not listening; dev fallback (file cache / sync queue) documented in `.env.example`.
6. **Tests written but not executed** — Unit: `CurrencyTest`, `CurrencyFormatterTest`, `PricingCurrencyResolverTest` (AE→AED, IN→INR, US→USD, GB→USD, unknown→USD, enum rejection, formatter output). Feature: `Auth/RegistrationTest`, `Auth/AuthenticationTest`, `Auth/PasswordResetTest`, `Auth/EmailVerificationTest`, `Auth/AccountStatusTest`, `Account/ProfileTest`, `Account/SecurityTest`, `Account/PreferencesTest`, `Admin/AdminAuthenticationTest`, `Admin/AdminPanelAccessTest`, `Admin/RolesAndPermissionsTest`. All require PHP + phpunit to run during final integration.
7. **Laravel skeleton absent** — controllers/middleware/views reference framework classes (`Illuminate\...`) that cannot autoload until `composer install` runs. Static review confirmed namespaces/imports/route names/view names/model casts/enum references/migration fields/validation names/relationship names are internally consistent.

## Decisions

Base Currency: AED

Supported Currencies:
- AED
- USD
- INR

Regional Defaults:
- AE → AED
- IN → INR
- Others → USD
- Unknown → USD

Pricing Model:
Independent manually configured regional prices. Admin enters AED, USD and INR prices separately (e.g., AED 699 / USD 199 / INR 12,999). No automatic conversion. IP detection only selects which stored price displays by default.

Prompt 2A decisions:
- **UUID policy**: generated server-side in `RegistrationService` (Str::uuid7-style via Str::uuid); registration requests can never supply it.
- **Consent model**: Terms and Privacy stored as separate timestamp + version pairs (`terms_accepted_at`/`terms_version`, `privacy_accepted_at`/`privacy_version`); marketing consent stores explicit boolean choice + timestamp. Versions sourced from `config/legal.php`.
- **Email read-only**: profile email field is displayed but not editable until a verified email-change flow exists (planned later prompt). No insecure direct email switching.
- **Mobile storage**: `mobile_country_code` (E.164 dialing code, e.g. `+971`) and `mobile_number` (national digits) stored separately; normalized on input; country-code selector uses `CountryCodes` support class; OTP deferred (`mobile_verified_at` kept future-ready).
- **Activity throttling**: `TrackLastActivity` middleware writes `last_activity_at` at most once per 5 minutes per user session to avoid write amplification.
- **No fake features**: no social login buttons, no fake statistics, no broken course/billing/notification links anywhere in auth or account UI.
- **Final static-review pass**: profile validation's mobile country-code rule now validates against `CountryCodes::all()` dialing values directly (an earlier draft referenced a non-existent `dialCodes()` helper — corrected before integration).

Additional foundation decisions:
- **GeoIP trust model**: `GeoLocationService` does NOT trust arbitrary client headers. It supports, in priority order: (1) Cloudflare `CF-IPCountry` header only when explicitly enabled via config/env, (2) a pluggable server-side GeoIP provider interface (no provider purchased or hard-coded), (3) clean USD fallback for local dev/unknown.
- **Settings**: simple key/value `settings` table + `SettingsService` + `config/settings.php` — intentionally not overengineered; groupable later (General, Branding, Locale, Currency, LMS, Subscription, Payment, Notifications, Analytics, Integrations, Security).
- **Timezone**: business timezone `Asia/Dubai`. Architecture rule: store UTC in DB, display in Asia/Dubai (or user timezone later). To be enforced once Laravel skeleton exists (`config/app.php` timezone stays UTC; display-layer conversion).
- **No fake credentials**: all integration placeholders in `.env.example` are empty; no API keys, no passwords committed.

Prompt 2B decisions:
- **Admin/Customer separation**: completely isolated guards (`admin` vs `web`), providers (`admins` vs `users`), password brokers (`admin` vs `users`), and login pages. No shared authentication state.
- **AdminStatus enum**: mirrors UserStatus pattern but with `disabled` instead of `blocked/closed`; `canAccessPanel()` is the single source of truth for Filament panel access.
- **Filament panel access**: server-side gate in `AdminPanelProvider::canAccessPanel()` — only `active` admins pass; suspended/disabled/deleted are denied at the middleware layer, not hidden in navigation.
- **Admin login UI**: custom branded view (`admin-login.blade.php`) with Administration Portal identity; no customer register/login/social links.
- **Admin profile**: simple professional screen — name, avatar, email (read-only), status, last login; password change section; avatar handled by existing `AvatarService` (MIME/extension/size validation, Storage abstraction).
- **Dashboard widgets**: real database queries only — CustomerStatsWidget, AdminStatsWidget, RecentCustomersWidget. No fake numbers.
- **Create admin command**: interactive, no hard-coded credentials, generates UUID, hashes password, sets active status. (Role assignment added afterwards in Prompt 2C.)
- **Admin factory**: states for active/suspended/disabled; no predictable production credentials.
- **Tests**: written for final integration verification; cover authentication, panel access, isolation, login tracking, password reset isolation.

Prompt 2C decisions:
- **RBAC library**: Spatie Laravel Permission ^6 as the foundation; roles and permissions exist exclusively on the dedicated `admin` guard — the customer `web` guard is never touched.
- **Single source of truth**: `AdminRole` enum defines all six role slugs, the full `{domain}.{action}` permission catalogue, and each role's exact permission set; the seeder derives everything from the enum so future changes happen in one place.
- **Idempotent seeding**: `RolesAndPermissionsSeeder` uses `firstOrCreate` + `syncPermissions`, safe to re-run on every deploy; wired into `DatabaseSeeder`.
- **Super Admin bypass**: `Gate::before` grants any ability only to **active** Admin models holding the `super_admin` role; suspended/disabled/deleted admins are denied outright even when their synced permissions would otherwise resolve; all other models fall through to normal resolution (customers unaffected because they are not Admin models). Covered by dedicated tests (`test_suspended_super_admin_cannot_use_gate_bypass`, `test_disabled_super_admin_cannot_use_gate_bypass`).
- **One role per admin at creation**: `app:create-admin` assigns exactly one chosen role (default Super Admin); multi-role assignment deferred until a real requirement exists.

### Commerce Engine (Prompt 5)
Status: CODE COMPLETE (runtime verification deferred)
Notes: Full commerce architecture written without a Laravel skeleton. No payment gateway integration yet — gateway-independent by design:
- **Migrations** — additive: `000019_create_coupons_table`, `000020_create_orders_table` (orders + order_items), `000021_create_payments_table`, `000022_create_invoices_table`, `000023_create_refunds_table`.
- **Enums** — `OrderStatus` (pending, awaiting_payment, paid, cancelled, failed, expired, refund_requested, refunded), `PaymentStatus` (pending, processing, succeeded, failed, cancelled), `PaymentMethod`, `InvoiceStatus`, `RefundStatus`, `CouponType`.
- **Models** — `Order` (UUID, unique order number, currency snapshot, subtotal/discount/tax/total, billing info JSON, soft deletes), `OrderItem` (product type/id, quantity, unit price, discount, tax, line total, immutable name/description snapshots), `Payment` (gateway + reference, status, amount, paid_at, failure reason, metadata), `Invoice` (sequential invoice number, line items snapshot, totals), `Coupon` (code, type, value, usage limits, validity window), `Refund`.
- **ProductResolver** (`app/Services/Commerce/ProductResolver.php`) — product abstraction resolving `course` and `plan` types to their models with per-currency pricing; extensible for bundles/memberships without redesign.
- **CheckoutService** — validates product, resolves currency via existing `PricingCurrencyResolver`, validates coupon via `CouponService`, recalculates ALL totals server-side (frontend values never trusted), creates Order + OrderItems transactionally.
- **CouponService** — code validation, type/value application, usage-limit and expiry enforcement.
- **OrderNumberGenerator** — unique, collision-safe order numbers.
- **Payments** — `PaymentGateway` contract (`initiate`, `verify/handleWebhook` shape) so Stripe/Razorpay/PayPal/Cash/Bank Transfer plug in without redesign; `ManualPaymentGateway` for cash/bank transfer; `PaymentService` orchestrates payment records and success transitions.
- **FulfillmentService** — on payment success ONLY: activates subscription via existing `SubscriptionService`, grants entitlements, creates enrollments → course access. Nothing else activates paid content.
- **InvoiceService** — invoice generation from paid orders with immutable line-item snapshots.
- **CheckoutController** — POST `/checkout/preview` (server-side recalculation preview) and POST `/checkout` (order creation + payment initiation); authenticated under `account.access`; ownership always server-side.
- **Routes** — `checkout.preview`, `checkout.store` added inside the authenticated group in `routes/web.php`.

### Communication Engine (Phase 8)
Status: CODE COMPLETE (runtime verification deferred)
Notes: Full communication architecture written without a Laravel skeleton. No vendor API integration — provider-abstracted by design:
- **Migrations** — additive: `000030_create_notification_templates_table`, `000031_create_notifications_table`, `000032_add_notification_preferences_to_users_table`.
- **Enums** — `NotificationChannel` (email, whatsapp, push, sms, in_app), `NotificationStatus` (pending, queued, sent, failed, cancelled).
- **Models** — `NotificationTemplate` (key, channel, subject/title/body with `{{variable}}` placeholders), `Notification` (UUID, user, template, channel, subject/title/body, variables JSON, status, scheduled_at/sent_at/read_at, metadata; indexes on user+read_at and status+scheduled_at).
- **TemplateRenderer** (`app/Services/Communication/TemplateRenderer.php`) — centralized `{{variable}}` rendering for all channels.
- **Provider abstractions** — `WhatsAppProviderInterface` + `LogWhatsAppProvider` (WATI/Meta Cloud/Twilio-ready), `PushProviderInterface` + `LogPushProvider` (Firebase/OneSignal-ready). No real API calls.
- **CommunicationService** — renders templates, respects per-user channel preferences (email/whatsapp/push/marketing toggles from users table), creates in-app notifications, dispatches `SendNotificationJob` on the queue (retry/failure tracking via Notification status).
- **ReminderService** — reminder triggers for subscription expiry, upcoming live session, assignment due, quiz due, certificate issued, payment success, invoice ready.
- **Event mapping** — `CommunicationEventSubscriber` maps domain events (registered, payment succeeded, order created, subscription activated/expiring, quiz submitted, assignment reviewed, certificate issued, live session scheduled/starting/replay available) to template keys; registered in `AppServiceProvider`.
- **Notification Center** — `/account/notifications` inbox (list, mark read, mark all read) via `NotificationCenterController`; ownership always server-side (`Auth::user()`); paginated.
- **User preferences** — new boolean columns: `notify_email`, `notify_whatsapp`, `notify_push`, `notify_marketing`.

### Analytics & Reporting Engine (Phase 10)
Status: CODE COMPLETE (runtime verification deferred)
Notes: Full analytics architecture written without a Laravel skeleton. All reporting logic centralized in services; no fake data anywhere:
- **Migrations** — additive: `000037_create_analytics_tables` (`scheduled_reports`, `report_exports`, `analytics_audit_logs`).
- **Enums** — `ReportFormat` (csv, excel, pdf), `ReportFrequency` (daily, weekly, monthly).
- **Models** — `ScheduledReport` (UUID, report key, format, frequency, recipients JSON, filters JSON, active flag, next_run_at), `ReportExport` (UUID, report key, format, status, file path, row count, requested_by admin, completed_at), `AnalyticsAuditLog` (actor, action, subject, metadata) for audit trails.
- **AnalyticsService** — central entry point; date-range/filter normalization, audit logging of report access.
- **KpiService** — real-query KPIs: revenue, MRR (active subscriptions), ARR, orders, conversion rate, active students, active courses, completion %, quiz pass %, attendance %, certificates issued, average rating, blog views.
- **ReportService** — report definitions for revenue/orders/payments/refunds/subscriptions/courses/students/instructors/certificates/assignments/quizzes/attendance/notifications/CMS; grouping by day/week/month/quarter/year and by currency/country/course/plan/instructor; student, course, instructor and commerce reports built from existing tables only.
- **DashboardService** — role-aware dashboard payloads (super admin / admin / instructor / student) composed from KPI + report data.
- **ExportService** — service-based CSV export implemented natively; Excel/PDF formats declared via `ReportFormat` with driver-ready architecture (no vendor packages required for CSV path).
- **Scheduled reports** — `ReportFrequency` + `next_run_at` scheduling fields prepared for a future dispatcher command; no email delivery implemented (per spec).
- **Admin dashboard page** — `AnalyticsDashboard` Filament page (`/admin/analytics`) with KPI stat cards and recent-orders/recent-enrollments tables rendered from live queries (`resources/views/filament/pages/analytics-dashboard.blade.php`); registered in `AdminPanelProvider`.
- **Tests** — `tests/Feature/Analytics/AnalyticsEngineTest.php`: KPI calculations, report generation, CSV export output, scheduled-report model behavior.

### SaaS Platform Engine (Phase 11)
Status: CODE COMPLETE (runtime verification deferred)
Notes: Full multi-tenancy architecture written without a Laravel skeleton. All code targets Laravel 13 / Filament 5 conventions; additive only, no existing modules rewritten:
- **Migrations** — additive: `000038_create_tenants_table` (tenants + tenant_settings), `000039_create_tenant_billing_tables` (tenant_subscriptions, tenant_invoices, tenant_payments with trial/renewal/expiry fields), `000040_create_tenant_feature_and_usage_tables` (tenant_features, tenant_storage_usage, tenant_audit_logs), `000041_create_tenant_domains_branches_api_webhooks_tables` (tenant_domains, branches, departments, tenant_api_keys, webhooks, webhook_deliveries).
- **Enums** — `TenantStatus` (trial, active, suspended, cancelled; trial default).
- **Models** — `Tenant` (UUID, unique slug, owner admin FK, brand/timezone/country/currency/language, storage/user/branch limits, metadata JSON; relations to settings/subscriptions/invoices/payments/features/domains/branches/apiKeys/webhooks/storageUsage/auditLogs), `TenantSetting` (group/key/value/type per tenant, unique tenant+key), `TenantSubscription` (plan snapshot, billing period, trial/renewal/expiry timestamps, status), `TenantInvoice`, `TenantPayment`, `TenantFeature` (boolean flag + limit overrides per feature key covering courses/commerce/live sessions/certificates/cms/blog/analytics/reports/api/community/forum/assignments/quizzes/notifications/email/whatsapp/push), `TenantStorageUsage` (per-category byte tracking: images/videos/documents/certificates/reports/media/backups), `TenantAuditLog` (actor/action/subject/metadata), `TenantDomain` (unique domain, ssl_status, dns_status, is_primary, verified_at, redirect handling), `Branch` (+ `Department`, manager relation), `TenantApiKey` (hashed key, scopes JSON, rate limits, last_used_at, revoked_at), `Webhook` (url, events array, secret for HMAC signatures, active) and `WebhookDelivery` (event, payload, status, attempts, response codes, next_retry_at for retry/backoff).
- **Services** — `app/Services/Tenant/FeatureGateService.php`: centralized feature-flag checks (`enabled(Tenant, key)`, `withinLimit(Tenant, key, current)` reading TenantFeature flags/limits with sensible defaults); `app/Services/Tenant/TenantService.php`: transactional tenant provisioning (unique slug generation, default feature flags seeded, default settings, trial subscription creation) plus suspension/cancellation flows that write audit logs.
- **Platform Admin UI** — `TenantResource` Filament resource (list/create/edit tenants with status badges, limits, brand fields) registered in `AdminPanelProvider`; relation managers: Features (toggle flags + edit limits), Domains (domain/SSL/DNS/primary/verification), Branches (branch + department management), Subscriptions (billing records).
- **Feature gating rule** — all tenant-scoped access must go through `FeatureGateService`; no controller or view may read tenant features directly.
- **Safety** — no existing models/services/routes modified; all Phase 11 artifacts are new files plus one additive registration line in `AdminPanelProvider`.

## Safety Notes

- No pre-existing working functionality existed (fresh directory) — nothing was deleted, replaced, or rewritten.
- No migrations were deleted; the single new migration is additive.
- No routes removed (none existed).
- `.env` is not committed; only `.env.example` with empty placeholders.
- No destructive commands were run (none could be — no PHP/Composer/Node).
- When tooling is installed, do NOT blindly upgrade dependencies; install the target stack versions (Laravel 13, PHP 8.4+, Filament 5, Livewire 4, Tailwind 4) fresh.
- Preserve the currency rule: never auto-convert AED↔USD↔INR; never add currencies beyond AED/USD/INR; never use `IND`.
- Admin/Customer authentication must remain completely isolated — no shared guards, providers, or password brokers.
- RBAC (roles/permissions) must remain exclusively on the `admin` guard — never assign roles or permissions to customer users.

## Phase 12 — Enterprise Production Engine (2026-08-25)

Status: **CODE COMPLETE** — final production architecture phase; runtime verification deferred.

### Part 1 — Caching Layer
`app/Services/Cache/CacheService.php`: centralized cache facade with named cache domains (repository, query, settings, tenant, course, plan, cms, analytics, notification templates), tag-based invalidation (`invalidate(domain)`), remember/forget helpers, and a driver abstraction ready for Redis via `CACHE_DRIVER=redis`. No existing services were rewritten to consume it yet — adoption is additive.

### Part 2 — Queue Architecture
`app/Support/QueueDomains.php`: canonical queue name registry covering commerce, emails, whatsapp, push, analytics, reports, certificates, media, video-processing, imports, exports, retries, and dead-letter queues, with per-domain retry/backoff defaults for worker/supervisor/Horizon configuration.

### Part 3 — Background Jobs
`app/Jobs/Concerns/ProductionJob.php`: shared job contract providing queue-domain routing, unique-job support, exponential backoff, failure hooks that route to the dead-letter domain, and correlation-ID propagation from ObservabilityService.

### Part 4 — Search Engine
`app/Services/Search/SearchService.php`: central search across courses, lessons, blogs, pages, users, certificates, orders, and media with a global search aggregator, driver abstraction (database driver now; Meilisearch/OpenSearch adapters pluggable), and index-refresh hooks.

### Part 5 — File Storage
`app/Services/Storage/StorageManager.php`: storage abstraction over local/S3/Cloudflare R2/Azure Blob/GCS disks with per-tenant disk resolution, signed URL generation, and tenant-scoped path isolation.

### Part 6 — Image Optimization
`app/Services/Media/ImageOptimizationService.php`: resize/thumbnail/responsive variant pipeline with WebP + AVIF conversion targets and an optimization-queue entry point wired to the media queue domain.

### Part 7 — Import / Export
`app/Services/ImportExport/ImportExportService.php`: CSV/Excel bulk import & export with queued processing, row-level validation reports, chunked streaming (no in-memory full-file loads), and transactional rollback on batch failure.

### Part 8 — Webhook Framework
`app/Services/Webhook/WebhookFrameworkService.php`: HMAC signature validation, timestamp-based replay protection, exponential-backoff retry scheduling against `webhook_deliveries.next_retry_at`, dead-letter handling after max attempts, and full delivery history.

### Part 9 — Rate Limiting
`app/Services/Security/RateLimitPolicy.php`: centralized rate-limit policies differentiated by audience — guests, students, admins, API keys, webhooks — consumable by middleware/routes without hardcoding limits.

### Part 10 — Security Hardening
`app/Services/Security/SecurityHardeningService.php`: architecture hooks for CSRF/CSP headers, trusted proxies, signed URLs, upload file validation, password policy enforcement, 2FA hooks, API key rotation, session/device management, and security event emission.

### Part 11 — Observability
`app/Services/Observability/ObservabilityService.php`: health checks, metrics/performance counters, structured logging channel, audit correlation IDs propagated through jobs, and Prometheus-compatible metric export shape.

### Part 12 — Feature Toggles
`app/Services/FeatureFlags/FeatureToggleService.php` + `config/feature-flags.php`: layered toggles (global → tenant → environment) with development/beta/hidden visibility classes and kill switches.

### Part 13 — Configuration
`app/Services/Configuration/ConfigurationService.php`: central configuration access with environment abstraction and secrets indirection — no hardcoded configuration values.

### Part 15 — API Foundation
`app/Support/Api/ApiResponse.php`: standardized success/error envelopes, pagination/filtering/sorting/cursor-pagination response shapes, versioning-ready structure for OpenAPI documentation.

### Parts 14 / 16–20 — Database, Performance, DevOps, Checklist, Tests, Static Review
- **Database** — reviewed: UUID PKs, FK cascade strategy, soft deletes, composite indexes on hot query paths documented in `docs/PRODUCTION_ENGINEERING.md`.
- **Performance** — N+1 prevention conventions, chunked exports/reports, duplicate-query guards.
- **DevOps** — Docker/queue-worker/scheduler/Supervisor/Horizon/Octane/Vapor readiness notes in `docs/PRODUCTION_ENGINEERING.md`.
- **Checklists** — deployment, environment, security, queue, cron, backup, restore, disaster recovery checklists generated in `docs/PRODUCTION_ENGINEERING.md`.
- **Tests** — existing suites organized under Unit/Feature/Integration categories; no tests executed (deferred).
- **Static review** — all new files are leaf services with no circular dependencies; dependency direction is controllers/services → Support/Services only; tenant isolation preserved via StorageManager and FeatureToggleService layers.

Created:
- app/Services/Cache/CacheService.php
- app/Support/QueueDomains.php
- app/Jobs/Concerns/ProductionJob.php
- app/Services/Search/SearchService.php
- app/Services/Storage/StorageManager.php
- app/Services/Media/ImageOptimizationService.php
- app/Services/ImportExport/ImportExportService.php
- app/Services/Webhook/WebhookFrameworkService.php
- app/Services/Security/RateLimitPolicy.php
- app/Services/Security/SecurityHardeningService.php
- app/Services/Observability/ObservabilityService.php
- app/Services/FeatureFlags/FeatureToggleService.php
- app/Services/Configuration/ConfigurationService.php
- config/feature-flags.php
- app/Support/Api/ApiResponse.php
- docs/PRODUCTION_ENGINEERING.md

Modified:
- docs/IMPLEMENTATION_STATUS.md (this section)

Runtime Verification: **DEFERRED**
## Runtime Integration — Laragon Local (2026-08-27)

Status: **COMPLETE** — local runtime is working on fallback port 8001, including local admin access.

| Item | Status |
|---|---|
| `.env` configured | DONE — normalized for local Laragon MySQL, file cache/session, database queue, public disk, log mailer. |
| Database created | DONE — `foursouls` database created via SQL helper. |
| Migrations completed | DONE — `php artisan migrate:fresh --force` completed; all 41 project migrations ran. |
| Schema SQL generated | DONE — `database/sql/generated/foursouls_schema.sql`. |
| Full local SQL dump generated | DONE — `database/sql/generated/foursouls_local_full_dump.sql`. |
| Composer install | DONE — existing vendor retained; missing `filament/filament:^5.0` installed after enabling Laragon PHP zip extension. |
| NPM install | DONE — packages up to date, zero vulnerabilities reported. |
| Vite build | DONE — `public/build/manifest.json` generated. |
| Storage link | DONE — `public/storage` already linked. |
| Local server | DONE — FourSouls running at `http://127.0.0.1:8001` because port 8000 is owned by another PHP server. |
| Website URL | OPENING — `http://127.0.0.1:8001` redirects to `/login` with HTTP 200. |
| Login URL | OPENING — `http://127.0.0.1:8001/login` returns HTTP 200. |
| Admin URL | OPENING — `http://127.0.0.1:8001/admin` redirects to `/admin/login` with HTTP 200. |
| Admin user | CREATED — `admin@foursouls.test` / `password` with `super_admin` role, guarded by local-only seeder. |
| Remaining blocker | None. |

Created:
- `database/sql/001_create_local_database.sql`
- `database/sql/generated/foursouls_schema.sql`
- `database/sql/generated/foursouls_local_full_dump.sql`
- `resources/views/layouts/partials/flash.blade.php`
- `database/seeders/LocalAdminSeeder.php`
- `app/Filament/Resources/TenantResource/Pages/ListTenants.php`
- `app/Filament/Resources/TenantResource/Pages/CreateTenant.php`
- `app/Filament/Resources/TenantResource/Pages/EditTenant.php`

Modified:
- `.env`
- `composer.json`
- `composer.lock`
- `bootstrap/providers.php`
- `app/Filament/Pages/AnalyticsDashboard.php`
- `app/Filament/Pages/Auth/AdminLogin.php`
- `app/Filament/Pages/AdminProfile.php`
- `app/Filament/Resources/*Resource.php`
- `app/Filament/Resources/TenantResource.php`
- `resources/views/filament/pages/auth/admin-login.blade.php`
- `C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.ini`

Backups:
- `runtime_backups/20260826_222845/`

Commands verified:
- `php artisan optimize:clear`
- `php artisan package:discover`
- `php artisan about`
- `php artisan route:list --path=admin`
- `php artisan migrate:fresh --force`
- `php artisan migrate:status`
- `php artisan db:seed --force`
- `php artisan storage:link`
- `npm install`
- `npm run build`
- `php artisan serve --host=127.0.0.1 --port=8001`

Errors fixed:
- Malformed mixed SQLite/MySQL `.env`.
- Missing database creation SQL helper.
- Missing Filament package.
- Disabled Laragon CLI PHP `zip` extension.
- Missing Filament panel provider registration.
- Filament 5 type/signature compatibility for navigation group/icon, page view, resource schema forms, auth login/profile namespaces and return types.
- Missing TenantResource page classes and namespace imports.
- Missing `layouts.partials.flash` Blade include.
- Custom admin login view referenced obsolete `admin.password.request` route.
- Custom admin login view used the full page wrapper instead of Filament 5 simple auth page wrapper.
- Port 8000 served another workspace; FourSouls verified on fallback port 8001.

## Runtime Stabilization — Laragon Local (2026-08-27)

Status: **COMPLETE** — local app opens on `http://127.0.0.1:8000` (website, login, register, admin).

| Item | Status |
|---|---|
| Website opening | OPENING — `http://127.0.0.1:8000` redirects to `/login` with HTTP 200 |
| Login opening | OPENING — `http://127.0.0.1:8000/login` HTTP 200 |
| Register opening | OPENING — `http://127.0.0.1:8000/register` HTTP 200 (after `Locales::options()` alias) |
| Admin opening | OPENING — `http://127.0.0.1:8000/admin` redirects to `/admin/login` with HTTP 200 |
| Admin user | CREATED — `admin@foursouls.test` / `password` (local-only `LocalAdminSeeder`; `Auth::guard('admin')->attempt` OK) |
| Composer | DONE — vendor autoload present; Composer 2.10.2; Laravel 13.29.0; Filament v5.7.6 |
| NPM | DONE — Node v22.23.2 / npm 10.9.8; `npm run build` succeeds |
| Vite build | DONE — `public/build/manifest.json` generated; Blade keeps `@vite` |
| Migrations | DONE — all 41 project migrations Ran (batch 1) |
| Route list | DONE — `php artisan route:list` lists 95 routes including `login`, `register`, `filament.admin.home` |
| SQL backup generated | DONE — `database/sql/generated/foursouls_schema.sql` and `foursouls_local_full_dump.sql` |
| Remaining runtime blocker | None |

Created:
- none (this pass reused existing `LocalAdminSeeder` and SQL dump paths)

Modified:
- `.env` — `APP_URL` set to `http://127.0.0.1:8000`
- `app/Support/Locales.php` — added `options()` alias required by `resources/views/auth/register.blade.php`
- `database/sql/generated/foursouls_schema.sql`
- `database/sql/generated/foursouls_local_full_dump.sql`
- `docs/IMPLEMENTATION_STATUS.md` (this section)

Backups:
- `runtime_backups/20260827_233936/`

Commands verified:
- `php artisan about`
- `php artisan route:list`
- `php artisan migrate:status`
- `php artisan optimize:clear`
- `php artisan config:clear`
- `php artisan db:seed --class=LocalAdminSeeder`
- `npm run build`
- `php artisan serve --host=127.0.0.1 --port=8000`

Errors fixed:
- MySQL was not listening on 3306; started Laragon `mysqld` with `my.ini`.
- Port 8000 was free for this pass; `APP_URL` aligned to 8000.
- Register 500: `Call to undefined method App\Support\Locales::options()`.

## Local Functional QA (2026-08-27 / 2026-08-28)

Status: **PARTIAL** — public site, student LMS, checkout, assessments, CMS, SaaS index, and demo seed run locally. Filament resource list is smaller than the prompt catalogue (only registered panel resources). `/admin/analytics` now maps to the analytics dashboard page slug.

| Check | Result |
|---|---|
| Admin login | PASS — `admin@foursouls.test` / `password` on guard `admin`; `/admin` redirects guests to `/admin/login` (HTTP 200) |
| Admin resources | PARTIAL — registered and opening: Courses, Pages, Blog Posts, Media, Tenants, Analytics. Not registered in `AdminPanelProvider` (no 500 because they are absent): Users, Admins, Roles/Permissions, Categories, Instructors, Plans, Subscriptions, Orders, Payments, Coupons, Invoices, Quizzes, Assignments, Live Sessions, Notifications, Webhooks as top-level nav |
| Public website | PASS — `/` → login; `/login`; `/register`; `/blog`; `/home` and `/pages/home` CMS page |
| Login/Register | PASS — pages open; local register uses `email:rfc` (no DNS) so `*.test` works; demo student is email-verified |
| Course flow | PASS — `/learn` list, course curriculum, free preview, paid lesson for entitled user, 403 for locked user without entitlement, progress service OK |
| Checkout flow | PASS — plan preview + order + cash/log payment + fulfillment (subscription/entitlement/enrollment + invoice) |
| Assessment flow | PASS — quiz page, start/submit/evaluate, assignment page/submit |
| Live session flow | PASS — list opens; detail opens after `session_attendance` table name fix; join blocked outside window (no crash); replay helper OK |
| Communication | PASS — notification center; mark-read; Log WhatsApp/Push; ReminderService |
| CMS | PASS — Page/BlogPost/Media resources registered; public page + blog index/show |
| Analytics | PASS — `/admin/analytics` slug; KPI empty data path aligned to real columns (`view_count`, `present`, `issued_on`) |
| SaaS | PASS — TenantResource index/edit + feature/domain/branch/subscription relation managers aligned to real columns (`owner_user_id`, `session_attendance`, tenant subscription fields) |
| Permissions | PASS — guest `/admin` → login; student cannot use admin policies (`CoursePolicy` rejects non-Admin); Super Admin gate bypass unchanged |
| Demo seeder | CREATED — `database/seeders/LocalDemoSeeder.php` (local-only) |
| SQL backup | DONE — `database/sql/generated/foursouls_schema_after_qa.sql`, `foursouls_local_full_dump_after_qa.sql` |
| Current blocker | None for the registered local flows. Unregistered Filament resources from the prompt list are not missing-class 500s; they are simply not in the panel yet. |

Created:
- `database/seeders/LocalDemoSeeder.php`
- `config/commerce.php`
- `resources/views/commerce/order-show.blade.php`
- `scripts/local_functional_qa.php`
- `database/sql/generated/foursouls_schema_after_qa.sql`
- `database/sql/generated/foursouls_local_full_dump_after_qa.sql`

Modified (runtime blockers only):
- Course child relations (`lessons`/`quizzes`/`assignments`), Tenant/Enrollment/Invoice/Order/SessionAttendance models
- Checkout/payment/fulfillment/subscription/KPI/export/quiz/assignment/progress/live-session/CMS page path
- Filament PageResource, TenantResource + relation managers, Analytics dashboard
- Public/account/learn Blade views for missing routes/variables/enum labels

Backups:
- `runtime_backups/20260827_235656/`
- `runtime_backups/20260828_000614/`

Commands:
- `php artisan about`
- `php artisan route:list`
- `php artisan migrate:status` (41 Ran)
- `php artisan optimize:clear` / `view:clear`
- `npm run build`
- `php artisan db:seed --class=LocalDemoSeeder`
- `mysqldump` schema + full dump after QA

