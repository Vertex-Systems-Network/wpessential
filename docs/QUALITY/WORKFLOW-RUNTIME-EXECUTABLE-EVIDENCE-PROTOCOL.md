# WPEssential — P-011 Workflow Runtime Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP03`  
Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Governs: ADR-0082, Workflow Runtime contracts, JobService/P-003, Policy/Abilities, Event Bus/Event Inbox, Forms, Membership, Notifications/Email, Import, Status Manager, Multisite/Site Lifecycle, ADR-0014.

## 1. Purpose

Define bounded executable evidence required before WPEssential can claim production-ready Workflow triggering, Run/Step/Wait/Approval durability, branching, joins, delayed execution, retries, idempotency, external side-effect recovery, manual intervention, cancellation, Multisite isolation or Workflow scale.

The protocol tests the accepted Workflow architecture. It does not authorize execution or make JobService/Action Scheduler the Workflow source of truth.

## 2. Canonical invariants

A future certified implementation must preserve:

1. **Workflow Definition/editor state is separate from Workflow Runtime business truth.**
2. **Every Run pins the intended published Workflow revision.**
3. **Run / Step / Wait / Approval durable state is authoritative; Job/queue rows only provide execution opportunities.**
4. **At-least-once Job/event delivery is expected; Workflow state transitions use explicit idempotency/preconditions.**
5. **A duplicate Job/event must not duplicate a protected Workflow transition or side effect.**
6. **A stale claim, missing queue row or worker crash is not proof that the domain action did not occur.**
7. **Authorization/business preconditions are revalidated at the actual action boundary where delayed execution can outlive the initiating request.**
8. **External unknown outcomes enter reconciliation/manual policy, never blind infinite retry.**
9. **Branch/join/wait/approval semantics are explicit durable state, not inferred from timing/order of queue delivery.**
10. **Site/network scope is explicit and cannot be selected by untrusted IDs alone.**
11. **Workflow logs/history exclude secrets and unnecessary sensitive payload data.**
12. **Restore/clone cannot blindly replay copied active Runs/Jobs without environment and state revalidation.**

## 3. Future certification profile

Every future evidence run records:
- WordPress/PHP/database versions;
- single-site/Multisite topology;
- Workflow Definition/compiler revision;
- Workflow Runtime physical profile (`WF1/PT-D`, `WF2/PT-E`, or later accepted profile);
- Run/Step/Wait/Approval/event/idempotency schema version;
- JobService/backend profile and P-003 certification status;
- Event Bus/Event Inbox adapter profile;
- Policy/Ability/Entitlement versions;
- external Connection/provider adapters used by fixtures;
- site/network timezone profile;
- object/persistent cache layers relevant to Workflow state;
- retention/privacy configuration;
- reference workload/scale profile.

# 4. Definition, publish and revision fixtures

### WF-01 — Draft Workflow is not executable
A Draft revision cannot start a production Run merely because its UUID/route is known.

### WF-02 — Publish validation
Publish rejects missing trigger/start node, invalid references, impossible branch/join graph, unsupported action contracts and unresolved required dependencies.

### WF-03 — Published revision pinning
New Run stores the intended published Workflow revision and all later Steps resolve against that pinned revision.

### WF-04 — Definition edited while Run active
Draft/new publish does not silently reinterpret already-started Run graph/actions.

### WF-05 — Explicit active-Run migration
If future product supports Run revision migration, it requires explicit compatible mapping/preconditions and records the migration; no implicit migration.

### WF-06 — Required historical revision retention
Revision required by retained Runs/history cannot be destructively removed without sufficient immutable historical execution metadata.

### WF-07 — Missing/corrupt revision
Affected Run enters degraded/manual/recovery state; it does not execute against an unrelated current Workflow.

### WF-08 — Dependency disabled/deleted after publish
Start/action behavior follows explicit blocked/degraded policy and never substitutes an arbitrary alternative dependency.

# 5. Trigger, event and Run-start fixtures

### WF-09 — Manual trigger authorization
Manual Run start requires current server-side Ability/Policy; visible button alone grants nothing.

### WF-10 — Form trigger
Accepted Form submission triggers at most one logical Run per configured idempotency/binding contract despite duplicate dispatch.

### WF-11 — Status transition trigger
Workflow reacts to authoritative state transition evidence and does not double-trigger from overlapping observer hooks for the same logical transition.

### WF-12 — Membership/entitlement trigger
Provider raw status cannot directly start privileged grant/revoke workflow without normalized authoritative Membership event/state.

### WF-13 — Webhook/Event Inbox trigger
Only verified/trusted normalized event identity can start configured Run; forged direct payload cannot bypass webhook verification/Connection policy.

### WF-14 — Cron/schedule trigger
Schedule occurrence identity is stable so duplicate Job delivery does not create duplicate logical Runs.

### WF-15 — REST/Ability trigger
Authenticated endpoint/Ability rechecks current Policy and cannot select another site/subject through forged IDs.

### WF-16 — Duplicate event identity
Exact logical event replay resolves to existing Run/dedupe record or explicit duplicate result rather than a second protected Run.

### WF-17 — Same event ID different material payload
Conflict/anomaly is recorded; existing Run is not silently overwritten/reinterpreted.

### WF-18 — Concurrent Run-start race
Two workers processing the same trigger identity admit one logical Run under certified idempotency storage semantics.

# 6. Run and Step state-transition fixtures

### WF-19 — Run creation durability
Run identity/revision/scope/correlation/trigger evidence commits before downstream Step execution becomes authoritative.

### WF-20 — Initial Step admission
Only graph-valid initial Step(s) become eligible; arbitrary node ID from Job payload cannot start another Step.

### WF-21 — Step precondition CAS/version
Stale/duplicate worker cannot commit a Step transition after another worker already advanced the Step/Run state.

### WF-22 — Step success commit
Step result/output reference and next-state admission are durable/reconcilable without relying on backend success log ordering.

### WF-23 — Step failure commit
Failure class, retry/manual/final policy and safe error metadata are durable before future decisions depend on them.

### WF-24 — Run terminal success
Run becomes successful only when all required graph semantics are satisfied; queued backend work alone is not completion evidence.

### WF-25 — Run terminal failure
Final failure is explicit and does not erase successfully committed prior Steps/side effects.

### WF-26 — Partial-success state
Mixed results are represented truthfully where the Workflow policy permits continue-on-error/partial outcomes.

### WF-27 — Duplicate Step Job
Repeated execution opportunity sees committed/precondition state and does not repeat a completed transition/side effect incorrectly.

### WF-28 — Out-of-order Step Job
A Job arriving before its durable predecessor/join/wait condition is ready does not bypass graph preconditions.

# 7. Conditions and branch fixtures

### WF-29 — Typed condition evaluation
Condition engine uses declared typed values/operators and rejects malformed/incompatible inputs safely.

### WF-30 — Missing value semantics
Missing/null/empty are distinguished according to condition contract and do not silently coerce into privileged branch decisions.

### WF-31 — Policy-sensitive condition data
Condition cannot fetch/inspect protected cross-user/site resource without the current Workflow execution authority/Policy contract.

### WF-32 — True branch
Exactly the intended branch becomes eligible from a deterministic condition result.

### WF-33 — False/else branch
Else path is explicit and does not depend on queue timing or missing backend action.

### WF-34 — Multi-branch priority/exclusivity
First-match/all-match semantics are explicit; concurrent workers cannot activate mutually exclusive branches accidentally.

### WF-35 — Condition evaluation retry
Transient source failure does not become false by default; retry/failure policy is explicit.

### WF-36 — Definition condition changed after Run start
Pinned revision continues using its original condition contract.

# 8. Parallel branch and join fixtures

### WF-37 — Parallel branch fan-out
Configured branches create bounded eligible Step state without uncontrolled synchronous Job explosion.

### WF-38 — Join waits for required predecessors
Join cannot advance until the configured predecessor success/completion policy is durably satisfied.

### WF-39 — Concurrent final predecessor race
Two branches completing simultaneously advance the join exactly once.

### WF-40 — Duplicate predecessor completion
Repeated completion signal does not increment join counters/conditions twice.

### WF-41 — Failed predecessor join policy
`all_success`, `all_complete`, threshold or future accepted policy is explicit and tested; failure does not silently deadlock forever.

### WF-42 — Cancelled predecessor join policy
Join handles cancellation according to explicit graph semantics, not as missing data indefinitely.

### WF-43 — Large fan-out bound
Branch/list fan-out respects accepted bounded expansion/backpressure limits.

### WF-44 — Join after restore/recovery
Restored branch state is recalculated/reconciled from durable predecessors and cannot double-advance join.

# 9. Wait, delay and timer fixtures

### WF-45 — Fixed delay wait
Run enters durable Wait state before delayed Job/timer opportunity is scheduled.

### WF-46 — Calendar wait/timezone semantics
Calendar wait follows the same accepted timezone/DST calendar engine as Job/Cron policy where applicable.

### WF-47 — Wait Job lost/enqueue failure
Durable Wait remains discoverable and reconciler can recreate execution opportunity without duplicating Step transition.

### WF-48 — Duplicate timer delivery
Repeated timer Job advances the Wait/Step exactly once.

### WF-49 — Wait condition changed externally
At wake time current external/resource preconditions are revalidated according to action semantics.

### WF-50 — Run cancelled while waiting
Wait/timer is disabled/reconciled and cannot resurrect cancelled Run later.

### WF-51 — Site deleted while waiting
Lifecycle drain/reconciliation prevents future wake from mutating deleted/wrong site.

### WF-52 — Restore copied future Wait
Restored timer does not auto-fire until Run/environment/revision/site identity is revalidated.

# 10. Approval fixtures

### WF-53 — Approval creation
Approval record stores Run/Step/revision/scope/allowed approver policy before notifying approvers.

### WF-54 — Unauthorized approval attempt
User outside current approval Policy cannot approve/reject by knowing Approval ID/URL.

### WF-55 — Concurrent double approval
Two authorized approvers racing on single-decision approval commit one accepted terminal decision according to policy.

### WF-56 — Approve vs reject race
Conflicting simultaneous decisions resolve deterministically via version/precondition semantics and preserve rejected competing evidence.

### WF-57 — Approval role/capability revoked
Previously notified user is reauthorized at decision time; stale UI/link does not preserve approval power.

### WF-58 — Approval expires
Expired approval cannot be decided through stale link; timeout branch/failure policy executes exactly once.

### WF-59 — Approval reminder duplicate Jobs
Reminders may retry without duplicating approval record or Workflow transition.

### WF-60 — Multi-approver quorum
Configured quorum/count/any/all semantics are concurrency-safe and count each eligible principal once.

### WF-61 — Approval after Run cancellation
Decision is rejected/recorded stale and cannot restart cancelled Run.

### WF-62 — Approval notification delivery failure
Approval remains durable/actionable according to policy; notification failure is not equivalent to approval failure or automatic decision.

# 11. JobService execution and retry fixtures

### WF-63 — Run state committed before Job enqueue
Enqueue failure leaves a recoverable pending Step/Wait state; Workflow truth is not lost.

### WF-64 — Backend Job exists before Workflow backend reference
Reconciliation adopts/deduplicates using Workflow Step/attempt identity rather than creating uncontrolled duplicate execution.

### WF-65 — Worker crash before action side effect
Retry can execute safely under Step preconditions/idempotency.

### WF-66 — Worker crash after action side effect before Step success
Action-specific idempotency/reconciliation decides outcome; duplicate Job never implies safe blind replay.

### WF-67 — Job lease expires while old worker still runs
Late old worker cannot commit stale Step transition after another worker has advanced state.

### WF-68 — Retry backoff/max-attempt policy
Transient retries are bounded, observable and separate from final/manual state.

### WF-69 — Manual retry
Authorized operator retry creates a new controlled attempt against current Step/Run preconditions; it does not edit queue/backend rows directly.

### WF-70 — JobService paused/backpressured
Workflow shows durable waiting-for-execution state and does not misreport pending Step as failed/completed.

# 12. Action and side-effect fixtures

### WF-71 — Read-only Ability action
Typed input/output and current Policy are enforced; arbitrary function/class/hook name from Workflow data cannot execute.

### WF-72 — Internal DB/entity mutation
Owning Data Source/domain API handles validation/authorization/versioning; Workflow does not bypass invariants through direct generic writes.

### WF-73 — Relation mutation
Relation cardinality/scope/Policy are enforced under duplicate/retry conditions.

### WF-74 — Status transition action
Uses Status Manager transition engine; raw state overwrite does not bypass guards/history.

### WF-75 — Membership/entitlement action
Uses authoritative Membership/Entitlement service; repeated Workflow attempt cannot grant/revoke twice incorrectly.

### WF-76 — Notification action
Occurrence/recipient/delivery semantics remain separate; retry does not claim exactly-once human delivery.

### WF-77 — Email action
Provider submission/evidence semantics are respected; unknown delivery outcome is not represented as unread/failed truth.

### WF-78 — Outbound webhook/HTTP action
Safe HTTP/Connection policy, SSRF controls, timeout, rate limit and idempotency/reconciliation apply.

### WF-79 — Import/Backup/Reset child operation
Workflow invokes typed owning operation and tracks returned domain Run/reference; it never treats child Job row as domain completion.

### WF-80 — Arbitrary code execution denial
Workflow configuration cannot become arbitrary PHP/JS/shell/eval execution through action names or payload expressions.

# 13. External unknown-outcome and reconciliation fixtures

### WF-81 — Provider timeout before request send
Retry is safe when evidence proves no remote side effect occurred.

### WF-82 — Provider timeout after request may have committed
Step enters outcome-unknown/reconciliation state where provider cannot guarantee idempotency.

### WF-83 — Provider idempotency key
Stable action-attempt identity is reused across retries and reconciled with provider result.

### WF-84 — Provider 429/5xx
Retry-after/backoff/rate policy is bounded and does not block unrelated Workflow work indefinitely.

### WF-85 — Provider credentials revoked
Action fails/blocks safely and does not expose raw secret in Workflow history/logs.

### WF-86 — Reconciliation proves remote success
Workflow commits success once with evidence/reference and prevents another side-effect attempt.

### WF-87 — Reconciliation proves remote failure
Workflow may retry according to policy with a new/appropriate attempt while preserving historical ambiguity evidence.

### WF-88 — Reconciliation remains unknown
Workflow enters manual intervention/terminal unknown policy; it does not retry forever.

# 14. Cancellation, pause, intervention and compensation fixtures

### WF-89 — Cancel pending Run
No new ordinary Steps become eligible; already committed side effects remain historical truth.

### WF-90 — Cancel running action
Cancellation is cooperative/requested; UI does not claim external side effect was undone if action cannot be interrupted.

### WF-91 — Pause Run
New ordinary Step execution pauses while explicit recovery/control actions may remain allowed.

### WF-92 — Resume Run
Resume revalidates revision/dependencies/security and does not duplicate completed Steps.

### WF-93 — Skip Step manual intervention
Only allowed when Workflow policy marks Step skippable; impact is audited and downstream join/branch semantics remain consistent.

### WF-94 — Override/retry with edited input
High-risk intervention is separately authorized/audited and cannot rewrite immutable historical attempt evidence.

### WF-95 — Compensation action
Where a Workflow defines compensation, compensation is a new explicit action/attempt, not rollback fiction; its own failure/unknown outcome is recorded.

### WF-96 — Compensation cannot undo irreversible effect
UI/history reports residual effect and does not mark Run fully rolled back when compensation is partial/impossible.

# 15. Security, privacy and observability fixtures

### WF-97 — Secret reference handling
Workflow Definition/Run/Step/log stores secret references/minimized metadata, not secret plaintext where avoidable.

### WF-98 — Sensitive trigger/action payload minimization
Only fields required for execution/audit are retained; protected Form/Profile/provider data does not become generic Workflow history.

### WF-99 — Cross-site Run/Step/Approval IDOR
Direct IDs cannot read/decide/retry/cancel another site's Workflow resource.

### WF-100 — Audit/correlation integrity
Run/Step/Attempt/Job/Event/Approval/provider evidence share safe correlation identifiers without exposing secrets or raw sensitive payloads.

### WF-101 — User access revoked before delayed action
Current authority/business preconditions are rechecked where action semantics require principal authorization.

### WF-102 — Error redaction
Stack traces, SQL, private paths, Vault/provider secrets and unrelated scope identifiers are absent from ordinary user-facing errors.

# 16. Multisite, restore and lifecycle fixtures

### WF-103 — WF1/PT-D site isolation
Shared scoped tables include authoritative site/network identity in every Run/Step/Wait/Approval/event/idempotency path.

### WF-104 — Network-owned Workflow
Network scope requires explicit network authority/storage/Policy and is not created by arbitrary site switching.

### WF-105 — Site delete with active Runs
Lifecycle drain blocks new unsafe Steps, reconciles running actions and applies explicit retention/delete policy to historical Workflow data.

### WF-106 — Site clone
Copied Workflow definitions may be remapped, but active Run/Wait/Approval execution state is not blindly replayed in clone.

### WF-107 — Backup/restore active Runs
Restored `running/waiting/approval_pending` states enter revalidation/reconciliation before any execution resumes.

### WF-108 — Restored external-effect Run
Provider/domain evidence is reconciled before retry when backup may have captured pre-success local state after remote success.

# 17. Physical topology and scale fixtures

### WF-109 — WF1/PT-D baseline
Measure shared scoped Run/Step/Wait/Approval/event/idempotency writes, queries, indexes and wrong-site predicates under reference workloads.

### WF-110 — WF2/PT-E mandatory comparison
Measure per-site table provisioning/migration/version skew/lifecycle/diagnostics fan-out and hot-site isolation.

### WF-111 — High Step-count Run
Large but bounded Run measures Step history/query/memory/UI pagination without unbounded graph/history load.

### WF-112 — Large Wait population
Measure due-wait reconciliation/index efficiency and Job enqueue/backpressure behavior.

### WF-113 — Large Approval population
Measure pending approval lookup/expiry/reminder/quorum behavior with authorization-safe pagination.

### WF-114 — 100k/1M Run/Step history
Measure append/read/retention/privacy/cleanup/query plans and storage growth.

### WF-115 — 100/1k/10k-site topology
Measure scope isolation, noisy-neighbor behavior, migrations, diagnostics and lifecycle cost for WF1 vs WF2.

### WF-116 — Mixed trigger/action stress
Concurrent Form/event/cron/webhook triggers plus waits/approvals/provider actions measure throughput, fairness, retry amplification and JobService interaction.

# 18. MUST NOT / stop-the-line gates

P-011 certification fails if any fixture demonstrates:
- Draft/current Workflow silently replacing pinned active Run revision;
- queue/backend row treated as authoritative Run/Step/Approval truth;
- duplicate event/Job causing repeated protected transition or non-idempotent effect contrary to certified semantics;
- stale worker committing after another worker advanced the same Step;
- branch/join/approval result determined by queue timing rather than durable state/preconditions;
- unauthorized user/site deciding Approval or manually retrying/cancelling another scope's Run;
- delayed privileged action bypassing required current Policy/resource precondition;
- external unknown outcome blindly retried without reconciliation policy;
- arbitrary code/function/class execution reachable from Workflow configuration;
- secret plaintext leaking to Definition/Run/Step/Job/log/history;
- restore/clone blindly replaying copied active Runs/Waits/Jobs;
- site lifecycle allowing active Run to mutate deleted/wrong site;
- compensation represented as full rollback when irreversible/residual effects remain.

These are stop-the-line defects for the affected certification scope.

# 19. Performance evidence

Capture at minimum:
- Run-start latency;
- trigger dedupe/idempotency contention;
- Step transition p50/p95/p99;
- join contention;
- Wait due-scan/wake latency;
- Approval decision contention;
- Job enqueue/reconciliation latency;
- retry amplification;
- external-action wait/outcome-unknown duration;
- Run/Step/Wait/Approval query counts;
- DB lock/index behavior;
- history storage growth;
- retention/cleanup throughput;
- one-hot-site noisy-neighbor impact;
- WF1/WF2 migration/provisioning/diagnostics cost.

Performance optimization must not weaken revision pinning, idempotency, authorization, branch/join correctness, external reconciliation or site isolation.

# 20. Required future P-011 report

Include:
- exact runtime/topology/profile;
- WF-01…WF-116 pass/fail/NA with rationale;
- trigger/dedupe/concurrency evidence;
- Run/Step/branch/join/wait/approval state evidence;
- JobService crash/retry integration evidence;
- action authorization/idempotency evidence;
- external unknown-outcome/reconciliation evidence;
- cancellation/intervention/compensation truth;
- security/privacy/log-redaction evidence;
- restore/clone/site-lifecycle evidence;
- WF1/WF2 physical measurements;
- scale/performance results;
- unsupported/degraded profiles;
- final Workflow Runtime topology/runtime recommendation.

## 21. Current state

**WF fixtures documented: 116.**  
**WF fixtures executed: 0/116.**  
**Workflow Runtime certified: none.**  
**Final Workflow physical topology: open / evidence-gated.**

WF1/PT-D remains the first future benchmark baseline. WF2/PT-E remains a mandatory comparison before final topology selection.

No Workflow Run/Step/Wait/Approval row, Job execution, event trigger, provider call, manual approval, migration, benchmark or runtime test has been executed.

## 22. Development gate

Execution requires explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md`, plus applicable P-001/P-003 environment/backend prerequisites.

Until then this protocol remains planning evidence only: `NOT EXECUTED`.