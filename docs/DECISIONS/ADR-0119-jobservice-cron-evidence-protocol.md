# ADR-0119 — JobService / Cron / Action Scheduler Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP03`

## Context

JobService already has accepted Phase 0 semantics and physical benchmark baselines:
- WPE owns stable Job Type / Schedule / Job / Attempt / Runner / Execution Policy semantics;
- Action Scheduler or another backend is an adapter/execution opportunity, not WPE business-domain truth;
- execution is at-least-once and exactly-once execution is never claimed;
- idempotency/reconciliation belongs to WPE/domain semantics;
- stale/expired claims are not proof that a side effect did not occur;
- Cron calendar recurrence is a WPE calendar/timezone contract, not backend-default recurrence behavior;
- resource/concurrency keys, fairness and backpressure are WPE policies;
- delayed jobs revalidate applicable current security/business preconditions;
- site scope is explicit;
- J1/PT-D is the first physical baseline, with J2 and J3 mandatory comparisons where applicable;
- Action Scheduler remains a preferred candidate adapter, not a certified backend.

The remaining gap was a bounded executable evidence contract that combines backend coexistence, Cron recurrence/DST, claim/lease behavior, crash ambiguity, fairness, backpressure, runner modes, security, Multisite and J1/J2/J3 physical evidence.

## Decision

JobService/Cron production-readiness and Action Scheduler adapter claims require the applicable fixtures in:

`docs/QUALITY/JOB-SERVICE-ACTION-SCHEDULER-EVIDENCE-PROTOCOL.md`

The refined protocol fixes **JS-01…JS-106** evidence covering:
- Action Scheduler packaging/load-order/coexistence and ownership isolation;
- stable backend-neutral mapping for Job Type/payload/Job/Attempt/cancellation/progress;
- one-time/fixed-interval/calendar recurrence, timezone changes, DST gaps/folds, missed runs and overlap policy;
- Job/Attempt persistence ambiguity and enqueue/commit crash windows;
- at-least-once delivery, idempotency and external unknown-outcome handling;
- claim/lease expiry, stale workers, runner crashes and late commits;
- urgency/fairness/starvation policies;
- resource/concurrency keys and Multisite collision resistance;
- bounded producer/backpressure/admission behavior;
- request-driven, loopback, real cron/WP-CLI and focused runner modes;
- cancellation/pause/manual retry semantics;
- payload/version/secret/current-authorization revalidation;
- retention, cleanup, observability and log redaction;
- Multisite/site lifecycle/clone/restore behavior;
- J1/J2/J3 physical mapping and large workload/site-count comparisons;
- explicit MUST-NOT/stop-the-line gates.

## Negative requirements locked

A certified JobService/Cron runtime MUST NOT:
- claim exactly-once execution without domain evidence;
- let duplicate execution opportunities repeat protected effects contrary to certified idempotency semantics;
- treat expired/stale claims as proof that a prior worker made no side effect;
- treat backend rows/logs as authoritative Workflow/import/backup/business truth;
- let backend defaults silently choose calendar/DST semantics;
- allow eligible lower classes/sites to starve indefinitely under documented healthy capacity;
- allow resource/concurrency keys to collide across sites;
- execute delayed privileged work after required authority/resource revoke without revalidation;
- place raw secrets in backend arguments/searchable logs;
- let WPE pause/cancel/cleanup/uninstall mutate third-party actions/groups;
- blindly replay copied active Jobs/Schedules after clone/restore;
- allow pending Jobs to mutate deleted/wrong sites.

## Physical/backend decision

This ADR does **not** certify Action Scheduler and does **not** finalize J1/J2/J3.

- `J1/PT-D` remains first future Jobs+Attempts baseline.
- `J2` PT-C current + PT-D history remains mandatory comparison.
- `J3` PT-C remains a low-volume control/exception profile.
- Action Scheduler remains a preferred candidate adapter until P-003 evidence is executed.

## Current state

JS fixtures documented: **106**.  
JS executed: **0/106**.  
JobService backend certification: **none**.  
Cron/DST runtime certification: **none**.

No package install, backend schema, scheduled action, runner, WP-CLI command, Job/Attempt mutation, cron trigger, benchmark or provider call was executed.

## Development gate

This is planning-only acceptance. Execution remains blocked until explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md` and prerequisite P-001 environment floor.