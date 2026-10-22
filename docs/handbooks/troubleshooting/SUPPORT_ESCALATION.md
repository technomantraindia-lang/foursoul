# Support Escalation

| Level | Owner | Examples | Include |
|---|---|---|---|
| 1 | Support | Navigation, password reset, join-window questions | User email, route, steps tried |
| 2 | Admin/content team | Unpublished course, disabled plan, subscription state | Record IDs, status, timestamps |
| 3 | Technical team | 404/500, queue, database, webhook errors | Exact error, URL, log timestamp; never secrets |
| 4 | External provider | Future payment/email/WhatsApp provider failures | Provider reference and redacted status |

Live UAE payment is currently not configured, so payment-provider incidents should be recorded as a product readiness blocker rather than diagnosed as gateway failures.
