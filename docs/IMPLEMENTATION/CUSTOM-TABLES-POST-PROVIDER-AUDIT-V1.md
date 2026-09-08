# Custom Tables — Post-Provider Next-Lane Audit V1

Status: **SUPERVISOR AUDIT / promotes bounded parallel prerequisites only**  
Issue: **#397**  
Exact-main audit anchor: **`b0419289a7f4f205e1fa40dd5c3158b4927badd6`**  
Date: **2026-09-08**

## Verdict

Surface 7 Custom Tables remains **ACTIVE / NOT PASS**.

The provider capability + pure DDL preview compiler from Issue #393 / merged PR #395 is promoted evidence. Its output remains review-only: `execution_allowed=false`, no database adapter/query dependency, and blocked/recovery/high-risk/unsupported semantics fail closed.

The next dependency-safe work is not a physical DDL executor. Three execution-free prerequisites may now proceed in parallel because they have separate ownership boundaries and compose only immutable contracts:

1. **Migration Run State V1** — immutable run envelope/state machine and legal transition guards.
2. **Precondition Contract V1** — typed precondition descriptors and deterministic evaluation-result aggregation.
3. **Recovery + Revalidation V1** — recovery classification plus reviewed-source fingerprint/revision revalidation decisions.

All three lanes must remain free of SQL/DDL dispatch, database persistence, job execution, Backup provider calls, row scans/backfills and public mutation surfaces.

## Promoted evidence reviewed

- #383 — canonical table Definition + deterministic schema descriptor.
- #387 — observed-schema normalization + pure Migration Plan/risk semantics.
- #390 — post-plan exact-main audit.
- #391 — trusted CT1/PT-E identity + read-only MySQL/MariaDB introspection.
- #394 — post-introspection exact-main audit.
- #395 — server-selected provider capability profile + deterministic execution-free provider statement previews.
- #396 — README reconciliation after provider-preview promotion.

The final #395 exact head passed PHP Quality Toolchain, Architecture Guards, Platform Compatibility Matrix and Distributable Package, with no unresolved review threads before merge.

## Why these prerequisites can run in parallel

The accepted Custom Tables migration language requires a reviewed plan to remain distinct from physical execution, requires source-state revalidation before mutation, defines explicit migration-run lifecycle states, defines machine-checkable preconditions, and requires recovery semantics without promising universal transactional rollback.

Those concerns can be modeled independently before any executor exists:

### Lane A — Migration Run State V1

Owned namespace:

- `frameworks/Modules/CustomTables/Migration/Run/**`
- focused tests under `tests/Unit/Modules/CustomTables/Run/**`

Required bounded behavior:

- immutable run identity bound to plan fingerprint/table/target generation;
- canonical states from the accepted migration language;
- explicit legal transition matrix;
- terminal-state protection;
- cancellation only before mutation/running unsafe boundary;
- deterministic canonical serialization/fingerprint where applicable;
- zero repository/database persistence and zero execution.

### Lane B — Precondition Contract V1

Owned namespace:

- `frameworks/Modules/CustomTables/Migration/Precondition/**`
- focused tests under `tests/Unit/Modules/CustomTables/Precondition/**`

Required bounded behavior:

- typed allowlisted precondition kinds;
- immutable expected facts/targets without raw SQL or row values;
- deterministic aggregate verdict: satisfied / blocked / unsupported;
- evidence metadata bounded to counts/ranges/fingerprints, never arbitrary row payloads;
- no database reads, no Query runtime, no scanning/backfill execution.

### Lane C — Recovery + Revalidation V1

Owned namespace:

- `frameworks/Modules/CustomTables/Migration/Recovery/**`
- focused tests under `tests/Unit/Modules/CustomTables/Recovery/**`

Required bounded behavior:

- recovery classes: trivially reversible, reversible while retained copy exists, Backup-recovery-required, irreversible after recovery window;
- source observed fingerprint + target Definition revision/schema-version revalidation decision;
- provider-profile identity/fingerprint consistency where available;
- stale/mismatched reviewed source fails closed;
- no Backup provider invocation, restore execution, SQL/DDL, persistence or leases.

## Explicitly still blocked

This audit does **not** authorize:

- physical `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` execution;
- dispatching provider preview SQL through `$wpdb->query()` or any database mutation API;
- migration-run/applied-generation persistence;
- leases/locks/retry persistence or Action Scheduler execution;
- row-count/data precondition scans, duplicate/null/range/max-length queries;
- backfill, deduplication, copy/shadow/swap execution;
- Backup creation, verification or restore calls;
- row CRUD/Data Source/Query provider runtime;
- CT2/PT-D or CT3 topology/runtime work;
- external-table adoption;
- Custom Tables admin/REST/Ability mutation surfaces;
- `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release approval.

## Merge/revalidation rule

Each worker PR must:

1. rebase/reconcile against latest `main` without force;
2. touch only its owned namespace plus focused tests/non-shared implementation notes;
3. pass all applicable exact-head hosted CI;
4. have zero unresolved review threads;
5. contain no database execution/persistence primitives.

After all three prerequisites promote, another exact-main Supervisor audit is mandatory before any execution, persistence, scanning, lease or recovery-provider lane is opened.

## Closure

When this audit PR is promoted:

- Issue #393 / PR #395 becomes historical DONE evidence in the coordination queue;
- Issue #397 becomes DONE Supervisor evidence;
- exactly three execution-free `ANY` worker slots become dependency-ready in parallel;
- physical mutation remains blocked.