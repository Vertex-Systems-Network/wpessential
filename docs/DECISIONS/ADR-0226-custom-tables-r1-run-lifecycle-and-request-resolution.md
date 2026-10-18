# ADR-0226 — Custom Tables R1 Run Lifecycle and Execution Request Resolution

Status: **ACCEPTED**
Date: **2026-10-18**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)
Builds on: ADR-0224 (R1 coordinator), ADR-0225 (run preparation and review snapshot)

## Context

ADR-0225 creates a `planned` run and an insert-once review snapshot. Between that run and the ADR-0224 coordinator, the codebase is missing four things:

1. **Recovery policy.** Readiness always verifies recovery evidence and rejects a class mismatch (`RecoveryReadinessDecision`). The only production evidence source, `LocalBackupRecoveryVerificationProvider`, always reports `VerifiedBackupRequired`. A `TriviallyReversible` requirement can therefore never become ready in production, and no production code decides which requirement an R1 plan carries.
2. **Provider profile.** `ProviderCapabilityProfile::fromTrustedServerInfo()` has no production caller.
3. **Lifecycle.** Readiness requires the run to be `revalidating`. No production code moves a run `planned → awaiting_review → approved → revalidating`.
4. **Request resolution.** Nothing builds an `R1AdditiveExecutionRequest` from a stored run.

Today the coordinator fails closed in production. That is safe, but it cannot be used.

## Decision

1. **Production R1 recovery requires a verified backup.** Production uses `RecoveryRequirement(VerifiedBackupRequired, verifiedBackupRequired: true)` for R1 runs, satisfied only by a local backup manifest that captures the database and is bound to the run's plan fingerprint. The backup must be at most 24 hours old (`CustomTablesRuntimeCompositionFactory::R1_MAX_RECOVERY_EVIDENCE_AGE_HOURS`). Additive changes are low risk, but this is the only evidence source that is real today. Relaxing it to `TriviallyReversible` needs its own trusted provider and its own ADR.
2. **Trusted provider profile.** The profile comes only from the server's own `wpdb->db_server_info()`, read through a repository-owned resolver. Callers never supply it.
3. **Lifecycle service.** `R1MigrationRunLifecycleService` performs only these transitions, each with revision CAS:
   - `planned → awaiting_review`: submit, only when a review snapshot exists.
   - `awaiting_review → approved`: approve. Requires an authenticated user who passes PolicyEngine for `custom-tables.migration.execute` / `manage_options`.
   - `approved → revalidating`: start revalidation, under the same authorization.
   - `* → cancelled_before_mutation`: cancel from any pre-mutation state, under the same authorization.
4. **Request resolver.** `R1ExecutionRequestResolver` takes a run id, the acting `ExecutionContext` and a backup artifact id, and builds the request from stored and freshly observed facts only:
   - It loads the run and its review snapshot and rejects either if missing.
   - It loads the current Definition. If its revision differs from the run's target revision, the run becomes `superseded` and nothing is resolved.
   - It compiles the desired schema from that Definition, re-observes the table, re-plans and recompiles.
   - It rejects the request unless the recompiled preview matches the snapshot's preview hash.
   - The reviewed generation comes from the snapshot; the current generation comes from the fresh observation.
   - Preconditions are an explicit empty list for R1. The artifact id is only a reference: the provider verifies the backup itself, so a caller cannot forge evidence.
5. **Surface.** Still internal. No REST, Ability, admin action or cron entry point in this ADR. Exposing these services is a later ADR.

## Consequences

- A production R1 run can be taken from preparation through approval to execution, provided a verified, plan-bound database backup exists.
- Every caller-supplied value is either an identifier that is verified server-side (run id, backup artifact id) or the acting context, which is authorized by policy.
- A Definition edited after review supersedes the run instead of executing against a stale review.
