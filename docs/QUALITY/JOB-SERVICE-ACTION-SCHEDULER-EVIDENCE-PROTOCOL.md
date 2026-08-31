# WPEssential — P-003 JobService / Cron / Action Scheduler Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP03`  
Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Governs: ADR-0006, ADR-0059, ADR-0068, ADR-0083, JobService contracts, Cron Job Builder specification, Action Scheduler packaging/coexistence profile, Multisite/Site Lifecycle, ADR-0014.

## 1. Purpose

Define bounded executable evidence required before WPEssential can claim a production-ready JobService backend, Cron/schedule runtime, runner, claim/lease model, fairness/backpressure policy or Action Scheduler adapter.

This refines the existing P-003 protocol; it does **not** create a second Job execution authority.

## 2. Canonical invariants

A future certified implementation must preserve:

1. **WPE JobService owns stable Job Type / Schedule / Job / Attempt / Runner / Execution Policy semantics.**
2. **Action Scheduler or another backend is an adapter/execution opportunity, never WPE business-domain truth.**
3. **Execution is at-least-once; exactly-once execution is never claimed.**
4. **Idempotency/reconciliation belongs to WPE/domain semantics, not to optimistic assumptions about queue delivery.**
5. **A stale/expired claim is not proof that a side effect did not occur.**
6. **Cron calendar recurrence is a product calendar/timezone contract, not whatever recurrence behavior a backend happens to provide.**
7. **Resource/concurrency keys, fairness and backpressure are WPE policies even when the backend lacks native equivalents.**
8. **Jobs revalidate current security/business preconditions when delayed execution can outlive the original request.**
9. **Backend/action rows and logs do not replace WPE durable Job/Attempt truth.**
10. **Site scope is explicit; same IDs/group names across sites must not collide.**

## 3. Future runtime profile

Every evidence run pins:
- WordPress/PHP/database versions accepted by P-001;
- single-site/Multisite topology;
- JobService schema/profile version;
- candidate backend and exact version;
- Action Scheduler bundled/standalone/third-party coexistence state where applicable;
- WP-Cron mode;
- `DISABLE_WP_CRON` + real cron/WP-CLI runner mode where applicable;
- loopback/network restrictions;
- persistent object cache profile;
- J1/J2/J3 physical profile under test;
- server/site/network timezone configuration;
- worker/batch/concurrency/resource limits;
- retention configuration;
- representative hosting resource constraints.

No runtime execution occurs until explicit owner consent and prerequisite environment floors exist.

# 4. Dependency, packaging and coexistence fixtures

### JS-01 — WPE-only candidate backend load
WPE-owned adapter/backend initializes once in the supported load order with required API surface available.

### JS-02 — Older compatible third-party Action Scheduler
Coexistence with an older compatible copy preserves WPE behavior and does not mutate third-party actions/groups.

### JS-03 — Newer compatible third-party Action Scheduler
Coexistence with a newer compatible copy uses verified loader/version semantics rather than assuming the bundled copy wins.

### JS-04 — Standalone Action Scheduler plugin
Standalone plugin presence does not cause duplicate bootstrap/schema/runner registration or WPE ownership of unrelated actions.

### JS-05 — Initialization ordering
WPE calls backend APIs only after the backend is actually initialized in each certified load-order profile.

### JS-06 — Unsupported/incompatible backend profile
Missing/too-old/unsupported features enter explicit degraded state; site/admin does not fatal or partially schedule corrupted work.

### JS-07 — Third-party ownership isolation
WPE pause/cancel/cleanup/uninstall never touches actions, logs or groups it does not own.

### JS-08 — Backend schema migration interruption
Interrupted/in-progress backend migration is detected and surfaced; WPE does not report healthy scheduling until required compatibility exists.

# 5. Logical mapping and abstraction fixtures

### JS-09 — Job Type mapping
Stable WPE Job Type maps deterministically to backend hook/handler without exposing arbitrary callback execution from user data.

### JS-10 — Typed payload/reference mapping
Payload is schema/version validated before enqueue and execution; oversized/malformed payload cannot become arbitrary serialized backend data.

### JS-11 — WPE Job/Attempt identity
WPE stable Job/Attempt IDs correlate with backend action/claim/log references without making backend IDs canonical public API.

### JS-12 — Backend abstraction isolation
Switching/test-doubling backend does not change module-facing stable JobService contracts.

### JS-13 — Cancellation/unscheduling mapping
Backend cancellation is projected into WPE requested/effective state without falsely claiming an already-running side effect was undone.

### JS-14 — Progress/checkpoint projection
Long-running/chunked jobs expose bounded durable progress/checkpoints independently from ephemeral backend logs.

# 6. Cron and recurrence fixtures

### JS-15 — One-time schedule
One-time future job becomes eligible at/after its intended instant and does not recreate itself after success unless explicitly rescheduled.

### JS-16 — Fixed interval recurrence
Interval recurrence is based on the accepted elapsed-time semantics and does not silently become wall-clock recurrence.

### JS-17 — Calendar recurrence in site timezone
Calendar expression resolves the intended local wall-clock occurrence while durable due instants are represented unambiguously.

### JS-18 — Site timezone changed after schedule publish
Defined policy determines whether future occurrences follow the new site timezone or remain pinned; behavior is deterministic and visible.

### JS-19 — DST spring-forward gap
A nonexistent local wall-clock time follows the documented skip/advance policy exactly once; no duplicate or infinite recalculation occurs.

### JS-20 — DST fall-back fold
An ambiguous repeated local wall-clock time follows the documented once/both occurrence policy without accidental duplicate execution.

### JS-21 — UTC/non-DST timezone baseline
Equivalent schedule in UTC/non-DST zone remains stable and provides control evidence for timezone logic.

### JS-22 — Several missed occurrences
Runner outage followed by recovery applies explicit `skip`, `run_latest`, `catch_up_bounded` or equivalent accepted missed-run policy; backend defaults do not choose product behavior implicitly.

### JS-23 — Long occurrence overlaps next due time
Accepted overlap policy (`skip_if_running`, `queue_one`, `allow_overlap`, `coalesce`, or future accepted names) is enforced by WPE resource/precondition semantics.

### JS-24 — Next-run calculation truth
Admin/diagnostic next-run time matches the same calendar engine used for actual eligibility and displays timezone/source clearly.

### JS-25 — Recurrence definition edited
Published schedule revision/change uses explicit follow-new/pinned semantics; already-created Jobs are not silently reinterpreted.

### JS-26 — Disabled/paused schedule
No new occurrences are admitted while paused/disabled; existing running Job behavior follows separate cancellation policy.

# 7. Persistence and ambiguity fixtures

### JS-27 — WPE Job committed before backend action
Crash in this window leaves a discoverable enqueue-pending/reconciliation state, not a lost Job.

### JS-28 — Backend action committed before WPE backend reference
Reconciliation finds/adopts or safely recreates without uncontrolled duplicate work.

### JS-29 — Claim acquired before Attempt update
Crash does not make a claimed backend action invisible to WPE reconciliation.

### JS-30 — Handler side effect completes before success commit
Retry does not blindly repeat a protected side effect; idempotency/reconciliation determines outcome.

### JS-31 — WPE success committed before backend success projection
Stores reconcile to one truthful logical outcome without reopening completed work.

### JS-32 — Retry scheduled but failure-state update lost
Recovery reconstructs valid retry/failure state and prevents duplicate unbounded retries.

### JS-33 — Enqueue backend unavailable
WPE Job remains durable and visibly pending/degraded according to policy; accepted producer request is not falsely reported fully dispatched.

### JS-34 — Restore copied queued/running Job state
Restored queue/backend rows cannot auto-resume blindly; WPE revalidates environment, Job state and domain preconditions.

# 8. Claims, leases and worker-crash fixtures

### JS-35 — Concurrent runners claim due population
A due Job receives only the allowed active claim/attempt semantics while unrelated Jobs can progress.

### JS-36 — Worker killed immediately after claim
Claim eventually becomes recoverable without assuming no side effect occurred.

### JS-37 — Worker killed mid-handler
Retry/reconciliation follows Job Type idempotency/unknown-outcome policy.

### JS-38 — Lease expires while old worker still runs
New worker cannot safely assume exclusive ownership merely because lease expired; resource/attempt fencing or equivalent preconditions prevent two protected commits.

### JS-39 — Heartbeat/lease renewal
Healthy long-running job can maintain ownership within configured bounds without permanent orphan claims.

### JS-40 — Stale-claim cleanup
Cleanup only releases eligible stale claims and preserves audit/attempt evidence.

### JS-41 — PHP fatal
Fatal produces recoverable failed/ambiguous Attempt state and does not leave WPE Job permanently invisible.

### JS-42 — Max execution timeout
Timeout follows same unknown-outcome/idempotency rules rather than automatic blind retry.

### JS-43 — Memory termination
OOM-style worker loss can be diagnosed/recovered from durable Job state and bounded payload/checkpoints.

### JS-44 — Loopback spawn failure
Request-driven runner failure is visible in health state and does not imply Job failure when execution never began.

# 9. At-least-once and idempotency fixtures

### JS-45 — Idempotent cache rebuild
Duplicate opportunity converges on equivalent final state without harmful repeated effect.

### JS-46 — Versioned DB state transition
CAS/version/precondition prevents duplicate/stale transition commit.

### JS-47 — Provider with idempotency key
Stable provider idempotency identity is reused across retries and provider outcome is reconciled.

### JS-48 — Provider unknown outcome/no idempotency support
Timeout after request enters outcome-unknown/manual/reconciliation policy; no blind infinite resend.

### JS-49 — Chunked import
Duplicate chunk Job does not duplicate owned target mutations due to import identity/checkpoint semantics.

### JS-50 — Notification/email send
Retry policy acknowledges external duplicate-delivery limits; evidence/attempt truth is not converted into an exactly-once claim.

### JS-51 — Backup remote part upload
Part identity/checksum/provider semantics prevent incorrect duplicate logical Part commitment.

### JS-52 — Destructive reset/restore checkpoint
Duplicate Job cannot re-enter an already-completed destructive stage without stage/precondition/recovery checks.

# 10. Urgency, fairness and starvation fixtures

### JS-53 — Mixed urgency steady state
Security/transactional, interactive, normal, bulk and maintenance classes receive service according to accepted policy.

### JS-54 — Constant high-priority arrivals
Lower allowed classes do not starve indefinitely under documented healthy capacity/fairness constraints.

### JS-55 — Huge bulk backlog then interactive work
Interactive work remains within accepted latency budget without violating explicit dependencies.

### JS-56 — Provider-limited backlog plus unrelated critical work
Email/provider backlog cannot monopolize all workers/connections and block membership/recovery work indefinitely.

### JS-57 — Two hot Multisite sites
One site cannot consume all fair-share capacity while another eligible site starves indefinitely.

### JS-58 — Fairness with explicit dependency
Scheduler fairness never executes a dependent Job before its durable predecessor/precondition is satisfied.

# 11. Resource/concurrency key fixtures

### JS-59 — Site-wide backup exclusive key
Two incompatible backup captures for same protected resource cannot run concurrently.

### JS-60 — Restore/reset destructive-exclusive key
Mutually destructive operations serialize and stale lease does not silently permit overlap.

### JS-61 — Same import Run key
Chunks respect declared per-Run concurrency rather than global over-serialization or unsafe overlap.

### JS-62 — Same provider connection key
Provider concurrency/rate resource cap is enforced independently of unrelated providers.

### JS-63 — Independent keys make progress
Safe independent resources/sites can run concurrently; exclusive policy is not unnecessarily global.

### JS-64 — Multisite key isolation
Same local resource identifiers on different sites do not collide accidentally unless a deliberate network-global key is used.

# 12. Backpressure and admission fixtures

### JS-65 — Large import producer
Producer creates bounded chunks/work windows instead of hundreds of thousands of synchronous backend actions.

### JS-66 — Large notification fan-out
Recipient expansion is bounded and backpressured; queue DB growth and memory stay within accepted budgets.

### JS-67 — Watermark/backup producer pressure
Low-urgency producer slows/pauses above high-water policy while critical/recovery work remains admissible.

### JS-68 — Consumer recovers after backlog drain
Backpressure state clears/reconciles and producers resume without double-generating logical work.

### JS-69 — Persistent overload diagnostics
Admin/health surfaces show oldest age/backlog/paused producer state instead of silently accepting unbounded work.

# 13. Runner-mode fixtures

### JS-70 — Request-driven low-traffic site
Due work can be late; UI/docs report best-effort/request-driven semantics truthfully rather than exact timing guarantees.

### JS-71 — No traffic after due time
No execution until a runner opportunity occurs; Job stays due/past-due, not falsely failed.

### JS-72 — Blocked loopback
Health detects degraded spawn/runner state and gives safe real-cron/CLI guidance.

### JS-73 — Real system cron/WP-CLI due runner
Only eligible due work executes under defined scope; recurrence/calendar semantics stay WPE-owned.

### JS-74 — Overlapping system cron invocations
Concurrent runners preserve claim/resource semantics and do not duplicate protected execution.

### JS-75 — Killed CLI runner
Claims/Attempts reconcile using same durable rules as web runner loss.

### JS-76 — Focused group runner
Focused group execution cannot bypass cross-group Workflow/domain dependency/precondition checks.

# 14. Cancellation, pause and manual-control fixtures

### JS-77 — Pending cancel
Pending Job stops becoming eligible and records requested/effective cancellation truthfully.

### JS-78 — Claimed-before-start cancel race
Outcome distinguishes cancelled-before-handler from handler-already-started ambiguity.

### JS-79 — Running chunked cooperative cancel
Handler observes cancellation at safe checkpoints; partial committed work remains truthfully recorded.

### JS-80 — Non-interruptible critical section
Cancellation stays requested/pending until critical section safely completes; UI does not claim immediate abort.

### JS-81 — Pause/resume parent workload
No new eligible children execute while paused except explicit recovery/control exceptions; resume does not duplicate prior completed work.

### JS-82 — Manual run/retry
Operator action reauthorizes current Job/domain preconditions and creates auditable Attempt semantics rather than mutating backend row directly.

# 15. Payload, security and secret fixtures

### JS-83 — Invalid payload at enqueue
Schema-invalid payload is rejected before durable executable Job is admitted.

### JS-84 — Old payload schema at execution
Supported migration/compatibility path is explicit; unknown incompatible payload fails safely.

### JS-85 — Secret reference rotated/deleted
Job resolves current permitted secret reference at execution or enters explicit blocked/degraded state; raw secret is not copied into backend args/logs.

### JS-86 — Capability/entitlement revoked before execution
Delayed privileged Job revalidates current authority/business policy where action semantics require it.

### JS-87 — Resource deleted/revoked
Job becomes no-op/cancelled/failed according to explicit domain policy; it does not recreate or act on stale resource blindly.

### JS-88 — Malicious/oversized payload
Bounded parser/schema rejects payload that could exhaust resources or select arbitrary callbacks/classes/functions.

# 16. Retention, cleanup and observability fixtures

### JS-89 — Completed/cancelled retention
WPE Job/Attempt retention follows declared policy independently from backend action cleanup.

### JS-90 — Failed-job evidence retention
Required failure/attempt evidence remains long enough for support/audit while respecting privacy policy.

### JS-91 — Backend log cleanup
Backend log pruning cannot make WPE UI falsely promise unavailable detailed evidence.

### JS-92 — Parent-domain retained after Job cleanup
Workflow/import/backup domain truth remains understandable after backend/WPE Job operational records are legitimately pruned.

### JS-93 — Health metrics
Expose bounded metrics for heartbeat, due/past-due, oldest age, running/claimed, retries/failures, stale claims, throughput and backpressure.

### JS-94 — Alert deduplication
Runner/backlog alerts do not recursively produce runaway notification/jobs and are rate/dedupe controlled.

### JS-95 — Log redaction
Backend/WPE logs exclude secrets and unnecessary sensitive payload data.

# 17. Multisite and Site Lifecycle fixtures

### JS-96 — Per-site queue isolation
Every site-scoped Job resolves only its authoritative site context/resources.

### JS-97 — Site deleted with pending work
Lifecycle drain blocks/reconciles pending/running site jobs before destructive cleanup; copied Jobs cannot mutate removed site later.

### JS-98 — Network-owned Job
Explicit network scope requires network authority/storage semantics and cannot be simulated by arbitrary site switching.

### JS-99 — Network activation/deactivation
Module/adapter lifecycle does not duplicate runners/schedules across sites or delete unrelated data.

### JS-100 — Site clone/restore
Cloned/restored Jobs/schedules undergo identity/environment revalidation before any automatic execution.

# 18. Physical mapping and scale fixtures

### JS-101 — J1/PT-D Jobs + Attempts baseline
Measure scoped shared-table writes/claims/history/indexes and validate wrong-site predicates under representative workload.

### JS-102 — J2 PT-C current + PT-D history
Compare current-state lookup/claim cost vs history-write/retention behavior and migration/reconciliation complexity.

### JS-103 — J3 PT-C low-volume control
Establish control evidence for low-volume platform jobs; reject J3 for workloads where contention/growth violates budgets.

### JS-104 — 100k/1M Job mixed workload
Measure admission, claim, retry, cleanup, fairness, oldest-age, DB/CPU/memory and frontend/admin impact.

### JS-105 — 100/1k/10k-site workload
Measure scope predicates, fairness, lifecycle, diagnostics and migration cost across large networks.

### JS-106 — Backend concurrency/batch tuning
Benchmark bounded batch/concurrent-runner candidates; no universal high-concurrency default is accepted without resource evidence.

# 19. MUST NOT / stop-the-line gates

P-003 certification fails if any fixture demonstrates:
- exactly-once execution being claimed without domain evidence;
- duplicate worker opportunity causing repeated protected side effect contrary to certified idempotency semantics;
- expired/stale claim treated as proof that prior worker made no side effect;
- backend row/log used as authoritative Workflow/import/backup/business truth;
- calendar/DST behavior silently chosen by backend defaults rather than WPE policy;
- unfair scheduling allowing an eligible lower class/site to starve indefinitely under documented healthy capacity;
- concurrency/resource key collision across sites;
- delayed privileged Job executing after required authority/resource revoke without revalidation;
- raw secret in backend args/searchable logs;
- WPE cleanup/uninstall deleting third-party actions/groups;
- restore/clone automatically replaying copied active jobs without revalidation;
- site lifecycle allowing pending Job to mutate deleted/wrong site.

These are stop-the-line defects for the affected certification scope.

# 20. Performance evidence

Capture at minimum:
- enqueue/admission latency;
- due-to-start latency p50/p95/p99 by urgency/site;
- claim throughput/contention;
- actions/jobs per second;
- Attempt write rate;
- oldest eligible age;
- starvation duration;
- DB query/lock load;
- PHP worker/memory/CPU usage;
- loopback/CLI runner behavior;
- retry/backoff amplification;
- queue/table/log growth;
- cleanup throughput;
- J1/J2/J3 comparison;
- 100/1k/10k-site noisy-neighbor/fairness results.

Performance optimization must not weaken idempotency, resource exclusion, fairness, security revalidation or site isolation.

# 21. Required future P-003 report

Include:
- environment/version matrix;
- exact backend/Action Scheduler dependency/coexistence strategy;
- JS-01…JS-106 pass/fail/NA with rationale;
- Cron timezone/DST/missed/overlap evidence;
- claim/lease/crash-window evidence;
- idempotency/unknown-outcome evidence;
- fairness/backpressure/resource-key evidence;
- runner-mode health evidence;
- security/secret/retention findings;
- Multisite/Site Lifecycle evidence;
- J1/J2/J3 physical measurements;
- accepted defaults/limits proposed from evidence;
- unsupported/degraded profiles;
- final backend-adapter recommendation.

## 22. Current state

**JS fixtures documented: 106.**  
**JS fixtures executed: 0/106.**  
**JobService backend certified: none.**  
**Cron/DST runtime certified: none.**

Action Scheduler remains a preferred candidate adapter, not a runtime-certified backend.

No package install, backend schema creation, scheduled action, runner, WP-CLI command, benchmark, cron trigger, Job/Attempt row, queue mutation or provider call has been executed.

## 23. Development gate

Execution requires explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md` and prerequisite P-001 environment floor.

Until then this protocol remains planning evidence only: `NOT EXECUTED`.