# Reset — Provisional UX Contract V1

Surface: **25 / Reset**  
Planning issue: **#591**  
Supervisor wave: **#583**  
Exact-main claim anchor: `97f9c29ffad14e1124a7565e8dd4e027e9ef7d10`

Lifecycle status: **PROVISIONAL PLANNING ONLY**. Surface 25 remains `UNSEEDED / 0` in the Master Options Bank. This document does not promote `UX_CONTRACT_COMPLETE` or authorize destructive execution.

## Product rule

Reset is never a casual one-click action. Every future execution requires explicit scope, exact impact preview, recovery-point policy, server-side authorization, re-auth/confirmation proportional to impact, locking and post-reset verification.

## Information architecture

1. Reset Profiles
2. Create/Edit Profile
3. Impact Preview
4. Reset History
5. Recovery
6. Diagnostics

## Progressive disclosure

### Essential

- profile name/status;
- preset (WPE config, module data/config, content cleanup, settings cleanup, dev/staging, near-factory, custom);
- explicit scope summary;
- preservation summary;
- restore-point requirement;
- Validate / Preview.

Presets only populate explicit options; they never hide what will be deleted.

### Advanced

- content/taxonomy/comment/settings filters;
- module config vs runtime-data separation;
- media behavior;
- activation-state actions;
- preservation/exclusion rules;
- restore-point age/tier/destination;
- impact drift tolerance;
- post-reset safe actions;
- dependency/usage summary.

### Expert

- owner-registered custom table/runtime deletion contracts;
- advanced user reset only when separately accepted;
- high-impact schema/runtime options;
- multisite certified modes;
- unsafe restore-point override only if later authorized by dedicated ability/re-auth.

No raw SQL, arbitrary wildcards, filesystem package deletion, PHP/shell hooks or generic table wipes.

### System / Diagnostics

Read-only diagnostics show:

- exact current counts/scope fingerprint;
- verified restore-point state;
- Backup/Job/Audit health;
- destructive-operation locks;
- protected admin/Super Admin checks;
- owner-module availability;
- current recovery state;
- boot/health checks;
- unsupported multisite/scope blockers.

## Impact Preview

Preview must enumerate actual current impact by category: posts/revisions/comments, terms/relationships, media/files, users if supported, options, definitions/runtime rows/tables, jobs/workflows, Membership effects, activation changes, preserved items and restore point state.

A preview has a fingerprint/version. Before execution, safety-critical data is re-evaluated. Material drift forces a new preview/confirmation or blocks execution.

## Preservation UX

Mandatory safety preservation is visually distinct and cannot be casually deselected:

- current operator;
- at least one recovery administrator;
- Super Admin/network protections;
- site/home identity where required;
- Vault/account connection/audit/backup catalog according policy;
- salts/secrets files;
- selected protected resources.

Explicit preservation wins over broad presets.

## Restore-point UX

Default is **Required**. Show minimum verification tier, maximum age, destination, protection state and health.

If required Backup health is unavailable, Run is blocked. Unsafe override, if ever supported, requires a separate capability, recent re-auth, reason and typed phrase; it is not an ordinary checkbox.

## Confirmation levels

- Level 1: harmless cache/UI-state operations.
- Level 2: content/config deletion with verified recovery point and explicit summary/acknowledgement.
- Level 3: high-impact/full-site/user/table scope with recent re-auth, typed site/profile phrase, restore-point proof, exact impact and audit reason.

Countdowns may support UX but never act as security controls.

## Execution/recovery presentation

Future phase view distinguishes queued, preflight, restore-point validation, lock acquisition, maintenance/recovery state, scoped cleanup phases, post-reset verification, completed-with-warnings, failed-recoverable and failed-recovery-required.

A failure never claims rollback completed unless recovery verification proves it.

## Concurrency

UI blocks conflicting Reset, Restore, destructive Import, schema migration and owner cleanup operations. Stale lock recovery is an explicit diagnostic/recovery workflow.

## Recovery screen

When unresolved failure exists, show safe diagnostic ID, restore point health, current boot health, recommended actions and support bundle. Recovery mutation remains separately permissioned.

## Accessibility

- keyboard-operable profile/preview/history/recovery screens;
- focus after preview/validation/confirmation failures;
- no color-only impact/severity/recovery state;
- clear typed-confirmation instructions;
- accessible tables/count summaries;
- non-noisy phase live regions;
- axe coverage for safe preview, blocked run, high-impact confirmation and recovery-required states once implemented.

## Multisite

Network-wide/full multisite reset requires separate certification. Subsite reset cannot touch network users/options/packages blindly. Unsupported scope is blocked, not attempted best-effort.

## Portability

Reset Profile export/import contains configuration and owner references only, not runtime deletion state or recovery artifacts. Generic package orchestration remains Surface 26.

## Lifecycle decision

This is provisional interaction guidance only. Exact-main Master Options Bank remains `UNSEEDED / 0`; Bank review and schema-valid Atomic Option Contracts must precede UX lifecycle/runtime promotion.
