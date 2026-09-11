# Import/Export — Bank Entry Readiness V1

Surface: **26 / Import-Export**  
Planning issue: **#592**  
Supervisor wave: **#583**  
Exact-main claim anchor: `4350862f3f3397abdf64cc897abdb615faac4af5`

Status: **BANK ENTRY READY FOR RESEARCH/SEEDING — NOT BANK_REVIEWED — NO OPTION CONTRACT PROMOTION**

This document is planning evidence only. It does not seed/review the Master Options Bank, create schema-valid Atomic Option Contracts, import/export data, mutate records/files, execute package orchestration or authorize runtime implementation.

## Current machine truth

- Surface 26 `import-export` is `UNSEEDED` with **0 normalized Master Options Bank records**.
- The atomic planning ledger marks Surface 26 `ATOMIC_INVENTORY_COMPLETE`; this is planning inventory only.
- The canonical ownership map assigns one-time package/data movement to Surface 26. Owner modules retain their own Definition/data semantics; recurring synchronization belongs Surface 41; typed transformations belong Surface 45 where delegated.
- Exact-main `frameworks/Modules` has no dedicated Import/Export runtime module.

The next valid gate is Bank seeding + native/market review, not mutation execution.

## Product boundary

Two separate systems must remain explicit:

1. **WPE Configuration Packages** — versioned definitions/settings with stable UUIDs, dependency graphs and compatibility/conflict negotiation.
2. **Runtime Data Import/Export** — posts/users/terms/media/custom tables/relations/owner-supported runtime records.

They may share runs/logs/jobs UX but must not share destructive semantics blindly.

## Candidate Bank families

1. Configuration package export scope, dependencies, manifest, checksums, schema/version metadata and secret exclusion.
2. Configuration package import verification, compatibility, semantic diff, conflict mapping and per-definition resolution.
3. Data Import identity/lifecycle/source/target/mode.
4. Source adapters: CSV/JSON/XML/XLSX/Connection/API/WPE adapters.
5. File/archive safety: MIME/size/private storage/traversal/bomb/cleanup.
6. Parser settings for CSV/JSON/XML/spreadsheets.
7. Source preview/profiling and type/null/duplicate diagnostics.
8. Target Data Source selection through canonical registry.
9. Create/update/upsert/synchronize modes with explicit risk levels.
10. Stable match/identity rules and duplicate-source handling.
11. Field mappings, null/empty semantics and create/update applicability.
12. Typed transform pipeline; no inline PHP/eval.
13. Taxonomy/relation/media/user/post-specific mapping through owner engines.
14. Synchronize missing-source policy with source ownership marker.
15. Update conflict policy using source fingerprints/target revisions where available.
16. Dry run, validation thresholds and destructive-change counts.
17. Batch/chunk/checkpoint/resume compatibility.
18. Run/row result states, bounded diagnostics and PII retention.
19. Rollback coverage/limitations and Backup restore-point integration.
20. Scheduled imports/exports through Job/Cron only.
21. Export source/fields/formats and sensitive-field restrictions.
22. CSV formula-injection protection.
23. Export destinations through approved storage/Connection owners.
24. Permissions/Abilities, multisite, portability and audit.

## Native WordPress audit required before Bank review

Audit current WordPress data APIs, import/export primitives and ownership boundaries:

- posts/users/terms/comments/media CRUD and revisions;
- capability/Policy checks and object-level ownership;
- WordPress IDs vs portable stable identities;
- taxonomy/relationship/meta semantics;
- media sideload/fetch constraints;
- WXR/native import/export where applicable;
- multisite user/site/network boundaries;
- CSV/XML parser/security constraints provided by PHP/WordPress ecosystem;
- privacy/export/erasure implications;
- cron/job limits for scheduled/batched imports.

Native APIs do not justify direct writes to another module's private tables.

## Market audit required before Bank review

Compare current specialist import/export/migration products for:

- source formats/adapters;
- mapping/transforms;
- identity/upsert/sync;
- relation/media/taxonomy/user handling;
- dry-run and conflict preview;
- large-data chunk/resume;
- scheduled imports;
- rollback/recovery;
- export security;
- package dependency/version conflict handling.

Reject patterns that allow arbitrary PHP, raw SQL execution, archive traversal/XXE, unbounded remote fetching, fuzzy destructive matching, unrestricted role elevation or silent delete-sync.

## Canonical ownership decisions

| Concern | Canonical owner |
|---|---|
| One-time package/data movement, mapping/run semantics | **Surface 26 Import/Export** |
| Owner Definition/data validation/mutation | each canonical owner surface |
| Recurring synchronization/cursor/conflict | **Surface 41 Sync** |
| Typed reusable transformation engine | **Surface 45 Transform** where delegated |
| Remote HTTP/provider credentials | **Surface 23 Connections/Webhooks** |
| Backup restore point | **Surface 24 Backup** |
| Scheduling | **Surface 18 / Job Service** |

## Rejected-unsafe / bounded candidates

Reject or tightly bound:

- arbitrary PHP/eval/raw SQL import scripts;
- direct writes into another module's private tables;
- XXE/external DTD expansion and archive traversal/bombs;
- arbitrary remote URL fetch outside Safe HTTP policy;
- plaintext password import or generic hash migration;
- admin-equivalent role assignment without dedicated high-risk controls;
- destructive sync without source ownership marker;
- fuzzy/title/email-only destructive matching;
- unbounded batch/concurrency;
- claiming generic rollback where later external/user changes make it unsafe;
- exporting passwords, hashes, application passwords, tokens or secrets;
- spreadsheet formula injection in CSV exports.

## Readiness decision

Surface 26 has enough semantic material to begin disciplined Master Options Bank seeding/native/market review, but exact-main `UNSEEDED / 0` blocks option-contract, UX and runtime promotion.

Next gate: normalize Bank records → native audit → market audit → resolve ownership/unsafe/deferred items → Bank review with zero unresolved items → schema-valid Atomic Option Contracts → UX re-review → separately authorized runtime slices.
