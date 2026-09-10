# Membership — Native & Market Evidence Matrix V1

Surface: **15 / Membership**  
Issue: **#498**  
Research anchor: **`main @ fad3b2f37bd44a01d77438e71877d7990b4bfe2c`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| MemberPress — Memberships and Groups | https://memberpress.com/docs/memberpress-memberships-and-groups/ | membership products/levels, groups, access structure, recurring/non-recurring subscription distinction |
| MemberPress — Creating Memberships | https://memberpress.com/docs/creating-memberships/ | plan identity, price, term and membership product lifecycle |
| Paid Memberships Pro — Membership Levels | https://www.paidmembershipspro.com/documentation/membership-levels/ | levels, level groups, multiple simultaneous levels, member level changes |

## Seed disposition evidence

- `membership.plan.identity` — strong market evidence for named membership plans/levels and lifecycle.
- `membership.pricing.model` — free, one-time and recurring pricing are established membership-product models; payment execution remains provider-owned and unauthorized in this lane.
- `membership.state.lifecycle` — membership duration/expiration/status needs canonical state semantics distinct from payment transaction state.
- `membership.change.policy` — upgrade/downgrade/change behavior is established market functionality; proration/provider side effects require later explicit contracts.
- `membership.restriction.rules` — access restriction is core membership behavior, but authorization must remain canonical and server authoritative.
- `membership.discount.policy` — valid parity candidate; coupon/payment semantics require dedicated provider and promotion-rule evidence.
- `membership.transaction.reference` — Surface 15 may keep references/reconciliation state, not invent a payment ledger/provider engine.
- `membership.reporting.summary` — valid derived reporting candidate; revenue truth must originate from canonical transaction/ledger sources.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Payment providers, subscription lifecycle edge cases, refunds/chargebacks, taxes, grace periods, coupons, entitlement conflicts, multisite and zero-unresolved review remain open. No payment or membership runtime execution is authorized.