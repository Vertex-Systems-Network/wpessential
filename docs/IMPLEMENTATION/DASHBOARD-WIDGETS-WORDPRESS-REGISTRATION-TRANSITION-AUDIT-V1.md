# Dashboard Widgets — Exact-Main WordPress Dashboard Registration Transition Audit V1

Surface: **10 / Dashboard Widgets**
Issue: **#1137**
Exact audited main: `d3817ee2ddc10a4c625f2b0e47ddf1d9b90cdf46`

## Verdict

**BLOCKED_FOR_DIRECT_WORDPRESS_DASHBOARD_REGISTRATION**

**READY_FOR_REGISTRATION_DESCRIPTOR_FOUNDATION_V1**

## Exact-main product state

Surface 10 already has:
- fail-closed `dashboard-widget` Definition ownership/type validation;
- shared DefinitionRepository-backed read service;
- Pro-owned read-only `get`/`catalog` Ability exposure;
- central Pro contribution through `wpessential-pro.php`;
- package evidence proving compatible activation and fail-closed incompatible/Free-only paths.

## Why direct WordPress registration remains blocked

The accepted security/runtime chain is:

`Dashboard Widget Definition → Compiled Widget Descriptor → server visibility Policy → trusted content renderer → WordPress Dashboard adapter`

Current main has the first element and read access, but not the remaining prerequisites required to safely call WordPress Dashboard registration APIs.

Missing before any direct `wp_add_dashboard_widget()` tranche:
- compiled/typed registration descriptor;
- strict runtime validation of native key/title/context/priority metadata;
- registered/allowlisted render-provider contract;
- server-evaluated widget visibility policy;
- trusted content renderer;
- WordPress Dashboard adapter/hook environment;
- admin-XSS and runtime integration evidence.

Calling WordPress hooks now would expose raw Definition payload to a privileged wp-admin origin before the trust boundary exists.

## Native metadata already accepted by product truth

The reviewed native contract maps:
- `widget.key` → native widget ID;
- `widget.title` → native widget name;
- context allowlist → `normal|side|column3|column4`;
- priority allowlist → `high|core|default|low`;
- network-dashboard target → typed registration target metadata;
- callback/control callback → registered provider mapping only, never arbitrary PHP text.

## Authorized prerequisite tranche

Issue: **#1138 — Dashboard Widgets: bounded registration descriptor/compiler foundation V1**
Branch: `agent/dashboard-widgets-registration-descriptor-foundation-v1`

Allowed files:
1. `frameworks/Modules/DashboardWidgets/DashboardWidgetRegistrationDescriptor.php`
2. `frameworks/Modules/DashboardWidgets/DashboardWidgetRegistrationCompiler.php`
3. `frameworks/Modules/DashboardWidgets/DashboardWidgetsModule.php`
4. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetsModuleTest.php`
5. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRegistrationCompilerTest.php`

Required behavior:
- compile only owned Surface 10 `dashboard-widget` Definitions;
- admit only published definitions;
- require non-empty canonical key/title;
- fail closed on unknown context/priority;
- carry network-dashboard target as typed metadata only;
- register the compiler as a module-local service;
- no WordPress hook, provider or renderer execution.

## Forbidden scope

The #1138 tranche must not:
- call `wp_dashboard_setup`, `wp_network_dashboard_setup`, `wp_add_dashboard_widget`;
- resolve or execute render/control providers;
- render raw HTML, shortcodes, blocks, iframe or remote content;
- mutate Definitions or user preferences;
- implement visibility authorization;
- change shared Platform, entitlement or compatibility semantics;
- claim `RUNTIME_CERTIFIED` / product parity;
- deploy or release.

## Subsequent gate

After #1138 merges and shared truth is reconciled, a fresh exact-main audit must decide the next prerequisite. Direct WordPress registration is not automatically authorized by this audit.
