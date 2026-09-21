# Dashboard Widgets — Exact-Main Central Pro Activation Transition Audit V1

Surface: **10 / Dashboard Widgets**  
Issue: **#1131**  
Exact audited main: `bce056cf837f7f34bdff4f876446d9714359f077`  
Decision: **READY_FOR_BOUNDED_CENTRAL_PRO_ACTIVATION_V1**

## Current product state

Surface 10 already has:
- fail-closed `dashboard-widget` Definition ownership validation;
- shared DefinitionRepository-backed read service;
- Pro-owned `DashboardWidgetsModule`;
- read-only `get` and `catalog` Ability exposure;
- `manage_options`, `mutates:false`, Internal/UI/REST descriptors.

The only intentionally missing step in this transition is central contribution from `wpessential-pro.php`.

## Exact-main activation safety

Current Pro bootstrap remains fail-closed before any premium contribution:

1. local Free/Pro compatibility preflight must resolve `compatible`;
2. required Free Platform API classes must resolve;
3. canonical entitlement state is published;
4. `EntitlementAwareModuleActivationPolicy` is installed before module contribution;
5. Pro module classes are package-completeness checked before registration;
6. `Plugin::registerModule()` contributes modules before Free boot.

Entitlement semantics remain suitable for the bounded read-only Dashboard Widgets module:
- `Free` and `IncompatibleVersion` deny premium activation;
- Pro/trial/grace allow premium activation;
- expired/suspended/verification-stale/verification-unavailable permit read-safe premium activation;
- mutation permission remains independently denied in those read-safe degraded states.

No entitlement or compatibility semantic change is required.

## Packaging evidence requirement

Central activation changes package behavior, so source activation alone is insufficient.

Issue #1132 must also update regression evidence so:
- canonical Pro manifest inventory includes `DashboardWidgetsModule`;
- packaged bootstrap verifier includes `dashboard-widgets` in implemented Pro modules;
- Free-only mode proves it is absent;
- incompatible Free/Pro mode proves it is absent;
- compatible Free+Pro mode proves it is registered;
- Pro ZIP integrity explicitly requires `frameworks/Modules/DashboardWidgets/DashboardWidgetsModule.php`.

## Authorized next tranche

Issue: **#1132 — Dashboard Widgets: bounded central Pro Activation V1**  
Branch: `agent/dashboard-widgets-central-pro-activation-v1`

Allowed files only:
1. `wpessential-pro.php`
2. `tests/Unit/Platform/EntitlementPolicyTest.php`
3. `tools/release/verify-free-pro-bootstrap.php`
4. `.github/workflows/distributable-package.yml`

## Forbidden scope

The next tranche must not:
- modify Dashboard Widgets implementation source;
- register WordPress dashboard widgets;
- call `wp_dashboard_setup` or `wp_add_dashboard_widget`;
- add Definition/user-preference mutation;
- execute Query/Listings/Analytics/Form/provider work;
- add cache/remote engines;
- alter entitlement or compatibility semantics;
- mutate shared Platform contracts;
- claim full-parity `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`;
- deploy or release.

## Claim rule

#1132 may be claimed only after this audit PR merges with the READY verdict above.

The activation implementation must begin from fresh exact main, retain zero unrelated changes, pass all path-applicable exact-head CI including distributable-package verification, remain current with main, and have zero unresolved review threads.
