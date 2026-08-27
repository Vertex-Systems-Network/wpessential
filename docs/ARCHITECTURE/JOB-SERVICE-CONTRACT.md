# WPEssential — Job Service Contract

Status: **Phase 0 architecture contract / Proposed / no queue dependency installed**  
Related: ADR-0006, Cron Builder, Workflows, Backup, Import, Membership, Notifications, Watermark

## Research note

Current official Action Scheduler documentation describes it as a scalable, traceable job queue designed for distribution in WordPress plugins. It can coexist with multiple bundled copies by selecting the newest registered version. Current WordPress.org release at the research date is Action Scheduler 4.1.0 (2026-08-05). It also supports WP-CLI processing.

WordPress WP-Cron remains traffic-triggered and cannot guarantee exact wall-clock execution. Action Scheduler defaults to WP-Cron/shutdown-based runners unless an alternative runner such as WP-CLI/system cron is configured.

This reinforces the architecture: **WPEssential owns a Job Service contract; Action Scheduler is the preferred implementation candidate, not the public product API.**

No executable spike or dependency installation has been authorized.

---

# 1. What Job Service is

Job Service is the shared execution layer for durable/background work.

Examples:
- workflow delayed/retry steps;
- large imports/exports;
- backup chunks/uploads/retention;
- watermark regeneration;
- Membership expiry/grace/reconciliation;
- notification/email delivery;
- webhook retry;
- diagnostics/maintenance;
- scheduled report generation.

Cron Job Builder configures **when/what** should be scheduled. Job Service handles **durable execution/observability**.

---

# 2. What Job Service is NOT

- not arbitrary PHP execution;
- not a second Workflow Builder;
- not a replacement for WordPress hooks;
- not a guarantee of exact timing on shared hosting;
- not tied publicly to Action Scheduler function names;
- not a place to serialize arbitrary PHP objects/closures.

---

# 3. Canonical job definition

A queued Job Run should conceptually contain:

| Field | Purpose |
|---|---|
| `job_id` | WPEssential stable run identifier |
| `job_type` | registered typed job/action name |
| `payload` | validated JSON-compatible arguments |
| `queue/group` | workload grouping |
| `priority` | bounded priority if adapter supports it |
| `scheduled_at` | earliest eligible UTC time |
| `principal_type` | `system` or `user`/approved service principal |
| `principal_id` | actor identity where applicable |
| `correlation_id` | request/workflow/import/backup trace |
| `idempotency_key` | duplicate protection where applicable |
| `concurrency_key` | optional overlap lock/group |
| `attempt` | current attempt number |
| `max_attempts` | bounded retry limit |
| `timeout_budget` | logical budget/diagnostic expectation |
| `created_at` | UTC |
| `source_definition_uuid` | workflow/schedule/profile source where applicable |

Actual storage may be delegated to adapter; these are WPEssential semantics.

---

# 4. Registered job types

Modules register job types through a typed registry.

Each job type declares:
- stable name;
- input schema;
- handler/Ability/action reference;
- whether idempotent;
- default max attempts;
- retry/backoff policy;
- concurrency policy;
- principal/authorization mode;
- observability metadata;
- sensitivity/redaction fields;
- expected resource class (`short`, `medium`, `heavy`) for diagnostics/routing only;
- cancellation support;
- compensation/recovery guidance where relevant.

Do not enqueue unregistered callback strings entered by users as a generic code runner.

---

# 5. Job states

WPEssential canonical states:

- `pending`
- `scheduled`
- `running`
- `retry_wait`
- `succeeded`
- `failed`
- `canceled`
- `dead` — retry policy exhausted/permanent failure requiring intervention

Adapter-specific statuses map into these.

A job may have detailed attempt history independent of canonical final state.

---

# 6. Enqueue modes

## Immediate asynchronous

Eligible as soon as runner can execute.

## Delayed one-time

Eligible at/after target UTC timestamp.

## Recurring schedule

Cron/Schedule definition creates future Job Runs according to schedule policy.

Recurring schedule definition is not the same row as each execution attempt.

## Manual run

Admin/CLI/manual Ability creates an audited run.

---

# 7. Timing semantics

UI must distinguish:

- **Scheduled for** — earliest intended time;
- **Started at** — actual runner start;
- **Delay/lag** — difference;
- **Finished at**.

On ordinary WP-Cron sites, do not promise `runs exactly at 02:00`.

UI wording:
- `Run after 02:00` / `Next eligible time` where appropriate;
- diagnostics indicate runner reliability.

For time-sensitive sites, System Status can recommend:
- real system cron triggering `wp-cron.php`;
- WP-CLI Action Scheduler runner where supported;
- hosting-specific runner adapter later.

---

# 8. Idempotency

Retryable jobs that can cause side effects must define idempotency behavior.

Examples:
- sending a webhook: delivery/event key;
- membership billing event: provider event ID;
- import chunk: run + chunk index;
- backup chunk: backup UUID + part index;
- email: notification event + recipient + channel version where duplicate suppression is desired.

Candidate rule:
- Job Service can enforce unique active idempotency keys within a declared scope;
- handler still owns business-level idempotency where external side effects are involved.

Never assume queue-level uniqueness alone prevents external duplicates.

---

# 9. Retry/backoff

Job type declares retry class:

## No retry

Use for:
- validation/permanent policy failure;
- destructive operations where blind repeat is unsafe.

## Bounded retry

Use for transient:
- timeout;
- provider 5xx;
- rate limit;
- temporary DB/lock condition when safe.

Candidate backoff primitives:
- fixed;
- exponential;
- provider `Retry-After` bounded by policy;
- custom registered policy.

Always cap attempts/time horizon.

Permanent 4xx/auth/config errors should normally surface as failed/dead instead of hammering provider.

---

# 10. Concurrency / overlap

Job type or source definition can set:
- allow parallel;
- singleton per site;
- singleton per source definition;
- singleton per resource/entity;
- bounded N concurrency.

Examples:
- same backup profile: normally no overlapping full backup;
- membership reconciliation: one per connection/site at a time;
- independent email deliveries: parallelizable;
- same import run chunk: idempotent/unique.

Locks require expiration/recovery semantics to avoid permanent deadlock after process crash.

Action Scheduler itself has runner locking/concurrency behavior, but WPEssential business concurrency remains an explicit higher-level contract.

---

# 11. Principal and authorization model

Delayed work introduces a security question: whose authority executes later?

## User-principal job

Created because a user requested an action.

Store actor identity and authorization mode.

Candidate execution policies:
- `reauthorize_at_run` — current user capability/policy must still allow it;
- `authorized_snapshot` — only for narrowly defined tasks where original authorization intentionally survives later role changes;
- `system_continuation` — workflow transitioned into a system-owned process after an authorized initiation.

Default for destructive/user-resource operations: **reauthorize or use explicit system transition**, never assume stale user permission indefinitely.

## System-principal job

Created by trusted WPEssential runtime for maintenance/lifecycle behavior.

Examples:
- entitlement expiry;
- retention prune;
- queue cleanup;
- reconciliation.

System principal is not a UI user and does not bypass module policies accidentally; job type explicitly permits system execution.

---

# 12. Payload security

Queue payloads must be JSON-compatible scalar/array data or stable IDs/references.

Do not store:
- PHP closures;
- arbitrary serialized objects;
- plaintext secrets;
- large raw files/blobs;
- payment-card data;
- unnecessary webhook bodies.

Secrets are resolved at execution by Vault reference with current authorization/provider context.

Sensitive payload fields are redacted in logs/admin UI.

Current Action Scheduler 4.1 includes hardening around stored schedule object deserialization; WPEssential should still avoid creating arbitrary-object payload dependencies.

---

# 13. Attempt logs / observability

Each run needs:
- job ID/type;
- source/correlation ID;
- state transitions;
- scheduled/start/end times;
- attempts;
- runner (`wp-cron`, async, CLI, manual, etc.);
- duration;
- safe error class/code/message;
- retry decision;
- result summary;
- memory/time warnings where measurable.

No secrets/full sensitive payloads.

Action Scheduler native logs may be used as adapter evidence but WPEssential exposes normalized diagnostics.

---

# 14. Cancellation

Cancellation can mean:
- prevent pending future run;
- cancel remaining recurrence;
- request cooperative cancellation of multi-step/chunked operation.

PHP cannot reliably kill an arbitrary already-running request safely.

Therefore heavy jobs must use cooperative checkpoints/chunks where cancellation matters.

UI must not claim an in-flight process was killed if only future chunks were canceled.

---

# 15. Timeout semantics

A logical timeout budget is used for:
- diagnostics;
- chunk sizing;
- runner selection;
- marking stuck attempts.

Action Scheduler may mark an action failed after timeout heuristics even if late callback completion eventually changes status; WPEssential cannot treat adapter timeout as proof that external side effects did not occur.

Handlers must be idempotent/reconcilable.

---

# 16. Heavy work / chunking

Do not enqueue one giant job for:
- 50 GB backup;
- 1M-row import;
- all media watermark regeneration;
- mass email batch.

Use controller/child jobs:
1. plan work;
2. persist checkpoint;
3. enqueue bounded chunk;
4. process;
5. persist progress;
6. enqueue next;
7. finalize/checksum/health.

This allows shared-host resilience and restart.

---

# 17. Runner health

System Status should report:
- pending oldest age;
- queue size;
- failed/dead count;
- last successful runner time;
- average/recent lag;
- WP-Cron spawn health;
- Action Scheduler adapter status/version if chosen;
- WP-CLI/system cron recommendation when lag is excessive.

Module UI can show localized health without inventing its own runner diagnostics.

---

# 18. Adapter interface — conceptual

Job Service should support an adapter contract equivalent to:
- enqueue;
- schedule once;
- schedule recurrence;
- cancel;
- query status;
- list/filter runs;
- claim/execute through adapter callback integration;
- retry/reschedule;
- inspect runner health;
- cleanup/retention.

Domain modules never call Action Scheduler functions directly outside adapter/integration layer.

---

# 19. Why Action Scheduler is the preferred candidate

Static evidence:
- built specifically for scalable WordPress background queues;
- designed for embedding/distribution in plugins;
- handles multiple bundled versions by choosing newest registered version;
- traceable logs/admin UI;
- WP-CLI support;
- proven large queues in WooCommerce ecosystem;
- current L-2 WordPress dependency policy may align well with a WordPress 6.9 minimum while current WordPress is 7.1 (must be rechecked at implementation time).

Risks still requiring executable proof:
- version arbitration with WPEssential/WooCommerce copies;
- install/upgrade table state;
- concurrency/locking under representative hosting;
- adapter behavior with object cache;
- multisite;
- queue lag/failure recovery;
- package size/dependency policy;
- compatibility floor changes over time.

---

# 20. Future acceptance spike — NOT AUTHORIZED

After explicit owner consent, compare/verify:
- latest Action Scheduler embedded with WooCommerce absent;
- WooCommerce with another Action Scheduler copy;
- older/newer compatible bundled copy ordering;
- 10k/50k synthetic no-op jobs;
- retry/idempotency;
- overlapping locks;
- stalled runner recovery;
- WP-Cron low-traffic lag;
- WP-CLI runner;
- cancellation/chunking;
- multisite if supported;
- cleanup/retention;
- admin/job log performance.

Do not execute this spike before owner consent under ADR-0014.

# 21. Current recommendation

Accept the **Job Service semantic contract** only after review, while keeping the concrete adapter Proposed.

Preferred concrete implementation candidate remains **Action Scheduler behind WPEssential Job Service**, with WP-Cron/system cron/WP-CLI treated as runner mechanisms rather than product-level exact-time promises.
