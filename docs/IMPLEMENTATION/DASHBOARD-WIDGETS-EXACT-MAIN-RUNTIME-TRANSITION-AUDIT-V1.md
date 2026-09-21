# Dashboard Widgets — Exact-Main Runtime Transition Audit V1

Surface: **10 / Dashboard Widgets**
Issue: **#1119**
Exact audited main: `b93fb27e13dba9ae4e2db8c749cc1ddfad44acd9`
Decision: **READY_FOR_BOUNDED_READ_ONLY_RUNTIME_FOUNDATION_V1**

## Purpose

Resolve the explicit current README next gate for Surface 10 without treating stale planning prose as implementation authority.

This audit is evidence/coordination only. It authorizes exactly one later bounded source tranche, Issue #1120, after this audit is promoted through protected PR flow.

## Exact current evidence

1. Surface 10 is `UX_CONTRACT_COMPLETE` in `config/product/atomic-option-contract-progress.json`.
2. The accepted Dashboard Widgets machine contract projects all **123** reviewed Options Bank records into **18** normalized Atomic Options with `missing=0` and `unclassified=0`.
3. `docs/UI/DASHBOARD-WIDGETS-UX-CONTRACT-V1.md` defines the accepted Essential/Advanced/Expert UX, server-authoritative validation, visibility, accessibility, Multisite, provider and no-arbitrary-executable boundaries.
4. `docs/IMPLEMENTATION/DASHBOARD-WIDGETS-RUNTIME-GAP-MATRIX-V1.md` correctly records that Surface 10 had no canonical runtime implementation baseline.
5. Fresh exact-main inspection still finds **no dedicated `frameworks/Modules/DashboardWidgets` runtime owner**.
6. Current accepted neighboring Pro modules such as Admin Menu, Settings Pages and Frontend Dashboard already establish a reusable fail-closed pattern:
   - Surface-owned Definition type/owner validation;
   - canonical shared `DefinitionRepositoryInterface`;
   - deterministic read projection and catalog ordering;
   - later, separately bounded read-only Module/Ability exposure.
7. The Phase 2 bounded runtime chain (Fields → Relations → Query → Admin Columns → Dynamic Listings → Status) is already promoted, so Surface 10 does not need to invent a private Query, Listing, Policy, Ability or storage engine.
8. The repository-wide owner consent `GOV-OWNER-CONSENT-001` permits milestone-gated source implementation, while destructive/live-provider/deploy/release and separately privileged operations remain independently gated.

## Readiness decision

The smallest reversible Surface 10 runtime tranche is dependency-ready:

**Dashboard Widgets read-only Runtime Foundation V1**

It may implement only:

- `DashboardWidgetDefinition` with Surface 10 owner/type validation;
- deterministic read projection of canonical Definition fields;
- `DashboardWidgetsReadService` backed only by the shared `DefinitionRepositoryInterface`;
- `get(id)` and deterministic `catalog()` reads;
- focused unit evidence;
- one implementation evidence document.

This tranche deliberately stops **before** Module/Ability/bootstrap exposure. That later exposure can be reviewed after the owner foundation is promoted.

## Why this tranche is safe

The tranche is read-only and composes already-promoted shared primitives. It does not require:

- WordPress dashboard widget registration;
- new persistence or migration;
- provider/network execution;
- Query/Listings/Analytics execution;
- user preference mutation;
- cache/job engines;
- remote credentials;
- destructive operations;
- deployment or release authority.

Failure behavior is naturally fail-closed: missing Definitions return no result, foreign owner/type Definitions are rejected, and no side effect is available.

## Canonical ownership boundaries

Surface 10 must reference, not absorb:

- Query execution → Surface 6 Query;
- Listing rendering → Surface 9 Dynamic Listings;
- analytics/journey semantics → Surface 33 Analytics;
- role/capability grants → Surface 30 Roles & Capabilities;
- Forms runtime → Surface 17 Forms & Workflows;
- business actions → the canonical target Ability owner;
- shared Definition/Policy/Ability/Multisite/diagnostics services → Platform.

No arbitrary PHP, callback, class, script, SQL, unchecked identifier or provider implementation may become authored Dashboard Widgets state.

## Explicitly deferred

Issue #1120 must **not** include:

- `DashboardWidgetsModule` or Ability registration;
- `wp_dashboard_setup` / `wp_add_dashboard_widget` registration;
- admin UI;
- Definition writes/status mutation;
- per-user reorder/hide/dismiss/reset preferences;
- refresh/cache/retry runtime;
- Query/Listings/Analytics provider execution;
- actions, AJAX or REST mutation endpoints;
- import/export execution;
- provider/remote fetch;
- full-parity `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` promotion;
- deploy/release.

## Next deterministic source slot

Issue: **#1120 — Dashboard Widgets: bounded read-only Runtime Foundation V1**
Branch: `agent/dashboard-widgets-read-runtime-foundation-v1`

Claim requirements after this audit PR merges:

1. resolve fresh exact main;
2. confirm #1120 remains OPEN and no accepted PR already implements it;
3. create the deterministic branch from exact main;
4. change only the bounded Surface 10/test/evidence paths needed by #1120;
5. run focused verification plus all path-applicable exact-head merge gates;
6. no force push or branch reuse.

## Certification boundary

A PASS for #1120 will certify only the bounded read-only owner foundation. It will not certify WordPress dashboard integration, Module/Ability exposure, mutation, providers, user preferences, performance, full Surface 10 runtime, product parity, deployment or release.
