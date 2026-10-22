# Customer Catalogue and Subscription QA

## Routes and Views

- `GET /courses` (`courses.index`) -> `resources/views/courses/index.blade.php`
- `GET /plans` (`plans.index`) -> `resources/views/plans/index.blade.php`
- `GET /account/subscription` (`subscription.show`) -> `resources/views/account/subscription.blade.php`
- `GET /subscription/renew` (`subscription.renew`) -> `resources/views/subscription/renew.blade.php`

## Implementation

The catalogue uses `Course::published()` and loads lesson counts in the listing
query. Plans use `SubscriptionPlan::purchasable()->ordered()` and the current
user's active subscription state. Subscription and renewal screens query only
the authenticated user's subscriptions. Prices use the existing currency
resolver and `CurrencyFormatter`; no exchange-rate conversion was added.

Renewal uses the existing `App\Services\SubscriptionService::renew()` method.
That method updates the owned subscription, reactivates existing entitlements,
and unlocks existing enrollments without creating duplicates or resetting
progress. The customer action is explicitly an internal test/manual flow while
payment is provider-blocked; no live UAE gateway was integrated.

## QA

- `/courses`: HTTP 200, published-only query, guest-safe currency resolution.
- Blade view cache: passed.
- Full regression: 193 passed, 556 assertions, 1 risky test.
- Vite build: passed.
- Generated CSS: `public/build/assets/app-c0XbyJ04.css` (89,300 bytes).
- Generated JS: `public/build/assets/app-BVfFs1lK.js` (1,600 bytes).
- Legacy `app-CZGy9YPv.css`: absent from the build output.
- Responsive source audit: pass at the requested breakpoint classes; browser
  screenshot tooling was not available.
- Dark mode: new surfaces include existing system dark-mode treatment.
- Accessibility: semantic headings, labelled status badges, meaningful links,
  disabled provider-blocked action, focus-compatible existing button styles.
- New Laravel log errors after `/courses` smoke test: 0.
- Renewal ownership is enforced by querying through the authenticated user; no
  subscription ID is accepted from the request.
- Repeat renewal is idempotent at the enrollment and entitlement keys.

## Remaining Limitation

Live payment remains blocked until a payment provider is selected. The plans
page therefore presents real plan data and a clear unavailable state rather than
claiming a successful purchase.
