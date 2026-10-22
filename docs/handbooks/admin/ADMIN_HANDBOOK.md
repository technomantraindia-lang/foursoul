# FourSouls Admin Handbook

## 1. Getting Started

Open `/admin` and sign in with an active administrator account. Admin access is separate from customer login. The dashboard provides operational navigation and analytics where configured.

## 2. Users

Use the user area when available to review account status, verification, and customer access. A blocked or closed customer cannot use learning routes. A suspended customer is directed to the account status screen. Never change a customer's status without the relevant support or business approval.

## 3. Courses

### Create and publish a course

**Who:** an administrator with course-management permission.

**Before you start:** decide the title, purchase mode, currency prices, and content owner.

1. Open **Courses** and choose **Create**.
2. Enter the title and description.
3. Set the purchase mode and independent AED, USD, and INR prices where applicable.
4. Save the course as draft while content is being prepared.
5. Add sections and lessons, then set their order and visibility.
6. Publish only after the course and its customer access path are ready.

**Result:** only a published course is eligible for the customer catalogue. Draft or archived content is not publicly listed.

**Common problem:** a course is missing from `/courses`. Check its status and publication date first.

## 4. Sections and Lessons

Course structure is `Course -> Section -> Lesson`. Add sections in the course editor, set position, then add lessons inside each section. Lessons use the configured lesson type and may contain a YouTube video identifier. A lesson must belong to its course and be available under the release rules before a customer can open it.

## 5. Enrollment and Entitlement

**Enrollment** records the customer's relationship with a course. **Entitlement** records whether the customer currently has permission to access protected content. Enrollment history can remain after expiry while entitlement becomes inactive. Access checks are server-side.

## 6. Plans and Subscriptions

Create plans under **Subscription Plans**. Use the real billing type, duration, access type, independent AED/USD/INR prices, status, and publication state. Only active, published plans appear on `/plans`.

Subscription states include `pending`, `trialing`, `active`, `expired`, `cancelled`, and `ended`. Expiry locks dependent access but preserves progress. Renewal uses `App\Services\SubscriptionService::renew()` and reuses existing entitlement and enrollment records.

## 7. Renewals

The customer renewal screen is `/subscription/renew`. The authenticated user's subscription is resolved by ownership, then the existing renewal service restores access. It does not reset lesson progress or create duplicate enrollment/entitlement records. Live paid renewal is unavailable until a UAE provider is selected.

## 8. Live Sessions and Replay

Create and schedule a live session with its course, instructor, provider, and timing. Customers see sessions only when their course access and the join window permit it. After completion, a replay remains subject to course access rules. YouTube replay/player access is not a bypass for entitlement checks.

## 9. Quizzes and Assignments

Attach quizzes and assignments to the relevant course or lesson. Customers must have the required course access. Quiz evaluation supports automatic scoring for supported question types and manual review for long-form/file submissions where configured.

## 10. Certificates

Certificates are issued only when course requirements and certificate eligibility are met. Customers can view earned certificates and use the public verification route when a verification code exists.

## 11. Payments

The payment architecture freezes order amount and currency and routes success through payment fulfillment before subscription, entitlement, and enrollment changes. Current configured gateways are offline manual adapters. **LIVE UAE PAYMENT PROVIDER: NOT SELECTED.** Pending or failed payments must not grant access.

## 12. Roles and Support

Admin capabilities are permission-based. Content, support, and super-admin roles should be used according to the configured role matrix. When escalating, include the user email, course/plan name, order reference if present, timestamps, and exact error text. Never include passwords, card data, API keys, or webhook secrets.

## 13. Quick FAQ

- **Why is a plan not visible?** It must be active and published.
- **Why is a lesson locked?** Check course access, entitlement status, release rules, and lesson ownership.
- **What happens at expiry?** Paid access is locked; progress and history remain.
- **Does renewal duplicate enrollment?** No. The existing service updates/reuses records.
- **Why is payment pending?** The order awaits confirmation; no access is granted until verified success.

See the [Admin Troubleshooting Manual](../troubleshooting/ADMIN_TROUBLESHOOTING.md) for issue IDs and checks.
