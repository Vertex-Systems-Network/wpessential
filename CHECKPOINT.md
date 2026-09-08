# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-08**  
Canonical reconciliation anchor: **`main @ f4c86e00ab71aa93032a13e7f506a01ee54b59a4`**  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Development approval: **`GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56`**

## Certified bounded implementation gates

- Fields / Surface 3 — **PASS FOR CERTIFIED NATIVE V1 SCOPE**.
- Relations / Surface 4 — **PASS FOR CERTIFIED NATIVE V1 BASELINE**.
- Query / Surface 6 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Admin Columns / Surface 8 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Dynamic Listings / Surface 9 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Status Manager / Surface 5 — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**, final closure Issue #378 / merged PR #379.
- Custom Tables / Surface 7 — **ACTIVE / NOT PASS**.

These bounded passes do not imply full Options Bank parity, `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release certification.

## Current dependency gate — Custom Tables

Promoted Surface 7 evidence now includes:

1. Issue #382 / PR #383 — canonical DDL-free table Definition + deterministic schema descriptor.
2. Issue #385 / PR #387 — deterministic observed-schema normalization + pure desired-to-observed Migration Plan with R0-R4 risk/blocking semantics.
3. Issue #388 / PR #390 — post-plan next-lane audit.
4. Issue #389 / PR #391 — trusted CT1/PT-E physical identity + strictly read-only MySQL/MariaDB schema introspection.
5. Issue #392 / PR #394 — post-introspection next-lane audit.
6. Issue #393 / PR #395 — trusted provider capability profile + deterministic execution-free provider DDL preview compiler.
7. Issue #397 / PR #398 — post-provider exact-main prerequisite audit.
8. Issue #399 / PR #402 — immutable Migration Run state machine V1.
9. Issue #400 / PR #403 — typed Precondition Contract V1.
10. Issue #401 / PR #404 — Recovery + reviewed-source Revalidation V1.
11. Issue #405 / PR #406 — post-prerequisite exact-main Supervisor audit.
12. Issue #407 / PR #410 — Migration Run Repository Contract V1.
13. Issue #408 / PR #411 — Precondition Evaluator V1.
14. Issue #409 / PR #412 — Recovery Readiness V1.
15. Issue #413 — post-contract-wave exact-main Supervisor audit and shared-truth reconciliation.

The latest contract wave was serialized through exact-head certification and latest-main reconciliation:

- PR #411 / Precondition Evaluator — merged at `7ebc340d96c852dd3591686cf574cdf126ed9c97`.
- PR #410 / Run Repository — reconciled after #411 and merged at `c140a2665a8ac0040dd923c707bf7315983350de`.
- PR #412 / Recovery Readiness — reconciled after #410; final head `cb4d92bbe94ed5c18aa7f8cde4ce5c48734f1d20` passed PHP Quality Toolchain, Architecture Guards, Platform Compatibility Matrix and Distributable Package with zero review threads before merge at `f4c86e00ab71aa93032a13e7f506a01ee54b59a4`.

Provider statements remain immutable review previews with `execution_allowed=false`. The promoted repository/evaluator/readiness contracts remain execution-free and contain no database-backed Custom Tables run persistence, direct row scanning, jobs/leases or Backup-provider calls.

## Post-contract-wave audit decision

Issue #413 / `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-CONTRACT-WAVE-AUDIT-V1.md` authorizes exactly four non-overlapping execution-free contracts when this Supervisor reconciliation is promoted:

1. **Run Transition Service V1** — load through `MigrationRunRepositoryInterface`, transition only through canonical `MigrationRun::transition()`, and persist replacement only through compare-and-swap; no concrete database store/jobs/leases.
2. **Precondition Probe Registry V1** — explicit allowlisted `PreconditionKind` to typed probe registration/dispatch; missing probes fail closed; no direct database reads/scans.
3. **Recovery Evidence Source Contract V1** — typed evidence source plus deterministic static/in-memory reference source; no Backup-provider call, snapshot or restore.
4. **Migration Execution Readiness V1** — pure aggregate decision across existing immutable Run, Precondition, Revalidation, Recovery and provider-preview facts; no statement dispatch.

These lanes may run in parallel because they own separate namespaces/responsibilities and do not require each other's new implementations.

### Physical mutation remains blocked

Issue #413 does not authorize:

- dispatching `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` statements;
- generic DDL execution through `$wpdb->query()` or shared database mutation APIs;
- database-backed Custom Tables migration-run/applied-generation persistence;
- leases/locks/retry workers or Action Scheduler execution;
- direct row-count/null/duplicate/range/max-length precondition scans;
- backfill, deduplication, shadow-copy or swap;
- Backup creation/verification provider calls or restore execution;
- row CRUD/Data Source/Query provider runtime;
- CT2/PT-D or CT3 runtime/topology conversion;
- external-table adoption;
- Custom Tables admin/REST/Ability mutation surfaces;
- product parity, deployment or release.

## Multi-agent coordination

`AUTO-AGENT.md` and `config/coordination/agent-work-queue.json` are the claim authority.

When Issue #413 reconciliation is promoted:

- `custom-tables-post-prerequisite-audit-v1` becomes historical DONE;
- `custom-tables-run-repository-contract-v1`, `custom-tables-precondition-evaluator-v1` and `custom-tables-recovery-readiness-v1` become historical DONE;
- `custom-tables-post-contract-wave-audit-v1` becomes DONE;
- the following four `ANY` implementation slots become dependency-ready in parallel:
  - `agent/custom-tables-run-transition-service-v1`;
  - `agent/custom-tables-precondition-probe-registry-v1`;
  - `agent/custom-tables-recovery-evidence-source-v1`;
  - `agent/custom-tables-execution-readiness-v1`;
- no physical DDL executor, database-backed run store, direct scanner/backfill, lease/job or Backup execution branch may be speculatively pre-created.

Workers may run in parallel only on their non-overlapping owned namespaces. An existing deterministic claim branch means the slot is already owned. Shared truth files remain Supervisor-only.

## Product / planning truth

Accepted structural scope remains **56/56 Exhaustive**, Multisite **56/56**, AI Prompt **56/56**.

Machine-readable planning lifecycle truth remains authoritative in:

- `config/product/options-bank-progress.json`;
- `config/product/atomic-option-contract-progress.json`.

The reviewed Bank surfaces and record counts are planning/research state and must not be conflated with runtime implementation completion.

## Current next action

1. Promote Issue #413 shared-truth reconciliation from `supervisor/custom-tables-post-contract-wave-audit-v1`.
2. Re-read exact `main` after merge.
3. Atomically claim all four dependency-ready execution-free worker branches from that exact main.
4. Develop and certify Run Transition Service, Precondition Probe Registry, Recovery Evidence Source and Migration Execution Readiness independently in parallel.
5. Merge each only after exact-head CI/review clean and latest-main reconciliation.
6. After all four promote, run another exact-main Supervisor audit before database-backed persistence, direct scans, Backup-provider integration, jobs/leases or any physical DDL execution is authorized.

Repository evidence overrides conversational memory.
