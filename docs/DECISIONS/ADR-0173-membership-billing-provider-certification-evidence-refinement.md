# ADR-0173 — Membership Billing Provider Certification Evidence Refinement

Status: **Accepted evidence protocol / execution pending**  
Date: **2026-08-28**  
Work package: **P0-M00-WP56**

## Context

ADR-0057 established provider-neutral Membership billing semantics and MB0–MB5 certification. ADR-0062 established the initial source-truth profiles for Manual/Free, WooCommerce Core orders, WooCommerce Subscriptions and SureCart. ADR-0066 established exact provider/plugin/API/version/environment-scoped certification and made WooCommerce HPOS a first-class compatibility dimension. ADR-0129 later defined core Membership runtime evidence as MBR-01…MBR-160 while keeping billing-provider and protected-file certification separate.

The existing billing certification contract already had strong source-fact, reconciliation and lifecycle semantics, but it predated the mature shared contracts for Vault, JobService, Event Inbox/Webhook Gateway, local/remote privacy, Error Taxonomy, Contract Versioning, Rate Limit, Multisite, Site Lifecycle and Product entitlement separation. It also lacked one canonical exhaustive executable fixture family.

Current provider research contains four BE3 static profiles. No MB runtime certification has executed.

## Decision

WPEssential accepts the in-place refinement of `docs/ARCHITECTURE/MEMBERSHIP-BILLING-ADAPTER-CERTIFICATION.md`.

### 1. MB0–MB5 remain the certification maturity ladder

The existing meanings are preserved:

- MB0 — Detected / Mapping Configurable
- MB1 — Source Read Certified
- MB2 — Grant Lifecycle Certified
- MB3 — Renewal / Failure / Cancellation Certified
- MB4 — Refund / Change / Reconciliation Certified
- MB5 — Production Billing Profile Certified

The ladder is cumulative. An upper level cannot bypass required lower-level evidence.

### 2. MB-F001…MB-F176 become the canonical executable evidence matrix

The protocol now defines **MB-F001…MB-F176** across sixteen evidence groups covering:

- profile identity/version/static-vs-runtime evidence;
- Vault credentials/provider scope;
- provider customer/user/site identity and source uniqueness;
- Mapping Definition/Plan/product-price semantics;
- initial paid/unpaid/trial/manual source lifecycle;
- webhook/hook authenticity and Event Inbox;
- dedupe/order/races/late events;
- renewal/failure/hold/grace/recovery;
- cancellation/expiry/pause/reactivation;
- refunds/disputes/chargebacks;
- product/price/quantity/seat changes;
- unknown outcomes/reconciliation/Job retries/manual repair;
- Enrollment/Membership Entitlement/Product Entitlement/Role separation;
- privacy/retention/audit/export/external-data boundaries;
- Multisite/clone/restore/module/site lifecycle;
- provider upgrades/HPOS/security/scale/production operations.

Fixture completion supplies evidence to MB certification; fixture IDs are not certification levels.

### 3. BE3 static evidence never becomes MB0

The current source/provider profiles remain:

- Manual / Free — BE3 static evidence;
- WooCommerce Core one-time purchases — BE3;
- WooCommerce Subscriptions — BE3;
- SureCart — BE3.

Current runtime certification remains **0 MB-certified profiles**. Documentation, code presence, plugin detection or current provider-version research cannot produce MB0.

### 4. Commercial facts and access authorities remain distinct

Canonical authority boundary:

`Commercial provider fact ≠ WPE Enrollment ≠ Membership Entitlement ≠ Product Entitlement ≠ WordPress Role`

Provider states/events are normalized source facts. Reconciliation and published Membership policy own Enrollment transitions. Membership Entitlements derive from Membership authority. Product/Pro entitlement remains a separate licensing domain, and provider-managed WordPress Role changes are not Membership truth.

### 5. Unknown source outcomes remain unknown until reconciled

Webhook/hook delivery is freshness, not guaranteed source completeness or ordering. Duplicate, late, missed, conflicting or out-of-order events must be reconciled idempotently. Provider/API outage or ambiguous source state cannot be guessed into paid, active, refunded or terminal Membership state.

JobService remains at-least-once; retries do not imply exactly-once provider or Membership effects.

### 6. Certification is exact-profile scoped

Certification identity includes WPE/adapter/Membership schema, provider/plugin/API/event-schema versions, account/store, live/test environment, Woo HPOS/storage mode where applicable, Vault/Event Inbox/JobService profiles, Mapping revision and Multisite/site scope.

A newer or materially different provider profile is not automatically Supported. Known insecure ranges may be security-blocked. Woo HPOS compatibility requires executable evidence; direct private order-storage assumptions are not accepted as the normal Woo adapter contract.

### 7. Lifecycle, privacy and tenant boundaries are certification gates

Clone/staging/restore must not duplicate live billing processing or access grants. Site/network credentials/source IDs must remain isolated. Local privacy erase must not claim external-provider deletion. Card/CVC/provider credentials must not enter WPE business records, logs, exports or diagnostics.

Module disable, Pro expiry, uninstall, site deletion and privacy erase remain distinct lifecycle operations and do not implicitly cancel/delete external billing.

### 8. Stop-the-line negative requirements are mandatory

Certification stops on cross-tenant/provider-source leakage, secret/payment-data exposure, unauthenticated provider-event acceptance where authenticity is required, uncontrolled duplicate Membership grants/revokes, sandbox→production access leakage, direct provider-status authority shortcuts, Product-entitlement cross-domain mutation, false static→runtime certification or destructive privacy/data corruption.

## Current evidence truth

At acceptance time:

- MB-F fixtures documented: **176**;
- MB-F fixtures executed: **0/176**;
- static billing profiles: **4 BE3**;
- runtime billing profiles certified: **0 MB-certified**;
- MB0/MB1/MB2/MB3/MB4/MB5 certified profiles: **0 each**;
- Membership core MBR remains separately **0/160**;
- protected-file delivery remains separately **0 PC1+**;
- no provider plugin mutation, Woo HPOS mutation, commerce source creation, payment action, webhook/API call, reconciliation, Membership transition, Job execution, migration, build, benchmark or runtime test occurred in WP56.

## Consequences

### Positive

- Provider support claims become reproducible and exact-profile scoped.
- Commercial facts can no longer collapse directly into Membership access authority.
- Duplicate/out-of-order/missed events, retries, HPOS, clone/restore and Multisite failure modes are first-class evidence.
- Static provider research and executable runtime certification remain auditable separate domains.
- Future UI/support can declare exact unsupported/unverified provider capabilities instead of one misleading brand-level Supported badge.

### Costs / open evidence

- All provider runtime certification remains open until explicitly authorized execution occurs.
- Each materially different provider/plugin/API/storage/security profile may need separate evidence.
- Recurring-provider lifecycle support may stop below MB5 if required production-grade evidence is absent.
- Exact version ranges and operational limits remain evidence-derived.

## Consent and execution gate

This ADR accepts documentation/evidence semantics only. It grants **no development or executable-spike authorization**.

Under ADR-0014, `DEVELOPMENT-CONSENT.md` and the Approval Ledger, billing-provider/plugin setup, commerce objects, HPOS mutation, webhooks/API calls, Jobs, Membership transitions, tests, migrations or benchmarks require explicit scoped owner consent.

Current owner authorization remains **0/31**.