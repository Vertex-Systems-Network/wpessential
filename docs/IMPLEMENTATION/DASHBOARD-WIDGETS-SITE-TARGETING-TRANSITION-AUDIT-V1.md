# Dashboard Widgets — Site Targeting Transition Audit V1

Surface: **10 / Dashboard Widgets**
Issue: **#1193**
Exact audited main: **179bff897b9d64a4297855e375d1c679a3550151**
Status: **READY_FOR_BOUNDED_SITE_TARGETING_CONTRACT_V1**

## Verdict

The bounded WordPress Dashboard adapter is merged and safe for its current site-vs-network split, but exact-main does not implement the complete canonical P0 site-targeting family.

The next safe tranche is a contract-only Site Targeting V1 gate. Direct runtime implementation remains blocked until payload/default/validation/current-site/collision-order semantics are frozen.

## Canonical evidence

The reviewed Options Bank marks these P0 records:
- `dashboard-widgets.target.scope`
- `dashboard-widgets.target.site_ids`
- `dashboard-widgets.target.network_dashboard`

All three project to the existing atomic owner `dashboard-widgets.targeting.policy`.

## Exact-main findings

Current source implements `network_dashboard`, deterministic registration planning, site/network hook isolation, collision suppression and fail-closed execution.

Current source does not compile or carry `target.scope` or `target.site_ids`. The WordPress adapter therefore has no current-site eligibility phase.

Strict unknown-key compilation rejects unsupported authored targeting rather than widening it silently.

## Security-critical ordering

Current-site eligibility must be resolved before same-site WordPress-id collision grouping. Definitions that target disjoint sites must not suppress each other on a site where only one is eligible.

The network Dashboard remains an independent collision domain.

## Contract decisions still required

Issue #1195 must freeze:
- exact finite scope values;
- backwards-compatible behavior when targeting is absent;
- positive/unique/bounded site-id validation and normalization;
- site/network mutual-exclusion semantics;
- malformed target fail-closed behavior;
- current-site eligibility ordering;
- exact later source/test scope.

No providers/sources, remote content, assets, controls/settings, mutation/preferences, cache/refresh, shared Platform changes, certification, deploy or release are authorized.

## Verdict

**READY_FOR_BOUNDED_SITE_TARGETING_CONTRACT_V1**

Direct site-targeting runtime implementation remains blocked until #1195 contract merges.
