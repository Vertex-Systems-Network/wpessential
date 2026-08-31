# ADR-0062 — Membership Billing Provider Source-Truth Profiles

Status: **Accepted membership integration profile / executable provider evidence pending**  
Date: 2026-08-27

## Context

ADR-0057 accepted the provider-neutral Membership billing architecture and MB0–MB5 certification model. Provider-specific research now establishes materially different source semantics for Manual/Free Enrollment, WooCommerce Core orders, WooCommerce Subscriptions and SureCart.

A generic mapping such as `provider status → WPE Enrollment status` would be incorrect. Examples:
- WooCommerce `Processing` can already mean payment is complete; `Completed` is fulfillment state, not the only paid state.
- WooCommerce Subscriptions `pending-cancel` preserves the prepaid term and is distinct from final `cancelled`.
- Woo failed renewals can enter `on-hold` and retry/recovery flows.
- SureCart exposes Purchase and Subscription as separate resources, with `cancel_at_period_end`/period dates and separate `subscription.set_to_cancel` vs `subscription.canceled` events.
- SureCart webhooks can be duplicated or delivered out of order and are designed to be reconciled with current API objects.

## Decision

The first Membership billing provider profiles are fixed as follows.

### Manual / Free
- WPE-owned explicit grant source; no fake payment/provider object.
- Stable grant/action identity + Plan/User/Team/provenance.
- Used as the reference adapter for proving Membership transitions without external commerce.

### WooCommerce Core one-time
- Source identity is order + line item + product/variation + mapping identity.
- Paid truth uses supported Woo order/payment APIs, including `WC_Order::is_paid()`/payment-complete semantics, not hard-coded `Completed` status.
- Pending/On-hold creation is not paid grant proof.
- Partial/full refund uses refund records/line-item quantities/amounts; full order status alone is insufficient for partial-refund semantics.
- Hook delivery is freshness; reconciliation of current supported Woo objects is the recovery path.

### WooCommerce Subscriptions
- Source authority uses supported `WC_Subscription`/order APIs.
- `pending-cancel` is cancellation intent/paid-through source fact, not immediate WPE revoke.
- `on-hold`/failed renewal is a temporary source fact subject to Plan grace/pause/retry policy, not automatic permanent revoke.
- renewal-success hooks feed fresh source-period reconciliation; hooks are not sole date truth.
- `cancelled` and `expired` remain distinct terminal provider facts.
- Woo role changes never become WPE Membership authority.
- scheduler stall/overdue execution is distinct from provider payment failure.

### SureCart
- Commerce source authority is cloud/provider objects: Purchase + Subscription + Order/Refund context.
- Stable provider UUIDs outrank mutable display identity/email.
- Purchase `created/invoked/revoked` is access-oriented source state but remains external commercial truth, not WPE authorization.
- Subscription `cancel_at_period_end` and `current_period_end_at` are separate source facts; `set_to_cancel` is not terminal `canceled`.
- Webhooks require endpoint-specific HMAC-SHA256 signature + signed timestamp/replay policy before source-fact dispatch.
- event order is not assumed; duplicates are deduped; current objects are fetched/reconciled after ambiguity.
- `live_mode` isolates test/live mappings.
- refund `created/pending` is not equivalent to successful refund; successful refund facts are correlated to Purchase/Subscription policy.
- upgrade/downgrade can revoke an old Purchase and create a new Purchase, so old-purchase revocation cannot be treated as an account-wide terminal revoke by itself.

## Shared rule

Every profile feeds the same provider-neutral transition path:

`verified current source facts → Billing Adapter → reconciliation → Membership policy → Enrollment transition → Entitlement materialization/invalidation`

Provider hooks/webhooks can trigger freshness work but do not create a second direct authorization path.

## Static evidence vs certification

Provider-profile documentation uses BE0–BE3 static evidence maturity only for planning. It does not grant MB0–MB5.

Current state:
- Manual/Free BE3 paper profile;
- WooCommerce Core BE3 paper profile;
- WooCommerce Subscriptions BE3 paper profile;
- SureCart BE3 paper profile;
- **0 billing profiles MB-certified**.

Recurring lifecycle public support should still require the release-defined MB level; ADR-0057 currently recommends MB4 minimum for a broad recurring-lifecycle claim and MB5 for `Production Billing Profile Certified`.

## Consequences

Positive:
- paid order grants no longer depend on `Completed` alone;
- prepaid subscription cancellation does not revoke access early;
- payment retry windows can map to explicit WPE grace/pause policy;
- SureCart duplicate/out-of-order webhooks are recoverable;
- refunds and plan switches cannot silently cause over-revocation;
- provider-specific semantics remain replaceable behind a stable Membership transition engine.

Cost:
- each provider needs version-scoped certification fixtures;
- reconciliation reads become mandatory for ambiguous states;
- customer→WP-user identity mapping requires dedicated race/security testing;
- refunds/switches/guest purchases require more than status-string mapping.

## Evidence still required

After explicit owner development/executable-spike consent:
- exact supported plugin/API version matrix;
- Manual grant concurrency/provenance fixtures;
- Woo HPOS/classic storage through supported APIs;
- Woo paid/unpaid/custom-paid-status/partial-refund/order-edit fixtures;
- Woo Subscriptions initial/renewal/failure/retry/pending-cancel/cancelled/expired/switch/refund/scheduler-stall fixtures;
- SureCart signature/replay/duplicate/out-of-order/API-reconciliation/webhook-disable/resync/test-live/refund/switch/restore-clone fixtures;
- customer/provider→WP-user resolution races;
- source uniqueness/concurrency;
- migration/privacy/export/erase behavior;
- Membership revoke-to-deny and cache invalidation evidence.

No WooCommerce/SureCart installation, hook, webhook, API call, payment, refund, subscription, migration or Membership runtime action has been executed by this decision.
