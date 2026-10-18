# ADR-0225 — Custom Tables R1 Run Preparation and Review Snapshot

Status: **ACCEPTED**
Date: **2026-10-18**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)
Builds on: ADR-0023 (typed migration language), ADR-0224 (R1 additive execution coordinator)

## Context

ADR-0224 added the R1 execution boundary and #1433 composes it from the trusted runtime factory. The coordinator consumes an `R1AdditiveExecutionRequest` whose most important inputs (the reviewed preview and the reviewed generation stamp) must describe what an administrator actually reviewed.

No production code creates a migration run or records a review:

- nothing moves a run into `planned`; runs exist only in tests;
- nothing produces a `MigrationGenerationStamp`;
- the persisted run stores only the plan fingerprint and target Definition identity, not the reviewed preview.

An entry point built now would have to accept the plan, preview and stamps from its caller, or re-derive them at execution time. The first is a trust hole; the second makes "reviewed equals current" true by construction, which empties the generation-revalidation check.

## Decision

Add server-side **run preparation** before any public entry point, limited to R1 additive plans.

1. **Preparation service.** `R1MigrationRunPreparationService` takes only a target table descriptor and its Definition identity (id, revision, schema version). It observes the live table, plans with `TableSchemaDiffPlanner`, applies the same R1 scope rules as the coordinator, compiles the preview with `ProviderDdlPreviewCompiler` (including the connection charset/collation), and builds the generation stamp from the plan fingerprint, observed fingerprint, Definition identity and detected provider/version. A no-op, blocked, non-clean or above-R1 plan is rejected and nothing is persisted.
2. **Run.** A prepared plan creates a new run in `planned` through `MigrationRunRepositoryInterface::create()` with a fresh UUID.
3. **Review snapshot.** A new insert-once store keeps, per run id: the reviewed preview canonical hash and the reviewed generation stamp canonical form. The snapshot is written at preparation time and is never updated. Execution later recompiles and re-observes; it compares against this snapshot, not against values it just computed.
4. **Storage.** The store follows the existing run and confirmation store pattern: a repository-owned migration creates the table, writes are prepared statements, readback verifies the inserted row.
5. **Surface.** Internal service only. No REST, Ability, admin action or cron entry point in this ADR.

## Consequences

- Runs now have a real origin and a durable record of what was reviewed.
- A later ADR can add the entry point by resolving everything from the run id and the snapshot, accepting nothing else from the caller.
- Desired schema is re-derived at execution time from the Definition at the run's target revision; a newer Definition revision supersedes the run instead of being executed against an old review.
- R2–R4 remain out of scope.
