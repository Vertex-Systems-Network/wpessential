# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-08**  
Canonical audited base anchor: **`main @ e4cd9f0d3c1edd33836fb628c6958731bb252ecc`**  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Development approval: **`GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56`**

## AI-Native mandatory work-cycle order

Issue #415 / merged PR #416 promoted the canonical work-cycle rule in `AUTO-AGENT.md`, `AGENTS.md` and README:

1. resolve exact current `main`;
2. inspect and triage **OPEN Issues first**;
3. inspect/fix/review/merge eligible **OPEN PRs/MRs second**;
4. re-read active deterministic claim branches and `config/coordination/agent-work-queue.json`;
5. only then claim/start new dependency-ready development;
6. after meaningful completed work reaches a stable final state, the Supervisor reconciles README current status and its module-wise progress table/progress bars before reporting completion.

Accepted issue/PR work must not be bypassed by speculative replacement branches. Merge order still follows dependencies, exact-head CI, review-thread cleanliness and shared-truth safety.

README percentages measure the currently approved/certified **bounded implementation milestone**, never full product parity unless machine-readable lifecycle state explicitly promotes that claim.

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

Promoted Surface 7 evidence includes:

1. Issue #382 / PR #383 — canonical DDL-free table Definition + deterministic schema descriptor.
2. Issue #385 / PR #387 — observed-schema normalization + pure Migration Plan with R0-R4 risk/blocking semantics.
3. Issue #389 / PR #391 — trusted CT1/PT-E physical identity + strictly read-only MySQL/MariaDB schema introspection.
4. Issue #393 / PR #395 — trusted provider capability profile + deterministic execution-free provider DDL preview compiler.
5. Issue #399 / PR #402 — immutable Migration Run state machine V1.
6. Issue #400 / PR #403 — typed Precondition Contract V1.
7. Issue #401 / PR #404 — Recovery + reviewed-source Revalidation V1.
8. Issue #405 / PR #406 — post-prerequisite exact-main Supervisor audit.
9. Issue #407 / PR #410 — Migration Run Repository Contract V1.
10. Issue #408 / PR #411 — Precondition Evaluator V1.
11. Issue #409 / PR #412 — Recovery Readiness V1.
12. Issue #413 / PR #414 — post-contract-wave exact-main Supervisor audit and 4-lane coordination.
13. Issue #415 / PR #416 — AI-Native issue-first/PR-second startup + README progress closeout governance.

Provider statements remain immutable review previews with `execution_allowed=false`. The promoted repository/evaluator/readiness contracts remain execution-free and contain no database-backed Custom Tables run persistence, direct row scanning, jobs/leases or Backup-provider calls.

## Current authorized parallel wave

Issue #413 / merged PR #414 authorizes exactly four non-overlapping execution-free contracts:

1. **Run Transition Service V1** — load through `MigrationRunRepositoryInterface`, transition only through canonical `MigrationRun::transition()`, and persist replacement only through repository compare-and-swap; no concrete database store/jobs/leases.
2. **Precondition Probe Registry V1** — explicit allowlisted `PreconditionKind` to typed probe registration/dispatch; missing probes fail closed; no direct database reads/scans.
3. **Recovery Evidence Source Contract V1** — typed evidence source plus deterministic static/in-memory reference source; no Backup-provider call, snapshot or restore.
4. **Migration Execution Readiness V1** — pure aggregate decision across existing immutable Run, Precondition, Revalidation, Recovery and provider-preview facts; no statement dispatch.

These four lanes are conflict-safe in parallel because they own separate namespaces/responsibilities and are execution-free.

### Physical mutation remains blocked

The current gate does not authorize:

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

At every invocation:

- reconcile OPEN Issues first;
- reconcile OPEN PRs/MRs second;
- re-read current main/claims/queue;
- only then claim dependency-ready work.

Current dependency-ready deterministic worker branches are:

- `agent/custom-tables-run-transition-service-v1`;
- `agent/custom-tables-precondition-probe-registry-v1`;
- `agent/custom-tables-recovery-evidence-source-v1`;
- `agent/custom-tables-execution-readiness-v1`.

No physical DDL executor, database-backed run store, direct scanner/backfill, lease/job or Backup execution branch may be speculatively pre-created.

Workers may run in parallel only on their non-overlapping owned namespaces. An existing deterministic claim branch means the slot is already owned. Shared truth files remain Supervisor-only.

## Product / planning truth

Accepted structural scope remains **56/56 Exhaustive**, Multisite **56/56**, AI Prompt **56/56**.

Machine-readable planning lifecycle truth remains authoritative in:

- `config/product/options-bank-progress.json`;
- `config/product/atomic-option-contract-progress.json`.

The reviewed Bank surfaces and record counts are planning/research state and must not be conflated with runtime implementation completion.

## Current next action

1. Complete this post-AI-Native shared-truth reconciliation.
2. Re-read exact `main`, OPEN Issues and OPEN PRs/MRs.
3. Atomically claim all four dependency-ready execution-free worker branches from that exact main.
4. Develop and certify Run Transition Service, Precondition Probe Registry, Recovery Evidence Source and Migration Execution Readiness independently in parallel.
5. Merge each only after exact-head CI/review clean and latest-main reconciliation.
6. After the wave promotes, update README module-wise progress and run another exact-main Supervisor audit before database-backed persistence, direct scans, Backup-provider integration, jobs/leases or any physical DDL execution is authorized.

Repository evidence overrides conversational memory.
