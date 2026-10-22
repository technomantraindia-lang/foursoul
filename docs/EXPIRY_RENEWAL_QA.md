# Expiry and Renewal QA

- `subscriptions:expire` is transactional and idempotent: active/trialing subscriptions past `expires_at` become expired, entitlements become expired, and enrollments become locked/expired according to the existing model state.
- Progress, certificates, users, and enrollment history are not deleted.
- `SubscriptionService::renew()` restores the subscription period and existing entitlement/enrollment access without resetting progress.
- Existing subscription access tests cover expiry history preservation and renewal activation.
- Payment and external gateway calls were not used for this verification.

Remaining follow-up: execute a browser-authenticated expiry/renewal scenario against a disposable fixture before production payment credentials are introduced.
