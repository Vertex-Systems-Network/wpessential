# Cron — UX Contract V1

Surface: **18 / Cron**  
Issue: **#501**  
Lifecycle: planning-only; no runtime promotion.

## Essential

- **Event inspector** — read-only scheduled-event identity using hook + normalized args + next-run information.
- **Schedule identity** — stable schedule definition, registered Job/Ability/provider target and lifecycle state.

## Advanced

- **Timing** — interval, first run, timezone, optional end/occurrence limit with effective-next-run diagnostics.
- **Operation controls** — planned run/pause/resume/reschedule/cancel/retry controls with privilege, confirmation and recovery requirements; controls are non-executable in this lane.

## Expert

- **Concurrency** — overlap/lock policy and effective lock diagnostics.
- **Retry policy** — bounded retry/backoff/timeout/catch-up policy with idempotency warnings.
- **Provider health** — read-only WP-Cron/system cron/CLI/registered scheduler availability and limitation diagnostics.
- **Queue diagnostics** — bounded attempts, failures, stale claims and retained log metadata.

## Interaction contract

- Search/focus reaches every authored setting and diagnostic.
- Timing UI distinguishes configured schedule from estimated/effective next execution.
- WP-Cron surfaces an explicit best-effort/traffic-triggered warning; it must never imply guaranteed wall-clock execution.
- Potentially destructive/side-effecting controls require clear target, consequence and later confirmation policy.
- Keyboard operation, semantic controls/tables, visible focus and announced status changes are required.

## Security / multisite / compatibility / performance

- Targets are registered Job/Ability/provider IDs only; no arbitrary PHP/callback/class/shell command input.
- Server-side capability/Policy and idempotency checks are required for later operational actions.
- Site/network scheduling scope is explicit and event inspection must not leak cross-site arguments/data.
- Provider loss degrades to diagnostic/unavailable state without silently changing schedule definitions.
- Event/log lists are paginated/bounded and polling is rate-limited; optional assets load only on Cron surfaces.

This contract does not authorize run-now, reschedule, cancel, retry execution, runtime implementation, deployment or certification.