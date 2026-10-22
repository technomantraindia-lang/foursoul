# UAE Payment Gateway QA

## Current status

- Selected provider: not selected.
- Environment: not configured.
- Sandbox credentials: absent.
- Live card payment: not ready.

## Existing architecture

`CheckoutService` creates an order using server-calculated totals and frozen
currency. `PaymentService` resolves a configured `PaymentGateway`, creates a
pending payment, and sends verified success through `FulfillmentService`.
Fulfillment is the only path to invoice, subscription activation, entitlement,
and enrollment. `PaymentWebhookEvent` validates normalized webhook fields and
`PaymentService::handleNormalizedWebhook()` validates amount/currency and ignores
terminal duplicate payments.

## Provider gate

A provider must be explicitly selected before an adapter is written. Approved
candidate providers in the integration brief are Network International, Amazon
Payment Services, Telr, and PayTabs. No provider should be chosen arbitrarily.

## Required before integration

1. Select one provider.
2. Supply sandbox merchant/API/webhook credentials through environment variables.
3. Implement the provider adapter behind `PaymentGateway`.
4. Add official signature verification and a provider webhook route.
5. Test amount/currency tampering, duplicate events, pending/failed/cancelled
   states, purchase, and renewal in sandbox.
6. Keep refund access behavior pending the documented business decision.

No secrets, card details, or production credentials belong in source control.
