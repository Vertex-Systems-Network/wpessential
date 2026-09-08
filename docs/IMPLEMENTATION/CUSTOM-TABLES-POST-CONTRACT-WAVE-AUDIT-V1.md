# Custom Tables — Post-Contract-Wave Exact-Main Audit V1

Status: **SUPERVISOR AUDIT / execution-free composition wave authorization**  
Issue: **#413**  
Exact-main audit anchor: **`f4c86e00ab71aa93032a13e7f506a01ee54b59a4`**  
Date: **2026-09-08**

## Verdict

Surface 7 Custom Tables remains **ACTIVE / NOT PASS**.

The post-prerequisite execution-free contract wave is fully promoted on exact `main`:

1. Migration Run Repository Contract V1 — Issue #407 / merged PR #410.
2. Precondition Evaluator V1 — Issue #408 / merged PR #411.
3. Recovery Readiness V1 — Issue #409 / merged PR #412.

All three lanes remained inside their owned namespaces and did not introduce database-backed Custom Tables persistence, direct data scans, Backup-provider calls, jobs/leases or SQL/DDL execution. Their merge serialization preserved exact-main CI certification.

The next maximum conflict-safe wave remains execution-free. Four independent contracts may proceed in parallel after this Supervisor reconciliation promotes.

## Authorized parallel lanes

### Lane A — Run Transition Service V1

Owned namespace:

- `frameworks/Modules/CustomTables/Migration/Run/**`
- `tests/Unit/Modules/CustomTables/Run/**`

Required bounded behavior:

- load one `MigrationRun` from `MigrationRunRepositoryInterface`;
- derive the next run only through the canonical `MigrationRun::transition()` state machine;
- persist the replacement only through repository compare-and-swap using the observed `stateRevision`;
- stale/missing runs and illegal transitions fail closed;
- no concrete database repository, job dispatch, leases/locks or SQL/DDL.

### Lane B — Precondition Probe Registry V1

Owned namespace:

- `frameworks/Modules/CustomTables/Migration/Precondition/**`
- `tests/Unit/Modules/CustomTables/Precondition/**`

Required bounded behavior:

- explicit registration of one typed `PreconditionProbeInterface` per allowlisted `PreconditionKind`;
- duplicate registration rejected;
- missing probe produces deterministic unsupported/fail-closed evaluation;
- dispatch must preserve requirement identity and existing bounded evidence rules;
- no direct database adapter, Query runtime, scans, row payloads or SQL.

### Lane C — Recovery Evidence Source Contract V1

Owned namespace:

- `frameworks/Modules/CustomTables/Migration/Recovery/**`
- `tests/Unit/Modules/CustomTables/Recovery/**`

Required bounded behavior:

- typed source interface returning `RecoveryEvidence` for a reviewed plan fingerprint/recovery requirement;
- deterministic static/in-memory reference source only;
- mismatched/missing evidence remains fail closed through `RecoveryReadinessDecision`;
- no Backup provider invocation, snapshot creation, verification call, restore, persistence or SQL.

### Lane D — Migration Execution Readiness V1

Owned namespace:

- `frameworks/Modules/CustomTables/Migration/Readiness/**`
- `tests/Unit/Modules/CustomTables/Readiness/**`

Required bounded behavior:

- pure aggregate decision over already-produced immutable facts only:
  - `MigrationRun` state;
  - `PreconditionReport` outcome;
  - `MigrationRevalidationDecision`;
  - `RecoveryReadinessDecision`;
  - `ProviderMigrationPreview` identity/plan fingerprint;
- exact plan fingerprint consistency required across inputs;
- blocked/unsupported preconditions, stale revalidation, recovery-not-ready, wrong run state or provider-plan mismatch fail closed;
- result may mean **ready for a future separately-authorized execution layer**, never permission to dispatch statements;
- provider preview's `execution_allowed=false` invariant remains unchanged;
- no database, jobs, leases, Backup calls or SQL/DDL execution.

## Why four workers are conflict-safe

The promoted contracts now expose stable immutable boundaries in three existing namespaces plus the provider preview contract. The four new lanes own mutually exclusive code paths. The readiness lane consumes only already-promoted public immutable contracts and does not depend on the three new worker implementations, so all four can proceed from the same promoted audit anchor without cross-worker writes.

## Shared-platform reuse constraints

The repository already contains shared Database and Jobs infrastructure. This audit does not authorize a Custom Tables private duplicate database/job engine. A future persistent/run-execution lane must compose shared Platform services and must be separately audited for table schema, scope, concurrency, idempotency, authorization, audit/event evidence and crash recovery.

The existing generic `MigrationStateStoreInterface` tracks platform migration IDs and is not evidence that it can safely store rich per-table Custom Tables migration-run lifecycle state. Reuse/extension versus a purpose-specific owned store must be decided by a later exact-main audit rather than inferred here.

## Explicitly still blocked

This audit does **not** authorize:

- physical `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` dispatch;
- passing provider preview SQL into `$wpdb->query()` or another database mutation API;
- database-backed Custom Tables migration-run/applied-generation persistence;
- leases, locks, retries or Action Scheduler execution;
- direct row-count/null/duplicate/range/max-length scans;
- backfill, deduplication, copy, shadow or swap execution;
- Backup creation, provider verification calls or restore execution;
- row CRUD/Data Source/Query provider runtime;
- CT2/PT-D or CT3 runtime/topology conversion;
- external-table adoption;
- Custom Tables admin/REST/Ability public mutation surfaces;
- `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release approval.

## Worker merge rule

Each authorized worker PR must:

1. start from the exact main containing this audit promotion;
2. touch only its owned namespace plus focused tests/non-shared notes;
3. contain no forbidden execution/persistence/provider primitive;
4. pass all applicable exact-head hosted CI;
5. have zero unresolved review threads;
6. reconcile latest main without force before merge.

After all four lanes promote, another exact-main Supervisor audit is mandatory before any database-backed persistence, direct scanner, Backup-provider integration, job/lease orchestration or physical DDL execution opens.

## Closure

When this reconciliation promotes:

- Issue #405 / merged PR #406 becomes historical DONE Supervisor evidence;
- Issues #407/#408/#409 and PRs #410/#411/#412 become historical DONE contract evidence;
- exactly four execution-free next-wave `ANY` slots become dependency-ready in parallel;
- physical Custom Tables mutation remains blocked.
