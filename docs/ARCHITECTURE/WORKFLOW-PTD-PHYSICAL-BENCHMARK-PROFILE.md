# WPEssential — Workflow PT-D Physical Benchmark Profile

Status: **Phase 0 paper architecture / benchmark profile only / no runtime DDL authorized**  
Related: Workflow Runtime Data Candidate, ADR-0025, ADR-0059, ADR-0068, ADR-0069, ADR-0071, ADR-0075, P-011.

## Purpose

Define the first future physical benchmark profile for durable Workflow execution without moving runtime state into Definition JSON or making JobService/Action Scheduler the Workflow source of truth.

## Benchmark topology

### WF1 — PT-D shared scoped Workflow Runtime — first benchmark baseline

One shared WPE runtime table family with explicit site/network scope.

Candidate logical stores:
- Workflow Run;
- Node/Step Attempt;
- Wait/Subscription;
- Approval;
- Run Context/large payload references where a dedicated store is justified;
- optional compact Run Branch/Join state when not representable safely in Run/Step rows.

### WF2 — PT-E per-site Workflow Runtime — mandatory large-network comparison

Equivalent logical stores per site.

WF2 measures physical isolation/noisy-neighbor behavior against table provisioning, migrations, Network Admin diagnostics and network-workflow coordination. Benchmark order is not final schema approval.

---

## Ownership boundary

Definition Repository owns Workflow configuration and immutable published revisions.

Workflow Runtime owns durable execution truth.

JobService owns execution opportunity/runner semantics, not Workflow state.

Therefore:
- deleting an Action Scheduler/backend action cannot silently mark a Workflow Run successful/failed;
- backend retention cannot erase Workflow business/runtime history;
- a Workflow Run can reconcile/re-enqueue a missing backend job from its own durable state;
- Workflow never relies on browser/request lifetime.

---

## Scope invariants

Every site-owned WF1 row carries explicit:
- `network_id`;
- `site_id`;
- stable run/node/wait/approval identity.

Run trigger payload fields do not choose durable scope. Scope is established by the trusted trigger binding/execution context before runtime creation.

Network workflows remain explicit future privileged mode. A site-scoped run cannot subscribe to or mutate another site's resource merely because numeric IDs match.

All runnable/wait/approval queries bind scope.

---

## Workflow Run physical invariants

Candidate fields/invariants:
- internal numeric ID + stable Run UUID;
- explicit scope;
- Workflow Definition UUID + pinned published revision UUID;
- trigger type/binding + source event/idempotency identity;
- principal policy/execution principal reference;
- status/current execution generation;
- started/finished/cancel timestamps;
- parent/causation/correlation identity;
- bounded safe input/context reference;
- error/retention/privacy class;
- optimistic state generation;
- created/updated timestamps.

Hot index families to benchmark:
- scope + Workflow Definition + status + created/start time;
- scope + status + runnable/updated time where Run-level scheduling requires it;
- scope + trigger/binding + idempotency identity unique/dedupe;
- scope + parent/causation/correlation;
- scope + terminal status + retention eligibility.

A Run row does not duplicate the full Workflow graph; it pins the immutable revision.

---

## Step / Node Attempt invariants

One logical node can have multiple attempts.

Candidate fields:
- scope + Run ID;
- stable node ID from pinned revision;
- attempt ordinal/UUID;
- state;
- scheduled/due/start/finish timestamps;
- JobService/backend reference;
- idempotency identity;
- retry policy/profile snapshot reference;
- input/output safe summary/reference;
- external outcome classification;
- error category;
- compensation state;
- optimistic generation/checkpoint.

Hot indexes:
- scope + Run + node + attempt;
- scope + state + next-at/due time;
- scope + Job/backend reference;
- scope + idempotency identity where node contract requires uniqueness;
- scope + failed/retryable state + time.

A worker crash after external side effect but before local completion can leave `outcome_unknown`; re-execution is governed by node idempotency/reconciliation, not assumed safe.

---

## Wait / Subscription invariants

Waits are first-class durable state, not sleeping PHP jobs.

Candidate fields:
- scope + Run/node;
- wait type;
- correlation/subscription key;
- registered-at lower bound;
- due/timeout timestamps;
- event filter/profile reference;
- consume policy;
- consumed Event Inbox/WPE event identity;
- state/generation;
- expiry.

Hot indexes:
- scope + state + due/timeout time;
- scope + event type/correlation/subscription key + state;
- scope + Run/node;
- scope + consumed event identity for duplicate-resume diagnostics.

Correctness gates:
- duplicate at-least-once event cannot resume same single-consume wait twice;
- event before subscription registration follows explicit replay-window policy, not race-dependent luck;
- wrong-site event cannot resume a site-local wait.

---

## Approval invariants

Candidate fields:
- scope + Run/node;
- approver policy snapshot/reference;
- assignment/resolution strategy;
- state/generation;
- decision actor/time/reason safe metadata;
- due/escalation state;
- notification correlation references.

Hot indexes:
- scope + approver principal + pending state + due time;
- scope + Run/node;
- scope + state + due/escalation time.

Approval decision is concurrency-safe and idempotent. Snapshot assignment does not bypass current capability/policy recheck at decision time when the accepted Workflow policy requires it.

---

## Parallel / join state

Parallel branches use bounded fan-out.

Physical representation must support:
- branch identity;
- expected/created branch count where known;
- branch terminal state;
- join mode;
- join generation/decision;
- failure tolerance.

A join cannot fire twice because two final branches complete concurrently. Exact unique/CAS/transaction primitive remains P-011 evidence.

Unbounded collections cannot explode into unlimited child rows/jobs synchronously; chunked fan-out/admission control is required.

---

## Run context and payloads

Do not copy complete prior outputs into each Step row.

Paper direction:
- small typed non-sensitive context in bounded versioned document/reference;
- large payload in owning domain/object store with stable reference;
- files as protected asset refs;
- secrets as Vault refs;
- private Form/Chat/Membership data remains in owning domain unless a minimum classified snapshot is genuinely needed.

Generic Workflow runtime logs do not become a duplicate warehouse of user/provider payloads.

---

## Scheduling boundary with JobService

Workflow state transition decides that a node is runnable; JobService provides execution opportunity.

Future safe sequence candidate:
1. under Workflow state transaction, transition node/run into durable runnable/scheduled state and create a stable execution generation/idempotency identity;
2. commit;
3. enqueue/reconcile corresponding JobService Job using that stable identity;
4. worker claims Job;
5. worker re-reads Workflow precondition/generation before side effect;
6. node handler executes under its idempotency contract;
7. Workflow state commits result;
8. next nodes/waits/approvals become durable;
9. future Job enqueues occur after committed state.

If enqueue fails after Workflow commit, a reconciler can re-enqueue. If duplicate Job delivery occurs, Workflow precondition/idempotency prevents duplicate logical transition.

The exact outbox/reconciliation mechanism remains evidence-gated.

---

## Retry and unknown outcome

Retryable node state stores intended next-at time independent of backend runner timing.

Unknown external write outcome is not ordinary retryable failure. It can require:
- provider idempotency key reuse;
- provider operation-status lookup;
- domain reconciliation;
- manual intervention.

No `catch all => retry` policy.

---

## Cancellation / compensation

`cancel_requested` and `cancelled` remain distinct.

Cancellation correctness:
- no new future nodes start after effective cancel boundary;
- running non-interruptible/external action can finish/enter unknown outcome;
- compensation is a new durable action/attempt with its own idempotency/retry state;
- `compensation_failed` remains visible and recoverable.

Do not label a run rolled back unless compensations actually completed according to contract.

---

## Retention

Separate retention for:
- Run summary;
- Step attempts;
- waits/approvals;
- sensitive context/payload refs;
- error diagnostics;
- compensation details;
- Audit links.

Definition revision needed by retained Run cannot be purged first.

Cleanup is chunked/idempotent and site-scoped. Large payload retention can be shorter than Run metadata.

---

## Site lifecycle

Archive/suspend can stop new runnable work while preserving deterministic resume/cancel/reconcile behavior according to workflow classification.

Destructive site deletion uses Site Lifecycle Coordinator:
- stop new triggers;
- drain/cancel scoped runnable jobs;
- resolve/expire waits/approvals according to policy;
- preserve required recovery/audit/retention state;
- remove site-owned WF1 rows or WF2 tables only after domain cleanup;
- never delete network-owned Workflow Definition/runtime by implication.

---

## Backup/Restore

Site Backup captures target-site Workflow Runtime only where recovery profile requires it.

Restore safety:
- completed terminal Runs do not re-execute;
- runnable/retry/unknown/wait states enter reconciliation against current JobService/Event Inbox/provider state;
- restored Job backend IDs are advisory/stale until reconciled;
- pinned Definition revision must exist or Run becomes degraded/recovery state, never silently reinterpreted against latest revision;
- restored approvals do not bypass current decision authorization;
- idempotency identities survive where required to prevent duplicate side effects.

---

## P-011 future evidence matrix — NOT AUTHORIZED

Datasets:
- 100k / 1M Run history where practical;
- 10k/100k simultaneous waits;
- 1k/10k pending approvals;
- high-rate trigger dedupe;
- parallel bounded fan-out/join;
- long retention history;
- 100/1k/10k-site networks.

Correctness/concurrency:
- duplicate trigger creates one run;
- duplicate Job delivery;
- two workers same node;
- worker crash before side effect / after side effect / after result commit;
- enqueue failure after Workflow commit;
- event arrives before/after wait registration;
- duplicate event resume;
- concurrent approvals;
- final parallel branches complete concurrently;
- cancel while node running;
- compensation failure;
- site archive/delete/restore mid-run;
- wrong-site Run/Wait/Approval/Event IDs.

Performance:
- runnable node selection;
- run detail query count;
- event-to-resume latency;
- approval inbox latency;
- retention cleanup;
- noisy-neighbor workflow/site;
- WF1 vs WF2 provisioning/migration/Backup cost.

Capture p50/p95/p99, rows examined/query plans, lock/deadlock/retry behavior, storage/index growth and duplicate-side-effect count.

## Selection gate

WF1 is rejected regardless of speed if duplicate side effects, wrong-site resume/mutation, double join/approval, cancellation state corruption or Restore replay occurs. WF2 remains mandatory comparison.

## Development gate

No Workflow table, migration, trigger runtime, Job enqueue/run, Event subscription, approval action, provider call, fixture or benchmark is authorized. ADR-0014 explicit owner consent remains required.