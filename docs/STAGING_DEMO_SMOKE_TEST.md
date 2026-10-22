# Staging Demo Smoke Test

Run after upload and cache commands. Record HTTP status and visible result for
each item.

## Public and customer

- `/` redirects to the correct login/customer destination.
- `/register` opens and validation is understandable.
- `/login` opens and accepts a valid staging account.
- `/dashboard` loads for an active customer.
- `/courses` lists only published courses.
- `/plans` shows active published plans and independent currency prices.
- `/account/subscription` shows only the signed-in customer's data.
- `/subscription/renew` shows the correct eligibility/provider state.
- `/learn` shows learning and progress.
- Course detail, lesson player, live sessions, replay, quizzes, assignments,
  and certificates open for an authorized test account.

## Admin

- `/admin/login` loads.
- `/admin` loads for an active admin.
- Courses, subscription plans, analytics, live sessions, assessments, and
  certificate resources open according to the admin role.

## Safety checks

- No unexpected 404 or 500.
- No stack traces or debug toolbar in the browser.
- Pending/manual payment does not grant paid access.
- No customer records, progress, certificates, enrollments, or entitlements
  were reset.
- Live UAE card payment remains clearly unconfigured.
- Review `storage/logs/laravel.log` for new errors after the smoke test.
