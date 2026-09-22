# Dashboard Widgets — Bounded Runtime Render Execution Contract V1

Status: **implementation-ready contract / WordPress Dashboard adapter still blocked**
Surface: **10 — Dashboard Widgets**
Issue: **#1180**
Exact base: `main@de147432457892b388d80ee368a30c7a9b26fa4d`

## 1. Purpose

Define the smallest module-local runtime boundary that may safely invoke the already-registered shared renderer after the trusted compile, visibility and canonical Blueprint gates have passed.

This contract authorizes a later bounded executor implementation. It does not authorize WordPress Dashboard hooks or `wp_add_dashboard_widget`.

## 2. Canonical V1 runtime flow

The implementation MUST preserve this order:

1. receive a Dashboard Widget Definition id plus the caller's `ExecutionContext`;
2. load the Definition through the canonical `DefinitionRepositoryInterface`;
3. if no Definition exists, return `missing_definition`;
4. compile registration metadata through `DashboardWidgetRegistrationCompiler`;
5. compile visibility through `DashboardWidgetVisibilityCompiler`;
6. evaluate visibility through `DashboardWidgetVisibilityEvaluator` using the exact incoming `ExecutionContext`;
7. if visibility denies, return `visibility_denied` and do not compile/render further;
8. compile trusted render source through `DashboardWidgetRenderSourceCompiler`;
9. convert the descriptor to canonical `RenderInput`;
10. invoke the canonical shared `RendererInterface` / `BlueprintRendererDispatcher` with that `RenderInput` and the exact incoming `ExecutionContext`;
11. translate the renderer-layer output into a module-local `DashboardWidgetRuntimeRenderResult`.

Existing compilers remain authoritative and pure. V1 MAY repeat deterministic validation already performed by `DashboardWidgetRegistrationCompiler` rather than widening or refactoring existing compiler contracts.

## 3. Module-local result states

`DashboardWidgetRuntimeRenderResult` MUST define exactly these V1 status identifiers:

- `missing_definition`
- `invalid_definition`
- `visibility_denied`
- `renderer_failed`
- `runtime_failure`
- `rendered`

The result is not a replacement for shared `RenderOutput`. It is a Dashboard Widgets orchestration result.

## 4. Result payload contract

The V1 result carries only:

- `status: string`
- `html: string`
- `assetHandles: list<string>`
- `visibilityReason: ?string`
- `renderFailure: ?RenderFailureCode`

It MUST NOT carry raw exception objects, exception messages, stack traces, provider payloads, secrets or arbitrary diagnostics.

### missing_definition

- `status = missing_definition`
- `html = ''`
- `assetHandles = []`
- `visibilityReason = null`
- `renderFailure = null`

### invalid_definition

Used only for expected malformed/untrusted Definition or compiler rejection.

- `status = invalid_definition`
- `html = ''`
- `assetHandles = []`
- `visibilityReason = null`
- `renderFailure = null`

### visibility_denied

- `status = visibility_denied`
- `html = ''`
- `assetHandles = []`
- `visibilityReason` is exactly one supported denial reason from `DashboardWidgetVisibilityDecision`
- `renderFailure = null`

An allowed visibility reason may never be stored in a denied result.

### renderer_failed

- `status = renderer_failed`
- `html = ''`
- `assetHandles = []`
- `visibilityReason = null`
- `renderFailure` preserves the non-null shared `RenderFailureCode`

Renderer HTML is never propagated on failure.

### runtime_failure

Used for unexpected repository/dependency/runtime exceptions that are not ordinary compiler rejection.

- `status = runtime_failure`
- `html = ''`
- `assetHandles = []`
- `visibilityReason = null`
- `renderFailure = null`

Raw exception information is intentionally not exposed.

### rendered

- `status = rendered`
- `html` is the successful shared renderer HTML
- `assetHandles` is the successful shared renderer asset-handle list
- `visibilityReason = null`
- `renderFailure = null`

V1 stores asset handles as data only. It MUST NOT enqueue, register or otherwise execute asset side effects.

## 5. Exception mapping

The executor MUST be non-throwing for malformed widget/runtime content.

### Expected compiler rejection

`InvalidArgumentException` raised by the trusted registration, visibility or render-source compile sequence maps to:

`invalid_definition`

The renderer must not be called.

### Unexpected dependency/runtime failure

Unexpected `Throwable` from repository access, role/capability runtime dependencies, compiler dependencies outside their expected validation failures, or renderer invocation boundary maps to:

`runtime_failure`

The result contains no raw exception detail.

The shared dispatcher already normalizes component-renderer exceptions into a failed `RenderOutput`. Such a returned failed output maps to `renderer_failed`, not `runtime_failure`.

## 6. Visibility ordering and authorization

Visibility MUST be evaluated before render-source execution is handed to the shared renderer.

If visibility denies:

- render-source compilation after the visibility decision is not performed;
- `RendererInterface::render()` is not called;
- no renderer HTML or assets exist;
- the bounded denial reason is preserved.

The executor MUST pass the exact incoming `ExecutionContext` object unchanged to both the visibility evaluator and the renderer. It MUST NOT synthesize a different user, site, network or channel context.

## 7. Definition and compiler ordering

A missing Definition returns before any compiler is called.

For an existing Definition:

- registration compilation is the first trust gate;
- visibility compilation/evaluation follows;
- trusted render-source compilation follows only after visibility allows;
- renderer invocation is last.

If registration compilation rejects, neither visibility evaluation nor renderer invocation may occur.

If visibility compilation rejects, neither visibility evaluation nor renderer invocation may occur.

If trusted render-source compilation rejects, renderer invocation may not occur.

## 8. Renderer boundary

The implementation MUST use the existing shared renderer service installed by `RenderingServiceRegistrar::SERVICE_RENDERER`.

No private dispatcher may be created.

The module may depend on the shared service through `RendererInterface` while the concrete service remains the existing `BlueprintRendererDispatcher`.

A successful `DashboardWidgetRenderSourceDescriptor::toRenderInput()` is the only input passed to the renderer.

## 9. Module wiring

The later source tranche adds exactly one module-local executor service constant:

`module.dashboard-widgets.runtime-render-executor`

`DashboardWidgetsModule::register()` constructs the executor from:

- canonical `DefinitionRepositoryInterface`;
- existing registration compiler instance;
- existing visibility compiler instance;
- existing visibility evaluator instance;
- existing render-source compiler instance;
- canonical shared renderer/dispatcher service.

No `boot()` hook behavior is added in V1.

## 10. Acceptance matrix

The implementation tests MUST prove at least:

| Scenario | Required result | Renderer called? |
|---|---|---:|
| Definition missing | `missing_definition` | no |
| Registration/Definition invalid | `invalid_definition` | no |
| Visibility metadata invalid | `invalid_definition` | no |
| Visibility denied | `visibility_denied` + bounded reason | no |
| Render-source invalid | `invalid_definition` | no |
| Repository/dependency unexpected exception | `runtime_failure` | no/contained |
| Shared renderer returns failure | `renderer_failed` + shared failure code | yes |
| Shared renderer succeeds | `rendered` + HTML/handles | yes |

Tests MUST also prove the exact same `ExecutionContext` instance reaches visibility evaluation and renderer invocation on the allowed path.

## 11. Authorized implementation tranche

Issue **#1182 — Dashboard Widgets: bounded runtime render executor V1**.

Exact source/test scope:

1. `frameworks/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderResult.php`
2. `frameworks/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderExecutor.php`
3. `frameworks/Modules/DashboardWidgets/DashboardWidgetsModule.php`
4. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderResultTest.php`
5. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderExecutorTest.php`
6. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetsModuleTest.php`

No shared Platform source file is authorized.

## 12. Explicit V1 denials

This contract does not authorize:

- `wp_dashboard_setup`;
- `wp_network_dashboard_setup`;
- `wp_add_dashboard_widget`;
- any WordPress Dashboard callback adapter;
- provider/query/source execution;
- Safe HTTP or remote fetch;
- iframe/embed execution;
- shortcode/block/action execution;
- asset enqueue/register side effects;
- Definition mutation;
- user-preference mutation;
- caching/refresh behavior;
- shared Platform source changes;
- full-parity runtime/product certification;
- deploy or release.

## 13. Promotion verdict

When this contract merges with exact-head Governance/Architecture green, zero unresolved review threads and zero behind, promote:

`READY_FOR_BOUNDED_RUNTIME_RENDER_EXECUTOR_V1`

Direct WordPress Dashboard registration remains blocked after that promotion.
