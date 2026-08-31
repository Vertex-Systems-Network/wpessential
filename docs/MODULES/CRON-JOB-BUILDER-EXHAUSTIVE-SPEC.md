# WPEssential — Cron Job Builder Exhaustive Option Specification

Status: **Phase 0 exhaustive product specification / no implementation authorized**

## 1. Concepts

Separate:
- **WP-Cron Event** — WordPress scheduled hook/event, possibly owned by core/third party;
- **WPE Schedule Definition** — WPE-owned user configuration;
- **Job Run** — execution record through Job Service;
- **Runner** — WP-Cron request runner, system cron/WP-CLI runner or future certified queue runner.

WP-Cron is traffic-triggered and does not guarantee exact wall-clock execution.

---

# 2. Screens

- Overview / Runner Health
- WP-Cron Events
- WPE Schedules
- Create/Edit Schedule
- Recurrence Intervals
- Run History
- Settings
- Diagnostics

---

# 3. Overview

Cards:
- WP-Cron enabled/disabled detection
- next due WPE job
- overdue count
- failed run count
- queue backlog
- runner last observed execution
- system cron/WP-CLI status if configured
- duplicate schedule warnings
- long-running/stuck jobs

Actions:
- Run health check
- View overdue
- Create schedule
- View runner instructions
- Retry selected failed WPE job where safe

---

# 4. WP-Cron Events list

Columns:
- checkbox where action permitted
- hook
- owner/source hint: core/plugin/theme/WPE/unknown
- next run site-local
- next run UTC
- overdue badge
- recurrence name/interval
- argument summary
- callback registration summary where discoverable safely
- duplicate count
- actions

Filters:
- WPE-owned / third-party / core / unknown
- recurring / single
- overdue
- recurrence
- hook search
- source/plugin hint

Sort:
- next run
- hook
- recurrence

Row actions:
- Inspect
- Run now
- Copy event details
- Create WPE equivalent schedule where semantics can be mapped
- Unschedule/delete event — high warning for non-WPE
- Reschedule — high warning for non-WPE

Third-party ownership warning:
- another plugin may recreate the event;
- editing/removing may break that plugin;
- WPE does not claim ownership afterward unless user creates a separate WPE schedule.

---

# 5. WP-Cron event inspector

Read-only fields:
- hook
- timestamp UTC/local
- schedule key
- interval
- args safe JSON/tree
- source hint
- callbacks registered for hook, callback names only where safe
- callback priority/accepted args where discoverable
- next/related same-hook events
- recurrence definition source

Actions:
- Run now with confirmation for unknown/high-risk hook
- Reschedule
- Delete one occurrence
- Delete all matching WPE-owned occurrences; third-party requires stronger warning

Never display object serialization that could expose secrets; arguments are redacted according to registry/privacy rules.

---

# 6. WPE Schedules list

Columns:
- name
- key
- enabled/paused
- action type
- target action/workflow/Ability
- schedule summary
- timezone
- next intended run
- next queued run
- last run/result
- consecutive failures
- overlap policy
- updated
- health

Filters:
- enabled/paused
- one-time/recurring
- action family
- failed/overdue
- owner/creator where policy

Actions:
- Edit
- Run now
- Pause/Resume
- Duplicate
- View Runs
- Validate
- Export
- Archive/Delete

Bulk:
- pause/resume
- export
- archive
- delete with dependency preview

---

# 7. Create Schedule — Identity

Fields:
- Name — required
- Key — generated stable key
- Description/internal notes
- Status — Draft default / Active / Paused
- Tags/category optional

Key rename after publish requires dependency impact because workflows/API may reference it.

---

# 8. Schedule type

Options:
- One-time
- Fixed interval
- Calendar recurrence
- Daily
- Weekly
- Monthly
- Yearly
- Selected weekdays
- Advanced recurrence builder
- Cron-expression-like input only if compiled to supported semantics and previewed; never pretend WP-Cron is Unix cron accuracy

## One-time
- date
- time
- timezone
- missed-run policy

## Fixed interval
- every N minutes/hours/days
- minimum supported interval policy
- anchor/start timestamp

## Daily
- time
- every N days optional

## Weekly
- weekdays multi-select
- time
- every N weeks

## Monthly
Modes:
- day of month 1–31
- last day
- first/second/third/fourth/last weekday
- selected months optional

Invalid month-day behavior:
- skip month — default candidate
- last valid day optional explicit

## Yearly
- month
- day
- time

---

# 9. Timezone and DST

Fields:
- timezone: Site timezone default / explicit IANA timezone
- display UTC equivalent
- DST behavior info

Calendar recurrence preserves intended local clock time.

DST ambiguous/nonexistent local time policy must be explicit:
- nonexistent time (spring forward): run at next valid local time candidate;
- repeated time (fall back): run once candidate.

Exact behavior requires tests against accepted scheduler implementation.

Fixed-duration interval is elapsed duration semantics, not “same clock time” semantics.

---

# 10. Missed-run policy

Because WP-Cron/runner can be late:
- Run once as soon as possible — default for most tasks
- Skip missed occurrence and schedule next
- Catch up all missed occurrences — disabled by default / bounded max because outage could create job storm
- Catch up maximum N

UI previews consequences after 1h/1d/7d downtime.

Never enqueue unbounded thousands of catch-up runs.

---

# 11. Action type

Allowed standard targets:
- registered WPE Ability/action
- Workflow definition
- WordPress action hook with validated typed arguments
- Connection/HTTP action through approved Connections Manager contract
- Notification send action
- Backup action through Backup Ability
- registered SDK action

Not standard:
- arbitrary PHP code/eval
- server-side arbitrary JS
- HTML as executable logic
- shell commands
- raw SQL

Shortcode/block rendering only where a downstream registered action genuinely consumes output; not a fake server task model.

---

# 12. Ability/action mapping

Fields generated from action schema:
- required/optional inputs
- static value
- dynamic token/context value allowlist
- secret reference picker where action expects secret handle
- validation preview
- sensitive field masking

Action classification shown:
- read
- write
- destructive
- external side effect

Destructive actions may be prohibited from unattended schedules unless dedicated policy explicitly allows.

---

# 13. WordPress hook action

Fields:
- Hook name — validated grammar
- Arguments — typed JSON/list structure
- Expected callback count preview
- warning when no callback currently registered
- owner/source notes

Do not allow arbitrary serialized objects/resources.

Scheduled hook execution does not bypass callback's own capability/security assumptions; user-created hook schedules should target deliberately registered automation hooks.

---

# 14. Conditions before run

Optional condition group:
- current date/time
- site state
- Query result/count
- entity/resource state
- Membership/entitlement state when appropriate
- previous run result
- registered condition provider

Options:
- If false: skip occurrence / mark skipped
- re-evaluate at actual run time — default yes

No raw PHP condition.

---

# 15. Overlap/concurrency policy

Per schedule:
- Allow overlap
- Skip if previous still running — safe default for many tasks
- Queue after previous
- Cancel/replace previous only if action explicitly supports cancellation
- Singleton by custom business key

Fields:
- lock timeout
- stale-lock recovery policy
- max concurrent runs where >1 allowed

Locks require durable Job Service implementation; UI-only lock is invalid.

---

# 16. Timeout/runtime budget

Fields:
- expected runtime hint
- hard/soft timeout policy from Job Service
- chunking required warning for long tasks
- memory risk warning from registered action metadata

A user cannot simply set unlimited PHP execution time as a reliability feature.

---

# 17. Retry policy

Options:
- no automatic retry
- fixed delay
- exponential backoff
- action/provider recommended

Fields:
- max attempts
- initial delay
- max delay
- retryable error categories
- jitter candidate

Non-idempotent actions require idempotency support before automatic retry can be enabled.

---

# 18. Failure policy

After final failure:
- mark failed
- notification rule
- workflow trigger
- pause schedule after N consecutive failures optional
- do not pause

Fields:
- consecutive-failure threshold
- alert recipients through Notification System
- reset failure counter after success — default yes

---

# 19. Run now

Run Now modal shows:
- action target
- parameters
- current condition result
- destructive/external badges
- overlap state
- expected async behavior

Options:
- use saved parameters
- temporary test override values only if action schema allows; not persisted unless Save chosen separately
- dry-run if Ability supports it

Run Now creates normal audited run, not hidden direct callback execution.

---

# 20. Pause/Resume

For WPE Schedule:
- pause prevents new scheduled runs;
- already-running job continues unless separately cancellable;
- queued future occurrences cancelled/ignored according to Job Service semantics;
- resume calculates next intended occurrence from recurrence + missed-run policy.

Third-party WP-Cron events do not have a fake universal pause flag. WPE may only offer pause where it can safely own/suppress a wrapper; otherwise use unschedule warning.

---

# 21. Recurrence intervals screen

List:
- key
- label
- seconds/duration
- source: core/plugin/WPE
- used-by count

WPE custom interval create fields:
- Name
- Key
- duration numeric
- unit
- minimum/maximum guard

Editing interval shows affected schedules and whether existing events need rescheduling.

Do not delete interval while active WPE schedules depend on it without migration.

---

# 22. Runner Health

Checks:
- `DISABLE_WP_CRON`
- loopback ability where relevant
- last observed cron spawn
- overdue event count
- WPE Job Service backlog
- oldest queued job age
- failed jobs
- system cron/WP-CLI heartbeat if configured
- Action Scheduler adapter health if selected later
- site traffic warning when relying only on WP-Cron

Statuses:
- Healthy
- Degraded
- No reliable runner observed
- Disabled
- Backlog
- Unknown

---

# 23. System cron / WP-CLI guidance

Generated guidance based on environment; not automatically edited into server crontab.

Show:
- recommended command/URL method according to accepted runner contract
- interval recommendation
- security caveats
- verification/heartbeat instructions
- disable built-in WP-Cron guidance only if external runner proven configured

Never expose secret URLs/tokens in copyable docs unless intentionally generated and revocable.

---

# 24. Run History

Columns:
- Run ID
- Schedule
- intended run time
- actual queued/start/end
- scheduling lag
- duration
- status
- attempt
- action
- actor/source
- result summary

Statuses:
- queued
- claimed
- running
- skipped_condition
- skipped_overlap
- succeeded
- succeeded_warning
- failed_retrying
- failed
- cancelled
- timed_out

Actions:
- inspect
- retry when safe
- cancel when supported
- copy correlation ID
- view related workflow/action logs

---

# 25. Run detail

Display:
- schedule/revision snapshot
- intended vs actual time
- arguments safe/redacted
- condition trace
- attempts
- errors normalized
- output safe summary
- related entity/job/workflow IDs
- runner information
- lock/concurrency details

No secrets/raw sensitive payload in normal logs.

---

# 26. Schedule revisions

Published schedule changes create revision.

Already queued/running occurrence records revision/config snapshot or sufficient immutable schedule version.

New schedule revision applies to future occurrences after publish.

Changing recurrence requires clear “next run will change from X to Y” preview.

---

# 27. Delete/archive

Archive preferred for schedules with run history.

Archive:
- no future runs
- history preserved
- references resolve read-only

Delete requires:
- no dependencies or explicit resolution
- queued job impact
- history retention choice according to audit policy

Deleting definition does not erase historical audit/run records automatically.

---

# 28. Module settings

Candidate controls:
- default timezone: Site
- default missed-run policy
- default overlap policy
- default retry cap
- run history retention
- successful-run retention
- failed-run retention
- max catch-up runs
- minimum custom interval
- runner-health warning threshold
- overdue threshold
- log detail level
- cleanup batch size later benchmarked

Settings cannot override action-specific safety limits.

---

# 29. Permissions

Separate capabilities:
- read schedules/events
- create/update/delete/publish WPE schedules
- run now
- pause/resume
- view run logs
- retry/cancel
- modify third-party events — dedicated higher-risk capability candidate
- manage recurrence intervals
- manage runner settings

Third-party mutation should not be granted to ordinary Automation Manager by default.

---

# 30. Ability surface

Candidate:
- schedule list/get/create/update/validate/publish/archive
- schedule run/pause/resume
- run list/get/retry/cancel
- cron event list/get/run
- third-party unschedule/reschedule only separate high-risk Ability
- runner health/explain

AI default exposure:
- list/get/explain/validate/health;
- create draft optionally;
- run/delete/third-party mutation disabled by default.

---

# 31. Events

- schedule created/updated/published/paused/resumed/archived
- run queued/started/skipped/completed/failed/retried/cancelled
- runner unhealthy/recovered
- recurring interval changed

Avoid generating repeated runner-unhealthy event storms; deduplicate/state transition events.

---

# 32. Empty/error/degraded states

- WP-Cron disabled + no external runner
- low traffic warning
- Job Service unavailable
- action dependency missing
- schedule invalid after imported dependency missing
- recurrence removed by third-party plugin
- clock/timezone changed
- Pro expired → schedule management read-only and mutating runtime follows ADR-0007 pause policy
- site in maintenance/recovery

---

# 33. Performance

- paginate event/run lists
- do not execute callbacks merely to inspect them
- batch health queries
- cap run log payload sizes
- cleanup history asynchronously
- no per-page global cron scans outside WPE/diagnostics screens
- bulk due jobs processed by Job Service, not one monolithic request

---

# 34. Assets

Cron admin assets only on Cron/WPE job screens.
No frontend assets required for scheduling engine itself.
Runner health dashboard card uses shared minimal platform assets only when shown.

---

# 35. Future tests

After consent:
- one-time recurring creation
- DST spring/fall cases
- low-traffic delayed execution
- missed-run skip/catch-up bounds
- overlap race
- stale lock recovery
- idempotent retry
- non-idempotent retry blocked
- pause with running job
- reschedule next-run correctness
- third-party mutation permission
- WP-Cron disabled health
- system runner heartbeat
- imported missing action dependency
- run-log redaction
- cleanup retention
- Pro expiry pause/read-only behavior

## Maturity

Cron Job Builder is now **Exhaustive option spec** at Phase 0 product level. Concrete Job Service/Action Scheduler integration remains a Proposed technical decision requiring explicit owner-authorized executable evidence.