# Query Gate C Closure Audit V1

Status: **ACTIVE / NOT PASS**  
Parent tracker: GitHub Issue #66  
Audit anchor: `main @ bb62f4aa223db3156ccc7ef0454d27226a2c0869`

This document audits the bounded Surface 6 Query implementation against the Gate C baseline declared by #66. It is evidence-only. It does not authorize downstream Admin Columns, Dynamic Listings, Status runtime, public Query execution, or broader product-parity claims.

## Decision

Gate C must remain **ACTIVE / NOT PASS** at this anchor.

Two baseline closure blockers are explicit:

1. **Fields predicates are not runtime-certified.** The typed AST accepts the `field` predicate class, but the native WordPress posts compiler still rejects `QueryPredicateType::Field`. Query also has no accepted Fields-owned public query-consumer contract on this anchor. Issue #161 owns that missing cross-surface contract; later Query-side resolution must consume it rather than infer Field storage/meta semantics.
2. **Canonical admin integration is not complete.** PR #160 promoted an accessible non-runtime Query authoring scaffold, but it deliberately does not register a WordPress admin route, project a server-owned Data Source bootstrap, add the Query bundle to the production build/enqueue path, persist Query definitions, or expose execution. Issue #162 owns the canonical route/bootstrap/build integration while keeping execution disabled.

The remaining criteria are either certified for the bounded V1 baseline or explicitly bounded below.

## Criterion matrix

| #66 Gate C criterion | Audit status | Merged evidence / current-main contract | Remaining boundary |
| --- | --- | --- | --- |
| Typed Query AST | **CERTIFIED V1** | `QueryDefinition`, `QuerySourceReference`, `QueryPredicate`, `QueryPredicateType`, `QueryOrderClause`, `QueryPagination`, and `QueryAstValidator` provide typed canonical Query semantics and fail-closed validation. | This is a bounded V1 AST, not all Query Bank/product-parity semantics. |
| Provider / Data Source adapter | **CERTIFIED V1** | `QueryModule` registers canonical `wordpress.posts`; `WordPressPostsQueryCompiler` compiles only supported source semantics; `WordPressPostsQueryExecutor` executes only the certified provider plan. PR #147 promoted execution and PR #148 removed accidental total-count semantics. | Additional providers remain unsupported unless separately owned/certified. |
| Validation + Policy boundary | **CERTIFIED V1** | `QueryAstValidator` resolves through the canonical Data Source registry. `QueryAuthorizedPlanner` performs canonical Policy authorization before compilation; `QueryAuthorizedExecutor` plans before provider execution. PR #158 real-WordPress evidence observes Policy-before-provider ordering. | No public/admin execution endpoint is certified. |
| Sorting / filtering / search / pagination | **CERTIFIED BOUNDED V1** | Native posts compiler/executor support finite certified post fields, comparison/set predicates, bounded text search, field ordering and offset pagination. PR #158 proves status filtering, text search, deterministic ordering, offset behavior, ID-only projection and the page-size ceiling on real WordPress. | Cursor, parameters, taxonomy/date/provider-extension and other unsupported grammar/provider cases remain fail-closed unless separately promoted. |
| Relations predicates | **CERTIFIED BOUNDED V1** | `RelationQueryConsumerInterface` is the public Relations owner seam. `QueryRelationPredicateResolver` requires a bounded `post.id` anchor set, consumes the owner contract and rewrites successful resolution to bounded post IDs before native provider compilation. PR #154 plus Supervisor wiring PR #156 promoted this path. | Richer traversal/provider semantics remain outside this bounded baseline. |
| Fields predicates | **BLOCKED** | `QueryAstValidator` has a typed `field` predicate grammar. Current `WordPressPostsQueryCompiler` explicitly rejects `QueryPredicateType::Field`; current `QueryModule` consumes Relations but no Fields public query consumer. | #161 must establish the Fields-owned bounded public contract; a later Query resolver/runtime tranche must consume it. No direct `meta_query`, raw SQL or storage-key inference is acceptable. |
| Cache / diagnostics rules | **CERTIFIED RULES; RUNTIME DISABLED** | PR #159 added `QueryCacheContract`, `QueryCacheDecision` and `QueryDiagnosticSnapshot` using the shared Platform cache value objects. Eligibility is fail-closed on source mismatch, non-cacheable source, missing generation keys, missing scope/principal, invalid TTL or unserializable semantics. | `wordpress.posts` remains `cacheable=false`; no cache get/put/delete/invalidation or public diagnostics are enabled. Gate C baseline requires rules, not an invented cache runtime claim. |
| Performance / no-unbounded-query safeguards | **CERTIFIED BOUNDED V1** | `QueryValidationBudget`, Data Source max page/batch bounds, compiler/executor allowlists and Relations bounded-anchor requirements prevent the promoted execution path from widening silently. PR #158 provides deterministic call/size evidence and real-WordPress execution at page size 100; page size 101 is blocked before provider execution. | Evidence is deterministic/bounded rather than wall-clock benchmarking. New providers/predicate classes need their own budgets. |
| Canonical admin UX | **PARTIAL / BLOCKED** | PR #160 promoted `admin-ui/src/query.ts` and `query.scss`: accessible source/projection/filter/order/offset authoring, fail-closed bootstrap parsing, read-only AST preview and visibly disabled execution. Exact-head Architecture, Browser E2E Accessibility and Distributable checks passed. | #162 must register the canonical WordPress admin page, server-project validated source metadata, add the production Query build/enqueue entry and add packaged route evidence. Execution remains disabled. |
| Exact-head evidence | **CERTIFIED PER PROMOTED SLICE; OVERALL CLOSURE PENDING** | #147/#148, #154/#156, #158, #159 and #160 were merged only after their applicable exact-head gates and scope review. #158 adds a dedicated WordPress/PHP/MySQL/MariaDB native-execution matrix. | Gate C itself cannot be marked PASS until #161/#162 and any blocker discovered by their integration are merged and a final current-main audit is clean. |

## Current source-level blocker proof

### Fields predicate

The validator can construct `QueryPredicateType::Field`, but the native compiler's predicate dispatch still treats that type as unsupported. This is intentional fail-closed behavior at this anchor.

The `wordpress.posts` Data Source descriptor currently exposes only canonical `post.*` fields. A custom Field reference therefore also needs an owner-backed resolution/validation seam before it can become executable. #161 must define that seam on the Fields side. Query must not discover post-meta keys or duplicate Surface 3 target/storage rules.

### Admin integration

The #160 scaffold intentionally states that it does not register a WordPress admin route, enqueue a Query bundle, save definitions or expose execution. The production admin shell already has module-owned submenu patterns (for example the certified Fields admin controller), so #162 can integrate Query through those patterns without turning the internal executor into a public endpoint.

## Explicit deferred semantics

The following are **not** represented as shipped by this audit:

- aggregation or total-count execution;
- cursor pagination or parameter binding;
- arbitrary taxonomy/date/provider-extension execution;
- cache reads/writes, persistent cache invalidation, or public Query diagnostics;
- public REST/AJAX/CLI/workflow/AI Query execution;
- unsupported provider-owned Field types or storage modes;
- full Query Options Bank parity or `PRODUCT_PARITY_CERTIFIED`.

PR #148 is specifically important evidence that scope rules override green CI: total-count behavior was removed because the execution V1 tranche explicitly excluded aggregation/total-count semantics.

## Safe parallel closure plan at this audit anchor

The Supervisor queue authorizes three disjoint lanes after PR #164 coordination reconciliation:

1. **#161 — Fields public query consumer V1**: Fields/Contracts owner boundary only.
2. **#162 — Query admin integration V1**: Query admin/build/route boundary only; execution disabled.
3. **#163 — this evidence audit**: this new document only.

A Query-side Field predicate resolver/runtime tranche is **dependency-blocked on #161** and must not be started in parallel with the owner contract as if its shape were already accepted.

## Gate C exit rule

After #161 and #162 are promoted, re-audit exact current `main`. Gate C may pass only if:

- the Fields predicate path is owner-contract-backed, bounded, authorized and exact-head green;
- the canonical Query admin route/bootstrap/build integration is packaged/accessibility green while execution exposure remains within accepted scope;
- no criterion in the #66 Gate C baseline remains unsupported for the declared bounded V1 baseline;
- `CHECKPOINT.md`, the coordination queue and parent #66 are synchronized to the same exact-main truth.

Until then: **Gate C ACTIVE / NOT PASS; Gate D Admin Columns, Gate E Dynamic Listings and Status runtime remain blocked.**
