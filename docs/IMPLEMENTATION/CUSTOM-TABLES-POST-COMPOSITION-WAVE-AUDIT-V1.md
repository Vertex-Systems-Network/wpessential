# Custom Tables — Post-Composition-Wave Exact-Main Audit V1

Status: **SUPERVISOR AUDIT / bounded execution-free hardening only**  
Issue: **#431**  
Exact-main audit anchor: **`cac9387f621d18ac03fc34d0f8724661f682d189`**  
Date: **2026-09-08**

## AI-Native preflight

At the audit anchor the mandatory startup order was executed before selecting new work:

1. exact `main` resolved;
2. OPEN Issues checked — **0**;
3. OPEN PRs/MRs checked — **0**;
4. deterministic claims and coordination queue reviewed;
5. only then was this Supervisor audit opened.

The issue-first / PR-second / queue-third / new-development-last order from Issue #415 / merged PR #416 remains canonical. End-of-cycle README module progress reconciliation is mandatory.

## Verdict

Surface 7 Custom Tables remains **ACTIVE / NOT PASS**.

The four execution-free composition lanes authorized by Issue #413 / merged PR #414 are now promoted:

- Run Transition Service V1 — PR #427;
- Precondition Probe Registry V1 — PR #428;
- Recovery Evidence Source Contract V1 — PR #429;
- Migration Execution Readiness V1 — PR #430.

All were serialized through latest-main reconciliation and exact-head hosted CI before merge. None dispatches provider SQL, persists Custom Tables run state to a real database, scans live row data, invokes a Backup provider, schedules execution, or mutates managed target tables.

The bounded Custom Tables V1 foundation/composition runway is therefore promoted from **80% to 90%** in README. This percentage measures only the currently defined bounded runway. It is **not** product parity, runtime certification, deployment certification or release readiness.

## Promoted composition evidence

### Run Transition Service

The service:

- loads through `MigrationRunRepositoryInterface`;
- requires a positive expected state revision;
- rejects missing/stale runs;
- delegates legal lifecycle transitions to `MigrationRun::transition()`;
- writes replacement state only through repository compare-and-swap semantics.

The current reference repository remains in-memory only.

### Precondition Probe Registry

The registry:

- maps explicit `PreconditionKind` values to typed probes;
- rejects duplicate registration;
- fails closed when a probe is missing;
- rejects mismatched requirement evidence;
- provides no implicit cross-kind fallback.

No direct database adapter is owned by the registry.

### Recovery Evidence Source

The source contract:

- exposes bounded typed `RecoveryEvidence` only;
- includes a deterministic static reference source;
- rejects invalid or duplicate plan evidence;
- returns no evidence explicitly when a plan is unknown.

It does not call Backup creation, verification or restore providers.

### Migration Execution Readiness

The pure decision composes:

- canonical Migration Run state;
- `PreconditionReport`;
- reviewed-source revalidation;
- recovery readiness;
- `ProviderMigrationPreview`.

It fails closed unless the run is at the revalidation boundary, preconditions are satisfied, revalidation and recovery are ready, the plan fingerprint matches and the preview contains work. A `ready=true` result means only **eligible for a future separately-authorized execution layer**. It does not override `ProviderMigrationPreview::executionAllowed=false` and does not dispatch statements.

## Shared Platform composition finding

`frameworks/Platform/Database/Migrations/**` already owns generic platform migration coordination/state infrastructure, including `MigrationCoordinator`, `MigrationRunner`, `InMemoryMigrationStateStore` and `WpdbMigrationStateStore`.

Custom Tables must not create a duplicate private platform-migration engine. Future Custom Tables run persistence may have richer run-state semantics, but it must compose with the canonical Platform database/migration boundary rather than bypass it.

## Next conflict-safe parallel wave

Exactly four further **execution-free** prerequisites are authorized after this audit reconciliation promotes:

### Lane A — Run Persistence Record Codec V1

Owned scope:

- `frameworks/Modules/CustomTables/Migration/Run/Persistence/**`
- focused tests under `tests/Unit/Modules/CustomTables/Run/Persistence/**`

Required behavior:

- deterministic immutable `MigrationRun` storage-row encoding/decoding;
- explicit schema/version marker;
- bounded scalar fields only;
- reject malformed/stale/unknown storage shapes;
- preserve UUID/fingerprint/revision/state invariants.

Forbidden: database adapter access, migrations/table creation, SQL, leases/jobs.

### Lane B — Precondition Read-Only Probe Plan V1

Owned scope:

- `frameworks/Modules/CustomTables/Migration/Precondition/Plan/**`
- focused tests under `tests/Unit/Modules/CustomTables/Precondition/Plan/**`

Required behavior:

- convert supported `PreconditionRequirement` values into typed bounded read-only probe-plan descriptors;
- use only allowlisted operation kinds/targets/parameters;
- no raw user SQL;
- unsupported kinds fail closed;
- descriptors contain no row payloads.

Forbidden: database adapter access, query execution, backfill/deduplication, SQL dispatch.

### Lane C — Recovery Evidence Binding/Freshness V1

Owned scope:

- `frameworks/Modules/CustomTables/Migration/Recovery/Binding/**`
- focused tests under `tests/Unit/Modules/CustomTables/Recovery/Binding/**`

Required behavior:

- bind evidence to reviewed plan fingerprint/provider identity where applicable;
- deterministic freshness/expiry decision;
- missing, stale or mismatched evidence fails closed;
- bounded metadata only.

Forbidden: Backup provider calls, snapshot creation/verification/restore, persistence.

### Lane D — Execution Authorization Envelope V1

Owned scope:

- `frameworks/Modules/CustomTables/Migration/Readiness/Authorization/**`
- focused tests under `tests/Unit/Modules/CustomTables/Readiness/Authorization/**`

Required behavior:

- pure immutable authorization request/decision layered **after** execution readiness;
- explicit actor/capability/confirmation/risk facts as bounded values;
- fail closed for stale readiness, missing confirmation or unsupported risk;
- no Policy bypass and no statement ownership.

Forbidden: SQL/DDL dispatch, capability mutation, database access, jobs/leases, public endpoints.

These four lanes are non-overlapping and may run in parallel after this audit is promoted.

## Still explicitly blocked

This audit does **not** authorize:

- physical `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` dispatch;
- generic DDL execution through `$wpdb->query()` or another mutation bypass;
- database-backed Custom Tables run repository implementation;
- internal run-store table migration;
- direct live row-count/null/duplicate/range/max-length scans;
- Backup provider calls or restore execution;
- leases/locks/Action Scheduler execution;
- backfill/deduplication/shadow-copy/swap;
- row CRUD/Data Source runtime;
- CT2/PT-D or CT3 runtime;
- external-table adoption;
- public admin/REST/Ability mutation surfaces;
- `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release approval.

## Next audit requirement

After all four hardening lanes promote, another exact-main Supervisor audit is mandatory before any concrete database-backed run repository, live precondition scanner, Backup-provider bridge, job/lease execution or physical DDL executor may be opened.
