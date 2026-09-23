# Dashboard Widgets — Query/Data-Source Transition Audit V1

Status: **fresh exact-main transition audit / contract only**
Surface: **10 — Dashboard Widgets**
Issue: **#1212**
Exact audited main: `27a8be9bb09a3f1f1c907506436600772af01ba6`

## 1. Purpose

Identify the smallest safe next Dashboard Widgets tranche after Bounded Site Targeting Source V1 and its shared-truth reconciliation.

This audit does not authorize runtime/source implementation.

## 2. Terminal prerequisite truth

The audited main includes:

- Issue #1203 / PR #1209 — Bounded Site Targeting Source V1 terminal PASS;
- Issue #1210 / PR #1211 — post-merge shared-truth reconciliation terminal PASS;
- PR #1211 exact head `252556e49a7a7efe77a3d3f24c69cf90873e04c0`;
- Governance Gate `35918566515` PASS;
- Architecture Guards `35918566390` PASS;
- zero unresolved review threads/comments and zero commits behind at the merge gate;
- PR #1211 merged as `27a8be9bb09a3f1f1c907506436600772af01ba6`.

RB-0046 may therefore be reconciled terminal PASS.

## 3. Current Surface 10 runtime boundary

Exact main currently supports:

- trusted Surface 10 Component Blueprints;
- literal-only render-source bindings;
- trusted component renderer registration;
- bounded runtime render execution;
- bounded native WordPress Dashboard registration;
- visibility evaluation;
- bounded site targeting.

`DashboardWidgetRenderSourceCompiler` accepts only `component_blueprint` render sources and only `source: literal` binding envelopes.

The following remain rejected/deferred at the Surface 10 render-source boundary:

- `query`;
- `provider`;
- `data_source`;
- `remote`;
- callback/class/file/URL executable references.

## 4. Canonical Atomic Option evidence

The authoritative Surface 10 Atomic Option Contract remains 18 normalized options over 123 reviewed Bank records.

Relevant integration contracts include:

- `dashboard-widgets.sources.policy`;
- `dashboard-widgets.providers.registry`;
- `dashboard-widgets.types.policy`.

Relevant projected Bank records include:

- `dashboard-widgets.type.registered_provider`;
- `dashboard-widgets.source.query_ref` → owner `query`;
- `dashboard-widgets.source.data_source_ref`;
- `dashboard-widgets.native.render_provider`;
- `dashboard-widgets.native.control_provider`;
- `dashboard-widgets.native.callback_args_provider`.

These are contract evidence, not proof that direct provider execution is safe.

## 5. Canonical shared read seams

### Data Source Registry

The Platform already exposes:

- `DataSourceRegistryInterface`;
- `DataSourceRegistry`;
- `DataSourceDescriptor`;
- `DataSourceAuthorizationMapping`.

A Data Source descriptor:

- has a stable semantic id;
- declares field schema and bounded capabilities;
- must require canonical Policy authorization;
- may carry an explicit authorization mapping;
- carries availability and bounded page/batch metadata.

This is descriptor/discovery truth. It is not itself an arbitrary execution API.

### Query bounded read consumer

Query already exposes:

- `QueryReadConsumerInterface::CONTRACT_VERSION = 1`;
- `QueryModule::SERVICE_READ_CONSUMER = module.query.read-consumer`.

The public read contract accepts only bounded semantic references and keeps:

- Query validation;
- Policy authorization;
- provider planning;
- provider execution

inside the canonical Query owner.

The consumer bounds projection, filters, filter values, order fields, page size, offset and search length, and returns a structured success/failure envelope.

This is a suitable cross-surface execution boundary because Surface 10 does not receive raw provider arguments or private Query internals.

## 6. Integration Registry finding

`IntegrationRegistry` exposes metadata registration/discovery:

- register integration descriptors;
- get by key;
- list integrations supporting a capability.

`IntegrationDescriptor` preserves provider identity, owner surface, capabilities, opaque credential reference, Surface 23 transport ownership and external-authority semantics.

There is no generic IntegrationRegistry execution contract.

Therefore Surface 10 MUST NOT reinterpret `IntegrationRegistry` as an executable provider registry.

## 7. Audit conclusion

The next safe bounded lane is **not** generic `registered_provider` execution.

The next safe lane is a contract that may define how Dashboard Widgets can bind trusted component inputs to **canonical Query/Data Source reads** through the existing public Query read-consumer boundary.

The contract must preserve canonical ownership:

- Data Source Registry provides descriptor/discovery truth;
- Query owns validation, authorization, planning and read execution;
- Surface 10 owns only widget-side stable references, bounded request intent, row-to-binding projection and final trusted rendering.

## 8. Required next contract

Dependency-gated Issue **#1213 — Dashboard Widgets: bounded Query/Data-Source binding contract V1** is the only next product slot authorized by this audit verdict.

The contract must define, before source code:

- exact Definition payload shape;
- stable `source_ref` grammar;
- exact Query consumer contract version;
- bounded projection/filter/order/page/offset subset;
- deterministic row/cardinality model;
- deterministic row → existing Blueprint binding mapping;
- exact type compatibility;
- empty/multi-row behavior;
- Query failure and unavailable Data Source behavior;
- exact `ExecutionContext` propagation;
- authorization ordering;
- no hidden fallback to provider/remote execution.

No implementation file allowlist is promoted by this audit; #1213 must freeze one before any source Issue exists.

## 9. Explicitly blocked

This audit does not authorize:

- `dashboard-widgets.type.registered_provider` execution;
- generic provider callback execution;
- direct IntegrationRegistry execution;
- remote/Safe HTTP/RSS/iframe execution;
- shortcode/block/action execution;
- Listings execution unless a separately proven typed public contract is admitted;
- asset registration/enqueue;
- refresh/cache/background jobs;
- Definition mutation;
- user-preference mutation;
- shared Platform source changes;
- P-006 runtime;
- RUNTIME_CERTIFIED/product parity;
- deploy or release.

## 10. Verdict

`READY_FOR_BOUNDED_QUERY_DATA_SOURCE_BINDING_CONTRACT_V1`

This verdict authorizes only dependency-gated contract work on #1213 after this audit PR itself merges terminal green.
