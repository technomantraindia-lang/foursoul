# Admin Troubleshooting Manual

## ADM-COURSE-001: Course not visible

**Symptoms:** missing from `/courses`.

**Checks:** confirm course status is published, publication date is set, and the record is not archived/deleted.

**Resolution:** correct the course state, save, and reload the catalogue. **Related:** Admin Handbook, Courses.

## ADM-LESSON-001: Lesson unavailable

Check course ownership, section position, lesson release/visibility, and customer entitlement. Do not unlock paid content by editing a URL.

## ADM-ACCESS-001: Customer cannot access paid lesson

Check account status, subscription state, entitlement status/expiry, enrollment state, and lesson release rules. Expired access is restored through the verified renewal flow.

## ADM-SUBSCRIPTION-001: Subscription inactive or expired

Confirm the plan, subscription status, current period, and expiry date. Expiry locks paid access but preserves progress. Renewal uses `SubscriptionService::renew()`.

## ADM-PLAN-001: Plan not visible

Confirm the plan is active, published, has a valid name, and has the required independent currency price. `/plans` intentionally hides inactive/unpublished plans.

## ADM-PROGRESS-001: Progress not updating

Confirm the customer is using the correct account and has valid lesson access. Check the lesson completion request and server log timestamp; do not delete progress records.

## ADM-LIVE-001: Live session missing or join fails

Check course assignment, scheduled status, start/end time, join window, instructor, and customer access. Replay remains behind the same access rules.

## ADM-ASSESSMENT-001: Quiz or assignment unavailable

Check publication/status, course relationship, access, attempts, and due/availability rules. File or long-form work may require manual review.

## ADM-CERT-001: Certificate unavailable

Check completion, assessment requirements, eligibility, issuance status, and certificate template/configuration.

## ADM-PAYMENT-001: Payment pending or failed

Current live provider is not configured. Pending/failed payments must not activate access. Retain the order reference and escalate without exposing payment secrets.

## ADM-SYSTEM-001: 404 or 500

Record the exact route, account, time, and correlation information. Confirm the route exists before changing content. A 500 belongs with the technical team; never expose stack traces to customers.
