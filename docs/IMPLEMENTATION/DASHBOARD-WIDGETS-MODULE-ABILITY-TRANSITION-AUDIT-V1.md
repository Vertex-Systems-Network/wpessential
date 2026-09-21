# Dashboard Widgets — Exact-Main Module/Ability Exposure Transition Audit V1

Surface: **10 / Dashboard Widgets**
Issue: **#1125**
Exact audited main: `cd03b5002e1562e7f04d8b37018eea8638dd4a24`
Decision: **READY_FOR_BOUNDED_READ_ONLY_MODULE_ABILITY_EXPOSURE_V1**

## Purpose

Resolve the next Surface 10 transition after the bounded read-only Runtime Foundation V1 merged and its shared-truth closeout landed.

This audit is evidence/coordination only. It authorizes exactly one later bounded source tranche, Issue #1126, after this audit is promoted through protected PR flow.

## Exact current evidence

1. Issue #1120 / PR #1122 merged `DashboardWidgetDefinition` and `DashboardWidgetsReadService` from exact head `143893fcfd9b12828277dc112023e3fb0fa33af3`.
2. Issue #1123 / PR #1124 reconciled queue, Runner Benchmark, compact state and README; current exact main is `cd03b5002e1562e7f04d8b37018eea8638dd4a24`.
3. Surface 10 reads remain canonical DefinitionRepository-backed and fail closed on foreign owner/type.
4. Accepted neighboring Pro surfaces establish the exact next-stage pattern:
   - Admin Menu PR #886 changed only `AdminMenuModule.php`, `AdminMenuReadAbilityHandler.php`, and `AdminMenuModuleTest.php`.
   - Settings PR #887 changed only the corresponding three files.
   - Frontend Dashboard PR #888 changed only the corresponding three files.
5. Central module activation was separately gated later in PR #898 rather than bundled into those exposure tranches.
6. Current `wpessential-pro.php` already enforces local Free/Pro compatibility before premium source autoload/registration and applies entitlement-aware module activation policy. That bootstrap is intentionally outside the next tranche.

## Readiness decision

Current main is ready for the smallest reversible next Surface 10 tranche:

**Dashboard Widgets read-only Module/Ability Exposure V1**

The tranche may add only:

- `DashboardWidgetsModule`;
- `DashboardWidgetsReadAbilityHandler`;
- focused module/handler unit evidence.

The module contract must:

- register the existing `DashboardWidgetsReadService` through the service registry;
- depend on the canonical shared Definition Repository, Ability Registry and WordPress Ability bridge;
- expose only:
  - `wpessential/dashboard-widgets/get`
  - `wpessential/dashboard-widgets/catalog`
- use Surface owner id `10`;
- require `manage_options`;
- declare `mutates:false`;
- permit only Internal, UI and REST execution channels;
- expose through the existing WordPress Ability bridge;
- use a Pro-owned module manifest.

## Why central activation is excluded

The accepted Surfaces 11–13 precedent separates source exposure from central bootstrap activation. Surface 10 must preserve the same rollback and review boundary.

Issue #1126 must therefore **not** edit `wpessential-pro.php` and must not register `DashboardWidgetsModule` into the active Pro module class list.

A later exact-main transition audit may decide whether central activation is safe after #1126 is merged and independently verified.

## Security and ownership boundaries

The tranche must remain read-only and fail closed.

It must not:

- register WordPress dashboard widgets;
- call `wp_dashboard_setup` or `wp_add_dashboard_widget`;
- create/update/delete Definitions;
- mutate user preferences;
- execute Query/Listings/Analytics/Forms/providers;
- add cache/refresh/remote engines;
- add AJAX mutation routes;
- accept arbitrary PHP/callback/class/script/SQL input;
- mutate shared Platform contracts;
- bypass policy/capability checks;
- claim full-parity `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`;
- deploy or release.

Canonical peer ownership remains unchanged: Query → Surface 6, Listings → Surface 9, Analytics → Surface 33, Roles → Surface 30, Forms → Surface 17, business actions → their canonical Ability owners.

## Next deterministic source slot

Issue: **#1126 — Dashboard Widgets: bounded read-only Module/Ability Exposure V1**
Branch: `agent/dashboard-widgets-read-module-ability-exposure-v1`

Claim requirements after this audit PR merges:

1. resolve fresh exact main;
2. confirm #1126 remains OPEN and no accepted PR already implements it;
3. create the deterministic branch from exact main;
4. change only the two Dashboard Widgets source files and focused module test authorized by #1126;
5. do not edit `wpessential-pro.php`;
6. run focused verification plus all path-applicable exact-head merge gates;
7. no force push or branch reuse.

## Certification boundary

A PASS for #1126 will certify only bounded read-only Module/Ability exposure source. It will **not** certify central activation, WordPress dashboard integration, mutation/provider execution, full Surface 10 runtime, product parity, deployment or release.
