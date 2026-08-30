# ADR-0222 — Durable JobService Persistence, Leases & Checkpoints

Status: **ACCEPTED**  
Date: **2026-08-29**  
Milestone: WP121 — Platform Foundation  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE

## Context

ADR-0221 accepted Action Scheduler as a tested operational backend profile. Action Scheduler remains a wake-up/materialization mechanism and cannot become WPEssential's authoritative business Job state, idempotency, attempt, checkpoint or audit store.

WPE therefore needs durable first-party persistence that survives PHP process/request boundaries and prevents stale/concurrent workers from silently committing conflicting execution state.

## Decision

Implement WPE-owned durable Job persistence with optimistic Job-state CAS plus explicit leased attempts.

### Authoritative tables

Migration `009.create-job-persistence` / sequence 90 creates non-destructively:

- `${base_prefix}wpe_jobs` — scoped Job identity, type, state, payload, idempotency digest, attempts, failure/retry state and revision;
- `${base_prefix}wpe_job_attempts` — scoped attempt identity, attempt number, worker, hashed lease token, lease/heartbeat times, monotonic checkpoint state, terminal result/failure and completion evidence.

Both tables use explicit network/site scope.

### Persistent JobService

`PersistentJobService` implements the existing `JobServiceInterface` and retains `JobType` definitions as runtime registration while persisting mutable Job state in MySQL.

Rules:
- stable-key idempotency persists only SHA-256 digest authority; the raw key is not a database column;
- a service re-instantiation reloads the same Job state/payload;
- state mutations use a monotonically increasing revision and compare-and-swap update;
- a stale revision is rejected rather than overwriting a newer state;
- retry state continues to use canonical `JobRecord` / `RetryPolicy` semantics;
- Job state is independent of Action Scheduler rows and retention.

### Attempt / lease contract

`WpdbJobAttemptStore` implements `JobAttemptStoreInterface`.

Rules:
- a Job row is locked during lease acquisition so two workers cannot successfully claim the same current Job attempt concurrently;
- one unexpired leased attempt per Job is permitted by service semantics;
- attempt numbers are monotonically increasing per Job;
- raw lease tokens are returned only to the worker and the database stores only SHA-256 token hashes;
- heartbeat requires exact scope + attempt + Job + worker + token hash + leased state + unexpired lease;
- heartbeat extends lease expiry;
- checkpoints require a valid unexpired lease and strictly increasing checkpoint sequence;
- stale/equal checkpoint sequence is rejected;
- completion requires a valid unexpired lease and terminal attempt state;
- failed completion requires an explicit canonical `JobFailureClass`;
- expired workers cannot heartbeat or commit terminal results;
- expired leases can be marked `abandoned` and a replacement worker receives a fresh attempt number;
- reclaim is bounded per call.

These rules prevent a worker that lost its lease from overwriting the result of a replacement worker.

## Privacy & security boundaries

- Action Scheduler args still contain only the WPE Job UUID.
- WPE Job payload remains in WPE-owned persistence.
- raw stable idempotency keys are not persisted in the durable Job table.
- raw lease tokens are not persisted; only digests are stored.
- checkpoint payloads are WPE-owned operational data and remain subject to future per-Job privacy/retention policy.
- no provider secret or Vault secret is permitted to rely on Job/Action Scheduler payload storage as a secret store.

## Executable evidence

Source head tested:
`8601d6f17325681c63cdbc97e6b64e1a3892db1e`

GitHub Actions run **33267525349 / #209** completed **SUCCESS** on GitHub-hosted Ubuntu 24.04, PHP 8.2, WordPress 7.1 and MySQL 8.4.

The run passed:
- Composer metadata;
- architecture validator;
- engineering-contract / `ABSPATH` validator;
- PHP syntax;
- existing 9/9 smoke suites;
- compiled-registration MySQL integration;
- Definition/Audit MySQL integration;
- real WordPress AJAX/nonce/Policy integration;
- Action Scheduler 3.9.3 + 4.1.0 coexistence/backend integration;
- **real WordPress durable JobService persistence/lease integration**.

The durable Job fixture proves:
1. migration 009 is re-applicable without destructive recreation;
2. Job + attempt tables exist under the WordPress network prefix;
3. stable-key duplicate enqueue returns the persisted Job;
4. only the SHA-256 idempotency digest is stored as dedupe authority;
5. a new service instance reloads Job payload/state;
6. start/fail/retry/start/succeed transitions persist correctly;
7. a stale revision CAS write is rejected;
8. worker A acquires attempt 1 and worker B cannot acquire the same unexpired lease;
9. the DB stores only the lease-token hash;
10. heartbeat extends a valid lease;
11. checkpoint sequence is monotonic;
12. terminal completion is one-way for the leased attempt;
13. expired worker heartbeat and completion are rejected;
14. expired attempt is reclaimable as abandoned;
15. replacement worker receives attempt 2 and can record terminal failure evidence.

## Certification boundary

This ADR does **not** yet certify:
- large-scale queue fairness or resource-class admission under high concurrency;
- cross-process distributed load/performance limits;
- Multisite switch/network-wide worker orchestration;
- automatic coupling of Action Scheduler dispatch callbacks to Ability execution and attempt lifecycle;
- Job checkpoint privacy erasure/retention policy implementation;
- admin Job/Attempt observability UI;
- provider unknown-outcome reconciliation workflows beyond existing failure-state semantics;
- live production migration/rollback.

## Consequences

WP121 may now proceed to the minimal Platform admin shell and Runtime Observatory diagnostic surface. The UI must read WPE-owned platform state and cannot expose Action Scheduler or database internals as a substitute for canonical WPE concepts.
