# Payment Architecture

## Current map

Checkout creates an order with server-calculated amount and selected currency.
`PaymentService` resolves configured `PaymentGateway` implementations. The
current gateways are offline `ManualPaymentGateway` adapters for cash, bank
transfer, and local logging. `FulfillmentService` is the only success path to
invoice, subscription activation, entitlement, and enrollment.

`PaymentGateway` currently supports `key`, `label`, `supports`, `initiate`, and
`handleCallback`. This is a clean provider-neutral base for a UAE provider;
provider-specific return verification, signed webhook handling, status query,
and refunds still require the selected provider contract.

## Safety and status

- AED, USD, and INR amounts are selected independently; no FX conversion occurs.
- Orders freeze their amount and currency at creation.
- Fulfillment is transaction-wrapped and order/invoice creation is idempotent.
- Pending and failed manual payments do not grant access.
- Callback handling locks the payment row and ignores terminal payments, preventing repeated fulfillment for duplicate callbacks on the same payment.
- No UAE provider has been selected and no sandbox credentials are configured.
- Live gateway integration is blocked pending provider and credentials.
- Refund access policy remains a business decision; progress is not automatically deleted.

## Operational documentation

See `handbooks/admin/ADMIN_HANDBOOK.md` for the current admin payment process
and `handbooks/knowledge-base/KNOWLEDGE_BASE.md` for deterministic support
answers. The UAE provider remains **NOT SELECTED** and sandbox testing remains
**NOT RUN**; do not describe card payments as live.
