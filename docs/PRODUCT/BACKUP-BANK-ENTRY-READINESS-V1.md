# Backup — Bank Entry Readiness V1

Surface: **24 / Backup**  
Planning issue: **#590**  
Supervisor wave: **#583**  
Exact-main claim anchor: `dab9944bf7ad174ceffb3b48ddebffac4ea71bcc`

Status: **BANK ENTRY READY FOR RESEARCH/SEEDING — NOT BANK_REVIEWED — NO OPTION CONTRACT PROMOTION**

This document is planning evidence only. It does not seed/review the Master Options Bank, create schema-valid Atomic Option Contracts, create archives, upload backup data, restore data, mutate files/database state or authorize runtime implementation.

## Current machine truth

- Surface 24 `backup` is `UNSEEDED` with **0 normalized Master Options Bank records**.
- The atomic planning ledger marks Surface 24 `ATOMIC_INVENTORY_COMPLETE`; this is pre-contract planning only.
- The canonical ownership map assigns backup artifact/restore truth to Surface 24. Provider transport/credentials remain Surface 23; scheduling/Job execution and Notifications remain delegated owners.
- Exact-main `frameworks/Modules` has no dedicated Backup runtime module.

The next valid product gate is Bank seeding + native/market review, not capture/restore implementation.

## Existing in-repo evidence

Primary sources:

- `docs/MODULES/BACKUP-MANAGER-EXHAUSTIVE-SPEC.md`;
- `docs/MODULES/BACKUP-RESTORE-SEMANTICS.md`;
- `docs/SECURITY/BACKUP-ENCRYPTION-KEY-RECOVERY.md`;
- `docs/MODULES/OPTION-INVENTORY.md`;
- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`;
- `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`.

The exhaustive spec already establishes the central product rule: a successful archive process is **not** a successful backup by itself. Capture, local verification, remote persistence, remote verification and restore-tested confidence remain distinct.

## Candidate Bank families

1. Backup Set identity/status/trigger/scope/source environment/verification tier.
2. Backup Plan identity/lifecycle/schedule/overlap/missed-run behavior.
3. Scope presets and explicit DB/files selections/exclusions.
4. DB capture consistency/chunking/table/object support and unsupported-state warnings.
5. Files capture roots/excludes/symlink/unreadable/change-during-read policy.
6. Archive format/compression/splitting/workspace/mandatory manifest/checksums.
7. Encryption/recovery profile and recovery-material readiness.
8. Destination refs, required/optional mirrors, provider capability/health and remote verification.
9. Verification tiers and target minimums (local/remote/restore-tested distinctions).
10. Retention/protection/GFS/minimum verified copies/prune ordering.
11. Notifications for success/partial/failure/verification/provider-health.
12. Preflight: capacity, disk, permissions, environment, runner, destructive locks.
13. Restore source/preflight/scope/existing-target behavior/environment mapping/plugin-theme strategy.
14. Pre-restore point requirement and unsafe override policy.
15. Delete/copy/retention semantics and last-verified/protected safeguards.
16. Destination adapter settings and credential references only.
17. Backup/restore activity evidence, audit, health and recovery actions.
18. Permissions/Abilities with high-risk restore/credential/recovery-key separation.
19. Multisite/network/site restore semantics and compatibility.
20. Portability/imported-manifest provenance.
21. Performance/concurrency/job/resource ceilings and cancellation.

## Native WordPress audit required before Bank review

Audit current WordPress/environment primitives explicitly:

- DB access/table prefix/site/network metadata;
- filesystem roots, uploads/plugins/themes/mu-plugins/languages/core boundaries;
- wp-content permissions and hosting restrictions;
- ZIP/TAR/PHP extension availability;
- WP-Cron/Job scheduling limitations;
- multisite/network table/site relationships;
- plugin/theme activation/version/schema interactions;
- site URL/home/upload path mapping;
- serialization-safe content transformations;
- WordPress privacy/security implications of backup artifacts.

Native primitives do not imply a safe backup/restore engine. Recovery correctness must be specified independently.

## Market audit required before Bank review

Compare current specialist backup products/providers for:

- full/database/files/config scopes;
- remote destinations and provider certification;
- incremental chains;
- encryption/recovery;
- verification vs mere upload success;
- restore workflows/preflight/migration mapping;
- retention/GFS/protection;
- multisite handling;
- scheduling/concurrency;
- large-site chunking/resume;
- logs/diagnostics;
- staging/restore testing.

Do not copy weak behavior such as generic green “success” for unverified/partial sets, weak ZIP-password encryption, disabled TLS, deleting last verified recovery points, or restore without preflight/recovery route.

## Canonical ownership decisions

| Concern | Canonical owner |
|---|---|
| Backup Sets/Plans, capture scope, manifests, verification tier, restore semantics, retention | **Surface 24 Backup** |
| Provider connection/credential/OAuth/Safe HTTP | **Surface 23 Connections/Webhooks** |
| Durable schedule/job execution | **Surface 18 Cron / shared Job Service** |
| Notifications | **Surface 19 Notifications** |
| Reset restore-point dependency | **Surface 25 Reset consumes Backup** |
| Staging restore-test environment | **Surface 55 Staging** |
| Generic package/data import/export | **Surface 26 Import/Export** |
| Secrets/recovery material | **Vault / approved encryption-recovery owner** |

## Rejected-unsafe / bounded candidates

Reject or tightly bound:

- treating archive-process exit code as verified backup success;
- disabling manifest/checksums;
- weak ZIP password mode presented as secure encryption;
- following symlinks outside approved site roots in normal mode;
- restoring without integrity/decryption/environment preflight;
- destructive mirror/delete behavior without impact preview;
- deleting the only verified/protected recovery point against policy;
- revealing destination credentials/recovery keys;
- restoring arbitrary serialized/foreign data through generic merge semantics;
- “ignore TLS” provider options;
- direct long-running backup/restore work in a normal request;
- claiming remote upload complete without remote verification where required.

## Readiness decision

Surface 24 has enough semantic material to begin disciplined Master Options Bank seeding/native/market review, but exact-main `UNSEEDED / 0` blocks option-contract, UX and runtime promotion.

Next gate: normalized Bank seed → native audit → market/provider audit → ownership/unsafe/deferred resolution → Bank review with zero unresolved items → schema-valid Atomic Option Contracts → UX re-review → separately authorized runtime slices.
