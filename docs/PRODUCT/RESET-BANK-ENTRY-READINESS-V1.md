# Reset — Bank Entry Readiness V1

Surface: **25 / Reset**  
Planning issue: **#591**  
Supervisor wave: **#583**  
Exact-main claim anchor: `97f9c29ffad14e1124a7565e8dd4e027e9ef7d10`

Status: **BANK ENTRY READY FOR RESEARCH/SEEDING — NOT BANK_REVIEWED — NO OPTION CONTRACT PROMOTION**

This document is planning evidence only. It does not seed/review the Master Options Bank, create schema-valid Atomic Option Contracts, delete data, reset settings, mutate users/tables/files, create restore points or authorize runtime implementation.

## Current machine truth

- Surface 25 `reset` is `UNSEEDED` with **0 normalized Master Options Bank records**.
- The atomic planning ledger marks Surface 25 `ATOMIC_INVENTORY_COMPLETE`; this is planning inventory only.
- Reset owns destructive reset semantics; Backup owns restore-point truth; DB Maintenance remains separate; module owners own their own runtime/config deletion contracts.
- Exact-main `frameworks/Modules` has no dedicated Reset runtime module.

The next valid gate is Bank seeding + native/market review, not destructive execution.

## Existing in-repo evidence

Primary sources:

- `docs/MODULES/RESET-MANAGER-EXHAUSTIVE-SPEC.md`;
- Backup/restore and common Policy/Audit contracts;
- `docs/MODULES/OPTION-INVENTORY.md`;
- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`;
- `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`.

The exhaustive spec establishes the product rule: Reset is a destructive, auditable workflow with explicit scope, impact preview, recovery-point policy, re-auth/confirmation and post-reset verification. There is no normal one-click reset.

## Candidate Bank families

1. Reset Profile identity/lifecycle/presets.
2. WPE module config/runtime scope and owner-specific deletion contracts.
3. Content selection/trash/permanent/meta/comment/relation/media behavior.
4. Taxonomy/term/meta/relationship/fallback behavior.
5. Comment selection/deletion/meta behavior.
6. Registered settings/options groups and safe defaults; no wildcard option deletion.
7. Advanced user reset with mandatory operator/recovery-admin/Super Admin protection.
8. Plugin/theme activation-state actions without package deletion.
9. Custom table/module data reset through registered owners only.
10. Preservation/exclusion rules with mandatory safety precedence.
11. Backup/Restore Point requirement, verification tier, age, protection and unsafe override.
12. Impact preview counts/estimates/dependencies and drift fingerprint.
13. Confirmation levels/re-auth/typed phrase/reason/audit.
14. Execution phases, destructive locks and recovery state.
15. Post-reset safe actions only; no arbitrary code.
16. Reset History/Recovery diagnostics and support bundle.
17. Permissions/Abilities with high-risk user/schema/unsafe separation.
18. Multisite/network/site scope and unsupported-mode blockers.
19. Accessibility, audit, portability and performance/reliability.

## Native WordPress audit required before Bank review

Audit:

- post/comment/term/user trash/delete APIs and hooks;
- options/transients/rewrite/cache behavior;
- multisite network/site user/options boundaries;
- attachment/file ownership and deletion semantics;
- plugin/theme activation APIs without package removal;
- current admin/Super Admin protections;
- DB table ownership/prefix/network relationships;
- privacy/export/erase implications;
- nonce/session/re-auth primitives and their limits.

Native delete APIs do not substitute for Reset impact/recovery/ownership semantics.

## Market audit required before Bank review

Compare reset/staging/dev utility products for scope presets, database/content/options cleanup, restore-point integration, preview/confirmation, multisite, recovery, logs and developer resets. Reject shortcuts that expose raw SQL/table wildcards, remove last admins, reset without recovery point, wipe Vault/account connections by default or promise rollback without verified recovery.

## Canonical ownership decisions

| Concern | Canonical owner |
|---|---|
| Reset profiles, scope, impact, preservation, confirmation, execution/recovery semantics | **Surface 25 Reset** |
| Restore points / verified recovery artifacts | **Surface 24 Backup** |
| Physical DB maintenance/cleanup | **Surface 48 DB Maintenance** |
| Module-specific runtime/config delete semantics | each owning surface/module |
| User/role/capability truth | WordPress + **Surface 30 Roles/Policy** |
| Generic data/package import/export | **Surface 26 Import/Export** |
| Audit evidence | shared Audit owner |

## Rejected-unsafe / bounded candidates

Reject or tightly bound:

- one-click destructive reset;
- arbitrary option-name/table/path wildcards;
- raw SQL `DROP`/truncate outside owner contracts;
- deleting current operator/last recovery admin/Super Admin through normal profiles;
- silently resetting Vault/account connections/audit/backup catalog;
- executing when required verified restore point is missing/unhealthy;
- generic module-table wipe;
- package file deletion/uninstallation as reset behavior;
- arbitrary PHP/shell post-reset actions;
- concurrent Reset/Restore/destructive Import/schema migration;
- claiming rollback succeeded without verified restore.

## Readiness decision

Surface 25 has enough semantic material to begin disciplined Master Options Bank seeding/native/market review, but exact-main `UNSEEDED / 0` blocks option-contract, UX and runtime promotion.

Next gate: normalize Bank records → native audit → market audit → resolve ownership/unsafe/deferred entries → Bank review with zero unresolved items → schema-valid Atomic Option Contracts → UX re-review → separately authorized destructive runtime slices.
