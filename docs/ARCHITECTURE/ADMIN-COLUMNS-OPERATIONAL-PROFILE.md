# WPEssential — Admin Columns Operational Profile

Status: **Phase 0 paper architecture / no list-table implementation authorized**  
Date: 2026-08-28  
Related: Data & Query specs, Query AST, Field Storage, Relations, Policy, Multisite, ADR-0014.

## 1. Purpose

Define how Admin Columns can show rich WordPress/WPE data without creating N+1 queries, false sorting/filtering, unsafe inline edits, cross-site leaks or expensive shortcode side effects.

A visual column definition is not enough. Runtime must compile a bounded **Column Execution Plan** for the whole list request.

## 2. Core runtime model

`Column Set Definition → Validate/Publish → Compiled Column Plan → List-table request → Base row set → Batched source hydration → Policy filtering → Format/render`

The base list table remains authoritative for row identity/order/pagination unless a certified adapter explicitly owns the full query.

## 3. Column source classes

### AC-S1 — Native row property
Examples: post title/status/date, user name, term name.

Preferred when list adapter already has the value or can expose it without extra per-row queries.

### AC-S2 — Native metadata/taxonomy
Must use batch preload/cache-aware APIs where available.

### AC-S3 — WPE Field Storage
Adapter declares a `batch_read(ids, fields, scope)` capability or a bounded equivalent.

### AC-S4 — Relations
Relation Service must support batch endpoint lookup/count for the visible row IDs. One relation query per row is rejected.

### AC-S5 — Query-derived aggregate
Allowed only when Query provider can bind the visible row IDs as a bounded batch/set operation or materialized projection. Running one full Query definition independently for each row is rejected.

### AC-S6 — Media/image
Resolve attachment metadata/thumb URLs in batch/cache-aware way. No remote media fetch during list render.

### AC-S7 — Computed/token
Must be deterministic/bounded and declare dependencies. No arbitrary PHP/eval.

### AC-S8 — Shortcode/server-rendered block
Advanced, disabled by default. Only registered safe renderers with declared side-effect-free list mode may publish. Unknown third-party shortcode cannot be certified merely because `do_shortcode()` returns HTML.

## 4. N+1 prevention contract

Every source adapter declares:
- `batch_read` support;
- maximum batch size;
- expected query/remote-call count class;
- whether cache priming is available;
- sort/filter backend capability;
- write capability;
- Policy requirements;
- remote/external dependency class.

Compile rules:
1. collect visible row IDs first;
2. group columns by source adapter/dependency;
3. execute bounded batch hydrators;
4. cache per-request hydrated results;
5. render rows from hydrated map;
6. reject or degrade a column when adapter can only do unbounded per-row expensive work.

An adapter may use a small number of chunked calls for thousands of rows only when the list itself is bounded and benchmarked; this is not considered N+1 if calls scale by batch chunks rather than rows.

## 5. Query budget

Candidate diagnostics metrics:
- base list SQL count;
- additional DB query count;
- WPE adapter batch count;
- remote calls;
- hydrated row/field count;
- total render duration;
- memory;
- slowest column source.

Paper performance classes:
- AC-P0: already hydrated/no meaningful extra I/O;
- AC-P1: one bounded batch;
- AC-P2: small constant/chunked batches;
- AC-P3: expensive/high-cardinality, requires warning/limit;
- AC-P4: blocked for normal list render.

Exact thresholds remain executable evidence.

## 6. Sorting truth

A column can be marked sortable only when the source adapter can apply sort **before pagination** in the authoritative backend query.

Prohibited false behavior:
- sorting only currently visible browser rows;
- fetching page 1 then sorting that subset;
- sorting formatted display strings when canonical typed values differ;
- silently falling back to title/date sort.

Sort descriptor declares:
- canonical sort key;
- type/collation/null semantics;
- stable tie-breaker;
- index/cost class;
- supported directions;
- permission sensitivity.

If efficient backend sort is unavailable, UI says **Not sortable** or offers explicit expensive mode only after future evidence.

## 7. Filtering truth

A filter must alter the authoritative row query before pagination.

Filter plan:
`validated filter input → typed Query/filter node → list adapter/backend predicate`

Controls:
- exact allowed operator set;
- max list/cardinality;
- date/time timezone semantics;
- taxonomy/Relation mapping;
- index/cost warning;
- unknown input rejection;
- URL persistence only for declared filters.

Client-side hiding of rows is not a real admin filter.

## 8. Inline edit

Inline edit remains off by default.

For each editable source:
- owning Data Source/Field API is authoritative;
- actor is checked per target row;
- current row/version/fingerprint can be required for stale-write protection;
- canonical validator/sanitizer reused;
- protected/derived/read-only fields cannot be made writable by column UI;
- successful write invalidates dependent Query/Listing/Column caches;
- audit applies according to data/risk class.

Bulk edit additionally requires:
- affected-count preview;
- per-row authorization or certified bounded bulk policy;
- partial-failure report;
- no silent skip presented as full success.

## 9. Audience/visibility

Column Set audience chooses presentation only after server Policy.

A hidden column is not a security control. If the actor cannot read a source field, the hydrator must not fetch/reveal it merely because CSS/UI hides it.

Export permission is evaluated separately from screen visibility.

## 10. Relation columns

Modes:
- count only;
- first N labels;
- first N selected fields;
- aggregate;
- link to filtered target list.

Required batch primitives:
- related counts for row ID set;
- related target IDs for row ID set with bounded N;
- batched target projection.

High-degree relations must not materialize all targets when UI only shows first 3 + count.

## 11. Image/media columns

Display uses attachment identity + registered image size. Large originals are never loaded into admin table merely to show thumbnail.

Rules:
- width/height bounded;
- lazy browser loading allowed;
- alt/accessibility text;
- broken/missing attachment safe fallback;
- protected media respects access policy.

## 12. Shortcode/block safety

Advanced renderer must declare:
- side-effect-free list mode;
- deterministic/caching behavior;
- maximum render cost;
- no redirects/header mutation;
- no enqueue storm per row;
- no form submission/nonce generation dependence unless specifically certified;
- output sanitization contract.

If renderer cannot meet contract, it is preview-only/unsupported for table columns.

## 13. Export

Export modes:
- raw canonical value — default machine export;
- formatted display value — optional;
- relation IDs/labels according to explicit schema.

Controls:
- capability/policy;
- CSV formula injection mitigation;
- bounded export Job for large sets;
- no HTML intended for wp-admin copied blindly into CSV;
- secrets/protected values excluded.

## 14. Multisite

Each list adapter operates under explicit Scope.

Rules:
- Site A column hydrator cannot read Site B because numeric IDs match;
- network aggregate table requires explicit network-capable adapter;
- `switch_to_blog()` is not a hidden authorization shortcut;
- per-site cache keys include site scope;
- network screen batch hydration must remain bounded across sites.

## 15. Failure/degraded behavior

- source adapter missing → column shows safe unavailable state, not fatal list page;
- batch source timeout → affected column degraded with diagnostics;
- unauthorized value → concealed/denied according to Policy;
- invalid sort/filter → structured validation error, not raw SQL fallback;
- stale inline edit → conflict;
- third-party list adapter lacks hook → definition marked unsupported rather than fake support.

## 16. Future evidence — NOT AUTHORIZED

After consent, fixtures must include:
- 20/100/500 visible rows;
- 5/20/50 columns;
- post meta/taxonomy/media;
- WPE Field Storage;
- Relations low/high degree;
- Query aggregate column;
- one deliberately N+1 adapter to prove rejection/diagnostic;
- sorting/filtering before pagination;
- inline edit permission/stale race;
- bulk edit partial failure;
- role/audience isolation;
- cross-site same numeric IDs;
- CSV injection;
- shortcode side-effect/cost cases.

Measure SQL count, batch count, p50/p95 render time, memory, rows hydrated and remote calls.

## 17. Paper recommendation

Accept **AC1 — compiled whole-request Column Execution Plan with batch hydration** as the first operational baseline.

No column may claim real sort/filter/edit support unless the owning adapter proves backend semantics. No per-row expensive query/shortcode loop is an accepted default.

## 18. Development gate

No WordPress list-table hook, SQL, REST lazy loader, inline write, export Job, shortcode execution or benchmark is authorized before explicit owner consent under ADR-0014.