# Cron — Market Audit V1

Status: `RESEARCH_COMPLETE / SUPERVISOR_INTEGRATION_PENDING`

Surface: 18 — Cron  
Snapshot: 2026-09-12  
Parent: #713  
Worker issue: #721  
Base: `54e4e70095f74a6ebff15da83cc2bbf3d447a49c`

## 1. Scope and boundary

This evidence-only audit reconciles all 15 current Cron records. WP-Cron remains native scheduling substrate. WPE owns bounded schedule/job definitions and diagnostics; system cron/CLI and Action Scheduler are explicit providers. Exact wall-clock execution, retries and concurrency guarantees are never inferred from WP-Cron.

## 2. Current market evidence

### E1 — WP Crontrol
Official evidence:
- https://wp-crontrol.com/docs/cron-events/
- https://wp-crontrol.com/docs/how-to-pause-cron-events/

Verified: inspect hooks/args/schedules/callbacks/next due time, edit/delete/pause/resume/run now, add cron schedules and export event data.

### E2 — Action Scheduler
Official evidence:
- https://actionscheduler.org/admin/
- https://actionscheduler.org/perf/
- https://actionscheduler.org/api/
- https://actionscheduler.org/wp-cli/

Verified: pending/in-progress/failed/completed action states, run-now and logs; queue claims/batches, stale-claim handling, time/memory limits, scheduled/single/recurring/cron actions, groups, unique flag/priority and CLI runners/concurrency tuning.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `cron.inspect.event` | KEEP native + market parity — E1/E2 expose event/action inspectors. |
| `cron.inspect.context` | MARKET_EVIDENCED / WPE_HARDENED — args, next run, overdue/state/group exist; WPE adds owner/scope/timezone interpretation. |
| `cron.schedule.identity` | MARKET_EVIDENCED_WITH_REGISTERED_TARGET — providers schedule hooks/actions; WPE only binds registered jobs/Abilities. |
| `cron.schedule.state` | MARKET_EVIDENCED — E1 pause/resume and E2 statuses prove richer lifecycle beyond native recurrence. |
| `cron.schedule.window` | MARKET_EVIDENCED / WPE_HARDENED — scheduled/recurring/cron timing exists; end/occurrence/timezone/catch-up must be explicit. |
| `cron.reliability.idempotency` | PARTIAL_MARKET / WPE_HARD — E2 has uniqueness/group semantics, but generic business idempotency/concurrency still requires explicit contract. |
| `cron.reliability.timeouts` | PROVIDER_EVIDENCED — E2 documents time/memory and claim handling; provider-specific, not WP-Cron guarantee. |
| `cron.reliability.retry` | KEEP_WPE_HARD — failed-state/runner behavior exists, but portable retry/backoff/catch-up/overlap policy must be explicit. |
| `cron.operation.run-now` | MARKET_EVIDENCED — E1/E2 expose run-now operations. |
| `cron.operation.lifecycle` | MARKET_EVIDENCED — pause/resume/reschedule/cancel/retry-like operations exist; mutations remain later Ability gate. |
| `cron.observability.runs` | MARKET_EVIDENCED — E2 logs/status and E1 next-run inspection prove observability family. |
| `cron.provider.system-runner` | PROVIDER_EVIDENCED — E2 WP-CLI/system runners prove alternate runner/health family. |
| `cron.provider.action-scheduler` | PROVIDER_EVIDENCED — E2 is direct evidence for Action Scheduler adapter health. |
| `cron.diagnostics.queue` | MARKET_EVIDENCED — E2 queue claims/stale claims/failures support diagnostics family. |
| `cron.portability.scope` | KEEP_WPE_HARD — event exports exist, but portable definitions/multisite ownership/dependency validation remain WPE contracts. |

Coverage: **15 / 15**. Unresolved research dispositions: **0**. Worker Bank/progress mutations: **0**.

## 4. WPE-exceed / safety

- WP-Cron due time is not exact execution time; UI must state lateness/request-driven behavior honestly.
- Run-now/pause/reschedule/cancel are privileged operations with capability/Ability checks and audit evidence.
- Job definitions reference registered targets, never arbitrary callbacks or executable strings.
- Provider concurrency/claim tuning is not a substitute for application-level idempotency.

## 5. Supervisor integration requirements

A later Supervisor may add E1/E2 market/provider provenance and classify inspector/operations/observability/provider families while retaining explicit WPE reliability/portability policy. `MARKET_AUDITED` requires exact-head Bank reconciliation and CI.

No job execution, scheduling mutation, `BANK_REVIEWED`, runtime/product certification, deployment or release is authorized.