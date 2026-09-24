# Dashboard Widgets — Bounded Renderer-Failure Error-State Contract V1

Status: **active bounded contract / runtime source NOT authorized by this contract issue**
Surface: **10 — Dashboard Widgets**
Issue: **#1230**
Exact audited base: `main@4fbe3ae7b65ec0ad627ceefac465d4269a872446`
Audit verdict: **READY_FOR_BOUNDED_RENDERER_FAILURE_ERROR_STATE_CONTRACT_V1**

## 1. Purpose

Freeze the smallest safe Surface 10 contract that allows an explicitly authored trusted error presentation to render only after the primary trusted Dashboard Widget renderer returns a typed failed `RenderOutput`.

This contract changes no runtime/product PHP. It defines the only later source shape that may be implemented after this contract itself merges terminal green.

The bounded intent is narrow:

- preserve current Definition, registration, visibility, Query/Data Source and Empty-State ownership;
- keep all authorization/source/runtime failures fail closed;
- reuse the existing Component Blueprint registry/catalog and existing shared Renderer;
- allow one trusted presentation fallback only for a typed final-render failure;
- retain the original typed primary `RenderFailureCode` without exposing raw errors;
- add no generic provider, remote transport, action, cache, refresh, background, mutation or shared-Platform execution path.

## 2. Exact-main evidence

The transition audit selected this tranche from exact main `4fbe3ae7b65ec0ad627ceefac465d4269a872446`.

Terminal predecessor evidence:

- Issue #1224 / PR #1225 — Bounded Empty-State Rendering Contract V1 — terminal PASS;
- Issue #1226 / PR #1227 — Bounded Empty-State Rendering Source V1 — terminal PASS;
- Issue #1228 / PR #1229 — Empty-State source post-merge shared-truth reconciliation V1 — terminal PASS;
- #1229 exact head `f64fe40331da2e8139897170aae881b8b93a23b7`;
- Governance `36005699579` PASS;
- Architecture `36005699569` PASS;
- exact five-file scope;
- zero review/comments/inline blockers;
- zero behind;
- expected-head merge `4fbe3ae7b65ec0ad627ceefac465d4269a872446`.

The exact-main gap is explicit:

- Options Bank record `dashboard-widgets.state.error` is `WPE_HARD / P0_PARITY`;
- reviewed projection maps it to `dashboard-widgets.presentation.states`;
- `dashboard-widgets.state.empty` in the same normalized contract is now implemented bounded PASS;
- current runtime has typed opaque `renderer_failed`, but no authored trusted error presentation;
- current WordPress adapter outputs trusted HTML only for `STATUS_RENDERED`;
- executable evidence requires renderer-failure isolation (`DW-33`) and non-leaking error presentation (`DW-108`).

## 3. Why this tranche precedes other remaining P0 lanes

This contract is smaller than the remaining alternatives:

- `state.loading` needs an asynchronous/loading lifecycle not owned by the current synchronous server-render path;
- refresh/cache requires cache generations, invalidation, concurrency ordering and Job semantics (`DW-109..DW-126`);
- actions require independent Ability/Policy authorization, confirmation, audit and mutation;
- dismiss/preferences require per-user mutation/lifecycle/privacy semantics;
- remote/RSS/iframe/providers introduce broader transport/output-trust boundaries.

Renderer-failure Error-State V1 stays local to the existing trusted render boundary.

## 4. Canonical owners remain unchanged

V1 MUST reuse:

- current Definition repository and Dashboard Widget compilers;
- exact incoming `ExecutionContext`;
- `DashboardWidgetComponentBlueprintCatalog`;
- `DashboardWidgetRenderSourceCompiler`;
- `DashboardWidgetRenderSourceDescriptor`;
- `DashboardWidgetRuntimeRenderExecutor`;
- `DashboardWidgetRuntimeRenderResult`;
- `DashboardWidgetWordPressAdapter`;
- shared `RendererInterface`;
- shared `RenderOutput` and `RenderFailureCode`.

Ownership remains fixed:

- Policy/Query/Data Source owners authorize source access/execution;
- Surface 10 owns only bounded authored error-state metadata, one-shot selection and trusted presentation;
- WordPress adapter owns only native registration/context/output bridging.

No new service locator, provider registry or transport owner is created.

## 5. Exact authored location

V1 adds one optional key to the existing `widget.render_source` object:

`error_state`

The later render-source key allowlist becomes exactly:

- `kind`
- `blueprint_id`
- `blueprint_revision`
- `query`
- `bindings`
- `empty_state`
- `error_state`

Unknown keys still fail closed.

Unlike Empty-State V1, `error_state` may be authored for either:

- literal trusted Component Blueprint render sources; or
- Query-bound trusted Component Blueprint render sources.

`error_state` may coexist with `empty_state`.

It does not create an alternate source/query/provider path.

## 6. Exact error_state object

The authored object accepts exactly:

- `kind`
- `blueprint_id`
- `blueprint_revision`
- `bindings`

`kind` MUST equal:

`component_blueprint`

No `query`, `empty_state`, nested `error_state`, provider key, URL, action, shortcode, block, callback, PHP, script, cache, refresh or dynamic token is admitted inside `error_state`.

## 7. Allowed error-state Blueprints

V1 allows only these existing trusted Surface 10 Blueprint types:

1. `rich_text`
   - Blueprint id `31000000-0000-4000-8000-000000000001`
   - revision `1`
   - exact binding schema: `content:string`

2. `announcement`
   - Blueprint id `31000000-0000-4000-8000-000000000005`
   - revision `1`
   - exact binding schema: `title:string`, `text:string`

All other current/future Component Blueprints are rejected for Error-State V1 unless a later contract explicitly widens this set.

The compiler MUST resolve the Blueprint through the canonical registry/catalog and confirm Surface 10 ownership. Contract ids are evidence, not permission to bypass canonical registry/catalog truth.

## 8. Error-state binding envelopes

Every error-state binding MUST use the existing literal envelope exactly:

```json
{
  "source": "literal",
  "value": "..."
}
```

Exact envelope keys:

- `source`
- `value`

`source` MUST equal `literal`.

No Query/provider/dynamic binding is allowed in Error-State V1.

Authored binding keys MUST exactly equal the resolved allowed Blueprint binding schema.

Every value MUST be a string.

## 9. Bounded authored content

For every error-state string:

- minimum encoded byte length: **1**;
- maximum encoded byte length: **2048**;
- after trimming ASCII whitespace, the value MUST not be empty;
- existing non-executable marker rejection remains authoritative at minimum for PHP open tags, `<script`, and `javascript:`.

The complete deterministic encoded `error_state` object MUST be no more than **4096 bytes**.

No raw HTML trust is introduced. Existing trusted renderer escaping/sanitization remains authoritative.

## 10. Compilation descriptor

The later implementation may introduce a module-local immutable:

`DashboardWidgetErrorStateDescriptor`

It contains only:

- resolved trusted Blueprint id;
- Blueprint revision;
- validated literal bindings.

`DashboardWidgetRenderSourceDescriptor` may hold at most one optional error-state descriptor.

The descriptor exposes no executable callback/provider/transport field.

Query resolution MUST preserve error-state metadata into the final resolved primary render descriptor.

When a valid zero-row Query selects the existing authored Empty-State descriptor, the resolved Empty-State primary render descriptor MUST also preserve the authored Error-State descriptor. This permits one Error-State fallback if rendering the Empty-State presentation itself returns a typed failed `RenderOutput`.

## 11. Exact fallback eligibility

Error-State fallback is eligible only after all earlier runtime stages have succeeded and the final primary renderer has actually returned a typed failure.

Required order:

1. Definition load;
2. registration compile;
3. visibility compile;
4. visibility evaluate;
5. source/Query/Empty-State compile and resolution as already applicable;
6. all Data Source / Query validation and authorization completes successfully;
7. primary trusted renderer invoked exactly once with the exact incoming `ExecutionContext`;
8. primary renderer returns `RenderOutput`;
9. only if `success === false` and `failure` is a non-null typed `RenderFailureCode`, evaluate §12.

A thrown exception from the primary renderer is NOT fallback eligible in V1.

Primary renderer throwable preserves current opaque:

`runtime_failure`

## 12. Error-State selection

### Primary renderer success

Return existing ordinary `rendered`.

No fallback call occurs.

### Primary renderer typed failure + no authored error_state

Preserve existing:

`renderer_failed`

with the primary typed `RenderFailureCode`.

### Primary renderer typed failure + valid authored error_state

Invoke the same existing shared Renderer exactly once more with:

- `DashboardWidgetErrorStateDescriptor::toRenderInput()`;
- the exact same incoming `ExecutionContext` instance.

No error code, exception, query, provider response, path, stack trace or diagnostics are interpolated into the authored fallback bindings.

This second invocation is the only fallback invocation.

## 13. No recursion

The fallback Error-State descriptor has no `error_state`, `empty_state`, Query or provider field.

If fallback rendering:

- returns success => use §14;
- returns typed failure => fail closed as ordinary `renderer_failed` using the fallback renderer's typed failure;
- throws => return opaque `runtime_failure`.

There is no third render attempt.

## 14. Public result semantics

V1 introduces one dedicated bounded public result status:

`rendered_error`

Successful Error-State fallback MUST NOT silently return ordinary `rendered`.

The later `DashboardWidgetRuntimeRenderResult` shape for `rendered_error` is frozen as:

- `status = rendered_error`;
- `html` = trusted fallback renderer HTML;
- `assetHandles` = fallback renderer handles;
- `visibilityReason = null`;
- `renderFailure` = original primary renderer typed `RenderFailureCode`.

The original primary failure is retained only as typed enum metadata. No raw renderer exception/text exists in this result.

Existing `renderer_failed` remains fail-closed with:

- empty HTML;
- no asset handles;
- typed failure;
- no visibility reason.

## 15. WordPress adapter behavior

The WordPress adapter may output trusted HTML only for exactly:

- `STATUS_RENDERED`;
- `STATUS_RENDERED_ERROR`.

For every other status it outputs nothing.

The adapter:

- does not render fallback itself;
- does not inspect or print `RenderFailureCode`;
- does not add source/Policy logic;
- does not add an asset enqueue path in this tranche;
- continues to use the fresh UI `ExecutionContext` it constructs for runtime execution.

Existing environment/output exceptions remain contained.

## 16. Ineligible failures

Error-State rendering MUST NOT be selected for:

- missing Definition;
- invalid Definition or compile rejection;
- visibility denial;
- repository failure;
- visibility evaluator exception;
- Data Source unknown/unavailable/degraded;
- missing Data Source authorization mapping;
- Query/Policy/provider exception;
- Query `ok !== true`;
- source/projection mismatch;
- malformed rows/cardinality;
- schema/type mismatch;
- unsafe authored or Query-derived value;
- malformed Empty-State metadata;
- malformed Error-State metadata;
- missing Query resolver;
- unresolved Query/Empty-State metadata;
- any generic runtime exception before primary renderer return.

Those retain existing current fail-closed mappings.

## 17. Security invariants

V1 MUST preserve:

- no fallback on authorization/source denial;
- no raw exception/error text in HTML;
- no query/provider payload disclosure;
- no authored renderer/provider callback selection;
- no `IntegrationRegistry` execution;
- no remote transport;
- no recursive fallback;
- no change to Query/Data Source/Policy ownership;
- exact incoming `ExecutionContext` identity for primary and fallback render calls.

This directly supports the bounded direction of `DW-33` and `DW-108`; it does not claim either fixture as formally executed/certified.

## 18. Existing Empty-State behavior remains unchanged

This contract does not widen Empty-State eligibility.

Valid canonical zero-row Query behavior remains governed by the merged Empty-State V1 contract/source.

The only new interaction is:

- if an Empty-State presentation becomes the resolved primary render input;
- and that trusted primary render returns a typed failure;
- an authored Error-State may receive the single fallback attempt.

## 19. Loading state remains blocked

This contract does not implement `dashboard-widgets.state.loading`.

Loading requires a separately frozen lifecycle tied to an actual asynchronous/refresh execution model. A synchronous render start is not sufficient evidence for a user-visible loading state.

## 20. Refresh/cache/actions/preferences remain blocked

This contract does not implement:

- manual refresh;
- TTL/cache scope;
- stale/last-updated states;
- cache generations or invalidation;
- background jobs;
- retry/timeout state;
- Ability-backed actions;
- confirmation/result notices/action audit;
- per-user reorder/hide/collapse/dismiss/reset;
- mutation endpoints.

Those remain separately gated.

## 21. Generic provider/remote boundaries remain blocked

This contract does not promote:

- `dashboard-widgets.type.registered_provider`;
- native render/control/callback provider execution;
- direct `IntegrationRegistry` execution;
- Listings execution;
- shortcode/block/action execution;
- Safe HTTP/remote/RSS/iframe;
- arbitrary URL/provider keys;
- asset registration/enqueue;
- shared Platform source changes.

## 22. Exact later source/test allowlist

After this contract merges terminal green, a **separate source issue** may be created/claimed.

That later source issue may modify/create exactly these eleven files:

1. `frameworks/Modules/DashboardWidgets/DashboardWidgetErrorStateDescriptor.php` — new
2. `frameworks/Modules/DashboardWidgets/DashboardWidgetRenderSourceCompiler.php`
3. `frameworks/Modules/DashboardWidgets/DashboardWidgetRenderSourceDescriptor.php`
4. `frameworks/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderExecutor.php`
5. `frameworks/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderResult.php`
6. `frameworks/Modules/DashboardWidgets/DashboardWidgetWordPressAdapter.php`
7. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetErrorStateDescriptorTest.php` — new
8. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRenderSourceCompilerTest.php`
9. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderExecutorTest.php`
10. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderResultTest.php`
11. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetWordPressAdapterTest.php`

No Query Binding Executor change is required by this contract: Error-State metadata must be preserved through render-source descriptor resolution rather than adding a second Query owner.

No shared Platform file is authorized.

## 23. Minimum later test matrix

The later source tranche MUST prove at minimum:

- legacy render sources without error_state remain unchanged;
- literal render source may carry valid error_state;
- Query-bound render source may carry valid error_state;
- empty_state + error_state coexist safely;
- unknown error_state keys reject;
- query/provider/dynamic/nested-state fields reject;
- only rich_text and announcement admit;
- wrong Blueprint id/revision/Surface owner rejects;
- binding keys exactly match schema;
- only literal string bindings admit;
- empty/whitespace strings reject;
- >2048-byte string rejects;
- >4096-byte encoded error_state rejects;
- executable authored markers reject;
- missing/invalid/visibility-denied paths never invoke fallback;
- Data Source/Query/Policy failures never invoke fallback;
- primary renderer success invokes renderer once and returns rendered;
- primary renderer typed failure without error_state stays renderer_failed;
- primary renderer typed failure with error_state invokes renderer exactly twice total;
- fallback receives the exact same `ExecutionContext` instance;
- fallback input contains authored literals only and no primary failure text/code interpolation;
- successful fallback returns `rendered_error` and preserves original primary typed failure metadata;
- adapter outputs trusted HTML for rendered_error;
- adapter outputs nothing for other failure statuses;
- primary renderer throwable produces runtime_failure with no fallback;
- fallback typed failure produces renderer_failed and no HTML;
- fallback throwable produces runtime_failure and no HTML;
- no third render attempt occurs;
- no IntegrationRegistry/remote/action/cache/mutation seam is introduced.

## 24. Contract issue allowlist

Issue #1230 itself may change exactly:

1. `docs/PRODUCT/DASHBOARD-WIDGETS-BOUNDED-RENDERER-FAILURE-ERROR-STATE-CONTRACT-V1.md`
2. `.ai/state/CURRENT-STATE.yaml`
3. `.ai/state/LAST-CHECKPOINT.md`
4. `README.md`
5. `config/coordination/agent-work-queue.json`
6. `config/coordination/runner-benchmark.json`

No runtime/product PHP or unit-test source is authorized in this contract tranche.

## 25. Promotion verdict

When this exact six-file contract/shared-truth tranche reaches:

- terminal path-applicable exact-head CI PASS;
- zero unresolved review blockers/comments;
- zero commits behind main;
- expected-head merge;

promote only:

`CONTRACT_FROZEN_READY_FOR_BOUNDED_RENDERER_FAILURE_ERROR_STATE_SOURCE_V1`

That contract-only result may authorize creation/claim of the separate exact eleven-file source issue from §22.

It MUST NOT promote:

- full Surface 10 `RUNTIME_CERTIFIED`;
- `PRODUCT_PARITY_CERTIFIED`;
- loading state;
- refresh/cache;
- actions/preferences;
- provider/remote parity;
- P-006 runtime;
- deployment;
- release.
