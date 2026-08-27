# WPEssential Membership — Billing Adapter Priority

Status: **Phase 0 research/prioritization / no adapter implementation exists**  
Research date: 2026-08-27  
Related: ADR-0013, M-006, Membership Enrollment State Machine, Connections/Webhooks

WPEssential Membership should own access/lifecycle state while commerce systems own checkout/payment/subscription billing.

This document ranks the first adapter candidates by commercial reach, API/lifecycle maturity, security burden and architectural fit.

## Primary/current sources

- WooCommerce plugin directory: https://wordpress.org/plugins/woocommerce/
- WooCommerce REST APIs: https://developer.woocommerce.com/docs/apis/rest-api/
- WooCommerce Webhooks: https://developer.woocommerce.com/docs/best-practices/urls-and-routing/webhooks
- Woo Subscriptions developer docs: https://woocommerce.com/document/subscriptions/develop/
- Woo Subscriptions action reference: https://woocommerce.com/document/subscriptions/develop/action-reference/
- Woo Subscriptions status guide: https://woocommerce.com/document/subscriptions/statuses/
- SureCart WordPress plugin: https://wordpress.org/plugins/surecart/
- SureCart API: https://developer.surecart.com/api-reference/introduction
- SureCart Subscriptions API: https://developer.surecart.com/api-reference/subscriptions/retrieve
- SureCart Webhooks: https://developer.surecart.com/api-reference/webhooks
- SureCart Authentication: https://developer.surecart.com/api-reference/authentication

---

# 1. Baseline — Manual / Free Enrollment — priority 0

Not an external billing adapter, but it is the first Membership source mode and must exist before paid integrations.

Supports:
- free plan enrollment;
- administrator complimentary grant;
- migration/import enrollment;
- invitation/promotion-generated enrollment;
- manually time-bounded access;
- lifetime access;
- approval-required access.

Why first:
- proves Membership is not dependent on a payment plugin;
- makes entitlement/access architecture testable independently of billing;
- supports internal portals, staff/customer access, migrated memberships and free communities;
- provides recovery/manual override when external billing is unavailable.

No card/payment data involved.

---

# 2. Adapter 1 — WooCommerce Core Orders/Products — highest initial commercial priority

## Scope

Use WooCommerce core as source for one-time/fixed/lifetime grants tied to completed/eligible orders or products.

Examples:
- buy product X → grant Plan Gold for lifetime;
- buy course bundle → grant 365-day Enrollment;
- refund/order reversal → apply configured Membership policy.

## Why high priority

At the research date, WordPress.org reports **7+ million active WooCommerce installations**.

WooCommerce provides mature:
- order/product/customer domain APIs;
- WordPress REST API integration;
- webhooks;
- internal actions/hooks;
- signed webhook mechanism for remote integrations.

Because WPEssential runs on the same WordPress site, the preferred same-site adapter should generally use documented/public WooCommerce object APIs/hooks rather than unnecessarily sending REST/webhook traffic back to the same site.

REST/webhooks remain useful for remote/multisite/external architecture where needed.

## Adapter responsibility

Map:
- Woo product/variation → WPEssential Plan or grant rule;
- eligible order state → Enrollment create/activate;
- refund/cancel/reversal → configured Enrollment transition;
- customer/user linkage → explicit mapping with no email-only authorization shortcut after association.

## Important boundary

Woo order status is not Membership state.

Example:
- `completed` order event can activate Enrollment;
- later partial refund does not universally revoke access; administrator-configured policy decides.

## Security/data

- no Woo payment method/card data copied into Membership;
- store only required order/product/customer references;
- authorization remains local Membership entitlement state after normalization;
- duplicate order-processing hooks require idempotency.

---

# 3. Adapter 2 — WooCommerce Subscriptions — highest recurring priority

## Scope

Recurring Membership enrollment sourced from Woo Subscriptions.

## Why second

Woo Subscriptions is designed to expose subscription lifecycle through public APIs/classes/actions/filters and documents status-change hooks, failed-payment retry behavior, caches, scheduled events and REST APIs.

It fits the WPEssential adapter model because Woo owns:
- checkout;
- recurring billing;
- payment gateway relationship;
- billing subscription lifecycle.

WPEssential owns:
- Plan mapping;
- Enrollment state translation;
- Membership grace/pause/access policy;
- Entitlements;
- protected resources.

## Status translation

Do not copy Woo subscription status strings into WPEssential Enrollment status blindly.

Adapter maps source facts/events to canonical states from `MEMBERSHIP-ENROLLMENT-STATE-MACHINE.md`.

Examples:
- Woo active → WPE active;
- pending cancellation with valid period → keep WPE active + scheduled end;
- failed renewal → WPE grace or paused based on Plan/source policy;
- recovery → active;
- source expiry → expired;
- fraud/admin revoke remains a separate WPE revocation decision.

## Event source

Same-site preferred:
- documented Woo Subscriptions hooks/object APIs;
- idempotent event reference derived/persisted appropriately;
- reconciliation scans/source fetches for missed/inconsistent events.

Do not rely on hooks alone as irreversible source truth; reconciliation must be able to compare current source subscription state.

## Why not integrate WooCommerce Memberships as the primary model

WooCommerce Memberships may be a useful **migration/interoperability source**, but WPEssential should not map its own Membership state to another membership plugin at runtime as the canonical access engine.

WooCommerce/Subscriptions should provide commerce/billing inputs; WPEssential's Membership module provides its own access/entitlement platform.

---

# 4. Adapter 3 — SureCart — strong modern secondary adapter

## Market/fit

At the research date WordPress.org reports about **80,000+ active SureCart installations**.

SureCart directly supports subscriptions and exposes a modern API/webhook model.

Its official webhook documentation explicitly covers:
- subscription lifecycle events;
- signatures;
- replay-attack prevention;
- duplicate event handling;
- event ordering/out-of-order delivery;
- fetching current objects via API when needed.

This aligns well with WPEssential's planned idempotent/reconciliation-first adapter contract.

## Architecture difference

SureCart is partly platform/service based rather than only local WordPress commerce state.

Therefore WPEssential needs:
- secret API key via Secrets Vault;
- HTTPS API client through Connections Manager;
- signed webhook verification;
- event ID deduplication;
- current subscription reconciliation through API;
- live/test mode separation;
- provider outage/rate-limit behavior.

## Status mapping

SureCart exposes fields/events such as:
- subscription active/trialing/canceled/completed;
- `cancel_at_period_end`;
- current period timestamps;
- trial timestamps.

This validates WPEssential's decision that cancellation intent must be separate from immediate access state.

The adapter translates SureCart state into WPE Enrollment semantics rather than exposing SureCart statuses to the policy engine.

---

# 5. Direct Stripe — defer from v1

## Why defer

A direct Stripe adapter would move WPEssential closer to owning:
- checkout/billing UX;
- customer/payment/subscription object orchestration;
- tax/proration/currency/payment recovery decisions;
- regulatory/payment support burden;
- more webhook/security surface.

This conflicts with the product architecture that WPEssential Membership should be an access platform while billing is handled by specialized commerce systems.

A direct Stripe source may be valuable later for API-first/headless teams, but should require evidence of demand and a dedicated billing-scope ADR.

If ever added:
- WPEssential still must never store card credentials;
- Stripe Checkout/Billing should own payment collection;
- Vault stores only server-side API/webhook secrets;
- adapter maps external subscription state into Enrollment.

---

# 6. Direct PayPal / other gateways — defer

Do not build separate payment-gateway adapters merely to increase feature count.

WooCommerce/SureCart already abstract many gateways.

Direct gateway integration would duplicate:
- billing lifecycle handling;
- refunds/disputes;
- retries;
- customer identity mapping;
- support burden.

Only add direct sources for a demonstrated use case not served by commerce-platform adapters.

---

# 7. Migration adapters — separate category

Migration compatibility is different from ongoing billing integration.

High-value import/migration candidates later:
- MemberPress;
- Paid Memberships Pro;
- WooCommerce Memberships;
- Restrict Content Pro;
- SureMembers;
- possibly legacy role-based membership systems.

Migration may import:
- plans/levels;
- users/membership history where exposed;
- expiry dates;
- access rules;
- billing/source references where safe and supported.

But migration must not claim it can take over another vendor's recurring billing without a valid billing-source adapter/credential/contract.

Example:
- import MemberPress member access into WPE Enrollment;
- recurring subscription continues only if source/payment system has a supported adapter or explicit migration path.

---

# 8. Adapter contract common to all billing sources

Each adapter must expose normalized operations conceptually equivalent to:

## Identity/mapping
- source type/version;
- product/price/subscription identifiers;
- customer/user mapping;
- Plan mapping validation.

## Lifecycle
- initial purchase/activation;
- trial;
- renewal;
- current period;
- cancel intent;
- cancellation effective end;
- payment failure;
- recovery;
- expiry/completion;
- refund;
- dispute;
- administrative/source change.

## Reconciliation
- retrieve current source state;
- list changed/relevant subscriptions where API supports;
- compare source vs local Enrollment;
- repair or flag divergence according to policy;
- never silently overwrite manual/security revoke without accepted precedence rule.

## Event security
For remote webhook sources:
- verify signature;
- replay protection/window;
- idempotent unique event storage;
- duplicate handling;
- out-of-order handling;
- timestamp/source validation;
- rate limiting;
- safe payload size;
- no log of secrets/sensitive payment data.

## Health
- connection health;
- last event;
- last successful reconciliation;
- mismatch count;
- credential expiry/error;
- provider/API version compatibility.

---

# 9. Mapping configuration options

Per Plan/source mapping eventually needs tiny-option specification for:

### General
- source adapter;
- connection/store;
- source product/price/variation;
- enabled;
- priority if multiple source mappings;
- environment live/test.

### Enrollment start
- eligible purchase/order/source statuses;
- activate immediately vs after approval;
- use source trial vs WPE trial;
- fixed/lifetime/source-controlled duration;
- user account creation/linking behavior.

### Renewal/failure
- renewal extends source-controlled period;
- failure → grace duration;
- failure → immediate pause option;
- recovered payment → reactivate;
- max reconciliation mismatch age before alert.

### Cancellation
- cancel intent keeps access through source period end by default;
- immediate source cancel behavior where provider indicates ended now;
- scheduled end timestamp source.

### Refund/dispute
- no access change;
- expire immediately;
- pause;
- revoke;
- manual review/workflow.

Default should not hard-code fraud semantics from financial event names.

### Deletion/source loss
- source subscription deleted/missing handling;
- preserve local history;
- mark reconciliation unhealthy;
- never delete Enrollment history automatically.

---

# 10. User identity mapping

Never authorize a paid Membership merely because webhook email matches an arbitrary existing account without a documented account-linking rule.

Candidate mapping order:
1. persisted source customer ↔ WordPress user mapping;
2. known checkout-created WordPress user/source metadata linkage;
3. explicit administrator/user account-link flow;
4. email matching only during controlled initial association with conflict checks/verification.

Once linked, stable IDs are source of truth.

Handle:
- changed email;
- guest order;
- duplicate customer records;
- multisite users;
- deleted WordPress user;
- source customer merged/deleted.

---

# 11. Webhook vs local hook strategy

## Same WordPress process/plugin
Prefer public domain hooks/APIs when reliable and documented.

Benefits:
- no HTTP loopback;
- no webhook secret setup;
- lower latency.

Still require:
- idempotency;
- missed-event reconciliation;
- version compatibility.

## Remote/SaaS source
Use Connections/Webhooks:
- signed event endpoint;
- replay/idempotency;
- provider API reconciliation.

The resulting normalized adapter event should feed the same Membership transition service regardless of transport.

---

# 12. Commercial/technical ranking

| Priority | Source | Commercial reach | Integration complexity | Recommended first-release role |
|---:|---|---|---|---|
| 0 | Manual/Free | universal | low | **core baseline** |
| 1 | WooCommerce Core | very high (7M+ WP installs) | medium | **one-time/fixed/lifetime purchase grants** |
| 2 | Woo Subscriptions | high within Woo ecosystem | medium-high | **primary recurring source** |
| 3 | SureCart | growing (~80k WP installs) | medium-high remote/API | **secondary recurring/modern source** |
| 4+ | direct Stripe | broad provider, but expands WPE billing scope | high | defer |
| 4+ | direct PayPal/gateways | fragmented | high | defer |

These are planning priorities, not implementation/support claims.

---

# 13. Definition of a supported adapter

A source is marketed as supported only after automated/manual evidence covers:
- install/connection/version detection;
- initial activation;
- renewal;
- cancellation intent/effective end;
- failure and recovery;
- refund/dispute configured behavior;
- duplicate event;
- out-of-order event;
- missed event + reconciliation;
- credential/API failure;
- idempotency;
- user mapping conflicts;
- source deletion/missing state;
- upgrade/downgrade if claimed;
- live/test isolation;
- security review;
- rollback/disable behavior.

No adapter has passed this because no adapter has been implemented.

---

# 14. Current recommendation

First Membership billing roadmap after future development consent:

1. Manual/Free Enrollment source;
2. WooCommerce Core product/order mapping;
3. WooCommerce Subscriptions recurring mapping;
4. SureCart adapter;
5. competitor migration/import adapters;
6. only then evaluate direct Stripe/other billing sources from real demand.

This order maximizes WordPress market reach while keeping WPEssential out of payment-card processing and preserving its role as the normalized access/entitlement platform.
