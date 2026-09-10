# Cron — Native & Provider Evidence Matrix V1

Surface: **18 / Cron**  
Issue: **#501**  
Research anchor: **`main @ fad3b2f37bd44a01d77438e71877d7990b4bfe2c`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress Cron Handbook | https://developer.wordpress.org/plugins/cron/ | page-load-triggered scheduling model and non-exact execution timing |
| WordPress Scheduling WP-Cron Events | https://developer.wordpress.org/plugins/cron/scheduling-wp-cron-events/ | hook-backed scheduling/unscheduling lifecycle |
| WordPress WP-Cron Scheduling | https://developer.wordpress.org/plugins/cron/understanding-wp-cron-scheduling/ | recurrence intervals and custom schedule semantics |
| Action Scheduler API | https://actionscheduler.org/api/ | single/recurring async actions, action IDs and a traceable queue-provider model |

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

WordPress documents that WP-Cron checks due events on page load. A task scheduled for a specific clock time can therefore run later if no request occurs at that time. WPE UX/contracts must expose this truth explicitly and may distinguish provider guarantees rather than promising exact execution from WP-Cron.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Native event argument identity, duplicate scheduling semantics, unscheduling, Action Scheduler groups/claims/failures, system-cron/CLI adapters, multisite, observability/security/performance and zero-unresolved review remain open. No scheduling or queue mutation runtime is authorized.