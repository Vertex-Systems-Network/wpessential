# ADR-0221 — Action Scheduler Coexistence & Backend Evidence

Status: **ACCEPTED**  
Date: **2026-08-29**  
Milestone: WP121 — Platform Foundation  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE

## Context

WPEssential requires a durable queue backend candidate without allowing business modules to depend on Action Scheduler implementation details or mutate third-party queue ownership.

The accepted static profile already required:
- JobService remains the WPE product authority;
- Action Scheduler is an operational backend candidate only;
- multiple plugin copies must coexist without WPE forcing its own copy to win;
- public `as_*` APIs and feature detection are preferred;
- WPE-owned actions use collision-resistant hook/group ownership;
- backend uniqueness is never the sole business-idempotency guarantee;
- Action Scheduler retention is not WPE Audit/Job history retention;
- action args may contain minimal immutable identifiers, never secrets or large product payloads.

## Decision

Implement a bounded Action Scheduler backend adapter and certify the current coexistence fixture profile.

### Production-source adapter boundary

Added:
- `ActionSchedulerBackendEnvironmentInterface`;
- `NativeActionSchedulerBackendEnvironment`;
- `ActionSchedulerBackend`.

Canonical backend ownership:
- dispatch hook: `wpessential/hook_job_dispatch`;
- group: `wpessential-jobs`;
- persisted backend arguments: only `job_id`;
- Job payload, secrets, checkpoints and business state remain WPE-owned;
- exact cancellation/query scope is hook + group + Job UUID;
- WPE does not query Action Scheduler private tables/classes from production adapter code.

### Public API correction discovered during implementation

Upstream `as_has_scheduled_action()` returns a boolean, not an action ID.

An early adapter draft incorrectly treated it as an ID source. This was corrected before acceptance. Backend action IDs are discovered via the supported `as_get_scheduled_actions(..., 'ids')` query path. `as_has_scheduled_action()` remains capability/presence semantics only.

### Executable coexistence fixture

CI uses:
- WordPress 7.1;
- PHP 8.2;
- MySQL 8.4;
- Action Scheduler 3.9.3;
- Action Scheduler 4.1.0.

The fixture verifies:
1. both Action Scheduler copies register during ordinary WordPress plugin bootstrap;
2. newest registered version 4.1.0 wins selection;
3. Action Scheduler initializes normally and exposes the required public API;
4. `as_supports()` feature detection reports the recurring-actions support profile;
5. shared Action Scheduler tables initialize successfully;
6. WPE can materialize a Job UUID through the public scheduling API;
7. duplicate backend materialization may collapse through backend uniqueness while WPE business idempotency remains external to that optimization;
8. separate WPE Job UUIDs remain separate actions;
9. exact WPE cancellation does not cancel another WPE action;
10. exact WPE cancellation does not modify a third-party hook/group/action.

The deterministic smoke suite separately verifies backend adapter ownership/cancellation behavior without Action Scheduler runtime dependency.

## Evidence

Historical run `33267016164` / #172 failed before runtime fixture execution because the engineering validator required the two new integration entrypoints to define `ABSPATH` before loading guarded source. The fixtures were corrected; the guard was not weakened.

Final evidence source head:
`8f911050483e46996f638d9cb5aca50a7f77f37e`

GitHub Actions run **33267115851 / #178** completed **SUCCESS**.

PASS includes:
- Composer metadata;
- canonical architecture validator;
- engineering-contract validator including `ABSPATH` rules;
- PHP syntax;
- 9/9 smoke suites including Action Scheduler backend smoke;
- compiled-registration MySQL integration;
- Definition/Audit MySQL integration;
- real WordPress AJAX/nonce/Policy integration;
- pinned Action Scheduler 3.9.3 + 4.1.0 fixture preparation;
- Action Scheduler activation/store bootstrap;
- real coexistence/backend integration.

## Certification boundary

This ADR certifies the **tested coexistence/backend profile**, not every possible Action Scheduler installation.

It does **not** mean:
- every future Action Scheduler version is automatically certified;
- Multisite queue behavior is certified;
- WPE may mutate third-party Action Scheduler actions;
- Action Scheduler becomes WPE business-state/history authority;
- backend uniqueness replaces WPE idempotency;
- the final WPE distribution packaging mechanism is locked;
- Action Scheduler has been bundled into the public release package yet.

Runtime capability readiness must not fabricate site-specific coexistence certification. A materially different runtime/profile requires fresh evidence.

## Packaging boundary

The current executable fixture proves public API/load-order coexistence using real upstream release packages. The final WPE distribution mechanism (for example reviewed vendoring/subtree versus build-time Composer packaging) remains a separate release/build decision and must preserve:
- registration before `plugins_loaded` version resolution;
- upstream license/source integrity;
- one WPE-owned candidate copy in Free/Platform only;
- no duplicate Pro/module copies;
- deterministic pinned dependency version;
- no runtime download of `latest`.

## Consequences

The next WP121 tranche may build durable WPE Job attempts/leases/checkpoints on top of this backend boundary. Those records remain WPE-owned persistence and must not be inferred from Action Scheduler rows alone.
