# ADR-0059 — Job Service Execution, Fairness & Backpressure Semantics

Status: **Accepted platform semantics / Action Scheduler adapter evidence pending under ADR-0006/P-003**  
Date: 2026-08-27

## Context

WPEssential background work spans security/access reconciliation, interactive admin work, workflows, notifications, integrations, imports, backups, media processing and maintenance. A single raw queue priority or unconstrained concurrent runner can create starvation, resource contention, duplicate side effects and misleading cancellation behavior.

Action Scheduler remains the preferred initial backend candidate, but its native priorities, groups, claim timeouts, batch/concurrency values and retention are backend details rather than sufficient WPE product semantics. Its current documentation also warns that increasing concurrency can overload sites, group-specific runners can reorder implicitly dependent work, and multisite has no special network-wide queue handling.

## Decision

WPEssential accepts a backend-neutral `JobService` semantic model:

`Job Type Registration + Schedule Definition + Job Record + Attempt Record + Runner + Execution Policy`

### Delivery guarantee

Mutating jobs assume **at-least-once execution opportunity**, not exactly-once side effects.

Every mutating Job Type must define a business idempotency, journal/checkpoint, compare-and-set, provider idempotency or explicit unknown-outcome reconciliation strategy.

Backend scheduling uniqueness is not business idempotency.

### Urgency classes

WPE exposes reviewed classes rather than arbitrary backend priority integers:
- `system_control` — tightly restricted recovery/control;
- `security_transactional`;
- `interactive`;
- `normal`;
- `bulk`;
- `maintenance`.

Urgency affects scheduling preference only. It is not authorization or business ordering.

### Fairness

Strict priority alone is insufficient because sustained high-priority work can starve lower classes. WPE requires a fairness policy with reserved/weighted service and bounded age protection. Exact weights and Action Scheduler mapping remain P-003 evidence.

### Resource/concurrency policy

Job Types declare resource classes such as DB read/write, CPU, filesystem/network I/O, provider-rate-limited, memory-heavy and destructive-exclusive.

Stable concurrency keys can cap work per site/run/provider/resource. Destructive site-wide operations use exclusive coordination.

### Backpressure/admission

Job Service observes backlog age/count, throughput, retries, provider rate limits and site resource health. Producers of high-volume/bulk work must chunk/fan out gradually and may be delayed/admission-controlled instead of creating unbounded queue rows synchronously.

Required security/transactional work is not silently discarded under pressure.

### Claims / crash behavior

Claims/leases are time-bounded. Lease expiry allows reclamation/reconciliation but does not prove the previous worker produced no side effect.

Long work should checkpoint/chunk rather than depend on one huge PHP execution.

### Cancellation/pause

Cancellation and pause are cooperative:
- pending work may cancel immediately;
- running work stops at a safe checkpoint where supported;
- non-interruptible/destructive critical sections remain truthful about cancel boundaries;
- external operations may require abort/reconciliation.

UI must not show `Cancelled` before the defined cancellation point.

### Ordering

Queue insertion order, timestamp, action ID, group order and priority are not domain dependency guarantees. Required ordering is represented by durable state preconditions/continuations or Workflow orchestration.

### Runner modes

- request-driven Action Scheduler/WP-Cron candidate for broad compatibility;
- system cron/WP-CLI recommended option for more predictable/high-volume sites after certification;
- future runner adapters must preserve the same JobService semantics.

WP-Cron timing is not an exact wall-clock guarantee.

### Multisite

Every job has explicit site/network scope. Network-wide fairness/orchestration is not assumed from Action Scheduler and remains a P-003 blocker.

## Consequences

Positive:
- modules remain backend-independent;
- high-volume workloads cannot legitimately bypass shared resource policy;
- retry/idempotency ambiguity is explicit;
- priority starvation becomes testable;
- cancellation/progress are truthful;
- provider rate limits and queue backpressure compose with shared Job Service;
- replacing Action Scheduler later does not require changing module contracts.

Cost:
- WPE needs logical Job/Attempt policy and possibly projection/persistence beyond the backend's raw records;
- fairness/resource scheduling needs executable evidence;
- Action Scheduler groups/priorities/runners cannot be adopted mechanically;
- multisite and high-volume runner modes require dedicated certification.

## Relationship to ADR-0006

ADR-0006 remains **Proposed/P-003** for the concrete Action Scheduler adapter because dependency/load-order/coexistence, physical mapping, fairness, runner, migration, retention and multisite behavior still need executable evidence.

ADR-0059 accepts the **WPE JobService semantics** that any adapter must satisfy.

## Evidence still required after explicit consent

- Action Scheduler coexistence/version/load order;
- Job/Attempt/backend reference persistence shape;
- claim/crash/reclaim semantics;
- mixed-workload fairness/starvation tests;
- resource/concurrency keys;
- admission/backpressure behavior;
- retry/idempotency/unknown outcome;
- cancellation/checkpoints;
- WP-Cron low-traffic/stall behavior;
- WP-CLI/system cron/group-specific runners;
- high-volume hosting matrix;
- multisite isolation/fairness;
- retention/cleanup/version migration/uninstall;
- sensitive payload/log redaction.

No queue package setup, action scheduling, database creation/migration, runner execution or benchmark has been authorized/executed.
