# WPEssential — JobService PT-C/PT-D Physical Mapping Profile

Status: **Phase 0 paper architecture / P-003 mapping only / no backend or DDL authorized**  
Related: ADR-0006, ADR-0059, ADR-0068, ADR-0069, ADR-0071, ADR-0075, Job Service Contract, Job Service Execution/Fairness/Backpressure, Action Scheduler Packaging/Coexistence.

## Purpose

Define how WPE-owned logical Job records/history should be benchmarked physically without turning Action Scheduler's native tables/statuses into the WPE public contract and without duplicating queue/backend state unnecessarily.

## Boundary

WPE distinguishes:
- Job Type registration — code-defined contract, no generic runtime DB row required;
- Schedule Definition — configuration/trigger semantics owned by its defining module/Definition or schedule store;
- WPE Job — durable logical unit of work/business execution identity;
- WPE Attempt — one claim/execution attempt;
- optional Job Checkpoint/Progress — bounded operational state;
- backend action/reference — adapter implementation detail;
- Audit/domain history — separate long-lived facts.

Action Scheduler, WP-Cron, WP-CLI or future runner is an execution adapter. Backend rows are not authoritative WPE business history.

---

## Physical comparison profiles

### J1 — PT-D shared scoped Jobs + Attempts — first benchmark baseline

Use shared scoped high-volume WPE runtime stores for:
- Job current/logical record;
- Attempt history;
- compact checkpoint/progress where required.

Schedule/definition configuration remains in its owning control-plane model rather than copied into Job rows.

### J2 — split PT-C current Job control + PT-D Attempts/history — mandatory comparison

Keep a smaller current Job/control row family in PT-C while attempt/history volume resides in PT-D.

Potential benefit: smaller current-control indexes. Cost: cross-store lifecycle/transactions/migration complexity and a potentially arbitrary boundary.

### J3 — PT-C Jobs + Attempts — low-volume control-plane comparison only

Useful only to prove whether expected volume is sufficiently low; not preferred for bulk/workflow/notification/import/backup workloads without evidence.

PT-E per-site Job history is not the first comparison because network coordination/fairness/diagnostics are core JobService concerns, but a future PT-E experiment can be added if large-network physical isolation evidence demands it.

---

## Scope invariants

Every site-owned J1/J2/J3 runtime row carries explicit:
- `network_id`;
- `site_id`;
- stable Job/Attempt UUID/internal identity.

Current blog context and backend group/hook names are not durable scope.

A network Job is a distinct privileged Job Type/instance with explicit network scope. Site Jobs cannot access another site's Definitions, Vault secrets or domain state merely because runner process switches context incorrectly.

All due/claim/admin queries include scope or an explicitly authorized bounded network aggregation path.

---

## WPE Job physical invariants

Candidate fields:
- internal numeric ID + stable Job UUID;
- scope;
- Job Type key/version;
- owning module/domain Run/reference;
- payload schema version + compact payload/reference;
- logical status;
- urgency/resource/cost class;
- concurrency key/reference;
- due/available/retry/expiry timestamps;
- business idempotency identity/profile;
- current Attempt/claim generation/reference;
- backend adapter/profile + backend action reference nullable;
- cancellation/pause state;
- progress/checkpoint summary reference;
- result/error category summary;
- retention class;
- created/updated/terminal timestamps;
- optimistic state generation.

Do not store:
- executable callbacks;
- Vault plaintext/API secrets;
- large Form/Chat/provider object dumps;
- arbitrary PHP serialized objects;
- backend raw logs as canonical payload.

Candidate hot indexes:
- scope + status + due/available time;
- scope + urgency/resource class + status + due time;
- scope + Job Type + status/time;
- scope + owning Run/domain reference;
- scope + concurrency key + active state;
- scope + idempotency identity;
- scope + backend adapter/reference;
- terminal/retention eligibility.

Exact order/types remain P-003 evidence.

---

## Attempt physical invariants

Attempt is immutable/append-oriented execution history except bounded heartbeat/checkpoint fields while active.

Candidate fields:
- Attempt UUID/internal ID;
- scope + Job ID;
- ordinal;
- claim/runner identity;
- claim/start/heartbeat/finish timestamps;
- lease/profile/version;
- state;
- handler/job-type version;
- backend claim/reference safe metadata;
- safe error/result category;
- retry decision/reason;
- external outcome classification including `outcome_unknown`;
- resource/progress safe metrics;
- correlation IDs.

Hot indexes:
- scope + Job + ordinal;
- scope + state + claim/heartbeat/finish time;
- scope + runner/claim identity;
- scope + failed/unknown state + time;
- scope + retention eligibility.

Attempt history does not replace owning Workflow/Import/Backup/Membership domain history.

---

## Claim / lease truth

Lease expiry means claim is stale/reclaimable; it does **not** prove no side effect occurred.

A recovered Job after lease loss must re-enter its Job Type idempotency/reconciliation contract.

Candidate future claim strategies to benchmark:
- WPE DB compare-and-set claim generation;
- backend-owned claim + WPE precondition/reconciliation;
- hybrid short WPE claim projected onto backend claim.

Requirements:
- abandoned claims recover;
- two workers cannot both commit one WPE logical transition when the Job Type contract is valid;
- no long provider/API work holds unnecessary DB transaction;
- claim identity is observable and correlated to Attempt;
- backend stale-claim cleanup cannot silently rewrite WPE Job outcome.

Exact mechanism remains P-003.

---

## Backend reference model

Backend action ID/reference is nullable/reconcilable.

States must tolerate:
- WPE Job committed but backend enqueue failed;
- backend action exists but local backend reference commit failed;
- backend action was externally deleted/cleaned;
- backend version/schema unavailable;
- duplicate backend action for same WPE Job identity;
- backend says complete but WPE Job did not commit success because handler crashed after side effect.

Reconciliation compares WPE Job state + Job Type idempotency/checkpoint + backend evidence. Backend status alone never fabricates WPE success.

---

## Enqueue idempotency

Job creation has a stable logical identity according to producer contract.

Possible producer classes:
- unique logical continuation/event occurrence;
- recurring schedule occurrence;
- intentionally repeatable manual invocation;
- chunk continuation;
- reconciliation job.

For unique logical work, concurrent/retried enqueue cannot create multiple effective WPE Jobs.

Action Scheduler `unique` scheduling, if used, remains an adapter optimization and is not the business idempotency guarantee.

---

## Fairness / backpressure physical implications

WPE fairness is not raw SQL `ORDER BY priority` only.

Physical profile must make it practical to observe/select by:
- urgency class;
- eligible age;
- resource/provider class;
- site/network scope;
- concurrency key;
- workload family;
- backlog/oldest age.

Future algorithm may use reserved service shares, weighted selection and bounded age boost. Exact scheduler is P-003, but schema/index choice must not make fairness impossible or force full-table scans.

One noisy site/provider/bulk workload cannot indefinitely starve security/interactive/other sites.

---

## Backpressure / admission

High-volume producers should create bounded parent Run/Plan + cursor/chunks instead of synchronous millions of Job rows.

Job physical layer must support:
- backlog counts/estimates by class/type/site/provider;
- oldest eligible age;
- active concurrency counts;
- provider rate-limit next-at state;
- parent Run continuation/checkpoint.

These are operational signals, not authorization state.

---

## Recurrence

Recurring Schedule occurrence creates a distinct logical Job identity; one mutable forever-row is rejected.

Occurrence semantics include missed-run and overlap policy. Backend recurrence may be an adapter feature, but WPE schedule occurrence identity/history cannot depend solely on backend recurrence internals.

---

## Retention

Separate:
- active Jobs — never history-purged;
- succeeded Job summaries;
- Attempt history;
- failed/unknown diagnostic history;
- checkpoint/progress data;
- backend logs;
- owning domain Run/Audit records.

Backend cleanup policy does not define WPE retention.

WPE can purge old Attempts sooner than domain Audit/Run history while keeping enough Job summary/correlation for explainability.

---

## Site lifecycle

Archive/suspend can pause/defer site-scoped non-critical work according to classification while preserving minimum control/recovery jobs.

Site deletion sequence uses Site Lifecycle Coordinator:
- stop producers/new admissions;
- identify active claimed/running Jobs;
- cooperatively drain/cancel/reconcile;
- preserve unknown/destructive recovery state;
- clean site-owned Job/Attempt rows only after owning domains reach safe state;
- never delete another site's Jobs because group/hook/backend IDs collide.

Network Jobs that reference the deleted site must reconcile their target set rather than being silently deleted.

---

## Backup/Restore

Job queue/backend rows are not blindly restored as executable work.

After Restore:
- terminal historical Jobs remain history;
- scheduled/available/retry/running/unknown Jobs enter domain-aware reconciliation;
- backend references are treated stale until remapped/re-enqueued;
- recurring schedules regenerate occurrences from accepted schedule semantics, not copied backend queue rows;
- destructive/import/backup/email/workflow jobs consult owning domain Run state before reactivation;
- idempotency/checkpoint identities survive where required to prevent duplicate side effects.

---

## P-003 future evidence matrix — NOT AUTHORIZED

Topology:
- J1 PT-D Jobs+Attempts;
- J2 PT-C current Job + PT-D Attempts/history;
- J3 PT-C all low-volume comparison;
- Action Scheduler physical adapter remains separately profiled.

Datasets/workloads:
- 100k / 1M / 10M Job history where practical;
- high-volume notification/workflow/import chunks;
- many small sites + one noisy site;
- provider-rate-limited workloads;
- destructive single-concurrency workloads;
- long-running/checkpointed jobs;
- recurring schedules;
- 100/1k/10k-site networks.

Correctness:
- concurrent duplicate enqueue;
- two runners same Job;
- lease expiry after external side effect;
- worker crash before/after domain commit;
- WPE commit/backend enqueue failure in both directions;
- backend action cleanup/deletion;
- cancellation/pause races;
- fairness/starvation under sustained high priority;
- backpressure/admission;
- site archive/delete/restore;
- wrong-site context/secret/Definition resolution;
- Action Scheduler coexistence/version/load order/migrations.

Measure:
- due-claim p50/p95/p99;
- enqueue throughput;
- Attempts write volume;
- queries/rows examined/query plans;
- claim/deadlock/retry behavior;
- index/storage growth;
- fairness lag by class/site;
- duplicate effective side effects (must satisfy Job Type contract; expected zero in idempotent fixtures);
- reconciliation throughput;
- cleanup/retention cost.

## Selection rule

J1 is first benchmark baseline because Jobs/Attempts can be high-volume and need shared network visibility/fairness, but J2 is a mandatory comparison before final mapping. J3 remains a low-volume control comparison.

A profile is rejected regardless of speed if backend state can fabricate WPE success, stale claims cause duplicate effective mutation, scope isolation fails, or Restore blindly reactivates unsafe work.

## Development gate

No Job table/migration, Action Scheduler bootstrap/action, WP-Cron event, runner, claim, queue execution, fixture or benchmark is authorized. ADR-0014 explicit owner consent remains required.