# Dashboard Widgets — Site Targeting Source Transition Audit V1

Status: **READY_FOR_BOUNDED_SITE_TARGETING_SOURCE_V1**
Surface: **10 — Dashboard Widgets**
Audit Issue: **#1202**
Dependency-gated Source Issue: **#1203**
Exact audited main: `2669449a23ba4fc965045b8f9e419b31544bd53c`

## Terminal prerequisite reconciliation

- Issue #1195 / PR #1200 merged the Bounded Site Targeting Contract V1 as `c525008c5c203863998a30d52201bee3905c0c4f`.
- PR #1200 exact head: `bd2ccc8f6f309a32608886bf1dd57ec718be8a59`.
- Governance Gate `35872950066`: PASS.
- Architecture Guards `35872950059`: PASS.
- Issue #1195 is closed completed.
- PR #1201 subsequently merged only the organization next-action handoff contract and did not widen product/runtime authority.

## Exact-main findings

### Registration compiler

`DashboardWidgetRegistrationCompiler` currently accepts the existing widget key set only. It has no `target.scope` or `target.site_ids` normalization and therefore still rejects authored targeting additions through strict known-key validation.

### Registration descriptor

`DashboardWidgetRegistrationDescriptor` currently carries the existing `networkDashboard` boolean but has no normalized site-targeting fields/accessors.

### WordPress adapter

`DashboardWidgetWordPressAdapter`:
- compiles definitions;
- separates site/network descriptors;
- groups by canonical WordPress widget id;
- suppresses same-target collisions;
- performs native registration after planning.

It does not yet evaluate current-site eligibility before site collision grouping.

### Environment seam

`DashboardWidgetWordPressEnvironmentInterface::currentSiteId(): int` already exists and the native implementation delegates to `get_current_blog_id()`.

Therefore no environment interface/native-environment mutation is needed for the bounded V1 source tranche. Invalid/non-positive/throwing site evidence can be handled fail-closed by the adapter around the existing seam.

## Exact later source/test allowlist

Issue #1203 is dependency-gated to exactly:

1. `frameworks/Modules/DashboardWidgets/DashboardWidgetRegistrationCompiler.php`
2. `frameworks/Modules/DashboardWidgets/DashboardWidgetRegistrationDescriptor.php`
3. `frameworks/Modules/DashboardWidgets/DashboardWidgetWordPressAdapter.php`
4. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRegistrationCompilerTest.php`
5. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetWordPressAdapterTest.php`

The environment interface/native environment and shared Platform are outside the write scope.

## Required source semantics

The bounded source tranche must implement only the merged #1195 contract:
- optional `scope = all_sites|site_ids`;
- positive integer, unique, ascending, max-100 `site_ids`;
- target-absent backwards-compatible current-site eligibility;
- existing network behavior with site/network mutual exclusion;
- fail-closed malformed combinations;
- current-site eligibility before site collision grouping;
- independent network collision domain;
- zero site registration side effects when current-site evidence is invalid or throws.

## Security boundary

No provider/query/source execution, remote/Safe HTTP/iframe execution, shortcode/block/action execution, assets, Definition/user-preference mutation, caching/refresh, shared Platform mutation, P-006 runtime execution, certification, deployment or release is authorized.

## Verdict

`READY_FOR_BOUNDED_SITE_TARGETING_SOURCE_V1`

This verdict activates only the dependency gate for Issue #1203 after this audit PR itself merges with exact-head Governance Gate and Architecture Guards green, zero unresolved review threads and zero commits behind main.
