# WPEssential — Work Coordination Ledger

Status: **Active governance ledger**  
Last reviewed: 2026-08-28

## 1. Current execution state

Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Production implementation WIP: **0**  
Active implementation approvals: **0**  
Authorized module/platform surfaces: **0/31**

## 2. Current/recent work packages

| Work ID | Scope | Lifecycle | Critical-path class | Parallelism | Shared surfaces | Notes |
|---|---|---|---|---|---|---|
| `P0-M00-WP01` | Universal Master Prompt governance hardening | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | governance/source-of-truth | Documentation-only. |
| `P0-M00-WP02` | Forms executable evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Forms/Workflow | ADR-0117; FM-01…FM-92; 0 executed. |
| `P0-M00-WP03` | Workflow + Job/Cron evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Workflow/JobService | ADR-0118/0119; WF 116 + JS 106; 0 executed. |
| `P0-M00-WP04` | Notification evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Notification/Jobs/providers | ADR-0120; NT-01…NT-142; 0 executed. |
| `P0-M00-WP05` | Message & Chat evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Chat/search/assets | ADR-0121; CH-01…CH-142; 0 executed. |
| `P0-M00-WP06` | Webhooks/Connections/Event Inbox evidence | `DONE` | `INTEGRATION` | `SERIALIZE` | Safe HTTP/Connections/Event Inbox | ADR-0122; WC-01…WC-156; 0 executed. |
| `P0-M00-WP07` | P-001 compatibility floor evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | WP/PHP/DB/Multisite | ADR-0123; CF-01…CF-112; 0 executed. |
| `P0-M00-WP08` | P-005 Vault evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Vault/security/recovery | ADR-0124; VT-01…VT-128; 0 executed. |
| `P0-M00-WP09` | P-002 UI + P-008 build evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | UI wrappers/React/assets/build | ADR-0125 UI-01…UI-104 + ADR-0126 BT-01…BT-112; 0 executed. |
| `P0-M00-WP10` | P-007 CI / Quality Matrix evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | CI lanes + FAST/FULL + artifact provenance | ADR-0127; CI-01…CI-120; 0 executed; workflows not verified; `main` + planning branch unprotected by direct branch reads; repo rulesets UNKNOWN (403). |
| `P0-M00-WP11` | P-006 Free↔Pro compatibility / boot evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Free core + Pro add-on + Platform API + entitlement + migrations/update ordering | ADR-0128; FP-01…FP-144; 0 executed; ADR-0010 remains Proposed. |
| `P0-M00-WP12` | P-012 Membership runtime/access/protected-files/provider evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Membership + Policy + Entitlement + protected assets + billing facts + Jobs/Notifications + Multisite | ADR-0129; MBR-01…MBR-160; 0 executed; 4 BE3 / 0 MB-certified; 0 PC1+. |
| `P0-M00-WP13` | P-013 Backup/Restore artifact/provider/recovery evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Backup manifest + crypto + Remote Copy + providers + Vault + JobService + Site Lifecycle + Restore | ADR-0130; BK-01…BK-180; 0 executed; 34 targets / 0 C-certified / 0 C3; V3 cert 0. |
| `P0-M00-WP14` | P-009 Query compiler/cost/cache/security evidence | `SPECIFICATION` | `SHARED_CONTRACT` | `SERIALIZE` | Query AST/compiler + Policy + Data Sources + Relations + Custom Tables + cache + Multisite | Current planning work; no dedicated fixed P-009 protocol found; no runtime execution. |

No production implementation work package is active.

## 3. Current planning queue

| Order | Planning item | Current state | Dependency / note |
|---:|---|---|---|
| 1 | P-009 Query compiler/cost/cache/security evidence refinement | `SPECIFICATION` current | Shared data-plane contract for Query Builder, Listings, Admin Columns, REST, Forms/Dashboard consumers |
| 2 | P-004 Definition exact physical evidence refinement | `QUEUED` | Shared control-plane definition storage/locking/migration foundation |
| 3 | P-010 Relations evidence reconciliation | `QUEUED` | Existing fixed protocol/benchmark docs exist; reassess whether canonical sync/refinement is sufficient after Query |
| 4 | Remaining unresolved shared/surface blockers | `BLOCKED` by sequence | Reassess by dependency/critical-path value after WP14 |

Planning documentation work does not create implementation authorization.

## 4. Critical-path/WIP rules

Current implementation WIP remains 0. Planning serializes shared contracts. Any future material implementation expansion follows:

`STOP → REASSESS → UPDATE IMPACT → RESCOPE OR SPLIT`

No active shared-surface implementation reservation exists.

## 5. Backup milestone truth preserved

- Backup Set, Artifact and Destination Copy are distinct truth domains.
- generated/uploaded ≠ restore-ready.
- V2 Remote Verified ≠ V3 Restore Tested.
- provider/static SE evidence never grants C certification.
- provider success/checksum cannot replace WPE manifest/integrity/restore verification.
- key recovery must survive loss of original site/database; the only recovery key cannot be colocated solely with ciphertext.
- parser/archive/path/symlink/decompression inputs remain hostile until validated and bounded.
- unknown provider commit/delete outcomes require reconciliation, not fake success/failure.
- restore/clone reauthorizes Vault/provider/commercial state and cannot resurrect stale Membership access.
- destructive operations that require a restore point must verify the configured Backup tier before commit.
- BK-01…BK-180 are documentation-only; executed 0/180; provider C-certified 0; V3 certifications 0.

## 6. Current next safe action

Continue `P0-M00-WP14`: reconcile ADR-0086, Query AST/runtime architecture, Data Source/Policy/Relations/Custom Tables contracts and generic P-009 into one fixed bounded executable-evidence protocol if no dedicated equivalent exists.

The protocol must cover at minimum compiler validation, adapter capability negotiation, server-side authorization, cost/budget rejection, stable order/pagination, count leakage, cache scope/key/invalidation, hostile AST/input complexity, SQL/identifier/parameter safety, relation/custom-table/native-WP semantics, remote-source boundaries, Multisite isolation, concurrency/revision behavior, scale and negative requirements.

Production implementation remains blocked until explicit scoped owner consent is granted and recorded.