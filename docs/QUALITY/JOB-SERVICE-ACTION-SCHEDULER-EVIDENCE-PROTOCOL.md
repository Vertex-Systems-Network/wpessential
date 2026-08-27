# WPEssential — P-003 JobService / Action Scheduler Evidence Protocol

Status: **Phase 0 protocol only / DO NOT EXECUTE without explicit owner consent**  
Governs: ADR-0006 + ADR-0059.

## Objective

Determine whether Action Scheduler can serve as WPEssential's initial `JobService` backend without weakening the accepted Job Type/Schedule/Job/Attempt/Runner/Execution Policy semantics.

This protocol authorizes no package install, DB migration, scheduled action, runner, WP-CLI command, benchmark or provider call.

---

# 1. Environment matrix

Future evidence pins:
- WordPress minimum/current matrix accepted by P-001;
- PHP minimum/current matrix;
- MySQL/MariaDB matrix;
- single site;
- multisite subdirectory/subdomain where supported;
- WP-Cron normal;
- `DISABLE_WP_CRON` + real system cron/WP-CLI;
- loopback restricted/degraded host;
- persistent object cache on/off where relevant;
- representative shared/managed/high-resource hosting constraints.

No P-003 runtime testing begins until P-001 establishes the compatible test floor.

---

# 2. Dependency / load-order / coexistence

Fixtures:
- WPE bundles candidate Action Scheduler version alone;
- WooCommerce/another plugin loads older compatible version;
- another plugin loads newer compatible version;
- plugin/theme load-order variants;
- Action Scheduler already active as standalone plugin;
- unsupported/too-old feature set;
- schema migration in progress/interrupted;
- deactivate/reactivate WPE while third-party actions exist.

Verify:
- latest-version loader behavior is understood;
- WPE API calls occur only after initialized state;
- no third-party action/group mutation;
- WPE feature-detects required capabilities where supported;
- incompatible environment degrades visibly rather than fatal/partial-corrupt scheduling;
- uninstall cleanup owns only WPE data.

---

# 3. Logical mapping

Prove mapping for:
- WPE Job Type → backend hook;
- payload/reference → validated args;
- WPE Job/Attempt ID → backend action/claim/log references;
- workload/resource family → backend group(s);
- WPE urgency → backend scheduling hints;
- due-at/schedule semantics;
- cancellation/unscheduling;
- recurring schedule recreation/ensure behavior;
- progress/checkpoint projection.

Acceptance requires backend details not leak into stable module/public APIs.

---

# 4. Persistence / ambiguity

Crash points:
- after WPE Job persisted but before backend action persisted;
- backend action persisted but WPE backend reference not committed;
- claim acquired before Attempt record update;
- handler side effect completed before success commit;
- success committed in one store but not another;
- retry scheduled but original failure state update lost.

Verify reconciliation detects and repairs or surfaces each ambiguity without duplicate uncontrolled effects.

Exact canonical physical ownership of Job/Attempt vs backend projection is decided from evidence, not assumed by this protocol.

---

# 5. At-least-once / idempotency

Representative job classes:
- pure idempotent cache rebuild;
- DB state transition with version/CAS;
- provider API with idempotency key;
- provider API with unknown outcome/no idempotency;
- chunked import;
- notification/email send;
- backup remote part upload;
- destructive reset/restore checkpoint.

Crash/retry each immediately before/after domain side effect.

Pass criteria:
- no claim of exactly-once execution;
- duplicated worker opportunity does not duplicate protected side effect when certified idempotency strategy exists;
- unknown-outcome job transitions to reconciliation/manual policy instead of blind infinite retry.

---

# 6. Claims / stale claims / runner crash

Test:
- worker killed after claim;
- worker killed mid-handler;
- PHP fatal;
- max execution timeout;
- memory termination;
- loopback request failure;
- stale claim cleanup/reclaim;
- concurrent runners claim same due population;
- handler completes after backend considers claim/action timed out.

Record actual Action Scheduler behavior/version.

WPE must not treat stale claim as proof of no side effect.

---

# 7. Urgency / fairness / starvation

Generate sustained mixed workload:
- security_transactional;
- interactive;
- normal;
- bulk;
- maintenance.

Scenarios:
- constant high-priority arrivals;
- huge bulk backlog then interactive request;
- provider-rate-limited email backlog plus membership reconciliation;
- maintenance backlog with regular normal jobs;
- recovery/control work during bulk pressure.

Measure:
- queue wait/oldest age per class;
- throughput per class;
- starvation duration;
- DB/CPU/memory pressure;
- impact of backend priority/group mapping.

Pass criteria:
- no lower allowed class can starve indefinitely under configured healthy capacity;
- interactive/security latency remains bounded by documented policy;
- fairness mechanism does not violate explicit dependencies.

If default Action Scheduler runner cannot enforce accepted fairness, evaluate WPE dispatcher/group-runner strategy while preserving adapter boundary.

---

# 8. Resource / concurrency keys

Test caps for:
- site-wide backup capture;
- restore/reset destructive-exclusive;
- same import run;
- same provider connection;
- CPU-heavy watermarks;
- DB-write-heavy membership/import work.

Verify:
- no two jobs violating same exclusive key execute concurrently;
- different safe keys can make progress;
- stale lock/claim can recover;
- lock loss does not erase external-side-effect ambiguity;
- multisite keys cannot collide cross-site accidentally.

---

# 9. Backpressure / admission

Producer fixtures:
- 500k-row logical import;
- 100k notification recipients;
- 100k watermark candidates;
- several large backup destinations;
- recurring job producing faster than consumer throughput.

Verify:
- bounded chunk producer rather than unbounded synchronous action creation;
- low-urgency producer slows/pauses above high-water threshold;
- required transactional work remains accepted;
- admin sees backpressure state;
- queue DB growth remains within evidence budget;
- recovery after backlog drains.

Exact thresholds require benchmark evidence.

---

# 10. Batch / concurrency tuning

Test default then bounded candidates for:
- Action Scheduler batch size;
- concurrent batches;
- web loopback runner;
- WP-CLI runner;
- focused groups/hooks runners.

Measure:
- actions/sec;
- DB connection/load;
- lock contention;
- memory;
- PHP workers;
- page/front-end impact;
- error/retry rate.

Official Action Scheduler warnings about concurrency/site load are treated as a reason to benchmark conservatively, not to expose a universal high-concurrency toggle.

---

# 11. Runner modes

## Request-driven/default
- low traffic;
- no traffic after due time;
- blocked loopback;
- WP-Cron spawn failure;
- recovery after traffic resumes.

## System cron/WP-CLI
- due-only recurring execution;
- focused group runner;
- catch-all runner;
- overlapping cron invocations;
- killed CLI process;
- cron stops/resumes.

Verify UI truthfully reports runner mode/health and does not promise exact schedule execution in request-driven mode.

---

# 12. Group-specific runner ordering

Construct explicit and implicit dependency scenarios across groups.

Verify:
- WPE never relies on equal timestamps or group speed for required ordering;
- explicit domain dependency blocks B until A durable state exists;
- focused group runners cannot bypass precondition;
- fairness improvements do not corrupt Workflow/import/billing order.

---

# 13. Cancellation / pause

Test:
- pending cancel;
- claimed-before-start cancel;
- running chunked job cancel request;
- non-interruptible critical section;
- provider upload abort;
- pause parent Run;
- resume;
- emergency service pause with recovery/control exception.

UI/log state must reflect requested vs effective cancellation separately.

---

# 14. Recurrence / overlap / missed runs

Profiles:
- fixed interval;
- cron-like wall-clock schedule;
- long-running occurrence overlaps next due time;
- several missed occurrences during runner outage;
- DST/timezone edge from Cron Builder policy.

Verify explicit `skip_if_running|queue_one|allow_overlap|coalesce` and missed-run policy.

Backend's automatic recurring behavior cannot silently choose product semantics.

---

# 15. Payload / secrecy / authorization

Fixtures:
- payload schema invalid at enqueue;
- schema version old at execution;
- secret reference rotated/deleted;
- user loses capability before delayed job runs;
- resource is deleted/revoked;
- provider credential revoked;
- malicious oversized/string payload.

Verify:
- secrets absent from searchable backend args/logs where avoidable;
- delayed jobs reauthorize relevant security/business preconditions;
- invalid payload fails safely without executing arbitrary callback;
- logs redact protected data.

---

# 16. Retention / cleanup

Compare WPE policy to actual current backend behavior for:
- completed/cancelled actions;
- failed actions;
- Action Scheduler logs;
- WPE Job/Attempt projection;
- parent domain Run/audit references.

Test cleanup while:
- no active work;
- related parent Run retained;
- plugin deactivated/reactivated;
- AS version changes;
- WPE uninstall with keep-data vs delete-data policy.

Backend cleanup must not make WPE falsely promise detail it no longer has.

---

# 17. Multisite

Future fixtures:
- per-site queue isolation;
- switching site context;
- site deleted while work pending;
- network-owned job;
- two high-volume sites competing;
- per-site fairness;
- credentials/definitions cannot resolve cross-site;
- network activation/deactivation.

Since Action Scheduler currently documents no special network-wide multisite handling, a WPE multisite strategy must be proven explicitly.

---

# 18. Observability / health

Verify metrics for:
- last runner heartbeat;
- due/past-due;
- oldest eligible age per urgency;
- claimed/running;
- retry-wait/final failure;
- throughput;
- stale claims;
- paused/backpressured workloads;
- runner disabled/degraded.

Health alerts are deduplicated and do not themselves create runaway jobs.

---

# 19. Pass/fail artifact

P-003 report must contain:
- environment/version matrix;
- adapter dependency strategy;
- logical/physical mapping;
- fixture IDs/results;
- measured load/fairness/backlog values;
- failure/ambiguity observations;
- unsupported features;
- multisite result;
- retention behavior;
- security/privacy findings;
- accepted defaults/limits proposed from evidence;
- decision whether ADR-0006 can become Accepted for Action Scheduler or requires a different adapter.

## Gate

Do not execute any part of P-003 until explicit owner development/executable-spike consent is granted under ADR-0014 and prerequisite P-001 environment floor is available.
