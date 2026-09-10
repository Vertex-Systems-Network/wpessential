# Cron — Native & Provider Evidence Matrix V1

Surface: **18 / Cron**  
Issue: **#501**  
Research anchor: **`main @ 42303a97938f1a80f9c1c9088fa70d40ba359279`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress Cron Handbook | https://developer.wordpress.org/plugins/cron/ | page-load-triggered scheduling model and non-exact execution timing |
| WordPress `wp_schedule_event()` | https://developer.wordpress.org/reference/functions/wp_schedule_event/ | recurring event identity from hook + args, recurrence/start semantics and duplicate-scheduling risk |
| WordPress `wp_next_scheduled()` | https://developer.wordpress.org/reference/functions/wp_next_scheduled/ | next-occurrence inspection keyed by hook + matching args |
| WordPress WP-Cron Scheduling | https://developer.wordpress.org/plugins/cron/understanding-wp-cron-scheduling/ | interval-based simulated cron and custom schedule semantics rather than guaranteed wall-clock execution |
| Action Scheduler API | https://actionscheduler.org/api/ | single/recurring async actions, action IDs, groups and traceable queue-provider model |

## Native/provider decisions added in this pass

- Native WP-Cron event identity includes both hook and arguments. WPE schedule deduplication/inspection must preserve that tuple instead of treating hook name alone as unique.
- `wp_schedule_event()` documentation explicitly recommends checking existing schedules to prevent duplicate events; duplicate-policy and idempotency therefore belong in the planning Bank.
- `wp_next_scheduled()` is read-only timing evidence, not a promise that the event will run at that timestamp.
- WP-Cron execution is request-driven and can be late. WPE must distinguish **desired due time**, **next native scheduled time**, and **observed execution time** in diagnostics.
- Action Scheduler is a separate queue provider with action/group identities and richer execution history. WPE may adapt to it through a provider contract but must not silently equate its guarantees/retries with native WP-Cron.
- Operational run/cancel/retry/reschedule controls are mutations and remain blocked until a later Policy/Ability/runtime gate.

## Seed disposition evidence

- `cron.inspector.event` — existing scheduled hook/event inspection is a valid native/provider capability.
- `cron.schedule.identity` — schedule identity must bind a registered target/Ability or hook contract; arbitrary executable callbacks are prohibited.
- `cron.schedule.timing` — interval/start/end/occurrence policy is valid, but WP-Cron must never be presented as an exact wall-clock scheduler.
- `cron.schedule.concurrency` — valid expert reliability contract for WPE/provider execution; no lock implementation is authorized by this planning lane.
- `cron.schedule.retry` — provider-backed retry/backoff/catch-up policy is valid but not native WP-Cron parity by default.
- `cron.operation.controls` — run/pause/resume/reschedule/cancel/retry are operational mutation controls and require a later Policy/Ability/execution gate.
- `cron.provider.health` — WP-Cron/system cron/CLI/Action Scheduler health and provenance are valid read-only diagnostics.
- `cron.diagnostics.queue` — WPE-exceed diagnostic candidate for attempts/logs/stale claims; must remain bounded/redacted and not imply a private duplicate scheduler.

## Timing truth

WordPress documents that WP-Cron checks due events in response to site activity rather than acting as a traditional system cron. A task whose due time has passed can therefore execute later. WPE UX/contracts must expose this truth explicitly and may distinguish provider guarantees rather than promising exact execution from WP-Cron.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Unscheduling APIs, custom recurrence registration, Action Scheduler claim/failure/cancel semantics, system-cron/CLI adapters, multisite schedule ownership, observability/security/performance and zero-unresolved review remain open. No scheduling or queue mutation runtime is authorized.
