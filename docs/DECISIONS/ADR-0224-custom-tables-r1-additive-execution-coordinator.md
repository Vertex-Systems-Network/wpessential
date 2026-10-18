# ADR-0224 — Custom Tables R1 Additive Execution Coordinator

Status: **ACCEPTED**
Date: **2026-10-18**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)
Builds on: ADR-0023 (typed migration language), `CUSTOM-TABLES-POST-TRUSTED-RUNTIME-EVIDENCE-EXACT-MAIN-AUDIT-V1` (all three trust blockers resolved by #1429/#1430/#1431)

## Context

Custom Tables can now produce a fully-evidenced readiness package: persisted run, metadata preconditions, generation revalidation, real Backup-verified recovery evidence, a trusted user confirmation and PolicyEngine authorization. No component executes a plan. ADR-0023 orders risk R0 metadata → R1 additive → R2 controlled data-affecting → R3 high-impact → R4 destructive.

Readiness deliberately rejects a preview whose `executionAllowed` is true: a preview is never itself executable.

## Decision

Add the first execution boundary, limited to **R1 additive** plans.

1. **Scope.** Only operations that add structure without touching existing data: create a new managed table, add a nullable/defaulted column, add a non-unique index. Any R2+ operation, any drop/rename/alter-type/truncate/unique index, or an empty plan is rejected before any statement runs.
2. **Gate.** The coordinator calls `RuntimeCompositionReadinessService::evaluate()` itself and executes only when the package is ready. Callers cannot pass a pre-computed decision.
3. **Statements.** Executable SQL is recompiled from the same typed plan by the repository compiler and must be byte-identical to the reviewed preview statements. No caller-supplied SQL, no raw DDL.
4. **State.** The run must already be `revalidating` (the state readiness evaluates and the confirmation binds to); it then moves `revalidating → running → verifying → applied` through `MigrationRunTransitionService` with revision CAS; a concurrent change aborts before mutation.
5. **Verification.** After execution the table is re-introspected; the observed schema must contain every desired column and index. Otherwise the run becomes `failed_recoverable` (additive changes are reversible while no data depends on them) and no success is reported.
6. **Surface.** Internal service only. No REST, Ability, admin action or cron entry point in this ADR; those are later lanes.

## Consequences

- R1 additive schema changes can be applied with full evidence and audit trail.
- R2–R4 remain unimplemented; they need their own ADRs (data checks, recovery copies, Backup-required paths).
- Real-database evidence (MySQL/MariaDB matrix) is required in CI for the executor before any public entry point is added.
