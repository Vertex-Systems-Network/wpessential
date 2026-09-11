# Cron — Options Bank Seed Plan V1

Surface: **18 / Cron**
Issue: **#501**
Current Bank: **UNSEEDED / 0 records**
Atomic inventory: **ATOMIC_INVENTORY_COMPLETE**
Runtime implementation: **not authorized**

## Candidate families

- existing event inspector: hook, next run, recurrence, arguments, owner/scope, overdue state and timezone display;
- WPE schedule identity, target Ability/job, pause/enabled state and one-time/recurring mode;
- intervals, start/end/occurrence limit and timezone;
- idempotency, concurrency, lock timeout, execution timeout and retry/backoff policy;
- catch-up behavior and overlap policy;
- run-now/pause/resume/reschedule/cancel/retry operations;
- attempts/logs/duration/next-occurrence previews;
- system cron/CLI runner and Action Scheduler provider health;
- queue health and stale-claim diagnostics;
- import/export and multisite scope.

## Evidence pass

Audit current WordPress WP-Cron APIs and semantics, including the fact that WP-Cron does not guarantee exact wall-clock execution. Audit Action Scheduler only as a provider/integration where supported, then benchmark representative scheduling/cron management products from official documentation.

## Ownership boundaries

Cron owns schedule definitions and scheduling observability. Target work is executed by the canonical Ability/job owner. Notifications owns failure notifications. Platform/jobs own shared execution infrastructure. Cron must not create a second business-operation engine.

## Promotion path

Seed normalized Bank records → native/runtime audit → market audit → ownership/provider review → zero unresolved → `BANK_REVIEWED` → schema-valid option contract → reviewed UX/gap matrix. No runtime scheduler implementation or exact-time guarantee is promoted by this plan.
