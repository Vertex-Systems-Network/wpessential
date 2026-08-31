# ADR-0118 — Workflow Runtime Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP03`

## Context

Workflow Runtime already has accepted Phase 0 semantics and physical benchmark baselines:
- Workflow Definition/editor state is separate from durable Workflow Runtime business truth;
- every Run pins the intended published Workflow revision;
- Run / Step / Wait / Approval durable state remains authoritative;
- JobService/backend rows only provide execution opportunities and never become Workflow truth;
- at-least-once event/Job delivery is expected, so state transitions and protected side effects require explicit idempotency/preconditions;
- branch/join/wait/approval semantics are durable state, not inferred from queue order;
- delayed actions revalidate the applicable current authorization/business preconditions;
- external unknown outcomes require reconciliation/manual policy rather than blind retry;
- WF1/PT-D is the first future physical benchmark baseline and WF2/PT-E is mandatory before final topology selection.

The remaining gap was a bounded executable evidence contract covering revision pinning, trigger dedupe, concurrency, joins, waits, approvals, retries, side effects, recovery, lifecycle, Multisite and scale.

## Decision

Workflow Runtime production-readiness claims require the applicable fixtures in:

`docs/QUALITY/WORKFLOW-RUNTIME-EXECUTABLE-EVIDENCE-PROTOCOL.md`

The protocol fixes **WF-01…WF-116** evidence covering:
- Draft/publish/revision pinning and historical revision retention;
- manual/Form/Status/Membership/Webhook/Cron/REST trigger authorization and dedupe;
- Run/Step durable state, CAS/version preconditions and duplicate/out-of-order execution;
- typed conditions, branch semantics, parallel fan-out and concurrency-safe joins;
- durable waits/timers, lost enqueue reconciliation and restore-safe wake behavior;
- approval authorization, expiry, quorum and decision races;
- JobService enqueue/crash/lease/retry/backpressure integration;
- typed internal actions and denial of arbitrary code execution;
- provider unknown-outcome/idempotency/reconciliation behavior;
- cancellation, pause, manual intervention and truthful compensation semantics;
- secret minimization, IDOR prevention, audit/correlation and delayed-authorization revalidation;
- Multisite/site lifecycle/clone/restore behavior;
- WF1/PT-D and WF2/PT-E physical/scale comparison;
- explicit MUST-NOT/stop-the-line gates.

## Negative requirements locked

A certified Workflow runtime MUST NOT:
- silently reinterpret an active Run against a newer/unrelated Workflow revision;
- treat Job/backend state as authoritative Run/Step/Approval truth;
- let duplicate event/Job delivery repeat a protected transition or non-idempotent effect contrary to certified semantics;
- allow stale workers to commit after another worker advanced the same state;
- derive branch/join/approval outcome from queue timing instead of durable state/preconditions;
- permit another user/site to read/approve/retry/cancel a Workflow resource through forged identifiers;
- let delayed privileged actions bypass required current Policy/resource preconditions;
- blindly retry external unknown outcomes without reconciliation policy;
- allow arbitrary function/class/PHP/JS/shell execution from Workflow configuration;
- leak secret plaintext through Definition/Run/Step/Job/history/log data;
- blindly replay copied active Runs/Waits/Jobs after clone/restore;
- misrepresent compensation as complete rollback when irreversible/residual effects remain.

## Physical topology

This ADR does **not** finalize WF1 vs WF2.

- `WF1/PT-D` remains first future benchmark baseline.
- `WF2/PT-E` remains mandatory comparison.
- Final topology requires executed scope/isolation/noisy-neighbor/migration/lifecycle/scale evidence.

## Current state

WF fixtures documented: **116**.  
WF executed: **0/116**.  
Workflow Runtime certification: **none**.  
Final Workflow physical topology: **OPEN / evidence-gated**.

No Workflow Run/Step/Wait/Approval row, trigger, Job execution, provider call, approval decision, migration, benchmark or runtime test was executed.

## Development gate

This is planning-only acceptance. Execution remains blocked until explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md`, plus applicable P-001/P-003 prerequisites.