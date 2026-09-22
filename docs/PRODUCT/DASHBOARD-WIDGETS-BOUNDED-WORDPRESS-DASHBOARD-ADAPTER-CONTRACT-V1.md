# Dashboard Widgets — Bounded WordPress Dashboard Adapter Contract V1

Status: **implementation-ready contract / direct adapter implementation gated until merge**
Surface: **10 — Dashboard Widgets**
Issue: **#1186**
Exact base: `main@7f448ff01b51f0abfd3551ef431f5cf5675ec5a9`

## 1. Purpose

Define the smallest fail-closed WordPress Dashboard adapter boundary that may connect the already-merged Dashboard Widgets registration/runtime stack to native WordPress Dashboard hooks.

The existing Surface 10 runtime remains authoritative:

`Definition → registration compile → visibility compile/evaluate → trusted render-source compile → shared renderer → DashboardWidgetRuntimeRenderResult`

This contract authorizes a later bounded adapter implementation. It does not itself execute `wp_dashboard_setup`, `wp_network_dashboard_setup`, or `wp_add_dashboard_widget`.

## 2. Canonical existing dependencies

The later adapter MUST reuse:

- `DefinitionRepositoryInterface`;
- `DashboardWidgetRegistrationCompiler`;
- `DashboardWidgetRuntimeRenderExecutor`;
- `DashboardWidgetRegistrationDescriptor`;
- `DashboardWidgetRuntimeRenderResult`;
- shared `ExecutionContext`, `ExecutionChannel`, and `Principal`.

No shared Platform source contract is widened.

The adapter MUST NOT create a second visibility evaluator, renderer dispatcher, Definition repository, capability policy, or provider execution path.

## 3. Module-local WordPress environment seam

V1 introduces a module-local `DashboardWidgetWordPressEnvironmentInterface` so native WordPress calls are isolated and unit-testable.

The seam exposes only the operations required by V1:

- register a WordPress action hook;
- register a Dashboard widget with id, title, render callback, context and priority;
- read current user id;
- read current site id;
- read current network id when available;
- emit already-trusted renderer HTML.

The native implementation is `NativeWordPressDashboardWidgetEnvironment`.

The native implementation may throw when required WordPress APIs are unavailable. The adapter boundary is responsible for containing those failures.

No generic arbitrary-function gateway is authorized.

## 4. Hook lifecycle

The adapter exposes one idempotent hook-registration operation.

It registers exactly:

- `wp_dashboard_setup` → site Dashboard registration;
- `wp_network_dashboard_setup` → network Dashboard registration.

Each hook-registration attempt is contained independently. Failure to register one hook MUST NOT throw into kernel boot or cause the other registration attempt to expose exception details.

The adapter does not register:

- `admin_init`;
- `init`;
- AJAX actions;
- REST routes;
- cron;
- frontend hooks;
- arbitrary authored hooks.

`DashboardWidgetsModule::register()` constructs and publishes the adapter service.

`DashboardWidgetsModule::boot()` retrieves the exact adapter service and invokes its idempotent hook-registration operation.

The module adds exactly one service id:

`module.dashboard-widgets.wordpress-adapter`

## 5. Deterministic two-phase target planning

Each Dashboard target has an independent idempotent registration pass.

Before the first `wp_add_dashboard_widget` side effect for that target, the adapter MUST complete a planning phase.

### 5.1 Canonical Definition loading and order

Load Definitions through:

`DefinitionRepositoryInterface::byType(DashboardWidgetDefinition::TYPE)`

Sort the returned Definitions using the same canonical catalog ordering already used by `DashboardWidgetsReadService`:

1. `slug` ascending;
2. Definition `id` ascending as the stable tie-breaker.

Repository failure or any unexpected planning dependency `Throwable` aborts that target with zero Dashboard registration side effects.

### 5.2 Registration compilation

Each Definition is compiled through the existing `DashboardWidgetRegistrationCompiler`.

Expected `InvalidArgumentException` compiler rejection means that Definition is omitted from the plan.

Unexpected compiler/dependency `Throwable` aborts the whole target plan with zero registration side effects.

The adapter never reconstructs registration metadata from raw payload fields.

### 5.3 Target isolation

A compiled descriptor participates only when:

- site target: `networkDashboard === false`;
- network target: `networkDashboard === true`.

Site and network targets are distinct collision domains.

The same authored widget key may therefore appear once on each target without being a collision.

## 6. Canonical WordPress widget id

The exact V1 WordPress-facing id is:

`wpe_dashboard_widget_` + `DashboardWidgetRegistrationDescriptor::key`

Examples:

- key `sales_kpi` → `wpe_dashboard_widget_sales_kpi`
- key `support-links` → `wpe_dashboard_widget_support-links`

No authored callback id, DOM id, class name, function name, Definition slug, provider id or arbitrary prefix is accepted.

The compiled key grammar remains authoritative.

## 7. Collision policy

After the entire target plan is compiled and normalized, group entries by canonical WordPress widget id.

If a group contains more than one Definition:

- every member of that colliding group is suppressed;
- no member of the group reaches `wp_add_dashboard_widget`;
- no first-wins behavior is allowed;
- no last-wins behavior is allowed;
- no implicit overwrite is allowed.

Collision detection for the entire target MUST complete before any native Dashboard registration side effect occurs.

Collision-free entries may still register after all collisions have been identified and removed.

V1 does not expose raw collision diagnostics to Dashboard HTML.

## 8. Native Dashboard registration

For every collision-free planned entry, the adapter calls the environment Dashboard registration operation with:

- canonical WordPress widget id;
- compiled bounded plain-text title;
- a render callback bound only to the canonical Definition id;
- compiled native context;
- compiled native priority.

The native environment maps this to `wp_add_dashboard_widget` with:

- control callback: `null`;
- callback args: `null`.

V1 has no Dashboard control/settings form.

Unexpected native registration failure for one widget is contained and MUST NOT take down wp-admin. Remaining collision-free planned entries may continue.

## 9. Callback execution context

Each render callback constructs a fresh current-request `ExecutionContext`.

Exact projection:

- `Principal::userId` ← current WordPress user id, or `null` when unauthenticated;
- `Principal::actorType` ← `user`;
- `ExecutionContext::siteId` ← exact current positive WordPress site id;
- `ExecutionContext::networkId` ← current positive WordPress network id when available, otherwise `null`;
- `ExecutionContext::channel` ← `ExecutionChannel::Ui`;
- correlation id ← `null` in V1.

The callback MUST NOT reuse the Ability-oriented `WordPressExecutionContextFactory` ordinary-request behavior because that factory classifies non-REST/non-CLI execution as `Internal`.

Invalid/missing current-request context APIs, invalid site/network ids, or other context-construction failures produce no output and are contained.

## 10. Authorization ownership

The adapter MUST NOT add a blanket `manage_options` check.

`DashboardWidgetsModule::CAPABILITY` belongs to the read-only Ability exposure, not to WordPress Dashboard widget visibility.

The existing `DashboardWidgetVisibilityEvaluator` remains authoritative:

- unauthenticated principal → deny;
- non-user actor → deny;
- current-user/site context mismatch → deny;
- populated roles dimension → ANY role within dimension;
- populated capabilities dimension → ANY capability within dimension;
- populated users dimension → exact user membership;
- populated dimensions combine through the existing evaluator semantics.

The adapter must not silently narrow or broaden that policy.

## 11. Runtime callback mapping

The callback invokes only:

`DashboardWidgetRuntimeRenderExecutor::render(definitionId, context)`

V1 output mapping is exact:

| Runtime result | Callback output |
|---|---|
| `missing_definition` | none |
| `invalid_definition` | none |
| `visibility_denied` | none |
| `renderer_failed` | none |
| `runtime_failure` | none |
| `rendered` | exact trusted renderer HTML |

Only `rendered` may reach the environment output operation.

The adapter MUST NOT emit:

- raw exception text;
- visibility denial reason;
- renderer failure code;
- Definition payload;
- stack trace;
- debug HTML;
- authored fallback HTML;
- provider diagnostics.

Unexpected callback/runtime/output `Throwable` is contained and emits nothing.

## 12. Asset policy

`DashboardWidgetRuntimeRenderResult::assetHandles` remains data only.

V1 ignores the list.

The adapter and native environment MUST NOT call:

- `wp_enqueue_script`;
- `wp_enqueue_style`;
- `wp_register_script`;
- `wp_register_style`;
- any equivalent asset side-effect API.

A later asset lifecycle requires a separate contract.

## 13. Idempotency

The adapter maintains independent in-memory V1 guards for:

- hook registration;
- site Dashboard registration pass;
- network Dashboard registration pass.

Repeated module boot or repeated hook callback invocation in the same request MUST NOT duplicate native registration side effects.

No persistent cache or user option is used for idempotency.

## 14. Exception containment

The following are fail-closed boundaries:

- action-hook API unavailable;
- Definition repository failure;
- unexpected compiler dependency failure;
- current-user/site/network API failure;
- Dashboard registration API unavailable/failure;
- runtime executor unexpected escape;
- output operation failure.

No raw exception text is returned or printed.

Expected compiler `InvalidArgumentException` remains a per-Definition rejection and does not abort other valid entries.

## 15. Native environment rules

`NativeWordPressDashboardWidgetEnvironment` may call only the native APIs required by this contract:

- `add_action`;
- `wp_add_dashboard_widget`;
- `get_current_user_id`;
- `get_current_blog_id`;
- `get_current_network_id` when available;
- direct output of the exact trusted HTML supplied by the adapter.

If `get_current_network_id` is unavailable, network id is `null`; this does not invent a network id.

The environment does not query roles/capabilities, providers, options, remote endpoints or user preferences.

## 16. Acceptance matrix

The implementation tests MUST prove at least:

| Scenario | Required behavior |
|---|---|
| Hook registration called twice | each exact Dashboard hook registered once |
| Site Definition on site target | planned and registered |
| Network Definition on site target | omitted |
| Network Definition on network target | planned and registered |
| Same key once per different target | each target may register its own id |
| Same-target duplicate canonical id | all colliders suppressed; no colliding native call |
| Duplicate plus unique entry | unique entry registers only after collision scan completed |
| Expected compiler rejection | invalid Definition skipped |
| Unexpected repository/compiler planning failure | zero target registration calls |
| Native widget registration failure | contained; no raw exception output |
| Callback unauthenticated | executor visibility denies / no output |
| Callback current-user/site/network | exact `ExecutionChannel::Ui` context reaches executor path |
| Runtime non-rendered result | no output |
| Runtime rendered result | exact trusted HTML output |
| Runtime rendered result with handles | HTML output; handles ignored |
| Callback/environment Throwable | contained; no output |
| Module register + boot | adapter service registered and hooks wired through module boot |

Tests MUST also prove the native registration path supplies no control callback and no callback args.

## 17. Authorized implementation tranche

Issue **#1188 — Dashboard Widgets: bounded WordPress Dashboard adapter V1**.

Exact source/test scope:

1. `frameworks/Modules/DashboardWidgets/DashboardWidgetWordPressEnvironmentInterface.php`
2. `frameworks/Modules/DashboardWidgets/NativeWordPressDashboardWidgetEnvironment.php`
3. `frameworks/Modules/DashboardWidgets/DashboardWidgetWordPressAdapter.php`
4. `frameworks/Modules/DashboardWidgets/DashboardWidgetsModule.php`
5. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetWordPressAdapterTest.php`
6. `tests/Unit/Modules/DashboardWidgets/NativeWordPressDashboardWidgetEnvironmentTest.php`
7. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetsModuleTest.php`

No shared Platform source file is authorized.

## 18. Explicit V1 denials

This contract does not authorize:

- provider/query/source execution;
- Safe HTTP or remote fetch;
- iframe/embed execution;
- shortcode/block/action execution;
- asset enqueue/register;
- Dashboard widget control/settings callback;
- Definition mutation;
- user-preference mutation;
- caching/refresh;
- shared Platform source changes;
- a new blanket capability gate;
- frontend registration;
- AJAX/REST mutation;
- full-parity runtime/product certification;
- deployment or release.

## 19. Promotion verdict

When this contract merges with exact-head Governance/Architecture green, zero unresolved review threads and zero behind, promote:

`READY_FOR_BOUNDED_WORDPRESS_DASHBOARD_ADAPTER_V1`

Only then may Issue #1188 be claimed.
