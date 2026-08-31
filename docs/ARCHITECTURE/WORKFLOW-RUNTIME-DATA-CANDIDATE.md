# WPEssential — Workflow Runtime Data Candidate Model

Status: **Phase 0 paper architecture / Proposed / no runtime tables or jobs authorized**

## 1. Goal
Separate Workflow **definitions** from execution **runs** so Forms, Membership, Cron, Notifications, REST and other modules can invoke durable workflows without serializing execution state inside definition JSON or depending on one HTTP request.

---

# 2. Storage boundary

Definition Repository owns:
- workflow definition UUID;
- graph nodes/edges;
- trigger bindings;
- condition/action config;
- revision/published revision;
- editor layout metadata;
- dependency graph.

Workflow Runtime owns:
- run state;
- step attempts;
- waits/delays;
- approvals;
- idempotency/dedupe records;
- safe input/output references;
- compensation/retry state;
- run logs/metrics.

Job Service schedules execution but is not the Workflow source of truth.

---

# 3. Candidate runtime entities

## Workflow Run
Logical fields:
- numeric ID;
- run UUID;
- workflow definition UUID;
- pinned published revision UUID;
- trigger type/source;
- trigger event ID/idempotency key;
- principal/system actor context;
- status;
- current/next runnable state;
- started/finished/cancelled timestamps;
- parent/causation run ID;
- correlation ID;
- input snapshot/reference;
- safe context snapshot;
- retry/attempt counters summary;
- error category/code;
- retention class;
- created/updated.

## Step Run / Node Attempt
- run ID;
- node stable ID from pinned definition revision;
- attempt number;
- state;
- scheduled/start/finish;
- input reference/safe summary;
- output reference/safe summary;
- error;
- idempotency key;
- external/job reference;
- retry-after/next-at;
- compensation state.

## Wait/Subscription
For delay/wait-for-event/manual continuation:
- run/node;
- wait type;
- resume condition/event filter;
- due/timeout time;
- subscription key;
- consumed event ID;
- state;
- expiry.

## Approval
- run/node;
- approver policy snapshot/reference;
- assigned users/roles/query result strategy;
- state pending/approved/rejected/expired/cancelled;
- decision actor;
- decision time;
- safe comment/reason;
- due time;
- escalation state.

---

# 4. Run states

Candidate:
- `queued`;
- `running`;
- `waiting`;
- `waiting_approval`;
- `retry_scheduled`;
- `completed`;
- `completed_with_warning`;
- `failed`;
- `cancel_requested`;
- `cancelled`;
- `compensating`;
- `compensation_failed`.

Terminal:
- completed;
- failed;
- cancelled;
- compensation_failed unless recovery flow resumes with explicit action.

Run status is derived from durable runtime state, not browser presence.

---

# 5. Definition pinning

Every run pins the **published workflow revision** at trigger time.

Publishing a new workflow revision:
- affects new runs;
- does not silently mutate in-flight graph semantics;
- old revision remains readable while retained runs depend on it;
- definition cleanup cannot delete required revision until retention/dependency policy allows.

Optional future migration of in-flight runs to new revision is not standard behavior and would require explicit migration mapping.

---

# 6. Trigger/idempotency

Trigger contract includes:
- event ID or derived idempotency key;
- workflow UUID/revision;
- trigger binding ID;
- subject/resource dimensions if needed;
- allowed duplicate policy.

Default external/event trigger behavior: same idempotency identity must not create duplicate run.

Manual Run may intentionally create multiple runs but receives unique invocation ID and audit actor.

---

# 7. Node execution model

Node categories:
- condition/branch;
- transform/map;
- Ability/action;
- delay;
- wait-for-event;
- approval;
- parallel/fan-out;
- join;
- loop bounded;
- terminate/success/fail;
- compensation action.

Each node type registers:
- input/output schema;
- side-effect classification;
- retry safety;
- timeout guidance;
- cancel capability;
- idempotency support;
- secret/sensitive fields;
- required capability/policy principal semantics.

No arbitrary PHP/JS eval node.

---

# 8. Principal / authorization

A workflow does not inherit its creator's unlimited permissions forever.

Execution principal strategies:
- triggering user principal;
- designated service/system principal with explicit allowlisted capabilities;
- resource-owner principal only through registered safe adapter;
- connection credential for external provider separated from WordPress authorization.

Workflow definition stores **principal policy**, not copied admin capability list.

At execution:
- current policy/capabilities checked where action is security-sensitive;
- a user losing permission may cause future user-principal steps to fail;
- system principal capabilities are explicit and minimal;
- AI/MCP cannot elevate workflow principal.

---

# 9. Side-effect transaction boundary

Workflow runtime cannot wrap arbitrary email/API/payment/DB actions in one universal transaction.

Rules:
- each node commits its own durable state;
- idempotency guards duplicate side effects;
- compensation is explicit business logic, not fake database rollback;
- critical local DB action may use transaction within owning Ability;
- external side effects record accepted/unknown/failed state accurately.

Do not label a workflow “rolled back” unless all relevant side effects were actually compensated/verified.

---

# 10. Retry model

Node declares retry class:
- safe/idempotent automatic;
- safe only with idempotency key;
- manual retry only;
- non-retryable.

Policy fields:
- max attempts;
- initial delay;
- backoff strategy;
- max delay;
- retryable error categories;
- jitter candidate;
- deadline/max elapsed time.

Retry reuses node idempotency identity when repeating same logical operation.

HTTP 4xx/validation/auth errors are not blindly retried; provider 429/5xx/network may be retryable according adapter.

---

# 11. Timeout / unknown outcome

A network timeout after sending a write may have unknown external outcome.

Step state must distinguish:
- definite failure before send;
- timeout/unknown;
- provider accepted;
- provider final delivery if known.

Unknown side-effect state should trigger reconciliation/status-check where adapter supports rather than blind duplicate retry.

---

# 12. Delay

Delay types:
- fixed duration;
- until absolute time;
- until relative business time through accepted calendar adapter future;
- until next scheduled local time.

Stored as durable due time. No PHP sleep.

Timezone/DST semantics defined in node config at publish time.

Job runner delay does not change intended due time; lag is observable separately.

---

# 13. Wait for event

Fields:
- event type;
- correlation/resource key;
- condition predicate;
- timeout;
- on-timeout edge;
- consume first/all/bounded-N behavior;
- historical events before wait begins: off by default unless explicit replay window.

Subscription registered before external side effect only when race semantics require it.

Event delivery at-least-once; consumed event IDs prevent duplicate resume.

---

# 14. Approval

Approver resolution modes:
- snapshot eligible approvers at node arrival;
- resolve dynamically at decision time;
- hybrid assigned group + current permission check.

Candidate default: snapshot notification recipients but re-check current approve capability/policy at decision time.

Options:
- any one approves;
- all approve;
- N of M quorum future;
- reject by any vs quorum;
- due time;
- reminder schedule;
- escalation;
- reassignment capability;
- comment required;
- anonymous/public approval prohibited normal mode.

Decision is idempotent; concurrent approvals use version/transaction guard.

---

# 15. Parallel branches

Fan-out options:
- fixed branches;
- map over bounded collection;
- maximum concurrency;
- failure policy fail-fast / wait-all / tolerate selected failures;
- join mode all / any / threshold future.

Unbounded data fan-out is prohibited; large collections become chunked/batched jobs.

Join stores branch completion state durably.

---

# 16. Loops

Only explicit loop node.

Required bounds:
- maximum iterations;
- maximum elapsed duration;
- collection size cap;
- exit condition;
- per-iteration idempotency key.

Graph cycles not passing through a recognized bounded loop node are invalid at publish.

---

# 17. Cancellation

Cancellation semantics:
- queued/waiting nodes can cancel immediately;
- running action receives cancel only if adapter supports;
- external irreversible side effect may complete despite cancellation request;
- future nodes stop after cancellation boundary;
- compensation optional according workflow design.

UI state distinguishes `cancel_requested` from `cancelled`.

---

# 18. Compensation

Optional node-level compensation reference for actions that support reversal.

Examples:
- delete created draft record;
- release reserved seat;
- cancel provider object if provider API supports.

Compensation:
- runs in reverse dependency order where designed;
- has own retries/idempotency;
- cannot assume every side effect reversible;
- failures produce `compensation_failed` with recovery actions.

---

# 19. Data passing / context

Do not duplicate entire prior step outputs into every row.

Candidate context storage:
- small typed values in versioned run context JSON;
- large/sensitive payload in owner-specific data store/object with secure reference;
- files as attachment/object refs;
- secrets as Vault references;
- protected records by IDs, re-authorized when fetched.

Expression/token engine can address:
- trigger input;
- selected prior node outputs;
- run metadata;
- current resource;
- parameters.

No unrestricted object deserialization.

---

# 20. Sensitive data

Each node schema marks sensitive fields.

Logs/UI:
- redact secret inputs;
- truncate payload previews;
- no card/payment secrets;
- private form/chat content not copied into generic workflow log unless explicitly needed and classified;
- support bundle excludes run payload by default.

Retention can differ between run metadata and sensitive payload references.

---

# 21. Observability

Run detail:
- graph with node statuses;
- event/trigger;
- revision;
- current wait/approval;
- attempts;
- timings;
- retry schedule;
- safe input/output summaries;
- correlation IDs;
- errors;
- jobs;
- audit actor/principal;
- compensation.

Metrics:
- runs by outcome;
- duration;
- queue lag;
- node failure rates;
- retry counts;
- waiting age;
- approval age;
- provider failure categories.

---

# 22. Retention

Per workflow/category:
- run metadata retention;
- step detail retention;
- approval history retention;
- sensitive context retention;
- attachment/reference retention;
- failed-run longer/shorter policy;
- anonymization for user-linked PII.

Cleanup chunked/idempotent and does not delete Definition revision still needed by retained run.

---

# 23. Runtime index patterns

Likely hot queries:
- runnable runs/nodes by due state/time;
- run detail by UUID;
- workflow runs by definition/status/date;
- trigger idempotency lookup;
- waits by event correlation key;
- approvals by approver/state/due;
- failed/retry scheduled nodes;
- retention cleanup.

Exact indexes/table split require benchmark.

---

# 24. Multisite

Default runs are site-scoped.

Network workflows require explicit future mode:
- network capability;
- site ID in scope;
- cross-site resource policy;
- job runner isolation;
- failure blast-radius controls.

No accidental cross-subsite event subscription.

---

# 25. Failure/recovery states

- definition revision missing/corrupt;
- node adapter unavailable;
- permission lost;
- secret unavailable;
- Job Service delayed/unavailable;
- unknown external outcome;
- wait timeout;
- approval orphaned;
- retry exhausted;
- compensation failed;
- payload reference expired/deleted;
- provider schema/version incompatible.

Recovery actions are registered safe operations: retry eligible node, resume wait, reassign approval, supply/repair connection, cancel, compensate, inspect.

---

# 26. Future executable benchmark — NOT AUTHORIZED

Fixtures:
- 100k runs/history;
- 10k simultaneously waiting;
- high-volume event trigger dedupe;
- 1k approvals;
- parallel fan-out;
- timeout/unknown external writes;
- runner crash mid-node;
- duplicate Job delivery;
- cancellation races;
- compensation failure;
- retention cleanup.

Measure:
- runnable-job query/index cost;
- run-detail query count;
- event-to-resume latency;
- duplicate-side-effect rate = zero under fixture;
- DB growth;
- cleanup throughput;
- lock/contention behavior.

No runtime tables, Action Scheduler queues or tests may be created/run before explicit owner consent.

## Current recommendation
Adopt the separation **Definition Repository → Workflow Runtime → Job Service** as the paper architecture. Pin every run to a published definition revision, assume at-least-once execution signals, and design every side-effecting node around idempotency/unknown-outcome/reconciliation rather than exactly-once claims.