# WPEssential — P-010 Relations Executable Evidence Protocol

Status: **Phase 0 paper evidence contract / execution NOT AUTHORIZED**  
Date: 2026-08-28  
Related: ADR-0074, Relations PT-D/PT-E Physical Benchmark Profile, Query ADR-0086, ADR-0014.

## Purpose

Define the exact future fixture, concurrency, query-plan, lifecycle and security evidence required before selecting final Relations physical storage from R1/R2 and any exceptional R3 optimization.

No table, query, lock or benchmark is executed by this document.

## Profiles under comparison

- **R1 — PT-D shared scoped universal edge table** — first baseline.
- **R2 — PT-E per-site universal edge table** — mandatory comparison.
- **R3 — per-relation physical table** — exceptional high-scale profile only when justified.
- **R4 — native/meta reference** — interoperability/control baseline only where equivalent semantics exist.

Endpoint representation subprofiles E1/E2/E3 and pivot PV1/PV2/PV3 are isolated variables; benchmark runs must not combine multiple changes and attribute gains ambiguously.

## Logical fixture generator

All profiles ingest the same deterministic relation graph seed.

Endpoint classes:
- posts/CPTs;
- users;
- terms;
- WPE Custom Table rows;
- mixed typed endpoints;
- selected external/portable references for import-remap tests only.

Relation classes:
- one-to-one;
- one-to-many;
- many-to-one;
- many-to-many;
- symmetric/undirected where product semantics permit;
- ordered relations;
- pivot metadata relation;
- no-pivot relation.

## Dataset classes

### RF-S
- 100k edges;
- 10 sites;
- mostly low-degree endpoints.

### RF-M
- 1M edges;
- 100 sites;
- mixed degree distribution.

### RF-L
- 10M edges when environment permits;
- 1k-site topology;
- one noisy/high-volume site.

### RF-N
- synthetic 10k-site operational topology;
- mostly small sites;
- provisioning/migration/health/Backup/site-delete focus.

### RF-H
High-degree stress:
- selected endpoint with 10k related edges;
- selected endpoint with 100k+ related edges when environment permits;
- ordered and unordered variants.

If an environment cannot run the declared class, result is incomplete rather than re-labelled certified at a smaller scale.

## Required read/query cases

### RQ1 — forward endpoint lookup
`scope + relation + endpoint A → B list`.

### RQ2 — reverse endpoint lookup
`scope + relation + endpoint B → A list`.

### RQ3 — exact pair existence
Used for duplicate attach/idempotency and pair management.

### RQ4 — relation count/existence
Bounded Query Builder predicate case.

### RQ5 — ordered related list
Manual order enabled; stable pagination/tie semantics recorded.

### RQ6 — pivot filter/order
Only for relation profile advertising queryable pivot fields.

### RQ7 — nested relation Query
One and two traversal-depth cases through Query Service, checking query count/N+1 behavior.

### RQ8 — orphan/source cleanup lookup
Find edges referencing one endpoint resource for delete/repair.

### RQ9 — Site Backup extraction
Exactly target-site logical relation rows/portable endpoint mappings.

### RQ10 — network diagnostics aggregate
Authorized network-level counts/health without unbounded site-by-site N+1.

### RQ11 — bounded cross-site relation query
Executed only if cross-site advanced profile is explicitly under certification; otherwise expected behavior is rejection.

## Mutation/concurrency cases

### RC1 — duplicate many-to-many attach
Two workers attach same pair concurrently. Result: one logical edge according to idempotency semantics.

### RC2 — one-to-one dual attach
Two competing endpoints attempt to occupy the same constrained side. At most one valid winner; loser receives deterministic conflict/retry result.

### RC3 — one-to-many child reassignment
Concurrent old-parent detach/new-parent attach cannot leave two active parents where cardinality forbids it.

### RC4 — detach vs pivot update
Final state must be deterministic and no detached edge resurrects through stale update.

### RC5 — reorder vs detach
Ordering operation cannot retain ghost row or corrupt remaining sequence semantics.

### RC6 — endpoint delete vs attach
Lifecycle/transaction ordering prevents edge creation to endpoint already committed deleted/unavailable according to owning source semantics.

### RC7 — Relation Definition generation change vs attach
Runtime mutation validates current compatible Definition/policy generation; stale semantics cannot silently write incompatible pivot/cardinality state.

### RC8 — site deletion vs mutation
No new site-owned edge commits after destructive lifecycle boundary selected by Site Lifecycle Coordinator.

## Lock/deadlock evidence

For RC1–RC8 capture:
- transaction/repository coordination strategy;
- lock key/range shape where observable;
- deadlocks/timeouts;
- retry count;
- p95/p99 mutation latency;
- unrelated relation/site write interference;
- final cardinality/duplicate invariant scan.

A candidate may use DB uniqueness, transactional lock/recheck or relation-scoped coordination, but the chosen mechanism must be documented and pass all invariant cases.

## Index/query-plan evidence

For RQ1–RQ11 capture where supported:
- selected index;
- key length;
- rows examined/estimated;
- sort/temp-table behavior;
- query count;
- p50/p95/p99 latency;
- result cardinality;
- cold/warm state.

Required hot paths must not depend on full shared-edge scans at representative scale.

For R1, scope must be present in every site-owned hot plan. For R2, correct physical site table/prefix context must be verified explicitly.

## N+1 rejection rule

Normal relation list/query execution is rejected if query count grows linearly with result rows for relation traversal when a batch/join strategy is part of the advertised provider profile.

A bounded exceptional post-processing path must declare its row cap and cannot silently activate on large result sets.

## Endpoint representation subtests

Compare E1/E2/E3 on identical logical graph:
- storage/index width;
- numeric WP endpoint lookup;
- mixed string/reference endpoints;
- import/remap ergonomics;
- wrong-type identity collision resistance.

Portable identity remains logical/export concern even when numeric internal key wins physical performance.

## Pivot subtests

PV1 compact payload + normalized hot columns baseline.

Compare only when query requirements justify:
- pivot read/write size;
- one/two indexed pivot filters;
- pivot ordering;
- schema version change;
- non-queryable pivot payload retrieval.

Reject any design that markets arbitrary payload property as efficiently queryable without a physical projection/index.

## Cache/invalidation evidence

Fixtures:
- attach;
- detach;
- pivot update;
- reorder;
- relation Definition publish;
- endpoint authorization/Membership revoke affecting visible result;
- site deletion.

No Site A result may satisfy Site B. No stale relation/access generation may preserve removed/denied relation visibility under accepted cache semantics.

## Site lifecycle / Backup / Restore

### R1
- target-site cleanup/extraction by explicit trusted scope;
- shared table never dropped for one site;
- noisy-site delete bounded/chunked;
- transfer/remap reviewed and identity-safe.

### R2
- site table provisioning/version skew detection;
- missing/outdated table recovery;
- bounded network migration across 100/1k/10k sites;
- correct table context under nested site switching.

Both profiles must export the same logical portable relation graph and restore with:
- endpoint remap;
- cardinality conflict handling;
- duplicate-pair handling;
- pivot version handling;
- order preservation.

## Security/attack fixtures

R1:
- omit/forge site scope attempt;
- use edge ID from another site;
- wrong-site cleanup/delete;
- crafted cross-site endpoint tuple;
- unauthorized network relation mutation.

R2:
- wrong blog/prefix context;
- failed/nested `switch_to_blog()` restoration scenario;
- stale per-site schema version;
- site A edge ID submitted while site B context active.

Common:
- endpoint type confusion;
- relation Definition mismatch;
- unauthorized target endpoint;
- cross-site advanced mode remains Off unless explicitly enabled.

Wrong-scope or unauthorized rows returned/mutated: **0 required**.

## Large-network operational measurements

At 100/1k/10k sites compare:
- table count;
- migration/provisioning duration;
- schema version skew incidence under injected failures;
- network health scan query count/time;
- Site Backup extraction;
- site delete cleanup;
- one noisy-site impact on other sites;
- index/storage footprint.

R1 is rejected if noisy-neighbor/scope risk cannot be bounded. R2 is rejected if table proliferation/version skew cannot be operated safely at target scale.

## Acceptance hierarchy

1. scope/security/cardinality correctness;
2. crash/retry/lifecycle/Backup recoverability;
3. Query integration/N+1 behavior;
4. compatibility/operations;
5. latency/storage.

Fast but incorrect topology cannot win.

## Decision output after future execution

P-010 completion must publish:
- selected R profile + exact endpoint/pivot representations;
- exact DDL/types/indexes;
- concurrency/locking strategy;
- Query Service compilation strategy;
- cache-generation contract;
- supported scale/site-count profile;
- lifecycle/Backup procedure;
- rejected alternatives and reasons;
- retained fixture/results evidence.

Executed P-010 cases: **0**.

## Development gate

No table, SQL, fixture graph, lock test, Query execution, Backup/Restore or benchmark may run before explicit owner consent under ADR-0014.