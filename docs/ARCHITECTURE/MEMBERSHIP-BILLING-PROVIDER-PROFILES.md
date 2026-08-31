# WPEssential — Membership Billing Provider Capability Profiles

Status: **Phase 0 planning / static official-document research only / no billing integration authorized**  
Related: ADR-0013, ADR-0016, ADR-0019, ADR-0020, ADR-0040, ADR-0055, ADR-0057, `MEMBERSHIP-BILLING-ADAPTER-CERTIFICATION.md`.

## 1. Purpose

ADR-0057 defines the provider-neutral rule:

`Billing Source → verified source fact/event → Billing Adapter → reconciliation → Membership policy → Enrollment transition → Entitlement materialization/invalidation`

This document narrows the first provider profiles:

1. Manual / Free Enrollment reference source;
2. WooCommerce Core one-time purchases;
3. WooCommerce Subscriptions;
4. SureCart Purchases/Subscriptions.

Provider status/event names remain **source facts**, not WPE Enrollment states.

All profiles below are planning candidates only. No provider is MB0–MB5 certified yet.

---

# 2. Stable provider-profile identity

Conceptual identity:

`billing_family + provider_key + provider/plugin/API profile + adapter_version`

Initial keys:
- `billing.manual`
- `billing.woocommerce-order`
- `billing.woocommerce-subscriptions`
- `billing.surecart`

A provider/plugin upgrade can change hooks, payloads or semantics; certification is version/profile-scoped rather than one permanent brand-level badge.

---

# 3. Static evidence maturity

Static research uses a separate paper-only scale:

- **BE0** — unreviewed/insufficient current official evidence;
- **BE1** — source identity/current-state semantics reviewed;
- **BE2** — grant/cancellation/failure lifecycle reviewed;
- **BE3** — refund/change/reconciliation/security semantics reviewed.

BE0–BE3 is not executable certification. It never grants MB0–MB5.

Current paper state:
- Manual/Free: BE3 product-owned semantics;
- WooCommerce Core: BE3;
- WooCommerce Subscriptions: BE3;
- SureCart: BE3.

Current MB-certified count: **0**.

---

# 4. Cross-provider normalized source facts

Provider adapters emit WPE normalized facts such as:

### Purchase
- `purchase.created`
- `purchase.paid`
- `purchase.unpaid`
- `purchase.refund_partial`
- `purchase.refund_full`
- `purchase.revoked`
- `purchase.reactivated/invoked`
- `purchase.product_or_quantity_changed`

### Subscription
- `subscription.created`
- `subscription.trialing`
- `subscription.active`
- `subscription.renewed`
- `subscription.payment_failed`
- `subscription.on_hold`
- `subscription.cancel_at_period_end_set`
- `subscription.cancel_at_period_end_removed`
- `subscription.cancelled_terminal`
- `subscription.expired_or_completed`
- `subscription.reactivated/restored`
- `subscription.product_or_quantity_changed`

A provider can supply multiple facts for one source snapshot.

Raw event/status remains diagnostic/source metadata.

---

# 5. Profile: Manual / Free Enrollment

Provider key: `billing.manual`

This is the internal reference profile and does not imply payment.

## Source identity

Candidate source identity:
- site/network scope;
- admin/business action UUID;
- target user/team;
- Plan + published Plan revision policy;
- granting actor/reason;
- created/effective/expires times;
- optional external business reference supplied as non-authoritative metadata.

## Truth model

Manual facts are explicit WPE-owned commercial/access facts:
- complimentary grant;
- admin grant;
- free Plan enrollment;
- fixed expiry;
- manual revoke;
- manual extend;
- manual pause/reactivate where product policy permits.

No fake payment/order/subscription object is manufactured.

## Certification role

Manual/Free is the first adapter to prove the Membership transition engine independently from commerce plugins.

Future MB2 evidence must prove:
- duplicate grant/idempotency policy;
- exact Plan/user identity;
- audit actor/reason;
- fixed expiry;
- manual revoke/restore;
- conflict with provider-owned Enrollment;
- migration/import provenance.

---

# 6. Profile: WooCommerce Core one-time purchases

Provider key: `billing.woocommerce-order`

## 6.1 Source authority

Use supported WooCommerce order/line-item APIs, not direct private DB assumptions.

Source identity candidate:
- site ID;
- Woo order ID;
- order line-item ID;
- product ID;
- variation ID when present;
- customer/user reference;
- quantity;
- order currency;
- environment/site identity;
- WPE Mapping revision.

Enrollment uniqueness must include the **line-item/mapping identity**, not only the order ID, because one order can contain multiple Membership products.

## 6.2 Paid truth

Do **not** equate `Completed` with “paid”.

Current WooCommerce semantics establish:
- `Processing` means payment has been received and fulfillment remains;
- `Completed` means fulfillment is complete;
- `On hold` can mean payment confirmation is pending;
- `Pending payment` is unpaid;
- `Failed` is unsuccessful payment;
- `Cancelled` is cancellation, not proof a captured payment was refunded;
- full `Refunded` represents a fully refunded order, while partial refunds do not necessarily change the overall order to `Refunded`.

Woo's supported order API exposes `WC_Order::is_paid()` based on paid statuses and `payment_complete()` as the canonical payment-complete operation. Custom status plugins can also designate statuses as paid.

Therefore the adapter candidate uses:
- supported `WC_Order` paid/payment APIs;
- payment/date/order facts where useful;
- line-item refund quantities/amounts;
- normalized order/refund transitions;

rather than a hard-coded `status == completed` rule.

## 6.3 Initial grant

Candidate positive source fact:

`mapped line item exists + order is paid according to supported Woo semantics + source is not already fully revoked/refunded according to mapping policy`

A created Pending/On-hold checkout does not grant paid Membership merely because an order exists.

Zero-total/free Woo orders need an explicit mapping policy; do not pretend payment occurred when no payment was required.

## 6.4 Refunds

WooCommerce distinguishes full and partial refunds.

WPE must inspect refund records/line-item quantities/amounts rather than rely only on final order status.

Mapping policy can choose:
- no access change;
- immediate revoke;
- revoke at access end;
- reduce seats when quantity semantics are explicitly configured;
- manual review for partial/refund ambiguity.

Manual Woo refund can update Woo records without proving the external processor returned funds, so WPE must label the fact according to the evidence source rather than claiming processor settlement.

## 6.5 Order edits

After paid grant, admin edits may alter product/variation/quantity/customer.

WPE must not silently transfer paid Membership based on an edited mutable email or line item without deterministic policy/audit.

Reconciliation compares current source line items/refunds/customer ownership to linked Enrollment and raises review where identity changed ambiguously.

## 6.6 Hooks are freshness, reconciliation is truth recovery

Candidate hooks/events may wake reconciliation, including payment-complete/status/refund/order update signals, but the transition engine consumes a fresh normalized order snapshot where ambiguity exists.

The adapter does not depend on one hook firing exactly once.

---

# 7. Profile: WooCommerce Subscriptions

Provider key: `billing.woocommerce-subscriptions`

## 7.1 Source authority

Use supported `WC_Subscription`/Woo APIs and documented subscription actions rather than direct post/meta table assumptions.

Source identity candidate:
- site ID;
- subscription ID;
- parent/initial order reference;
- current/last renewal order reference;
- customer/user reference;
- product/variation line identity;
- quantity;
- payment method/gateway identifier only as non-secret source metadata;
- start/trial/next-payment/end/paid-through relevant dates;
- Mapping revision.

## 7.2 Current source statuses

Current documented lifecycle includes:
- `pending`;
- `active`;
- `on-hold`;
- `pending-cancel`;
- `cancelled`;
- `expired`;
- custom/unknown statuses possible through ecosystem code.

These are source statuses, not WPE Enrollment states.

## 7.3 Pending vs active

`pending` means the subscription exists but initial payment has not been processed.

A created subscription alone is not paid access proof.

`active` is a positive recurring-source fact, but WPE still records source period/mapping rather than simply mirroring the string `active` into Enrollment.

## 7.4 Pending cancellation

Current WooCommerce Subscriptions semantics are explicit: customer/store cancellation during a prepaid term normally moves the subscription to `pending-cancel`, and only after the prepaid term ends does it become `cancelled`.

Therefore:
- `pending-cancel` → WPE normalized cancellation-intent fact;
- prepaid/access-through date remains a separate source fact;
- Membership Plan policy determines continued access through the paid term;
- final `cancelled` is a distinct terminal source fact.

Immediate revoke merely on `pending-cancel` would contradict provider semantics.

## 7.5 On-hold and failed renewals

Current Subscriptions behavior can put a subscription on hold when:
- renewal payment is awaiting/failed;
- customer/store manually suspends it;
- manual renewal is awaiting payment.

Documented failed-payment actions include:
- `woocommerce_subscription_payment_failed`;
- `woocommerce_subscription_renewal_payment_failed`.

The retry system can schedule automatic retries for eligible failed renewals.

Therefore `on-hold`/payment-failed is not automatically permanent revocation. WPE policy chooses active-during-retry, grace, paused or terminal behavior from source facts and configured Membership policy.

## 7.6 Renewal success

Documented actions include:
- `woocommerce_subscription_payment_complete` for subscription payments, including initial or renewal;
- `woocommerce_subscription_renewal_payment_complete` specifically for renewal-order payments.

Hooks do not replace current-source reconciliation and should not be used as the sole source of next-payment date truth; Woo documentation explicitly warns that early-renewal date changes have special handling.

A renewal-success fact updates paid-through/current-period source state through fresh subscription/order data.

## 7.7 Cancelled vs expired

`cancelled` and `expired` are distinct source facts:
- cancelled reaches the end of prepaid cancellation term;
- expired reaches the configured subscription end date/length.

Both are terminal provider facts for that source, but WPE can still preserve independent manual/complementary/grandfathered Entitlements according to policy.

## 7.8 Role changes are not WPE authority

Woo Subscriptions can change a user's WordPress role based on subscription lifecycle.

Per ADR-0020, WPE Membership does not infer Enrollment/Entitlement truth from that role. Optional WPE role sync remains separate/provenance-aware.

## 7.9 Scheduler degradation

Woo Subscriptions uses scheduled actions for renewals. Current Woo docs explicitly note renewals can fail to occur when Action Scheduler/WP-Cron is stalled.

WPE must distinguish:
- provider source is active but renewal processing is overdue/stalled;
- provider source has actually reported payment failure;
- source has become terminal.

A stale local schedule is not automatically a billing-failure fact.

---

# 8. Profile: SureCart

Provider key: `billing.surecart`

SureCart is cloud-authoritative for commerce data while WordPress receives webhooks/cache/integration events.

## 8.1 Source resources

Relevant authoritative resources include:
- Purchase;
- Subscription;
- Order/initial order;
- Refund;
- customer;
- price/product/variant;
- webhook Event.

WPE should prefer stable SureCart UUIDs over mutable display names/email.

## 8.2 Purchase resource as access-oriented source

SureCart's current developer documentation describes Purchase lifecycle around access/integrations:
- Purchase Created when checkout/one-time purchase or plan change creates the purchase;
- Purchase Invoked/reactivated;
- Purchase Revoked manually or automatically, including subscription cancellation/expiration or product change.

Purchase fields include:
- `id`;
- `live_mode`;
- `quantity`;
- `revoked`;
- `revoked_at`;
- `revoke_at`;
- customer;
- initial order;
- price/product/variant;
- subscription reference.

This makes Purchase a strong source object for product-access linkage, but WPE still applies its own Membership policy and does not delegate authorization to SureCart.

For recurring products, Subscription + Purchase must be reconciled together rather than assuming one event tells the whole lifecycle.

## 8.3 Subscription source fields

Current Subscription API exposes fields directly useful to normalized facts:
- `status`;
- `cancel_at_period_end`;
- `current_period_start_at`;
- `current_period_end_at`;
- `ended_at`;
- `trial_start_at` / `trial_end_at`;
- `quantity`;
- `live_mode`;
- `manual_payment`;
- `pending_update`;
- `restore_at`;
- purchase/customer/price/variant references.

`cancel_at_period_end=true` explicitly indicates an active subscription is scheduled to cancel at current-period end. WPE records intent and period end separately.

## 8.4 Current webhook events

Relevant current event vocabulary includes:
- `purchase.created`;
- `purchase.invoked`;
- `purchase.revoked`;
- `purchase.updated`;
- `refund.created`;
- `refund.succeeded`;
- `subscription.created`;
- `subscription.made_trialing`;
- `subscription.made_active`;
- `subscription.renewed`;
- `subscription.set_to_cancel`;
- `subscription.canceled`;
- `subscription.completed`;
- `subscription.updated`.

`subscription.set_to_cancel` is explicitly distinct from terminal `subscription.canceled`.

Unknown future event types are ignored/logged safely, not treated as fatal or as implied access changes.

## 8.5 Webhook verification

Current SureCart webhook contract:
- `x-webhook-signature`;
- endpoint-specific signing secret;
- HMAC-SHA256;
- `x-webhook-timestamp` included in signed payload;
- timestamp can be checked for replay age;
- retry attempts generate a fresh timestamp/signature.

Verification occurs before normalized source facts enter the Event Inbox/transition pipeline.

Signing secrets are Vault P3.

## 8.6 Ordering and duplicates

SureCart explicitly does not guarantee webhook delivery order and recommends idempotent independent handling plus retrieving related resources when needed.

Duplicate events can occur; current guidance recommends storing the event ID and also considering event type + underlying object ID.

Therefore:
- Event Inbox dedupes provider event identity;
- ordering is never assumed;
- current Purchase/Subscription state is fetched/reconciled after ambiguity;
- repeated equivalent facts are transition-idempotent.

## 8.7 Webhook timeout/backpressure

Current SureCart docs expect acknowledgement within approximately 10 seconds and recommend returning `2xx` quickly with time-consuming processing asynchronous.

WPE ingress therefore performs bounded signature/schema/dedupe acceptance and delegates reconciliation/transition work to JobService after persistence.

This paper rule does not authorize a worker implementation.

## 8.8 Test/live isolation

Purchase/Subscription/Refund resources expose `live_mode`.

WPE mapping/source identity includes environment. Test-mode objects cannot grant production Membership merely because product/customer IDs resemble live data.

## 8.9 Refund truth

SureCart Refund has its own status such as `pending` or `succeeded` and emits `refund.created`/`refund.succeeded`.

WPE does not revoke from `refund.created` alone if the provider still reports the refund pending, unless an explicit business policy intentionally treats initiation as a hold/review signal.

Successful refund facts are reconciled to Purchase/Subscription and Mapping policy for full/partial access consequences.

## 8.10 Upgrade/downgrade/change

SureCart can create a new Purchase and revoke the old Purchase during subscription plan change. Subscription updates also expose `pending_update` and an update behavior that can be immediate or pending according to protocol/configuration.

WPE must avoid treating `purchase.revoked` during a product switch as a standalone account-wide revoke. It correlates old/new Purchase + Subscription + mapping revision and applies Membership Plan transition policy.

## 8.11 Pause/restore

SureCart cancellation endpoint/profile can use `restore_at`, effectively producing a temporary canceled/set-to-cancel period before automatic restoration.

WPE normalizes this as source pause/cancellation/restore facts and applies its own Enrollment semantics. It does not manufacture a WPE `paused` state solely from one field without accepted mapping policy.

---

# 9. Provider comparison

| Capability | Manual | Woo one-time | Woo Subscriptions | SureCart |
|---|---|---|---|---|
| Stable source object | WPE grant UUID | order + line item | subscription + order/line | Purchase + Subscription UUID |
| Paid/grant positive fact | explicit admin/free policy | `WC_Order` paid semantics | active/payment-complete + source dates | Purchase/Subscription/current state |
| Cancellation intent separate | explicit | mapping-specific | yes: `pending-cancel` | yes: `cancel_at_period_end` / `set_to_cancel` |
| Temporary failure/grace source | explicit | order payment state | failed renewal/on-hold/retry | subscription/payment/refund/source reconciliation |
| Partial refund evidence | n/a | Woo refund records/line quantities | renewal/order refund + subscription context | Refund + Purchase/Subscription context |
| Webhook signature | n/a | local WP hooks for core | local WP hooks for extension | HMAC-SHA256 + timestamp |
| Out-of-order external events | n/a | hook/reconciliation races possible | scheduler/hook races possible | explicitly expected |
| Duplicate external events | n/a | idempotency still required | idempotency still required | explicitly expected |
| Test/live field | site/staging policy | WP site/env policy | WP site/env policy | `live_mode` |
| Reconciliation read | WPE state | current order/refunds | current subscription/orders | SureCart API Purchase/Subscription/Refund |
| Current executable certification | none | none | none | none |

---

# 10. Customer → WordPress user resolution profiles

## Manual
Target WordPress user/team is explicit.

## WooCommerce
Candidate precedence:
1. stored provider-source ↔ WP user link from prior successful mapping;
2. Woo order/subscription customer user ID when present and valid;
3. carefully bounded verified billing email only where explicit policy permits and no ambiguity exists;
4. invite/create-user workflow if configured;
5. unresolved review.

Guest order email alone must not silently take over an existing user's paid Membership.

## SureCart
Candidate precedence:
1. existing SureCart customer/Purchase ↔ WP user link recorded by certified integration;
2. supported local SureCart customer/user linkage;
3. verified email match only under explicit ambiguity checks;
4. configured invite/create workflow;
5. unresolved review.

Mutable email is not primary source identity when stable provider IDs exist.

---

# 11. Reconciliation triggers

All external profiles support reconciliation after:
- initial link/grant;
- ambiguous/unknown event;
- duplicate/out-of-order source facts;
- webhook/hook/scheduler outage;
- manual Repair/Reconcile;
- mapping change impact review;
- site restore/clone;
- migration/import;
- provider/plugin/API upgrade where source semantics may differ;
- bounded scheduled drift audit according future Job policy.

Reconciliation computes expected source facts then feeds the **same Membership transition engine**. It is not an alternate direct-write path to Entitlements.

---

# 12. Restore/clone/staging

After WordPress restore/clone:
- source IDs can remain as historical identity;
- environment/site identity is checked before processing external callbacks/actions;
- staging must not process production renewals/webhooks as if it were the production site;
- reconciliation determines whether local state is stale;
- duplicate Enrollment creation is prevented by source uniqueness;
- webhook endpoint/site bindings are re-evaluated where the provider is cloud-based.

SureCart's current operational docs specifically expose webhook resync after migration/domain changes; WPE must model this as provider connection/reconciliation state, not silently reconnect a clone.

---

# 13. Provider-profile certification targets

## Manual / Free
- MB1 current source read is internal;
- MB2 initial grant/revoke/expiry/idempotency;
- MB3 lifecycle pause/reactivate where supported;
- MB4 migration/reconciliation/conflict;
- MB5 production profile/security/privacy/concurrency.

## WooCommerce Core
Target MB4 evidence before broad public `WooCommerce purchase → Membership lifecycle` claim:
- paid/unpaid/on-hold/failed/cancelled;
- virtual/downloadable Processing/Completed difference;
- custom paid status compatibility;
- guest/customer resolution;
- multiple Membership line items;
- partial/full/manual/automatic refund;
- order edit/customer change;
- duplicate hook/race;
- HPOS/classic storage compatibility through supported APIs;
- restore/clone/multisite.

## WooCommerce Subscriptions
Target MB4 minimum for recurring lifecycle claim:
- pending/active/on-hold/pending-cancel/cancelled/expired;
- initial + renewal success;
- failed renewal + automatic/manual retry/recovery;
- scheduler stall vs real payment failure;
- early/manual renewal date behavior;
- cancellation at prepaid term end;
- reactivation where provider allows;
- upgrade/downgrade/switch;
- refund;
- role changes ignored as WPE authority;
- duplicate hook/order/scheduled-action races;
- restore/clone/staging.

## SureCart
Target MB4 minimum for recurring lifecycle claim:
- Purchase create/invoke/revoke;
- subscription created/trialing/active/renewed/set-to-cancel/canceled/completed;
- `cancel_at_period_end` + period dates;
- refund pending/succeeded and partial amount policy;
- HMAC/timestamp/replay rejection;
- duplicate event ID;
- out-of-order events;
- webhook timeout/retry;
- webhook disabled/resync + API reconciliation;
- Purchase replacement during upgrade/downgrade;
- test/live isolation;
- restore/clone/domain migration;
- provider API unavailable and eventual repair.

MB5 remains required for a `Production Billing Profile Certified` claim.

---

# 14. Current official-document research snapshot

Reviewed 2026-08-27; static evidence only.

WooCommerce:
- Order statuses: `https://woocommerce.com/document/managing-orders/order-statuses/`
- Current `WC_Order` code reference (`is_paid`, `needs_payment`, `payment_complete`, paid date): `https://woocommerce.github.io/code-reference/classes/WC-Order.html`
- Payment Gateway API: `https://developer.woocommerce.com/docs/features/payments/payment-gateway-api/`
- Refunds: `https://woocommerce.com/document/woocommerce-refunds/`
- Subscriptions statuses: `https://woocommerce.com/document/subscriptions/statuses/`
- Subscriptions action reference: `https://woocommerce.com/document/subscriptions/develop/action-reference/`
- Renewal process / failed payment: `https://woocommerce.com/document/subscriptions/renewal-process/`
- Failed recurring payment retry developer guide and scheduled-action troubleshooting.

SureCart:
- Webhooks/signatures/order/duplicates/events: `https://developer.surecart.com/api-reference/webhooks`
- Subscription retrieve/update/cancel API: `https://developer.surecart.com/api-reference/subscriptions/`
- Purchases lifecycle/API: `https://developer.surecart.com/documentation/orders-and-purchases` and `/api-reference/purchases/`
- Refund API: `https://developer.surecart.com/api-reference/refunds/`
- Current webhook troubleshooting/resync operational guide: `https://surecart.com/docs/webhook-troubleshooting/`

Static documentation can change; provider profile certification must pin/review the actual plugin/API version used by a release.

---

# 15. Future evidence — NOT AUTHORIZED

After explicit owner development/executable-spike consent only:
- install/activate exact commerce plugin versions;
- create sandbox/test commerce objects;
- register local hooks/webhooks;
- send/receive provider events;
- call SureCart APIs;
- process payments/refunds/subscriptions;
- run Action Scheduler/cron;
- create WPE Enrollment/Entitlement records;
- execute concurrency/reconciliation/migration/privacy tests.

No WooCommerce/SureCart plugin interaction, API request, webhook, purchase, subscription, refund or Membership transition was executed while producing this document.
