# Cron — Atomic Option Contract Readiness V1

Surface: **18 / Cron**  
Issue: **#501**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Decision

Do **not** promote a canonical `config/product/option-contracts/cron.json` yet. The branch contains 8 candidate Bank records, all still `UNREVIEWED`, while canonical main Bank truth remains `UNSEEDED / 0`. A canonical contract before reviewed source truth would overstate lifecycle readiness.

## Candidate families

- existing scheduled-event inspection;
- schedule identity, target and lifecycle;
- interval/start/timezone/end/occurrence timing;
- concurrency/overlap/lock policy;
- retry/backoff/timeout/catch-up policy;
- run/pause/resume/reschedule/cancel/retry controls;
- system-cron/CLI/scheduler provider health;
- queue/attempt/log/stale-claim diagnostics.

## Prerequisites for canonical contract

1. Preserve WordPress hook+args event identity and duplicate-scheduling semantics.
2. Model WP-Cron as traffic-triggered/best-effort scheduling, never guaranteed wall-clock execution.
3. Define schedule targets as registered Jobs/Abilities/providers rather than arbitrary callbacks.
4. Define concurrency locks, retry/backoff, timeout and catch-up semantics with idempotency requirements.
5. Define privileged operation controls with preview/confirmation and recovery.
6. Define provider health for WP-Cron/system cron/CLI/other registered schedulers without exposing secrets.
7. Define bounded diagnostics/log retention and multisite ownership.
8. Review all Bank records to zero unresolved and promote through Supervisor reconciliation.

## Safety requirements

- No arbitrary PHP/callback/class/shell command execution input.
- Run-now/reschedule/cancel operations remain unauthorized in this planning lane.
- Retry/catch-up must never imply safe replay for non-idempotent targets.
- Scheduler/provider health is diagnostic only and must not claim wall-clock guarantees that the active provider cannot make.
- Secrets and command credentials never enter portable definitions or frontend data.

## Outcome

The V1 planning lane may close without `BANK_REVIEWED`, canonical option-contract promotion, runtime certification or product-parity certification.