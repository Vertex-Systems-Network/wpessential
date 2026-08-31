# ADR-0006 — Background Jobs and Scheduling

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27

## Context

WPEssential needs reliable background execution for:
- forms/workflows;
- delayed actions;
- notifications;
- imports/exports;
- backups/restores;
- batch watermark generation;
- provider retries;
- maintenance/cleanup;
- user-created schedules.

WordPress WP-Cron is traffic-triggered: due events are checked when WordPress receives requests, so an event can run later than its nominal schedule. It is useful as a scheduler trigger but is not a guarantee of exact wall-clock execution.

Action Scheduler is a mature, traceable WordPress background queue designed for distribution inside plugins and is widely used in the WordPress ecosystem.

References:
- https://developer.wordpress.org/plugins/cron/
- https://developer.wordpress.org/plugins/cron/understanding-wp-cron-scheduling/
- https://actionscheduler.org/

## Proposed decision

Separate three concepts:

1. **Schedule Definition** — when the user wants work to become due.
2. **Job/Action Record** — a durable unit of executable work with state, attempts, args and idempotency metadata.
3. **Runner** — the mechanism that claims/executes due work.

WPEssential exposes a platform `JobService` contract. Modules never call a queue library directly.

### Candidate implementation
Use **Action Scheduler behind the WPEssential JobService adapter** for the initial implementation, provided dependency/version/coexistence tests pass.

### Runner modes
- WP-Cron/default request-driven runner for broad hosting compatibility;
- documented real system cron/WP-CLI runner for sites requiring more predictable processing;
- optional provider/worker integrations only later if needed.

## Required JobService behavior

- enqueue now
- schedule once
- recurring schedule abstraction
- named action/type
- validated typed payload
- idempotency key where appropriate
- claim/locking behavior
- retry policy/backoff
- max attempts
- timeout/batch budget
- progress metadata where module needs it
- cancellation/pause
- success/failure state
- run logs/correlation ID
- safe retry of failed work
- retention/cleanup

## Cron Builder relationship

Cron Builder is a **user-facing scheduling/inspection module**, not the underlying queue implementation.

It can:
- inspect existing WP-Cron hooks;
- schedule WPEssential-managed work;
- show next runs;
- run WPEssential actions now;
- explain whether the site uses request-driven WP-Cron or a real runner.

Third-party cron events are not silently taken over by WPEssential.

## Safety

- callbacks/actions are registered code, not arbitrary serialized callables;
- payloads are validated at enqueue and execution;
- secrets are referenced, not copied into logs/payloads when avoidable;
- retries are aware of idempotency;
- poison work reaches a failed state rather than infinite retry;
- concurrent operations use locks/idempotency as appropriate;
- a job library failure must degrade modules visibly rather than fatal the whole site where recovery is possible.

## Why not build a custom queue immediately

A custom queue would require solving durable claiming, concurrency, retries, logs, cleanup, runner semantics, migrations and hosting edge cases before WPEssential has proved unique value. That is unjustified unless Action Scheduler fails acceptance requirements.

## Acceptance tests before making this ADR Accepted

1. dependency/coexistence strategy with sites/plugins already loading Action Scheduler;
2. minimum/current WP/PHP matrix;
3. high-volume representative queue fixture;
4. retry/idempotency behavior;
5. system cron/WP-CLI runner path;
6. multisite semantics;
7. upgrade/migration/version-conflict behavior;
8. uninstall/retention ownership;
9. administrative job visibility without exposing sensitive args;
10. failure recovery when runner is disabled/stalled.

## Fallback

If Action Scheduler cannot meet platform requirements, keep `JobService` unchanged and replace only its adapter through a superseding ADR.
