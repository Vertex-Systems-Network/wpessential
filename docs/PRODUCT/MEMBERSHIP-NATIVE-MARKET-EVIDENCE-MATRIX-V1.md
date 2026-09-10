# Membership — Native & Market Evidence Matrix V1

Surface: **15 / Membership**  
Issue: **#498**  
Research anchor: **`main @ 42303a97938f1a80f9c1c9088fa70d40ba359279`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress Users / least privilege | https://developer.wordpress.org/plugins/users/ | user identity, role/capability foundation and least-privilege authorization boundary |
| WordPress Roles & Capabilities | https://developer.wordpress.org/plugins/users/roles-and-capabilities/ | role/capability assignment semantics and Multisite/Super Admin distinction |
| WooCommerce Memberships | https://woocommerce.com/document/woocommerce-memberships/ | membership plans, user memberships, content/product restrictions, dripping, import/export and subscription integration |
| WooCommerce Membership Plans | https://woocommerce.com/document/woocommerce-memberships-plans/ | access grant modes, membership duration, content/product restriction rules, member discounts and members-area behavior |
| MemberPress — Memberships and Groups | https://memberpress.com/docs/memberpress-memberships-and-groups/ | membership products/levels, groups, access structure, recurring/non-recurring subscription distinction |
| Paid Memberships Pro — Membership Levels | https://www.paidmembershipspro.com/documentation/membership-levels/ | levels, level groups, multiple simultaneous levels and member level changes |

## Native/market decisions added in this pass

- WordPress natively supplies users, roles and capabilities but not a complete membership-plan lifecycle. Surface 15 therefore owns membership plan/enrollment/entitlement semantics while still composing canonical user identity and Policy authorization.
- Market evidence distinguishes **plan configuration**, **user membership state**, and **payment/subscription state**. WPE must not collapse those into one mutable record.
- WooCommerce Memberships supports manual, registration and purchase-based access grants plus fixed/unlimited duration and content dripping. These are valid Bank families, but purchase/payment execution remains an external provider concern.
- Content restriction must resolve through canonical Policy/entitlement evaluation. Merely hiding frontend content is not authorization.
- Membership discounts are benefit/entitlement rules; final price/order transaction truth remains with the commerce/payment owner/provider.
- Roles may be an integration target or effective diagnostic, but Membership must not own arbitrary role grants.

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

This matrix does **not** promote `BANK_REVIEWED`. Subscription provider lifecycle, payment/refund/chargeback/tax boundaries, grace and trial states, cancellation/renewal edge cases, entitlement conflict precedence, import/export privacy, multisite and zero-unresolved review remain open. No payment or membership runtime execution is authorized.
