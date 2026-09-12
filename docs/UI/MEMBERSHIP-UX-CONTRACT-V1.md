# Membership — UX Contract V1

Surface: **15 / Membership**  
Machine source: `config/product/option-contracts/membership.json`  
Lifecycle: **UX certification candidate**. Machine truth is `OPTION_CONTRACT_COMPLETE`; this document does not promote runtime or product certification.

## Lifecycle preconditions

- Machine status must remain `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 15 current Atomic Option IDs are mapped exactly once below.
- Pricing, lifecycle, access and provider state remain server-authoritative; UI/local state cannot manufacture entitlement or settlement truth.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Identity & Access → Membership**.

The IA separates **Plans**, **Pricing**, **Lifecycle**, **Change Policy**, **Restrictions**, **Discounts**, **Checkout**, and **Transactions & Reliability**. Authored membership policy is visually distinct from provider/payment state and Roles/Policy references.

## UX state classes

### Authored definition
Plan identity/visibility, pricing base, lifecycle policy, change rules, restrictions/drip, discounts, checkout endpoint/consent are revisioned Membership-owned definitions.

### Effective/runtime state
Current member entitlement/lifecycle, effective restriction and checkout eligibility are derived states and must not be edited as raw stored truth.

### Diagnostic/provider state
Tax/trial/fee provider data, role/capability mapping, payment/change provider state and transaction reconciliation are canonical-owner/provider references with explicit health/degraded state.

### Deferred / prohibited state
UI cannot claim payment settlement, grant roles, or infer entitlement from local browser state. Provider failures are surfaced, never converted to successful local state.

## Atomic Option → UX map

- `membership.plan.identity` — Plans → plan identity/name/key/lifecycle definition.
- `membership.plan.visibility` — Plans → presentation visibility/availability policy; not authorization.
- `membership.pricing.base` — Pricing → base amount/currency/period configuration with deterministic validation.
- `membership.pricing.trial-fee-tax` — Pricing → Expert payment/tax/trial/fee provider reference and health state.
- `membership.state.lifecycle` — Lifecycle → authored transition policy and effective lifecycle preview.
- `membership.state.role-capability` — Lifecycle → Roles/Policy-owned role/capability reference; Membership cannot grant capabilities directly.
- `membership.change.policy` — Change Policy → upgrade/downgrade/cancel/pause rules and effective consequences preview.
- `membership.change.provider` — Change Policy → provider-specific change/settlement reference with degraded state.
- `membership.restriction.rules` — Restrictions → content/resource restriction rule builder with owner-aware references.
- `membership.restriction.drip` — Restrictions → drip/delay scheduling policy with timezone and dependency disclosure.
- `membership.discount.policy` — Discounts → coupon/discount eligibility and conflict policy.
- `membership.checkout.endpoint` — Checkout → registered checkout/return endpoint definition and route validation.
- `membership.checkout.consent` — Checkout → Expert consent/legal acceptance requirements and evidence policy.
- `membership.transaction.reconciliation` — Transactions & Reliability → read-only/provider reconciliation state and mismatch diagnostics.
- `membership.transaction.idempotency` — Transactions & Reliability → idempotency/correlation/replay-protection policy and diagnostic state.

## Interaction and persistence

Plan/policy editing follows draft → validate → save revision. Provider-backed fields validate provider availability but never persist secret/payment settlement state. High-impact lifecycle/change edits show affected entitlements and dependencies before save. Transaction diagnostics are read-only unless a separately authorized provider operation exists.

## Loading, empty, validation, conflict and recovery

Required states include no plans, loading provider, invalid pricing, missing role/policy owner, missing checkout endpoint, provider unavailable, tax/payment unknown, transaction mismatch, stale revision, saved, retryable validation failure and recovery. Unknown settlement is shown as unknown, not success or failure guessed from stale UI.

## Security and ownership

Roles/Policy owns role/capability grants. Payment providers own authorization, charge, refund and settlement truth. Membership owns plan/restriction/entitlement policy, not external payment execution. Local UI state never creates entitlement or settlement truth. Consent evidence and member-affecting changes are server-authoritative and auditable.

## Accessibility

Pricing, plan and restriction forms have programmatic labels, keyboard navigation, clear units/currency, visible focus and linked errors. Provider status uses text plus icon rather than color alone. Confirmation dialogs for member-affecting changes describe consequences and return focus predictably.

## Multisite and scope

Plan, entitlement and provider scope are explicit per site/network. Network/global scope is server-derived and cannot be escalated by import or client payload. Cross-site role/content references require explicit remapping.

## Portability and reference remapping

Exports are definition-only and secret-free; payment credentials, transactions and settlement evidence are excluded. Imports validate Roles/Policy, content, checkout and provider references before commit and surface unresolved mappings explicitly.

## Performance and scale

Large plan/restriction sets use bounded filtering and lazy detail panels. Effective-access previews avoid unbounded per-user scans. Transaction diagnostics paginate and never block policy editing on a full history load.

## Degraded/provider states

Missing payment/tax, Roles/Policy or checkout providers show explicit unavailable/degraded states. The surface never fabricates a charge, refund, settlement, role grant or entitlement result. Recovery points to the canonical owner/provider.

## UX lifecycle exit criteria

Certification requires complete 15-ID mapping, zero missing/unclassified machine semantics, reviewed payment/role ownership, accessible high-impact flows, portability/performance/degraded states and exact-head validator CI. Shared lifecycle promotion remains Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No payment, refund, role/user mutation, provider execution or external settlement action is authorized by this UX contract.
