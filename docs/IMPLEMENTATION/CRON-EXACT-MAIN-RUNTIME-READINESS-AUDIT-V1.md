# Cron — Exact-Main Runtime-Readiness Audit V1

Issue: #644  
Parent: #636  
Audit anchor: `35874640a62b9cb08cec5268e949dacd55c8427f`  
Surface: 18 / `cron`

## Decision

**NOT RUNTIME-READY.** Keep `runtime_allowed=false`.

Surface 18 remains `ATOMIC_INVENTORY_COMPLETE` on exact main. The accepted gap matrix records no canonical event inventory, Schedule Definition/target resolver, timezone-aware recurrence service, concurrency/lease policy, retry state machine, privileged operation service, provider-health catalog or bounded diagnostic store. No dedicated Cron runtime module exists in `frameworks/Modules`.

## Classification

Event inspector, schedule identity, timing, concurrency, retry, operations, provider health and queue diagnostics are all **MISSING / BLOCKED** behind product-contract gates.

## Ownership boundaries

Future runtime must target registered Job/Ability/provider identities only and reject arbitrary PHP/callback/class/shell command input. Retry/catch-up semantics must depend on explicit idempotency classification. WP-Cron limitations must be represented honestly and multisite scope must be explicit.

## Smallest future bounded runtime slice — only after product gates

Candidate first slice: **read-only registered event/provider catalog + effective schedule diagnostics**, with no run-now/reschedule/delete operations.

Candidate files if separately authorized:
- `frameworks/Modules/Cron/CronModule.php`
- `frameworks/Modules/Cron/CronReadService.php`
- `frameworks/Modules/Cron/ScheduleDefinition.php`
- unit tests under `tests/Unit/Modules/Cron/`
- WordPress integration tests for event inventory and multisite scope

Required first: schema-valid option contracts, reviewed UX/operations contract, Job/Ability owner contract and exact-main Supervisor authorization.

## Exit

Issue #644 is evidence-accounted as **BLOCKED / NOT RUNTIME-READY**. No scheduling operations, side effects, certification or shared-truth changes are introduced.
