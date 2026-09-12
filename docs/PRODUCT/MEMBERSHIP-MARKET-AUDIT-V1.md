# Membership — Market Audit V1

Status: `RESEARCH_COMPLETE / SUPERVISOR_INTEGRATION_PENDING`

Surface: 15 — Membership  
Snapshot: 2026-09-12  
Parent: #713  
Worker issue: #718  
Base: `54e4e70095f74a6ebff15da83cc2bbf3d447a49c`

## 1. Scope and boundary

This evidence-only audit reconciles all 15 `NATIVE_AUDITED` Membership records. WordPress users/roles/capabilities remain identity/authorization substrate only. Membership owns plan/entitlement semantics; Roles/Policy owns grants, and payment providers own charge/settlement execution.

## 2. Current market evidence

### E1 — MemberPress
Official evidence:
- https://memberpress.com/docs/memberships-overview/
- https://memberpress.com/docs/groups/
- https://memberpress.com/docs/creating-coupons/

Verified: recurring/non-recurring memberships, billing intervals, trials, groups/pricing pages, upgrade/downgrade paths, coupons and membership targeting.

### E2 — WooCommerce Memberships
Official evidence:
- https://woocommerce.com/document/woocommerce-memberships-plans/
- https://woocommerce.com/document/woocommerce-memberships-restrict-content/

Verified: membership plans, content/product restriction rules, delayed/drip access and member discounts integrated with WooCommerce commerce truth.

### E3 — Paid Memberships Pro
Official evidence:
- https://www.paidmembershipspro.com/documentation/content-controls/

Verified: level-based content restriction and membership access rules across WordPress content.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `membership.plan.identity` | MARKET_EVIDENCED — E1/E2 expose named membership plans/products. |
| `membership.plan.visibility` | MARKET_EVIDENCED — public/available plan and pricing presentation is common. |
| `membership.pricing.base` | MARKET_EVIDENCED — recurring/non-recurring amount, currency/provider billing interval. |
| `membership.pricing.trial-fee-tax` | MARKET_EVIDENCED_WITH_PROVIDER_BOUNDARY — trials/sign-up pricing are common; tax calculation remains commerce/provider-owned. |
| `membership.state.lifecycle` | MARKET_EVIDENCED / WPE_HARDENED — active/expired/cancelled access states exist; WPE keeps membership state distinct from payment/user state. |
| `membership.state.role-capability` | MARKET_EVIDENCED_WITH_ROLES_OWNER — integrations may map roles; canonical role/capability truth stays Surface 30/Policy-owned. |
| `membership.change.policy` | MARKET_EVIDENCED — E1 groups support upgrade/downgrade paths; scheduled/proration details remain explicit policy. |
| `membership.change.provider` | PROVIDER_EVIDENCED — billing changes require payment-provider adapter and must not be inferred from local state. |
| `membership.restriction.rules` | MARKET_EVIDENCED — E2/E3 prove content/resource restriction rules. |
| `membership.restriction.drip` | MARKET_EVIDENCED — E2 supports delayed/drip access; denied behavior remains explicit WPE policy. |
| `membership.discount.policy` | MARKET_EVIDENCED — E1 coupons and E2 member discounts prove family. |
| `membership.checkout.endpoint` | MARKET_EVIDENCED_WITH_DASHBOARD_OWNER — checkout/account flows are standard; shell composition may be delegated. |
| `membership.checkout.consent` | KEEP_WPE_HARD — consent is a compliance/policy contract and must not be inferred from generic checkout fields. |
| `membership.transaction.reconciliation` | KEEP_WPE_HARD / PROVIDER_REFERENCED — local membership state must reconcile to canonical payment transaction/provider evidence. |
| `membership.transaction.idempotency` | KEEP_WPE_EXCEED — webhook/payment idempotency, duplicate suppression, privacy and notification diagnostics require explicit guarantees. |

Coverage: **15 / 15**. Unresolved research dispositions: **0**. Worker Bank/progress mutations: **0**.

## 4. Safety and ownership conclusions

- A membership level is not a WordPress role; any role mapping is a reviewed reference and never silent privilege mutation.
- Provider payment status is not trusted from client/UI state; reconciliation and idempotency remain server/provider contracts.
- Restriction is server-authoritative. Hiding navigation/content teasers alone is not entitlement enforcement.
- Tax, refunds, charge state and settlement remain provider/commerce-owner truth.

## 5. Supervisor integration requirements

A later Supervisor may add E1–E3 as market sources and classify the evidenced plan/pricing/change/restriction/discount families while preserving provider, Roles/Policy and transaction boundaries. Promote `MARKET_AUDITED` only after exact-head Bank reconciliation and CI.

No payment execution, role mutation, `BANK_REVIEWED`, runtime/product certification, deployment or release is authorized.