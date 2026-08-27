# WPEssential — Performance Budgets & Scale Planning

Status: Phase 0 planning. No runtime implementation authorized.

## Principle
Performance is a product contract, not a final optimization pass. Optional modules must impose effectively zero frontend/wp-admin cost when disabled or unused.

## Global budgets
### Asset isolation
- No optional-module JS/CSS on unrelated wp-admin screens.
- No frontend WPEssential asset unless a rendered WPE feature on that request requires it.
- Shared runtime is loaded once even when several modules use it.
- Third-party builder adapter assets load only inside that builder/integration context.

### HTTP/DB behavior
- No unbounded list endpoint or table view.
- All large collections paginate.
- Avoid N+1 by batch/prefetch contracts.
- Remote services never block ordinary admin navigation where cached/deferred behavior is possible.
- Expensive preview/explain operations have explicit row/time ceilings.

### Admin UX targets (planning targets, to be benchmarked)
- Module list/configuration screens should remain interactive with thousands of definitions through server pagination/DataViews rather than loading all records.
- Save/publish operations should not perform unrelated expensive reconciliation synchronously.
- Large background work uses Job Service.

## Reference scale fixtures
Future benchmark fixtures must include at minimum:
- 100, 1,000 and 10,000 WPE definitions;
- 100k posts;
- 100k users where identity/member tests apply;
- 1M postmeta-like rows;
- 100k relation edges and a higher stress fixture;
- 100k custom-table rows, then 1M for index-sensitive tests;
- 100k form entries/workflow runs where applicable;
- 100k membership enrollments + entitlements;
- 1M chat messages for pagination/index stress;
- large media library fixtures;
- large backup/import fixtures constrained by shared-hosting memory.

These are test fixtures/targets, not claims that every hosting plan can process all workloads equally.

## Module-specific budgets/risks

### CPT / Taxonomy / Fields
- registration definitions are cached/compiled; no DB query per registered field where avoidable.
- rewrite flushing only on relevant structural changes, never normal requests.

### Relations
- no serialized full graph in meta.
- indexes support both traversal directions and relation type.
- cardinality checks avoid whole-table scans.

### Query Builder
- preview defaults bounded.
- runtime queries declare max rows/page size.
- public queries can have stricter complexity limits.
- cache requires known invalidation; indefinite stale authorization-sensitive cache forbidden.
- EXPLAIN/diagnostics identify missing indexes/full scans where provider supports it.

### Custom Tables
- schema designer requires index review for filter/sort/reference columns.
- large migration operations cannot be assumed synchronous.
- row browser paginates at source.

### Admin Columns
- column renderers receive batch context.
- relation/meta/user/term values prefetch rather than per-row repeated queries.
- expensive remote/query columns may lazy-load with explicit UX.
- sorting/filtering must occur in source query when possible rather than loading all records into PHP.

### Listings/Dashboards
- SSR page/query budgets.
- load-more/infinite-scroll pages remain bounded.
- dynamic filters debounce/cancel obsolete requests where frontend behavior requires it.

### Forms/Workflow
- form submission authorization/validation remains synchronous; non-critical integrations run async.
- workflow run/step history paginated.
- queue backpressure visible.

### Cron/Jobs
- no overlapping duplicate business jobs when a lock/idempotency key is required.
- chunk large tasks by memory/time budget.
- queue lag and failure rate observable.

### Notifications/Email
- recipient fan-out uses chunks/jobs.
- digest computation avoids one query per user.

### Chat
- cursor/index pagination preferred for large message history.
- unread counts use scalable state/index strategy, not counting entire conversation history repeatedly.
- polling interval adapts/background-throttles; realtime adapter optional.

### REST API Builder
- max page sizes.
- rate/complexity policy.
- no arbitrary caller-controlled deep relation expansion without depth/field limits.
- response cache cannot leak permission-specific data.

### Backup/Import
- streaming/chunked processing.
- never require entire archive/export in memory.
- provider upload chunk/multipart where available.
- resumability considered part of reliability at large sizes.

### Membership
Authorization hot path is security-sensitive and performance-sensitive.
- access check must not issue an unbounded query graph.
- active enrollment/entitlement indexes must support user + status + expiry/source lookups.
- revocation/force-deny invalidation is immediate enough that stale access is treated as a security defect.
- `access explain` may be more expensive than normal `access check`; do not execute full explanation on every request.
- team seat counters require concurrency-safe strategy, not full recount on every invite.

### Protector
- request-level rules need compiled/cacheable form.
- IP/path policies avoid slow remote lookups in hot path.
- logs cannot become unbounded synchronous inserts without retention/batching strategy.

### Watermark
- generation/regeneration is background work for batches.
- original upload path is not repeatedly reprocessed on every frontend request.

## Proposed measurement categories
Record during future implementation:
- PHP wall time and peak memory;
- DB query count/time;
- external request count/time;
- JS initial/async chunk bytes;
- CSS bytes;
- interaction latency for large lists;
- job throughput/lag/retry rate;
- cache hit/miss and invalidation latency where applicable.

## Regression gates
A PR that causes a material regression in a protected benchmark requires:
- measured evidence;
- explanation;
- mitigation or explicit accepted exception.

Exact numeric thresholds will be locked after baseline implementation benchmarks; Phase 0 deliberately avoids inventing false universal millisecond limits across shared hosts.

## Release performance evidence
Before a module is marketed as production-ready:
- small + representative + stress fixture tests exist;
- no obvious N+1/unbounded path remains;
- module-disabled asset/query overhead verified;
- background tasks demonstrate chunk/recovery behavior where relevant;
- authorization caches prove prompt invalidation;
- benchmark results/known hosting limitations documented.