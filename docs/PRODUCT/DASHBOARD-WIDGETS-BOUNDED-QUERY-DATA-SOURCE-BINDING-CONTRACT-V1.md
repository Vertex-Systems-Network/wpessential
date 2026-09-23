# Dashboard Widgets — Bounded Query/Data-Source Binding Contract V1

Status: **corrected planning contract / source implementation NOT authorized until contract merge + Query activation prerequisite**
Surface: **10 — Dashboard Widgets**
Issue: **#1213**
Exact base: `main@a63adf38e960842de19c94dd797628d1f2d578e5`

## 1. Purpose

Define the smallest safe Surface 10 contract that can bind canonical Query/Data Source reads into existing trusted Dashboard Widget Component Blueprint bindings.

This contract does not implement or execute the new binding path. Query remains the owner of validation, Policy authorization, provider planning and provider execution.

## 2. Dependency truth

Issue #1212 / PR #1214 merged the transition audit with:

`READY_FOR_BOUNDED_QUERY_DATA_SOURCE_BINDING_CONTRACT_V1`

Terminal audit evidence:

- PR #1214 exact head `c9f26ddef503f302ab92cc28579b08fbf873568e`;
- Governance Gate `35919710237` PASS;
- Architecture Guards `35919709973` PASS;
- zero unresolved review threads;
- zero PR comments/blockers;
- zero commits behind main;
- merge `a63adf38e960842de19c94dd797628d1f2d578e5`;
- Issue #1212 closed completed.

RB-0047 may therefore be reconciled terminal PASS.

## 3. Canonical owners

V1 MUST reuse:

- `WPEssential\Contracts\QueryReadConsumerInterface`;
- `QueryModule::SERVICE_READ_CONSUMER`;
- `WPEssential\Contracts\DataSourceRegistryInterface`;
- existing Surface 10 Component Blueprint registry/catalog;
- existing `DashboardWidgetRenderSourceCompiler`;
- existing `DashboardWidgetRuntimeRenderExecutor`;
- exact incoming `ExecutionContext`;
- existing shared renderer.

Ownership is fixed:

- Data Source Registry owns descriptor/schema/availability truth;
- Query owns validation, Policy authorization, provider planning and provider execution;
- Surface 10 owns only bounded query intent, binding mapping, fail-closed orchestration and final trusted rendering.

`IntegrationRegistry` is not an execution API and is not used by V1.

## 3A. Query Module activation prerequisite

The architecture/security review of PR #1216 found a required runtime precondition that is not currently satisfied on exact main:

- `QueryModule` implements and registers `QueryModule::SERVICE_READ_CONSUMER`;
- the current Pro bootstrap contributes `DashboardWidgetsModule`;
- the current Pro bootstrap does **not** contribute `QueryModule`;
- therefore `module.query.read-consumer` is not guaranteed to exist when Dashboard Widgets registers.

Dependency-gated Issue **#1217 — Query: bounded central Pro activation prerequisite V1** is required before Dashboard Widgets source Issue #1215 may be claimed.

#1217 is limited to exactly:

1. `wpessential-pro.php`
2. `tests/Smoke/query-pro-activation-contract.php`

The activation must contribute `QueryModule::class` exactly once and place it before `DashboardWidgetsModule::class` so Kernel registration makes Query services available first. It must preserve the existing compatible-Pro and entitlement gates.

No Query provider/compiler/validator/executor source change is authorized by this prerequisite.

## 4. Exact Definition payload

The existing `widget.render_source` remains the canonical render boundary.

V1 adds one optional `query` object to `render_source` and allows a binding envelope to use either `literal` or `query`.

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
        "filters": [
          {
            "field_ref": "post.status",
            "operator": "eq",
            "value": "publish"
          }
        ],
        "order_by": [
          {
            "field_ref": "post.date",
            "direction": "desc"
          }
        ],
        "page_size": 20,
        "offset": 0
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
      }
    }
  }
}
```

Existing literal-only Definitions remain valid and unchanged.

## 5. Exact render-source keys

V1 accepts only:

- `kind`
- `blueprint_id`
- `blueprint_revision`
- `query`
- `bindings`

Unknown keys fail closed.

`kind` remains exactly:

`component_blueprint`

No new render kind is promoted.

## 6. Query object

If present, `query` MUST be an object/map with exactly these admitted keys:

- `contract_version`
- `source_ref`
- `filters`
- `order_by`
- `page_size`
- `offset`

Unknown keys fail closed.

### contract_version

Must equal:

`QueryReadConsumerInterface::CONTRACT_VERSION === 1`

No version fallback or coercion.

### source_ref

Must match the Data Source identifier grammar:

`^[a-z][a-z0-9._-]{1,127}$`

It is a stable semantic reference only. It is never interpreted as:

- a class;
- callback;
- function;
- file;
- URL;
- SQL fragment;
- provider executable id;
- credential reference.

### Authored projection

There is **no authored `projection` key** in Surface 10 V1.

The Query projection is derived as the unique lexically sorted set of all `field_ref` values used by `source: query` binding envelopes.

This prevents request projection from drifting away from actual Blueprint binding use.

### search

Search is not admitted in V1.

Surface 10 must not author or forward the Query consumer `search` key.

## 7. Bounded filters

`filters` is optional and defaults to `[]`.

Maximum Surface 10 V1 filters:

**8**

Each filter accepts exactly:

- `field_ref`
- `operator`
- `value`

`field_ref` uses:

`^[a-z][a-z0-9._-]{0,127}$`

Operators are exactly:

- `eq`
- `neq`
- `in`
- `not_in`

For `eq|neq`:

- value may be string, integer, float, boolean or null;
- arrays/objects/resources are rejected.

For `in|not_in`:

- value must be a non-empty list;
- maximum 20 values;
- every member must be a non-null scalar;
- nested lists/objects/resources are rejected.

No coercion is permitted.

## 8. Bounded ordering

`order_by` is optional and defaults to `[]`.

Maximum Surface 10 V1 order fields:

**2**

Each entry accepts exactly:

- `field_ref`
- `direction`

Direction is exactly:

- `asc`
- `desc`

Duplicate order fields fail closed.

## 9. Page and offset bounds

`page_size` is optional and defaults to **20**.

Surface 10 V1 range:

**1..50**

`offset` is optional and defaults to **0**.

Surface 10 V1 range:

**0..1000**

The later implementation must also respect the lower effective bound from the resolved `DataSourceDescriptor::maxPageSize`.

The final Query request must remain within **8192 encoded bytes**, which is intentionally below the shared Query consumer's 16384-byte maximum.

## 10. Binding envelope forms

Blueprint binding keys must still exactly match the resolved Blueprint binding schema.

Every binding uses one of two exact envelope forms.

### Literal

```json
{
  "source": "literal",
  "value": "Safe authored value"
}
```

Exact keys:

- `source`
- `value`

Existing literal validation remains authoritative.

### Query

```json
{
  "source": "query",
  "field_ref": "post.title",
  "mode": "first"
}
```

Exact keys:

- `source`
- `field_ref`
- `mode`

No `value` key is allowed.

Modes are exactly:

- `first`
- `column`

A Query binding requires a `render_source.query` object.

A `render_source.query` object requires at least one Query binding.

Unknown envelope keys or source values fail closed.

Mixed literal and Query bindings are allowed when every Blueprint binding is satisfied exactly once.

## 11. Data Source preflight

Before Query execution, the later bounded executor must resolve `source_ref` through `DataSourceRegistryInterface`.

It must fail closed when:

- the source is not registered;
- the descriptor is unavailable/degraded;
- canonical Policy authorization is not required;
- an authorization mapping is absent;
- requested page size exceeds descriptor `maxPageSize`;
- a projected, filtered or ordered field is absent from descriptor `fieldSchema`;
- a Query-bound field logical type is not compatible with its Blueprint binding type.

The registry is used for descriptor truth only. It does not execute the read.

## 12. Logical type compatibility

V1 admits these exact Data Source logical-type → Blueprint binding-type relationships.

### mode = first

- `string` → `string`
- `datetime` → `string`
- `integer` → `int`
- `float` → `float`
- `boolean` → `bool`

### mode = column

- `string` → `string_list`
- `datetime` → `string_list`
- `integer` → `int_list`

No `float_list` or `bool_list` exists in shared Blueprint V1, so those column mappings are rejected.

Unknown logical types fail closed.

Actual runtime row values must also exactly match the expected scalar type. Descriptor metadata never permits coercion.

## 13. Query request projection

The later implementation constructs the canonical Query consumer request as:

- `contract_version` = 1;
- `source_ref` = normalized query source ref;
- `projection` = unique lexically sorted Query binding field refs;
- normalized bounded filters;
- no search;
- normalized bounded order;
- bounded page size;
- bounded offset.

Surface 10 may not add provider-specific arguments.

## 14. Row/cardinality semantics

A successful Query consumer result must still satisfy the Query-owned result contract.

Surface 10 additionally requires:

- `ok === true`;
- returned rows are a list;
- `returned === count(rows)`;
- `returned <= page_size`;
- returned `source_ref` equals requested source;
- returned `projection` equals the derived projection.

### Empty result

Zero rows fail closed for V1.

No renderer call occurs.

A future explicit empty-state tranche may change this; V1 does not invent one.

### mode = first

Use row index `0` only.

Additional rows may exist but are ignored for that binding.

### mode = column

Collect the mapped field from every returned row, preserving Query result order.

The list must be non-empty and can contain at most 50 items due to the Surface 10 page-size bound.

## 15. Query-derived value safety

Query output is data, not trusted HTML.

Every resolved scalar/list value must pass the same non-executable safety boundary already enforced by the Dashboard Widget render-source descriptor.

At minimum, query-derived strings containing executable authored markers such as PHP open tags, `<script`, or `javascript:` must fail closed before renderer invocation.

No Query/provider error text or raw payload becomes a render binding.

The existing component renderer remains responsible for context-appropriate output escaping and URL rules.

## 16. Execution ordering

The later runtime implementation must preserve this order:

1. load Definition;
2. registration compilation;
3. visibility compilation;
4. visibility evaluation;
5. if denied, stop;
6. render-source/query-binding compilation;
7. Data Source descriptor preflight;
8. execute Query through `QueryReadConsumerInterface::read()` with the exact incoming `ExecutionContext`;
9. map result rows into Blueprint bindings;
10. build the resolved trusted render descriptor/input;
11. call the existing shared renderer with the **same exact `ExecutionContext` instance**.

No Query read occurs before visibility allows.

No renderer call occurs when Query binding resolution fails.

## 17. Failure mapping

Expected malformed Definition/query-binding metadata remains:

`invalid_definition`

Runtime Data Source/Query failures, including unknown/unavailable source, missing authorization mapping, Query `ok:false`, provider failure, row mismatch, value-type mismatch or unsafe Query-derived value map to the existing bounded:

`runtime_failure`

V1 does not add a new public result status.

Query/provider error codes, messages, paths, stack traces or payloads are not exposed through `DashboardWidgetRuntimeRenderResult`.

## 18. No cache/refresh promotion

Even if a resolved Data Source descriptor is cacheable, Surface 10 V1 does not author or execute:

- TTL;
- stale state;
- refresh interval;
- retries;
- background refresh;
- cache invalidation;
- generation keys.

Those remain a later `dashboard-widgets.refresh_cache.policy` tranche.

## 19. Generic provider boundary remains blocked

This contract does not promote:

- `dashboard-widgets.type.registered_provider`;
- native render/control/callback providers;
- direct `IntegrationRegistry` execution;
- arbitrary provider keys as executable references.

Provider execution occurs only behind the Query-owned public read-consumer contract.

## 20. Exact later implementation allowlist

Dependency-gated Issue **#1215 — Dashboard Widgets: bounded Query/Data-Source binding source V1** remains the only later Dashboard Widgets source tranche, but it is **dual-gated** by this contract and Query activation prerequisite #1217.

It may modify/create exactly:

1. `frameworks/Modules/DashboardWidgets/DashboardWidgetRenderSourceCompiler.php`
2. `frameworks/Modules/DashboardWidgets/DashboardWidgetRenderSourceDescriptor.php`
3. `frameworks/Modules/DashboardWidgets/DashboardWidgetQueryBindingDescriptor.php`
4. `frameworks/Modules/DashboardWidgets/DashboardWidgetQueryBindingExecutor.php`
5. `frameworks/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderExecutor.php`
6. `frameworks/Modules/DashboardWidgets/DashboardWidgetsModule.php`
7. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRenderSourceCompilerTest.php`
8. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetQueryBindingExecutorTest.php`
9. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetRuntimeRenderExecutorTest.php`
10. `tests/Unit/Modules/DashboardWidgets/DashboardWidgetsModuleTest.php`

No other Dashboard Widgets source/test file is authorized by V1.

Issue #1215 MUST remain unclaimable until both conditions are terminal PASS:

1. Issue #1213 / PR #1216 merges this corrected contract; and
2. Issue #1217 activates the existing QueryModule before DashboardWidgetsModule and proves the canonical read-consumer service is available.

## 21. Minimum later test matrix

The source tranche must prove at least:

- literal-only backward compatibility;
- query object without Query bindings rejects;
- Query binding without query object rejects;
- unknown query/binding keys reject;
- authored projection/search rejects;
- source-ref grammar;
- filter/order/page/offset bounds;
- derived projection dedupe + lexical sort;
- unknown/degraded source rejects at runtime;
- missing authorization mapping rejects;
- descriptor page-size bound enforced;
- descriptor field-schema presence enforced;
- exact logical-type compatibility;
- first-row scalar mapping;
- ordered column list mapping;
- mixed literal + Query bindings;
- zero rows fail closed;
- Query `ok:false` fails closed;
- row/projection/source mismatch fails closed;
- unsafe Query-derived strings fail closed;
- visibility denial causes zero Query reads;
- Query failure causes zero renderer calls;
- exact `ExecutionContext` instance reaches Query and renderer;
- successful resolution renders through the existing shared renderer only.

## 22. Explicitly still blocked

V1 does not authorize:

- generic registered-provider execution;
- direct IntegrationRegistry execution;
- Safe HTTP/remote/RSS/iframe;
- Listings execution;
- shortcode/block/action execution;
- asset registration/enqueue;
- refresh/cache/background jobs;
- Definition mutation;
- user-preference mutation;
- shared Platform source changes;
- P-006 runtime;
- full-parity RUNTIME_CERTIFIED/product certification;
- deploy or release.

## 23. Promotion verdict

When this corrected contract merges with exact-head Governance/Architecture green, zero unresolved review threads/comments and zero behind, promote only:

`CONTRACT_FROZEN_AWAITING_QUERY_MODULE_ACTIVATION_V1`

That terminal contract merge makes Issue #1217 claimable. It does **not** make Issue #1215 claimable.

Only after Issue #1217 itself closes terminal PASS with the bounded central Pro activation may the repository promote:

`READY_FOR_BOUNDED_QUERY_DATA_SOURCE_BINDING_SOURCE_V1`

Only then may Issue #1215 be claimed.
