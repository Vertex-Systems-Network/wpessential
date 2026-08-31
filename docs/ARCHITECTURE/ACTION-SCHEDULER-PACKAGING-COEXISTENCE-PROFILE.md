# WPEssential — Action Scheduler Packaging, Load Order & Coexistence Profile

Status: **Phase 0 static architecture / Action Scheduler backend remains unverified / no package install authorized**  
Review date: **2026-08-28**  
Related: ADR-0006, ADR-0059, P-003, `JOB-SERVICE-EXECUTION-FAIRNESS-BACKPRESSURE.md`.

## 1. Purpose

Action Scheduler remains WPEssential's preferred concrete JobService backend candidate, but WPE must coexist with WooCommerce and other plugins that also bundle or install Action Scheduler.

This document defines the static packaging/load-order boundary without claiming P-003 has passed.

Current official snapshot reviewed:
- Action Scheduler **4.1.0**, released 2026-08-05;
- WordPress.org lists WordPress 6.8+ and PHP 7.2+ for that package;
- 4.0.0 introduced breaking changes around unique-action argument handling and default failed-action retention;
- 4.1.0 includes security hardening around stored schedule deserialization plus lock/query improvements.

No package has been installed or loaded in WPEssential.

---

# 2. Ownership model

If future P-003 selects Action Scheduler, the **WPE Platform Kernel / Free plugin owns WPE's bundled candidate copy**.

Rules:
- WPE Pro does not bundle a second WPE-owned copy;
- individual WPE modules never bundle their own Action Scheduler copy;
- third-party/WooCommerce copies are expected and must coexist;
- JobService remains the product-facing abstraction;
- module code must not depend directly on Action Scheduler classes/storage tables/admin screens.

Reason: Free is the platform dependency for Pro, while a second Pro copy would add avoidable load-order/version drift.

---

# 3. Distribution candidate

Official Action Scheduler guidance recommends including the library in a plugin codebase via a stable subtree; Composer is also supported.

WPE paper preference for the first P-003 spike:
- vendor/pin a known stable release inside the platform package;
- preserve upstream files/license/history according to dependency policy;
- do not modify/fork upstream behavior unless a separately justified architecture decision requires it;
- dependency update is explicit/reviewed, not `latest` at build time.

Exact subtree vs Composer build mechanism remains P-003/P-008 evidence because packaging/toolchain interactions still need proof.

---

# 4. Load-order contract

Official current behavior:
- including `action-scheduler.php` registers that bundled version;
- registered versions are resolved around `plugins_loaded` priority 0;
- the most recent registered plugin copy is selected;
- Action Scheduler initializes around `init` priority 1;
- public APIs should be used only after initialization / `action_scheduler_init` as documented.

Future WPE adapter rules:
1. include/register the WPE bundled candidate during platform bootstrap **before `plugins_loaded` priority 0**;
2. do not initialize or fork Action Scheduler manually;
3. bind WPE JobService adapter readiness only after Action Scheduler is initialized;
4. if expected public capabilities are unavailable, JobService reports backend incompatibility/degraded mode rather than fataling through module code;
5. scheduled WPE jobs are created only after adapter readiness.

This is a paper load-order rule; actual plugin/Free/Pro/Woo coexistence remains a P-003 fixture.

---

# 5. Multiple-copy rule

Action Scheduler is intentionally designed to be distributed in multiple plugins and to load the newest registered plugin version.

Consequences for WPE:
- the runtime copy may be newer than WPE's bundled candidate;
- WPE cannot assume its own vendored files/classes are the executing runtime;
- WPE must use supported public APIs/feature detection;
- WPE must not patch runtime globals/classes to force its bundled copy to win;
- a future newer runtime that fails WPE evidence becomes `backend_newer_unverified`, not automatically compatible;
- a runtime older than WPE-required capability becomes `backend_below_floor`.

Where available, public feature detection such as `as_supports()` is preferred over private version internals.

---

# 6. WPE adapter isolation

Only `JobService/ActionSchedulerAdapter`-equivalent implementation may call Action Scheduler public APIs.

Modules use WPE concepts:
- Job Type;
- Job Record;
- Attempt;
- Schedule;
- urgency;
- resource/concurrency key;
- idempotency key;
- checkpoint;
- cancellation token/state;
- retry policy.

The adapter translates these to/from Action Scheduler primitives.

Modules must not:
- query Action Scheduler tables directly;
- instantiate Action Scheduler store classes directly;
- use Action Scheduler status strings as WPE business states;
- assume queue IDs are WPE Job UUIDs;
- depend on Action Scheduler retention for WPE audit/business history.

---

# 7. Hook/group namespace

Future WPE action hooks/groups use a reserved WPE namespace/profile.

Conceptual direction:
- hook prefix `wpessential/` or an equally collision-resistant stable prefix;
- group key derived from WPE platform/module/job class, not user-facing labels;
- group/hook names versioned only when semantics actually break;
- no secret/user PII encoded into hook/group names.

Exact naming is implementation detail to be fixed before coding starts.

---

# 8. Payload contract

Action Scheduler persists action arguments. WPE therefore treats backend arguments as persisted operational data.

Rules:
- pass scalar/array identifiers and minimal immutable references;
- do not place API keys, OAuth tokens, passwords, signed upload URLs, private keys or Backup recovery secrets into action args;
- do not persist arbitrary PHP objects/classes as Job payloads;
- large payloads belong in WPE-owned Job payload/checkpoint storage and backend args carry only WPE Job UUID/reference;
- sensitive payload fields use Vault references where needed;
- support/diagnostic exports redact backend action args according to JobService privacy rules.

Action Scheduler 4.1.0's schedule-deserialization hardening reinforces the rule that WPE should avoid unnecessary serialized object graphs.

---

# 9. Unique scheduling is not business idempotency

Action Scheduler 4.0 changed unique-action behavior so action arguments participate in uniqueness.

WPE therefore never makes backend `unique` scheduling its only business idempotency guard.

JobService owns:
- deterministic business idempotency key;
- source event/import/provider identity;
- result/checkpoint state;
- duplicate suppression policy;
- unknown external-outcome reconciliation.

Backend unique scheduling can be an optimization only after P-003 proves exact semantics.

---

# 10. Retention/history separation

Action Scheduler 4.0 introduced default cleanup of failed actions after roughly three months, subject to its filter/configuration.

WPE must not assume backend action/log retention equals product audit/history retention.

Separate responsibilities:
- Action Scheduler record/log: operational queue/backend history;
- WPE Job/Attempt: product operational history according to WPE retention policy;
- Audit Log: authorization/configuration/destructive-operation evidence according to Audit retention.

WPE can allow backend cleanup without silently deleting WPE-required product/audit history.

---

# 11. Scheduling/recurrence rule

Official Action Scheduler guidance includes scheduling recurring actions on plugin activation/update and provides an ensure-recurring hook on supported versions.

WPE does not expose raw recurring-action repair logic to modules.

JobService owns:
- Schedule Definition;
- desired recurrence;
- backend materialization;
- missing-schedule repair;
- timezone/DST interpretation;
- duplicate reconciliation.

Feature-specific use such as `action_scheduler_ensure_recurring_actions` is capability-gated (`as_supports()` where available) rather than assumed from provider name.

---

# 12. Admin UI ownership

WPE Job/Cron admin UI is not a reskin of Action Scheduler's native admin table.

WPE UI shows WPE concepts and can link/display backend diagnostics where appropriate.

Backend rows from Woo/other plugins remain third-party-owned and are not edited/deleted by WPE merely because they share Action Scheduler storage.

Cron Job Builder's “existing jobs” view must clearly distinguish:
- WPE-owned Job/Schedule;
- WordPress Cron event;
- third-party Action Scheduler action;
- read-only/limited-management external action according to ownership/capability.

---

# 13. Upgrade/downgrade rule

When the selected runtime Action Scheduler version changes:
- detect public capability availability;
- compare to WPE certified backend profile;
- run future migration/compatibility checks before enabling high-risk jobs;
- never rewrite Action Scheduler shared DB schema independently of upstream;
- never downgrade shared site runtime merely to match WPE's bundled candidate;
- WPE bundled dependency update is reviewed like any production dependency.

A future major Action Scheduler release defaults to backend `newer_unverified` until P-003 regression evidence exists.

---

# 14. Current static profile

| Item | Current paper state |
|---|---|
| preferred backend | Action Scheduler candidate |
| reviewed upstream version | 4.1.0 |
| WPE owner | Platform Kernel / Free package if selected |
| Pro bundles copy | No |
| modules call `as_*` directly | No |
| multiple third-party copies expected | Yes |
| version resolution | upstream newest registered plugin copy behavior |
| required registration timing | before `plugins_loaded` priority 0 |
| API-use readiness | after AS initialization / `action_scheduler_init` |
| feature strategy | public API/capability detection |
| business idempotency | WPE JobService, not backend uniqueness |
| product history | WPE-owned; not backend retention |
| P-003 executable status | **Not executed** |
| backend selected as Verified | **No** |

---

# 15. Future P-003 fixtures — NOT AUTHORIZED

After explicit development consent:
- WPE bundled 4.1.x alone;
- WooCommerce-bundled copy + WPE bundled copy load order;
- third-party older copy;
- third-party newer copy;
- load before/after plugins_loaded boundary;
- API availability before/after init;
- DB-store creation/migration collision;
- 4.0+ uniqueness semantics;
- failed-retention differences;
- action args serialization/redaction;
- WPE Job UUID ↔ backend action mapping;
- crash/claim/reclaim;
- recurring schedule repair;
- WP-Cron/async/WP-CLI runners;
- multisite;
- uninstall/deactivation while third-party actions remain;
- upgrade/downgrade/newer-unverified behavior.

No dependency installation, Action Scheduler bootstrap, DB migration, action scheduling or runner execution has occurred.

---

# 16. Static references reviewed

- Action Scheduler official Usage documentation: library inclusion, multiple-version resolution, load order, initialization and capability checks.
- Action Scheduler FAQ: safe distribution in multiple plugins and newest-version selection.
- Action Scheduler API reference: `as_supports()` and public APIs.
- Action Scheduler 4.0.0 / 4.1.0 official changelog.
- WordPress.org Action Scheduler metadata, current 4.1.0 snapshot.

## Development gate

**This document does not authorize bundling/installing Action Scheduler or running any queue work. ADR-0014 explicit owner consent is still required before P-003 execution or implementation.**
