# Membership — Native WordPress Audit V1

Surface: **15 / Membership**  
Issue: **#691**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/membership.json` — **15 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; NO NATIVE MEMBERSHIP-PLAN ENGINE EXISTS IN WORDPRESS CORE.** No new Membership-owned native family is required for V1. Supervisor integration should add native source/boundary evidence, not misclassify users/roles/site membership as paid-membership semantics.

WordPress provides users, roles/capabilities, registration/session/password primitives and Multisite site membership. It does not natively provide membership plans, billing intervals, trials, subscription state, dripping, discounts, checkout, transaction reconciliation or an entitlement-plan lifecycle.

## Current native sources

- Users — https://developer.wordpress.org/plugins/users/
- Roles & Capabilities — https://developer.wordpress.org/plugins/users/roles-and-capabilities/
- `current_user_can()` — https://developer.wordpress.org/reference/functions/current_user_can/
- `user_register` — https://developer.wordpress.org/reference/hooks/user_register/
- `is_user_member_of_blog()` — https://developer.wordpress.org/reference/functions/is_user_member_of_blog/
- `add_user_to_blog()` — https://developer.wordpress.org/reference/functions/add_user_to_blog/
- accepted planning evidence: `docs/PRODUCT/MEMBERSHIP-NATIVE-MARKET-EVIDENCE-MATRIX-V1.md`

Native role/capability or Multisite site-membership truth is an **input/reference**. Surface 15 must not convert those primitives into a false core membership-product claim.

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `membership.plan.identity` | **WPE/MARKET-ONLY** | no native claim |
| `membership.plan.visibility` | **WPE/MARKET-ONLY** | consume Policy for viewing; no core plan visibility primitive |
| `membership.pricing.base` | **MARKET/PROVIDER-ONLY** | no WordPress core billing claim |
| `membership.pricing.trial-fee-tax` | **MARKET/PROVIDER-ONLY** | provider/tax owners remain external |
| `membership.state.lifecycle` | **WPE/MARKET-ONLY** | keep membership state distinct from user/account/payment state |
| `membership.state.role-capability` | **NATIVE INPUT + OWNERSHIP REFERENCE** | roles/caps are native but owned by Surface 30/Policy; Membership may map refs only |
| `membership.change.policy` | **WPE/MARKET-ONLY** | no native upgrade/downgrade lifecycle |
| `membership.change.provider` | **PROVIDER-ONLY** | no native payment-provider lifecycle |
| `membership.restriction.rules` | **WPE ENTITLEMENT + NATIVE CAPABILITY SUBSTRATE** | final authorization server-side; hiding content is insufficient |
| `membership.restriction.drip` | **WPE/MARKET-ONLY** | no native drip engine |
| `membership.discount.policy` | **MARKET/COMMERCE-ONLY** | no native coupon/membership benefit engine |
| `membership.checkout.endpoint` | **WPE/MARKET COMPOSITION** | checkout is not WordPress core Membership semantics |
| `membership.checkout.consent` | **WPE/LEGAL UI** | no native membership consent contract |
| `membership.transaction.reconciliation` | **CROSS-OWNER REFERENCE** | transaction/ledger truth remains external |
| `membership.transaction.idempotency` | **WPE RELIABILITY / CROSS-OWNER** | no native transaction engine; notification/privacy refs remain delegated |

## Required Supervisor Bank integration

1. Populate `snapshot.native_sources` with Users/Roles/Multisite membership sources as **substrate evidence**, not product parity.
2. Add notes to `state.role-capability` and `restriction.rules` that role/capability/site-membership inputs are read-only references to canonical owners.
3. Keep every plan/pricing/state/change/drip/discount/checkout/transaction family non-native.
4. Do **not** add a `native membership plan` record merely because WordPress has users or roles.

## Native completeness / unresolved items

No current WordPress core Membership-owned option family is missing from this V1 Bank because core has no membership-plan/subscription engine. Native site membership and roles/capabilities are fully accounted for as external identity/authorization inputs.

Provider billing/refunds/tax, enrollment runtime, role mutation, entitlement execution and checkout remain later market/provider/runtime gates.

## Gate boundary

Worker conclusion: **native evidence complete; boundary metadata integration pending**.  
Not promoted here: `NATIVE_AUDITED`, market review, `BANK_REVIEWED`, payment/provider runtime, Atomic Option Contract, UX or product parity.