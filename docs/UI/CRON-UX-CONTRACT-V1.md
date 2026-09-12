# Cron — UX Contract V1

Surface: **18 / Cron**  
Machine source: `config/product/option-contracts/cron.json`  
Lifecycle: **UX certification candidate**. Machine truth is `OPTION_CONTRACT_COMPLETE`; scheduler/job execution remains separately gated.

## Lifecycle preconditions

- Machine status must remain `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 15 current Atomic Option IDs are mapped exactly once below.
- WP-Cron is request-driven; next-run timestamps and due windows are not presented as exact wall-clock execution guarantees.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Automation → Cron**.

The IA separates **Event Inspector**, **Schedule Definition**, **Reliability**, **Operations**, **Observability**, **Providers**, **Diagnostics**, and **Portability**. Read-only runtime observation is distinct from authored schedule definitions and privileged lifecycle actions.

## UX state classes

### Authored definition
Schedule identity/state/window, idempotency/retry policy and portability scope are revisioned Cron-owned definitions.

### Effective/runtime state
Next run, recurrence, overdue interpretation, attempts, duration and queue health are read-only observations. They are never stored back as authored truth.

### Diagnostic/provider state
System-cron/CLI, Action Scheduler and timeout/claim semantics are provider-profiled. Provider health is shown explicitly and cannot be inferred from local UI state.

### Deferred / prohibited state
Run-now, pause/resume/reschedule/cancel/retry are privileged high-impact operations. UX contract presence does not authorize their execution.

## Atomic Option → UX map

- `cron.inspect.event` — Event Inspector → read-only hook/next-run/recurrence/native-event detail; no exact execution promise.
- `cron.inspect.context` — Event Inspector → read-only arguments/owner/scope/overdue/timezone interpretation.
- `cron.schedule.identity` — Schedule Definition → registered job/Ability target identity selector.
- `cron.schedule.state` — Schedule Definition → enabled/paused/one-time/recurring policy.
- `cron.schedule.window` — Schedule Definition → interval/start/end/occurrence/timezone due-window semantics.
- `cron.reliability.idempotency` — Reliability → Expert idempotency/concurrency policy and effective diagnostics.
- `cron.reliability.timeouts` — Reliability → provider-profile lock/execution-timeout reference.
- `cron.reliability.retry` — Reliability → retry/backoff/catch-up/overlap policy.
- `cron.operation.run-now` — Operations → privileged run-now action with confirmation/audit boundary; runtime remains separately gated.
- `cron.operation.lifecycle` — Operations → privileged pause/resume/reschedule/cancel/retry actions with affected-schedule preview.
- `cron.observability.runs` — Observability → read-only attempts/logs/duration/next occurrence.
- `cron.provider.system-runner` — Providers → read-only system cron/CLI runner health and remediation.
- `cron.provider.action-scheduler` — Providers → read-only Action Scheduler health/coexistence state.
- `cron.diagnostics.queue` — Diagnostics → read-only queue/stale-claim diagnostics.
- `cron.portability.scope` — Portability → Expert import/export/multisite scope policy with environment-sensitive remapping.

## Interaction and persistence

Definition edits use draft → validate → save revision. Timezone/interval changes preview due-window consequences without claiming exact run time. Privileged operations require explicit target, current state, confirmation and post-operation refreshed observation. Failed operations never mutate UI state optimistically into success.

## Loading, empty, validation, conflict and recovery

Required states include no schedules, inspector loading, invalid target/interval/timezone, provider unavailable, overdue, paused, stale claim, retry exhausted, operation pending/failed, stale revision, saved and recovery. Unknown runner health is shown as unknown rather than healthy.

## Security and ownership

Cron owns schedule/reliability definitions and observations; registered jobs/Abilities retain execution semantics and authorization. Providers own lock/runner-specific behavior. Run-now and lifecycle operations are privileged and audited. Client state cannot forge due state, lease ownership or operation success.

## Accessibility

Tables/inspectors expose headers and keyboard navigation. Relative and absolute time are both readable. Status is not color-only. High-impact action dialogs describe the schedule and consequence, expose cancel and restore focus. Live run/provider updates use non-disruptive announcements.

## Multisite and scope

Site/network schedule scope is explicit and server-derived. Network actions require network authorization. Imports cannot silently promote site schedules to network scope or bind environment-specific providers without remapping.

## Portability and reference remapping

Exports contain definitions, not runtime attempts/logs/leases. Imports validate registered job/Ability and provider references plus timezone/scope. Environment-sensitive provider references remain unresolved until explicitly mapped.

## Performance and scale

Large event/run sets paginate and filter server-side. Diagnostics avoid loading full logs by default. Provider health checks are bounded/cached. Admin assets load only on the Cron surface.

## Degraded/provider states

Disabled WP-Cron, missing system runner, missing Action Scheduler or provider timeout profile shows explicit degraded state and effect on due processing. The surface never fabricates exact timing, runner health, lease ownership or successful execution.

## UX lifecycle exit criteria

Certification requires complete 15-ID mapping, zero missing/unclassified machine semantics, truthful timing/provider state, accessible high-impact operations, portability/performance/degraded states and exact-head validator CI. Shared lifecycle promotion remains Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No schedule/job/provider operation is executed or authorized by this UX contract.
