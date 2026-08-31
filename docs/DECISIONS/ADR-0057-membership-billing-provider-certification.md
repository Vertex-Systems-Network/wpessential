# ADR-0057 — Membership Billing Source Facts, Reconciliation and Provider Certification

Status: **Accepted product/integration architecture / executable provider evidence pending**  
Date: 2026-08-27

## Context

WPEssential Membership integrates with commerce/billing systems but must not allow provider-specific statuses, webhook ordering, transient payment failures or cancellation intent to directly define protected-resource access.

WooCommerce Subscriptions and SureCart both expose lifecycle semantics where cancellation intent can exist before terminal cancellation. Webhooks can also be duplicated, delayed or out of order. Treating one provider event as direct WPE access truth would create premature revocation, duplicate Enrollment records and unrecoverable drift.

## Decision

Billing integrations are modeled as **verified commercial source facts**, not Membership authority.

Canonical path:

`Billing Source → verified source fact/event → Billing Adapter → reconciliation → Membership policy → Enrollment transition → Entitlement materialization/invalidation`

### Domain ownership

Billing provider owns:
- order/purchase/subscription identity;
- payment/renewal facts;
- billing periods;
- cancellation/refund/dispute facts;
- provider customer identity;
- amounts/currency and provider billing internals.

WPE Membership owns:
- Plan/benefit definitions;
- Enrollment lifecycle;
- access/grace policy;
- Entitlements;
- protected-resource authorization;
- team/seat state;
- manual/complimentary access;
- grandfathering/follow-current semantics.

Provider status is never copied directly as WPE Enrollment state.

### Cancellation

`cancel at period end`/`pending-cancel`/equivalent source state is a cancellation **intent/fact**, not automatic immediate access revocation.

WPE records the verified intent and paid/access-through source facts, then applies Plan policy. Final provider cancellation/expiration remains a separate source fact.

### Payment failure

A transient failed renewal/on-hold/past-due equivalent is normalized as a source fact. WPE policy determines whether Enrollment remains active, enters grace, pauses or eventually expires/revokes.

One failed webhook does not automatically permanently revoke paid access unless explicit published policy requires it.

### Reconciliation

Reconciliation is mandatory after ambiguous/out-of-order events, webhook outages, manual repair, site restore/clone and on bounded schedules where provider APIs permit.

Reconciliation fetches current provider truth and feeds the same deterministic transition engine; it does not create a second hidden access path.

### Idempotency

Provider/source identity is unique enough to prevent duplicate Enrollment creation from duplicate webhook/hook delivery. Event Inbox dedupe and Enrollment source uniqueness are required.

### Initial adapter priority

1. Manual / Free Enrollment
2. WooCommerce Core one-time purchase
3. WooCommerce Subscriptions
4. SureCart
5. additional billing providers after demand/security/API evidence

WPE does not become a direct card-processing/Stripe billing engine merely to offer Membership v1.

## Certification levels

Billing provider profiles use:

- **MB0 — Detected / Mapping Configurable**
- **MB1 — Source Read Certified**
- **MB2 — Grant Lifecycle Certified**
- **MB3 — Renewal / Failure / Cancellation Certified**
- **MB4 — Refund / Change / Reconciliation Certified**
- **MB5 — Production Billing Profile Certified**

For recurring subscription providers, normal public claim of complete Membership lifecycle support should require at least **MB4** for the advertised lifecycle, while `Production Certified` requires MB5.

A provider may be certified for a narrower capability set; unsupported actions remain explicit.

## Security/privacy

- WPE never stores card number/CVC.
- provider secrets live in Vault.
- required webhook signature/replay verification occurs before source-fact dispatch.
- billing payload retention is minimized.
- hidden/frontend fields cannot forge paid status/source IDs.
- test/sandbox and live environments are isolated.
- billing source IDs are access-controlled business/PII data but are not authentication secrets.

## Consequences

Positive:
- cancellation intent no longer causes premature revocation;
- duplicate/out-of-order events become recoverable;
- provider-specific status vocabularies do not leak into WPE core;
- manual/complementary Membership remains possible without billing provider;
- changing billing provider does not redesign authorization;
- billing adapters can be certified independently.

Cost:
- reconciliation service required;
- source-fact normalization and mapping definitions add complexity;
- provider-specific lifecycle fixtures are mandatory before support claims.

## Evidence still required

After explicit owner development consent:
- Woo one-time paid/unpaid/refund fixtures;
- Woo Subscriptions pending/active/on-hold/pending-cancel/cancelled/expired/renewal fixtures;
- SureCart webhook signature/replay/duplicate/out-of-order/reconciliation fixtures;
- customer→WP user resolution ambiguity/race tests;
- refund/upgrade/downgrade/trial mappings;
- test/live isolation;
- site restore/clone behavior;
- migration/privacy/export fixtures;
- source uniqueness/concurrency tests.

No billing adapter, provider hook/webhook, API call or test has been implemented/executed.
