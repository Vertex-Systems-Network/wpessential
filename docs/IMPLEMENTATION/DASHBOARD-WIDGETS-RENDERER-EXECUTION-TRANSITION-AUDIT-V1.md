# Dashboard Widgets — Renderer-Execution Transition Audit V1

Surface: **10 / Dashboard Widgets**  
Issue: **#1175**  
Exact audited main: `e3c7b9dbf6f6465a4487aeaf309651923524c771`

## Purpose

Reconcile the merged trusted Component Blueprint registrar/renderer milestone and determine whether Dashboard Widgets runtime renderer invocation can be promoted safely.

## #1170 / #1174 terminal evidence

Exact implementation head: `b32939b5665d98be21fafd892cb5419e6b7f5c8d`

- Governance Gate `35672066125` — PASS
- Architecture Guards `35672066118` — PASS
- PHP Quality Toolchain `35672066106` — PASS
- Distributable Package `35672066117` — PASS
- Platform Compatibility Matrix `35672066124` — PASS
- zero unresolved review threads
- zero commits behind main
- exactly eight Issue #1170-authorized source/test files
- merged as `e3c7b9dbf6f6465a4487aeaf309651923524c771`
- Issue #1170 closed completed

The merged source registers:

- seven canonical revision-1 Surface 10 Component Blueprints;
- one bounded module-local renderer under all seven canonical component types;
- escaped authored output;
- bounded chart/link behavior;
- zero V1 asset handles;
- no Dashboard runtime renderer invocation and no WordPress Dashboard hooks.

## Exact-main trust-class audit

The runtime path is still not safe to invoke.

`DashboardWidgetContentClassCompiler` validates `widget.type` independently.

`DashboardWidgetRenderSourceCompiler` validates:

- Surface 10 ownership;
- exact registered Blueprint id/revision;
- exact Blueprint binding keys and types;
- literal-only bindings.

It does **not** require the selected Blueprint to be the canonical Blueprint for the Definition's declared trusted content class.

`DashboardWidgetRegistrationCompiler` invokes both compilers, but does not compare the resulting content-class and render-source descriptors.

The merged `DashboardWidgetComponentBlueprintCatalog` now provides the missing canonical mapping, but current compiler wiring does not consume that mapping for consistency enforcement.

## Reproducible semantic mismatch

A Definition may declare:

`widget.type = rich_text`

while referencing the canonical KPI Blueprint and supplying valid KPI bindings.

Both values are individually trusted. The current compilers can accept them independently, so the effective renderer trust class can differ from the declared content class.

This is a trust/semantic integrity gap even though both individual classes are currently bounded.

## Why renderer invocation stays blocked

ADR-0051 requires:

`Definition → compiled descriptor → server visibility Policy → trusted content renderer → WordPress Dashboard adapter`

The trusted content class must therefore determine the permitted renderer class, not merely coexist with an independently selected trusted Blueprint.

Promoting runtime renderer invocation before this coupling is enforced would allow the Definition to change effective rendering class without failing its declared content-class contract.

## Verdicts

- **READY_FOR_CONTENT_CLASS_BLUEPRINT_CONSISTENCY_GATE_V1**
- **BLOCKED_FOR_DASHBOARD_RUNTIME_RENDERER_INVOCATION**
- **BLOCKED_FOR_DIRECT_WORDPRESS_DASHBOARD_REGISTRATION**
- **BLOCKED_FOR_WP_ADD_DASHBOARD_WIDGET**
- **BLOCKED_FOR_PROVIDER_QUERY_SOURCE_EXECUTION**
- **BLOCKED_FOR_REMOTE_IFRAME_SHORTCODE_BLOCK_ACTION_EXECUTION**
- **BLOCKED_FOR_ASSET_REGISTRATION**
- **BLOCKED_FOR_MUTATION_PREFERENCES**
- **BLOCKED_FOR_FULL_PARITY_RUNTIME_PRODUCT_CERTIFICATION**

## Authorized next tranche

Issue **#1176 — Dashboard Widgets: content-class ↔ canonical Blueprint consistency gate V1**.

The bounded source tranche must:

1. compile the trusted content class;
2. resolve its canonical revision-1 Blueprint from `DashboardWidgetComponentBlueprintCatalog`;
3. require the authored render-source Blueprint id/revision to equal that canonical pair;
4. retain all current owner/schema/literal binding checks;
5. make registration compilation inherit the same fail-closed behavior;
6. add all-seven-pair and cross-class mismatch tests.

It may not invoke a renderer or add Dashboard hooks.

## Open-Issue reconciliation

The only pre-existing open repository Issues after #1170 closure are:

- #858 — broader required-CI ruleset enforcement; repository-admin work;
- #1102 — P-006 Wave 1U formal execution; explicit owner authorization gate remains unsatisfied;
- #947 — independent Worker-only Wave 1A evidence audit; Supervisor must preserve independence.

None is a safe substitute for the Surface 10 consistency gate and none is silently closed or widened by this audit.

## Promotion condition

Issue #1176 remains dependency-gated until the #1175 audit PR merges with terminal green Governance/Architecture evidence, zero unresolved review threads and zero commits behind main.
