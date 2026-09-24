# Dashboard Widgets — Bounded Empty-State Rendering Contract V1

Status: **active bounded contract / runtime source NOT authorized by this contract issue**
Surface: **10 — Dashboard Widgets**
Issue: **#1224**
Exact audited base: `main@675466f37354c24749902f80e3c553cc394368d8`
Audit verdict: **READY_FOR_BOUNDED_EMPTY_STATE_RENDERING_CONTRACT_V1**

## 1. Purpose

Freeze the smallest safe Surface 10 contract that allows a successful, authorized canonical Query read returning zero rows to render an explicitly authored trusted empty-state presentation.

This contract changes no runtime/product PHP. It defines the only later source shape that may be implemented after this contract itself merges terminal green.

The bounded intent is narrow:

- preserve all existing literal-only and non-empty Query behavior;
- keep visibility evaluation before any Query read;
- keep Query as validation, Policy authorization, planning and provider-execution owner;
- keep Dashboard Widgets as bounded orchestration + trusted presentation owner;
- reuse the existing Component Blueprint registry/catalog and trusted renderer;
- distinguish a valid zero-row result from every authorization, provider, schema, type or runtime failure;
- add no generic provider, remote transport, action, cache, refresh, background, mutation or shared-Platform execution path.

## 2. Exact-main evidence

The transition audit selected this tranche from exact main `675466f37354c24749902f80e3c553cc394368d8`.

Terminal predecessor evidence:

- Issue #1215 / PR #1221 — Bounded Query/Data-Source Binding Source V1 — terminal PASS;
- #1221 exact head `a6be8af09ddce141a952885b0ddd5e6fff39ebdc`;
- Governance `35996551115` PASS;
- Architecture `35996551192` PASS;
- PHP Quality `35996551145` PASS;
- Platform Compatibility `35996551121` PASS;
- Distributable Package `35996551287` PASS;
- expected-head merge `815f35910367b9103954de80975fc76bf8807d69`;
- Issue #1222 / PR #1223 shared-truth reconciliation terminal PASS;
- #1223 exact head `e578d81beac789ad0606207a4562fe653c8b9341`;
- Governance `35997232559` PASS;
- Architecture `35997232640` PASS;
- expected-head merge `675466f37354c24749902f80e3c553cc394368d8`.

The exact-main gap is explicit:

- Options Bank record `dashboard-widgets.state.empty` is `WPE_HARD / P0_PARITY`;
- reviewed Atomic projection maps it to `dashboard-widgets.presentation.states`;
- the Query/Data-Source binding contract intentionally makes zero rows fail closed and explicitly reserves a later empty-state tranche;
- current `DashboardWidgetQueryBindingExecutor` rejects `returned < 1`;
- current Surface 10 runtime/compiler/renderer/adapter contains no empty-state runtime seam.

## 3. Canonical owners remain unchanged

V1 MUST reuse:

- `QueryReadConsumerInterface`;
- `DataSourceRegistryInterface`;
- exact incoming `ExecutionContext`;
- `DashboardWidgetComponentBlueprintCatalog`;
- `DashboardWidgetRenderSourceCompiler`;
- `DashboardWidgetRenderSourceDescriptor`;
- `DashboardWidgetQueryBindingExecutor`;
- `DashboardWidgetRuntimeRenderExecutor`;
- existing shared `RendererInterface` / trusted Dashboard Widget renderer.

Ownership remains fixed:

- Data Source Registry owns descriptor/schema/availability truth;
- Query owns validation, Policy authorization, provider planning and provider execution;
- Surface 10 owns authored empty-state metadata, fail-closed selection and final trusted rendering.

No new service locator, provider registry or transport owner is created.

## 4. Exact authored location

V1 adds one optional key to the existing `widget.render_source` object:

`empty_state`

Therefore the later render-source key allowlist becomes exactly:

- `kind`
- `blueprint_id`
- `blueprint_revision`
- `query`
- `bindings`
- `empty_state`

Unknown keys still fail closed.

`empty_state` is permitted **only when the same render_source contains a valid Query object and at least one `source: query` binding**.

Rules:

- literal-only render source + `empty_state` => invalid Definition;
- Query object without Query bindings remains invalid;
- Query bindings without Query object remain invalid;
- Query-bound render source without `empty_state` remains valid and preserves current zero-row failure behavior.

This contract does not add empty-state behavior to literal-only widgets.

## 5. Exact empty_state object

The authored object accepts exactly:

- `kind`
- `blueprint_id`
- `blueprint_revision`
- `bindings`

`kind` MUST equal:

`component_blueprint`

No `query`, provider key, URL, action, shortcode, block, callback, PHP, script, cache, refresh or dynamic token is admitted inside `empty_state`.

Example:

```json
{
  "widget": {
    "type": "chart",
    "render_source": {
      "kind": "component_blueprint",
      "blueprint_id": "31000000-0000-4000-8000-000000000003",
      "blueprint_revision": 1,
      "query": {
        "contract_version": 1,
        "source_ref": "wordpress.posts",
        "page_size": 20
      },
      "bindings": {
        "labels": {
          "source": "query",
          "field_ref": "post.title",
          "mode": "column"
        },
        "values": {
          "source": "query",
          "field_ref": "post.id",
          "mode": "column"
        }
      },
      "empty_state": {
        "kind": "component_blueprint",
        "blueprint_id": "31000000-0000-4000-8000-000000000005",
        "blueprint_revision": 1,
        "bindings": {
          "title": {
            "source": "literal",
            "value": "No results"
          },
          "text": {
            "source": "literal",
            "value": "There is nothing to display yet."
          }
        }
      }
    }
  }
}
```

## 6. Allowed empty-state Blueprints

V1 deliberately allows only these existing trusted Surface 10 Blueprint types:

1. `rich_text`
   - Blueprint id `31000000-0000-4000-8000-000000000001`
   - revision `1`
   - exact binding schema: `content:string`

2. `announcement`
   - Blueprint id `31000000-0000-4000-8000-000000000005`
   - revision `1`
   - exact binding schema: `title:string`, `text:string`

All other current/future Component Blueprints are rejected for Empty-State V1 unless a later contract explicitly widens this set.

The compiler MUST resolve the Blueprint through the canonical registry/catalog and confirm Surface 10 ownership. The hard-coded ids above are contract evidence, not permission to bypass registry/catalog truth.

## 7. Empty-state binding envelopes

Every empty-state binding MUST use the existing literal envelope shape exactly:

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

No Query-bound or provider-bound empty-state binding is allowed.

The authored binding keys MUST exactly equal the resolved allowed Blueprint binding schema.

Because V1 permits only `rich_text` and `announcement`, every empty-state binding value MUST be a string.

No null, integer, float, boolean, list, object or coercion is admitted in Empty-State V1.

## 8. Bounded authored content

For every empty-state string:

- minimum encoded byte length: **1**;
- maximum encoded byte length: **2048**;
- after trimming ASCII whitespace, the value MUST not be empty;
- existing non-executable marker rejection remains authoritative at minimum for PHP open tags, `<script`, and `javascript:`.

The complete encoded `empty_state` object MUST be no more than **4096 bytes** using the same deterministic JSON-encoding convention used by the later implementation.

No raw HTML trust is introduced. The existing trusted renderer continues to escape the final string bindings.

## 9. Compilation result

The later implementation may introduce a module-local immutable:

`DashboardWidgetEmptyStateDescriptor`

It must contain only the resolved trusted Blueprint id, revision and validated literal bindings.

`DashboardWidgetRenderSourceDescriptor` may hold at most one optional empty-state descriptor.

The descriptor must expose no executable callback/provider/transport field.

When Query resolution returns non-empty rows, the empty-state descriptor has no effect.

## 10. Exact zero-row semantics

A zero-row result is eligible for empty-state rendering **only after all normal Query preconditions have succeeded**.

The later executor must preserve this order:

1. load Definition;
2. registration compile;
3. visibility compile;
4. visibility evaluate;
5. if denied, stop with existing visibility result and execute zero Query reads;
6. render-source / Query / empty-state compile;
7. Data Source descriptor preflight;
8. execute `QueryReadConsumerInterface::read()` using the exact incoming `ExecutionContext`;
9. validate canonical Query result header;
10. validate `rows` is a list;
11. validate `returned` is an integer exactly equal to `count(rows)`;
12. validate `0 <= returned <= page_size`;
13. if `returned === 0`, apply the rules in §11;
14. if `returned > 0`, preserve the existing Query binding/type/safety mapping;
15. render exactly one resolved trusted Blueprint using the same exact `ExecutionContext`.

## 11. Empty-state eligibility

For `returned === 0`:

### With valid authored empty_state

The later implementation may resolve the render source to the compiled empty-state Blueprint/bindings and invoke the existing shared renderer.

### Without authored empty_state

Preserve current behavior:

`runtime_failure`

This backward-compatibility rule is mandatory. A missing empty state does not silently create default content.

## 12. Zero rows must not hide failures

Empty state MUST NOT be selected for any of the following:

- visibility/Policy denial;
- Data Source not registered;
- Data Source unavailable/degraded;
- missing canonical authorization metadata;
- page size above Data Source bound;
- missing projected/filter/order field;
- incompatible logical type;
- Query exception;
- Query `ok !== true`;
- contract-version mismatch;
- source-ref mismatch;
- projection mismatch;
- rows not a list;
- `returned` not an integer;
- `returned !== count(rows)`;
- `returned < 0`;
- `returned > page_size`;
- any non-empty malformed row;
- missing projected field in non-empty rows;
- unsafe Query-derived value;
- invalid/malformed empty-state authored metadata;
- trusted renderer failure.

All such cases keep the existing bounded failure mapping.

Provider/Query error text, payloads, stack traces or paths MUST NOT appear in the empty-state bindings, HTML or public result.

## 13. Public result semantics

V1 introduces **no new public DashboardWidgetRuntimeRenderResult status**.

When a valid zero-row result selects a valid authored empty state and the existing trusted renderer succeeds:

- return the existing successful `rendered` result;
- returned HTML/assets are exactly the existing renderer output;
- no hidden "empty" success code is added to public HTML.

When the trusted renderer fails, preserve the existing renderer-failure/runtime-failure mapping.

## 14. Context and authorization invariants

The exact same `ExecutionContext` instance supplied to `DashboardWidgetRuntimeRenderExecutor::render()` MUST be passed unchanged to:

- canonical Query read;
- final trusted renderer.

Empty-state selection creates no alternate principal, site, network or authorization context.

Visibility remains presentation-only and does not replace Query/Data Source Policy authorization.

## 15. No implicit fallback content

V1 has no hard-coded "No results" text.

If empty-state metadata is absent, invalid or rejected, the system does not fabricate presentation copy.

Localization/content authoring remains Definition-owned and later UI/editor work remains separately gated.

## 16. No cache/refresh promotion

This contract does not implement or authorize:

- `dashboard-widgets.refresh_cache.policy`;
- TTL;
- cache scope;
- cache generations;
- stale-state rendering;
- last-updated metadata;
- manual refresh;
- async refresh;
- retry policy;
- background jobs;
- Job-produced snapshots.

Those remain a later cross-surface contract because the executable-evidence protocol requires security/scope/source generations and async/Job semantics.

## 17. Generic provider/action/remote boundaries remain blocked

This contract does not promote:

- `dashboard-widgets.type.registered_provider`;
- native render/control/callback provider execution;
- direct `IntegrationRegistry` execution;
- Listings execution;
- shortcode/block/action execution;
- Ability-backed mutation;
- Safe HTTP/remote/RSS/iframe;
- arbitrary URL/provider keys;
- asset registration/enqueue;
- Definition/user-preference mutation;
- shared Platform source changes.

## 18. Exact later source allowlist

After this contract merges terminal green, a **separate source issue** may be created/claimed.

That later source issue may modify/create exactly these nine files:

1. `frameworks/Modules/DashboardWidgets/DashboardWidgetEmptyStateDescriptor.php` — new
2. `frameworks/Modules/DashboardWidgets/DashboardWidgetRenderSourceCompiler.php`
3. `frameworks/Modules/DashboardWidgets/DashboardWidgetRenderSourceDescriptor.php`
4. `frameworks/Modules/DashboardWidgets/DashboardWidgetQueryBindingExecutor.php`
5. `frameworks/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderExecutor.php`
6. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetEmptyStateDescriptorTest.php` — new
7. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRenderSourceCompilerTest.php`
8. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetQueryBindingExecutorTest.php`
9. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderExecutorTest.php`

No Module bootstrap/service change is required by this contract.

No shared Platform file is authorized.

## 19. Minimum later test matrix

The later source tranche MUST prove at minimum:

- literal-only render source without empty_state remains unchanged;
- literal-only render source with empty_state rejects;
- Query-bound source without empty_state keeps zero-row `runtime_failure`;
- unknown empty_state keys reject;
- empty_state query/provider/dynamic envelope rejects;
- only `rich_text` and `announcement` Blueprint types admit;
- wrong Blueprint id/revision rejects;
- non-Surface-10 Blueprint rejects;
- bindings must exactly match Blueprint schema;
- only literal string bindings admit;
- empty/whitespace-only strings reject;
- >2048-byte binding rejects;
- >4096-byte encoded empty_state rejects;
- executable authored markers reject;
- visibility denial causes zero Query reads and zero renderer calls;
- Data Source preflight failure never renders empty state;
- Query `ok:false` never renders empty state;
- source/projection mismatch never renders empty state;
- malformed cardinality never renders empty state;
- valid `rows=[]`, `returned=0` + valid empty_state renders exactly once;
- valid zero-row result forwards the exact same ExecutionContext to Query and renderer;
- non-empty Query results preserve current binding behavior and do not select empty state;
- renderer failure on empty-state input keeps existing failure mapping;
- Query/provider error body is not exposed;
- generic provider, remote, action, cache/refresh and mutation seams remain absent.

## 20. Contract issue allowlist

Issue #1224 itself may change exactly:

1. `docs/PRODUCT/DASHBOARD-WIDGETS-BOUNDED-EMPTY-STATE-RENDERING-CONTRACT-V1.md`
2. `.ai/state/CURRENT-STATE.yaml`
3. `.ai/state/LAST-CHECKPOINT.md`
4. `README.md`
5. `config/coordination/agent-work-queue.json`
6. `config/coordination/runner-benchmark.json`

No runtime/product PHP or unit-test source is authorized in this contract tranche.

## 21. Promotion verdict

When this exact six-file contract/shared-truth tranche reaches:

- terminal path-applicable exact-head CI PASS;
- zero unresolved review blockers/comments;
- zero commits behind main;
- expected-head merge;

promote only:

`CONTRACT_FROZEN_READY_FOR_BOUNDED_EMPTY_STATE_RENDERING_SOURCE_V1`

That contract-only result may authorize creation/claim of the separate exact nine-file source issue from §18.

It MUST NOT promote:

- full Surface 10 `RUNTIME_CERTIFIED`;
- `PRODUCT_PARITY_CERTIFIED`;
- refresh/cache parity;
- action/provider/remote parity;
- deployment;
- release.
