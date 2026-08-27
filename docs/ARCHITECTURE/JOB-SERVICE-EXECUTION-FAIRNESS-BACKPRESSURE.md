# WPEssential — Job Service Execution, Fairness & Backpressure Model

Status: **Phase 0 planning only / no queue implementation authorized**  
Related: ADR-0006, Workflow runtime, Cron Job Builder, Notifications, Import/Export, Backup, Watermark, Membership, Connections.

## 1. Purpose

WPEssential needs one background-execution contract that can support very different work without letting one workload starve, overload or corrupt another.

Examples:
- security/account work;
- membership/billing reconciliation;
- notification/email delivery;
- provider webhook follow-up;
- workflow continuation;
- imports/exports;
- backups/restores;
- watermark batches;
- cleanup/reverification;
- user-authored schedules.

The queue backend is an adapter. Modules target `JobService`, not Action Scheduler/WP-Cron directly.

---

# 2. Logical model

Separate:

1. **Job Type Registration** — code-defined executable contract.
2. **Schedule Definition** — when work becomes due.
3. **Job Record** — durable logical unit of work.
4. **Attempt Record** — one claim/execution attempt.
5. **Runner** — mechanism that claims due work.
6. **Execution Policy** — urgency/resource/concurrency/retry/fairness rules.

Action Scheduler may implement the initial backend after P-003 evidence, but its native action record/status/priority values are not the public WPE semantic contract.

## 2.1 Job Type Registration

Registered code declares:
- stable type key/version;
- payload schema/version;
- owning module;
- handler/Ability reference;
- default urgency class;
- resource class(es);
- idempotency requirement/profile;
- retryable error classes;
- maximum attempt policy bounds;
- cancellation/checkpoint support;
- progress capability;
- expected cost class;
- retention class;
- concurrency-key derivation;
- multisite scope;
- permission required for manual Run/Retry/Cancel.

No arbitrary serialized callback/PHP code is accepted from user data.

## 2.2 Schedule Definition

Schedule is a trigger source, not the job itself.

Candidate schedule modes:
- enqueue now;
- once at absolute timestamp;
- fixed interval;
- wall-clock/cron-style recurrence;
- module-managed continuation;
- event-triggered enqueue.

Schedule policy can include:
- timezone/anchor;
- missed-run behavior `run_once_asap|skip|bounded_catchup`;
- overlap behavior;
- active/inactive;
- next due timestamp;
- schedule revision.

A recurring Schedule creates distinct Job Records/runs. It does not reuse one mutable execution row forever.

---

# 3. Job states

Logical Job state candidates:
- `scheduled` — future due time;
- `available` — due and eligible to claim;
- `blocked` — dependency/concurrency/admission rule prevents claim;
- `claimed` — runner lease/claim acquired;
- `running`;
- `retry_wait`;
- `pause_requested`;
- `paused`;
- `cancel_requested`;
- `cancelled`;
- `succeeded`;
- `failed_final`;
- `expired` — no longer meaningful before execution;
- `orphaned_adapter` — backend reference lost/inconsistent and needs reconciliation.

Backend status may differ and is mapped through the adapter.

## Attempt states
- `claimed`;
- `started`;
- `heartbeat/checkpointed` where supported;
- `succeeded`;
- `failed_retryable`;
- `failed_permanent`;
- `timed_out_or_lease_lost`;
- `cancelled_cooperatively`;
- `outcome_unknown`.

A stale/expired claim does **not** prove the handler did no external side effect. Re-execution therefore requires idempotency/continuation semantics.

---

# 4. Delivery guarantee

WPE assumes **at-least-once execution opportunity**, not magical exactly-once side effects.

Reasons:
- process can die after external side effect but before success state commit;
- runner lease can expire;
- network/API result can be unknown;
- database/worker failure can leave ambiguous checkpoints.

Therefore every mutating Job Type must define one of:
- naturally idempotent operation;
- stable idempotency key at the external/domain boundary;
- durable operation journal/checkpoint;
- compare-and-set/version precondition;
- explicit non-idempotent/unknown-outcome policy requiring reconciliation/manual intervention.

Action Scheduler's `unique` scheduling flag, if used, only prevents certain duplicate pending scheduling; it is **not** equivalent to business idempotency.

---

# 5. Urgency classes

Modules do not choose raw backend priority integers freely.

Canonical classes:

## `system_control`
Very restricted platform/recovery work needed to keep queue/recovery control operable. Not exposed to ordinary module/user choice.

Examples: lease recovery/reconciliation, critical queue control markers.

## `security_transactional`
Time-sensitive security/account/access/billing facts where prolonged delay can cause incorrect authorization or unsafe user state.

Examples: entitlement revoke/reconcile, password/security notification enqueue, billing reconciliation fact.

## `interactive`
User explicitly started work and is waiting for visible progress, but work is safe to run in background.

Examples: small export, test-send, manual verification.

## `normal`
Default workflows/integrations/notifications.

## `bulk`
Large fan-out/data/image/import/export work.

## `maintenance`
Cleanup, re-verification, retention pruning, statistics rebuild.

Urgency affects scheduling preference, not authorization or business correctness.

### No unlimited critical priority

A module cannot mark every job `system_control`/`security_transactional`. Registration review controls allowed class and elevated class transitions.

---

# 6. Fairness / starvation prevention

Raw strict priority can starve lower classes during sustained high-priority load.

WPE therefore requires a fairness policy independent of backend integer priority.

Paper direction:
- reserve capacity for system/security classes;
- use weighted service shares for interactive/normal/bulk/maintenance;
- apply bounded **age boost** so an old eligible job eventually gets service;
- resource/concurrency blockers still override age;
- do not reorder jobs across explicit domain dependency/checkpoint constraints;
- expose oldest-age/backlog per class in diagnostics.

Exact weighted algorithm and mapping to Action Scheduler groups/priorities/runners remain P-003 executable evidence.

A simple mapping of classes directly to AS priorities without starvation evidence is **not sufficient**.

---

# 7. Resource classes

Jobs declare expected dominant resources so concurrency can be bounded intelligently:
- `db_read`;
- `db_write`;
- `cpu`;
- `filesystem_io`;
- `network_io`;
- `provider_rate_limited`;
- `memory_heavy`;
- `destructive_exclusive`.

Multiple classes may apply.

This is a scheduling hint/policy input, not a guaranteed profiler result.

Examples:
- image watermark batch: CPU + filesystem + memory;
- S3 upload: filesystem + network + provider-limited;
- import chunk: DB write + filesystem/network;
- restore/reset phase: destructive exclusive + DB/filesystem;
- email batch: network + provider-limited.

---

# 8. Concurrency keys

Job Types may derive keys such as:
- `site:<blog_id>:backup_capture`;
- `site:<blog_id>:restore`;
- `import:<run_uuid>`;
- `provider:<connection_uuid>:email`;
- `membership:<provider_connection>:reconcile`;
- `watermark:<rule_uuid>:attachment:<id>`.

Policy can specify:
- max 1 per key;
- max N per resource/provider class;
- site-wide destructive mutex;
- global/network cap when a network-aware runner is explicitly supported.

Concurrency key must use stable opaque identifiers and cannot contain secrets.

---

# 9. Claims, leases and heartbeats

Runner claims must be time-bounded so crashed workers do not hold jobs forever.

Logical claim contains:
- runner/claim ID;
- claimed-at;
- lease deadline/profile;
- attempt ID;
- optional heartbeat/checkpoint timestamp.

Lease expiry means the system may reclaim/reconcile the job; it does not mean previous side effects were rolled back.

Long work should be chunked/checkpointed rather than relying on one enormous PHP execution whenever practical.

Exact lease duration and Action Scheduler claim mapping remain evidence-gated.

Current Action Scheduler documentation describes batch claims and stale-claim cleanup based on runner time limits; WPE must certify behavior rather than copying those values into the public contract.

---

# 10. Chunking / continuation

Large jobs should normally execute bounded work units:

`parent/run → chunk job → durable checkpoint → next chunk`

Benefits:
- host timeout/memory tolerance;
- visible progress;
- cancellation checkpoints;
- retry only affected chunk;
- fairer queue sharing;
- provider rate control.

Examples:
- import rows;
- backup file/DB parts;
- remote uploads;
- watermark attachments;
- notification recipient batches;
- privacy export/erase batches.

Job Service provides continuation primitives but does not become a generic DAG engine. Complex branching remains Workflow runtime responsibility.

---

# 11. Backpressure

Backpressure prevents producers from creating work faster than the site can safely process it.

Signals:
- total pending jobs;
- pending by urgency/resource/type/group;
- oldest eligible age;
- runner throughput;
- failure/retry rate;
- provider 429/rate-limit pressure;
- memory/disk health;
- destructive operation lock;
- Job Service stall/runner unavailable.

Policy responses:
- reduce fan-out batch size;
- delay/batch low-urgency jobs;
- stop generating additional continuation work until backlog falls;
- respect provider Retry-After/rate limiter;
- reject/defer optional bulk admission with explicit UI state;
- pause maintenance work;
- alert on unhealthy backlog.

Backpressure never silently discards required transactional/security work.

---

# 12. Admission control

Before enqueueing high-volume work, producer may be required to create a parent Run/Plan and estimate workload.

Examples:
- 1 million recipient notification fan-out;
- 500k-row import;
- site-wide watermark regeneration;
- large backup copy to several destinations.

Do not enqueue one queue record per item synchronously if a bounded cursor/chunk producer can represent the same workload safely.

Admission limits are configurable within safe platform bounds and must show degraded/backlogged state rather than pretending work was accepted normally.

---

# 13. Retry policy

Retry decisions use typed error classes, not `catch Throwable => retry forever`.

Candidate categories:
- transient local/resource;
- provider rate-limited;
- provider transient/network;
- dependency unavailable;
- concurrency conflict;
- validation/permanent;
- authorization revoked;
- unknown external outcome;
- poison/programming error.

Policy fields:
- max attempts;
- initial delay;
- exponential/backoff factor;
- bounded jitter;
- max delay;
- `Retry-After` override where valid;
- retry deadline/expiration;
- final failure action/event.

Exact numeric defaults are evidence-gated.

A job with unknown external outcome may require reconciliation instead of blind retry.

---

# 14. Cancellation

Cancellation is cooperative.

States:
- pending job can be cancelled before claim;
- running job receives `cancel_requested` and stops at a safe checkpoint if handler supports it;
- non-interruptible critical section completes before cancellation becomes effective;
- external provider operation may need abort/reconciliation rather than local process termination.

UI must not say `Cancelled` until the defined cancellation point is reached.

Destructive jobs document a **point of no simple cancel** and recovery route.

---

# 15. Pause

Pause means no new work for the scoped Run/Schedule/type is claimed/generated according to policy.

It does not freeze a PHP process already executing unless the handler cooperatively checks a checkpoint.

Scopes:
- schedule;
- parent Run;
- module workload;
- selected provider connection;
- maintenance class;
- entire WPE Job Service emergency pause, excluding minimum recovery/control jobs.

---

# 16. Progress

Optional progress contract:
- phase key;
- completed units;
- total units optional/estimated;
- bytes/rows/items where meaningful;
- last checkpoint;
- human-safe status message key;
- warnings count.

Progress is best-effort operational metadata. It must not require a database write for every tiny item.

Estimated totals can change and must be labeled estimate where appropriate.

---

# 17. Ordering

Global queue ordering is **not** a business transaction ordering guarantee.

If A must happen before B, domain code must express a durable dependency/continuation or state precondition.

Do not rely on:
- equal timestamps;
- insertion order;
- backend action IDs;
- group runner timing;
- raw priority alone.

This is especially important because Action Scheduler's WP-CLI group/hook-specific runners can process groups at different speeds; its own docs warn about implicit dependencies when splitting queues.

---

# 18. Recurring schedules and overlap

A recurring trigger defines occurrence semantics separately from execution duration.

Overlap policies:
- `skip_if_running`;
- `queue_one` — at most one pending successor;
- `allow_overlap` — only for explicitly safe Job Types;
- `coalesce` — multiple missed occurrences become one reconciliation job.

Default for destructive/heavy/site-wide work: no overlap.

Missed schedule behavior is explicit. WP-Cron request-driven timing is never marketed as exact wall-clock execution.

---

# 19. Runner modes

## Default compatibility runner
Action Scheduler/WP-Cron/request-driven path candidate.

Pros: broad hosting compatibility, no server access required.

Truthful limitation: due work may run late when site traffic/loopback/cron is unhealthy.

## System cron / WP-CLI
Recommended for predictable/high-volume sites after certification.

Can run focused groups to control concurrency, but group partitioning is allowed only when dependency/fairness analysis proves it safe.

## Future worker/managed runner
Possible later adapter; must preserve JobService semantics and site isolation.

No module may depend on a specific runner transport.

---

# 20. Multisite

Logical job scope always includes site/network ownership.

Rules:
- site job runs under correct blog/site context;
- site payload cannot accidentally resolve another site's definitions/secrets;
- network jobs require explicit network-level type/capability;
- fair processing across sites is not assumed from Action Scheduler.

Current Action Scheduler documentation says it is designed to manage scheduled actions on a single site and has no special network-wide multisite handling. Therefore multisite queue orchestration remains a P-003 certification blocker.

---

# 21. Action Scheduler adapter boundaries

Current candidate mappings:
- WPE type → registered AS hook;
- WPE payload reference/validated compact args → action args;
- WPE workload family → group;
- WPE backend scheduling hint → AS priority;
- AS action ID → backend reference;
- AS claim/log/status → adapter evidence/projection;
- default runner/WP-CLI → runner adapter modes.

But WPE must **not** expose directly as stable product semantics:
- AS raw priority 0–255;
- claim timeout values;
- batch size;
- concurrent batch count;
- retention defaults;
- table/log schema;
- exact status list.

Those are backend details and can change by version/adapter.

---

# 22. Action Scheduler coexistence/version policy

P-003 must prove:
- library load order when other plugins bundle different versions;
- minimum/maximum accepted AS feature set;
- `action_scheduler_init`/API availability timing;
- schema migration/update coexistence;
- groups/hooks naming collision avoidance;
- no mutation/deletion of third-party actions;
- WPE cleanup only owns WPE jobs/projections;
- degraded safe behavior if expected AS capabilities unavailable.

Current Action Scheduler supports feature checks such as `as_supports()` for declared features; WPE should feature-detect where appropriate instead of assuming a version string alone proves behavior.

---

# 23. Retention

WPE retention is category/policy based, not blindly equal to backend cleanup defaults.

Logical categories:
- active/pending/running: never cleanup as history;
- succeeded attempts: shorter operational history;
- failed-final jobs: longer diagnostic retention;
- destructive/security/billing/import/backup parent Runs: domain audit policy can retain references longer than queue execution logs;
- provider error/raw details: privacy-minimized;
- job payload snapshots: avoid or minimize sensitive data.

Backend history may expire earlier/later; WPE must not promise recovery/debug data that the backend has already deleted.

Current Action Scheduler defaults include cleanup behavior for old complete/cancelled actions and separate failed-action retention in current releases; these are adapter facts, not WPE product guarantees.

---

# 24. Payload/security

Payload rules:
- versioned schema;
- IDs/references instead of large object dumps;
- no executable callbacks;
- no raw secret values when a Vault reference can be resolved at execution;
- no passwords/tokens in backend searchable args/logs;
- bounded payload size;
- validate at enqueue and execution;
- authorize operation/resource again when security state can change before delayed execution.

A job being present in the queue is not authorization.

---

# 25. Manual Run / Retry

Admin tools can expose:
- Run now;
- Retry failed;
- Cancel;
- Pause/Resume parent work;
- inspect safe payload summary;
- inspect attempts/logs;
- copy correlation ID;
- health/recovery guidance.

Manual retry still follows idempotency/reconciliation policy and required capability. It cannot bypass business preconditions simply because an administrator clicked Retry.

---

# 26. Health model

Metrics/status:
- runner mode;
- last successful runner heartbeat;
- due/past-due count;
- oldest eligible age by urgency;
- running/claimed count;
- retry-wait/final-failed counts;
- throughput window;
- provider-rate-limited backlog;
- stuck claims/leases;
- paused workloads;
- cleanup/retention health.

Health states should distinguish:
- healthy;
- delayed;
- backpressured;
- stalled;
- dependency unavailable;
- runner disabled/misconfigured;
- adapter degraded.

Past-due does not automatically mean failed; request-driven WP-Cron can run due events late.

---

# 27. Paper acceptance

Accept as WPE platform semantics:

1. JobService is backend-neutral and exposes Job Type, Schedule, Job, Attempt, Runner and Execution Policy concepts.
2. Mutating jobs assume at-least-once execution opportunity; business idempotency is explicit.
3. Raw priority is replaced by reviewed urgency classes plus fairness/age protection.
4. Resource classes/concurrency keys/backpressure bound work.
5. large workloads use checkpointed chunks/continuations.
6. cancellation/pause are cooperative and truthful.
7. ordering dependencies are explicit, never inferred from timestamps/group order.
8. Job presence is not authorization; delayed work rechecks relevant policy.
9. Action Scheduler remains preferred initial adapter candidate, but exact fairness/concurrency/multisite/coexistence mapping remains P-003 evidence.

---

# 28. Future P-003 evidence — NOT AUTHORIZED

After explicit consent:
- Action Scheduler version/load-order/coexistence matrix;
- enqueue/schedule/recurring/unique/priority mapping;
- claim/crash/reclaim behavior;
- retry + unknown external outcome;
- idempotency fixtures;
- batch/concurrency/load limits on representative hosting;
- fairness/starvation fixtures under mixed urgency classes;
- resource/concurrency-key behavior;
- high backlog/backpressure/admission tests;
- cancellation/checkpoint behavior;
- WP-Cron stalled/low-traffic behavior;
- system cron/WP-CLI runners;
- group-specific runner dependency/fairness tests;
- multisite isolation/fairness;
- retention/cleanup ownership;
- AS upgrade/migration/version conflict;
- uninstall/deactivate/re-enable recovery;
- sensitive payload/log redaction;
- runner health monitoring.

No Action Scheduler package install, queue creation, database migration, runner execution or benchmark is authorized by this document.

## Current research references

- https://actionscheduler.org/
- https://actionscheduler.org/api/
- https://actionscheduler.org/wp-cli/
- https://actionscheduler.org/perf/
- https://actionscheduler.org/faq/
- https://developer.wordpress.org/reference/functions/wp_schedule_event/
