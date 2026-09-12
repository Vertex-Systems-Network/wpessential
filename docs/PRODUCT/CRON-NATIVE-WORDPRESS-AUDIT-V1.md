# Cron — Native WordPress Audit V1

Surface: **18 / Cron**  
Issue: **#694**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/cron.json` — **15 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; CURRENT SEED COVERS THE CORE WP-CRON FAMILIES AFTER PROVENANCE/SEMANTIC ENRICHMENT.** No scheduling mutation occurs here and this worker does not promote `NATIVE_AUDITED`.

WordPress supplies single/recurring event scheduling, recurrence registration, event inspection/rescheduling/unscheduling/clearing and request-driven execution. WP-Cron is not a guaranteed wall-clock scheduler: due events run when WordPress receives traffic after the due time.

## Current native sources

- Cron Handbook — https://developer.wordpress.org/plugins/cron/
- `wp_schedule_event()` — https://developer.wordpress.org/reference/functions/wp_schedule_event/
- `wp_schedule_single_event()` — https://developer.wordpress.org/reference/functions/wp_schedule_single_event/
- `wp_get_scheduled_event()` — https://developer.wordpress.org/reference/functions/wp_get_scheduled_event/
- `wp_next_scheduled()` — https://developer.wordpress.org/reference/functions/wp_next_scheduled/
- `wp_reschedule_event()` — https://developer.wordpress.org/reference/functions/wp_reschedule_event/
- `wp_unschedule_event()` — https://developer.wordpress.org/reference/functions/wp_unschedule_event/
- `wp_clear_scheduled_hook()` — https://developer.wordpress.org/reference/functions/wp_clear_scheduled_hook/
- `cron_schedules` — https://developer.wordpress.org/reference/hooks/cron_schedules/
- accepted planning evidence: `docs/PRODUCT/CRON-NATIVE-MARKET-EVIDENCE-MATRIX-V1.md`

Native event identity includes **hook + arguments**. Those arguments must match for inspection/unscheduling and help distinguish otherwise identical hooks.

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `cron.inspect.event` | **NATIVE-CONFIRMED** | attach scheduled-event/next-run provenance and hook+args identity |
| `cron.inspect.context` | **NATIVE-PARTIAL + WPE DIAGNOSTIC** | native args/timestamp/recurrence are available; owner/overdue/timezone interpretation is WPE diagnostic |
| `cron.schedule.identity` | **NATIVE-CONFIRMED SUBSTRATE + WPE TARGET OWNER** | registered hook/Ability/job target only; no arbitrary callback text |
| `cron.schedule.state` | **NATIVE-PARTIAL** — one-time/recurring native; pause/enabled is WPE/provider state | record capability split explicitly |
| `cron.schedule.window` | **NATIVE-CONFIRMED TIMING SUBSTRATE + WPE POLICY** | timestamp/recurrence native; end/occurrence-limit/timezone policy WPE-owned |
| `cron.reliability.idempotency` | **WPE RELIABILITY** | native duplicate avoidance informs diagnostics but core has no generic idempotency/concurrency policy engine |
| `cron.reliability.timeouts` | **WPE/PROVIDER** | no native generic lock/execution-timeout contract |
| `cron.reliability.retry` | **WPE/PROVIDER** | native WP-Cron has no generic retry/backoff/catch-up product engine |
| `cron.operation.run-now` | **WPE/CLI/PROVIDER OPERATION** | not a core WP-Cron scheduling API; later Ability-gated mutation only |
| `cron.operation.lifecycle` | **NATIVE-PARTIAL + WPE OPERATIONS** | reschedule/unschedule/clear are native; pause/resume/retry are WPE/provider semantics |
| `cron.observability.runs` | **WPE DIAGNOSTIC** | native next occurrence exists; historical attempts/logs/duration require WPE/provider evidence |
| `cron.provider.system-runner` | **PROVIDER/ENVIRONMENT** | external system cron/CLI health only; not native WP-Cron product state |
| `cron.provider.action-scheduler` | **PROVIDER-ONLY** | Action Scheduler remains distinct provider; never call it WP core |
| `cron.diagnostics.queue` | **WPE/PROVIDER DIAGNOSTIC** | no native claims/stale-claim queue model |
| `cron.portability.scope` | **WPE/IMPORT-EXPORT + MULTISITE** | Definition scope portable; scheduled runtime state remains separately governed |

## Required Supervisor Bank integration

1. Populate native sources for schedule/single-event/get/next/reschedule/unschedule/clear/custom schedules.
2. Enrich `inspect.event`, `schedule.identity`, `schedule.state`, `schedule.window` and `operation.lifecycle` with native provenance.
3. Add explicit notes that native event identity is hook + args and execution may be late.
4. Keep retry/idempotency/timeouts/run-history/provider/queue semantics non-native.
5. Keep Action Scheduler and system cron as provider identities, not native features.

## Native completeness / unresolved items

No new V1 Bank family is necessary: single/recurring schedules, recurrence, inspection and lifecycle mutation primitives map cleanly into existing records. Native execution timing truth is fully accounted for once provenance/notes are integrated.

Actual scheduling/unscheduling/run-now/retry mutations, locks, provider claims and execution history remain runtime gates.

## Gate boundary

Worker conclusion: **native evidence complete; Bank provenance/semantic integration pending**.  
Not promoted here: `NATIVE_AUDITED`, market/provider audit, `BANK_REVIEWED`, Cron runtime, Atomic Option Contract, UX or product parity.