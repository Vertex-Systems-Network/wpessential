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
| `P0-M00-WP10` | P-007 CI / Quality Matrix evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | CI lanes + FAST/FULL + artifact provenance | ADR-0127; CI-01…CI-120; 0 executed; workflows not verified; `main` + planning branch unprotected by direct reads; repo rulesets UNKNOWN (403). |
| `P0-M00-WP11` | P-006 Free↔Pro compatibility / boot evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Free core + Pro add-on + Platform API + entitlement + migrations/update ordering | ADR-0128; FP-01…FP-144; 0 executed. |
| `P0-M00-WP12` | P-012 Membership runtime/access/protected-files/provider evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Membership + Policy + Entitlement + protected assets + billing facts + Jobs/Notifications + Multisite | ADR-0129; MBR-01…MBR-160; 0 executed; 4 BE3 / 0 MB-certified; 0 PC1+. |
| `P0-M00-WP13` | P-013 Backup/Restore artifact/provider/recovery evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Backup manifest + crypto + Remote Copy + providers + Vault + JobService + Site Lifecycle + Restore | ADR-0130; BK-01…BK-180; 0 executed; 34 targets / 0 C-certified / 0 C3; V3 cert 0. |
| `P0-M00-WP14` | P-009 Query compiler/cost/cache/security evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Query AST/compiler + Policy + Data Sources + Relations + Custom Tables + cache + Multisite | ADR-0131; QRY-01…QRY-168; 0 executed; QP1–QP4 certifications 0. |
| `P0-M00-WP15` | P-004 Definition Repository evidence completeness / physical proof audit | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Definition Repository + revisions + dependencies + migrations + Multisite + import/export | ADR-0132; canonical ADR-0092 protocol refined in place to DEF-01…DEF-144; 0 executed; final D1–D4/DDL open. |
| `P0-M00-WP16` | P-010 Relations evidence completeness / physical proof audit | `SPECIFICATION` | `SHARED_CONTRACT` | `SERIALIZE` | Relations + Query + Policy + Fields + content lifecycle + Multisite | Current planning work; audit existing canonical/supplementary P-010 evidence before any refinement. |

No production implementation work package is active.

## 3. Current planning queue

| Order | Planning item | Current state | Dependency / note |
|---:|---|---|---|
| 1 | P-010 Relations evidence completeness / physical proof audit | `SPECIFICATION` current | Shared relation graph contract; existing canonical/supplementary protocols already exist; avoid duplication |
| 2 | Remaining unresolved shared/surface blockers | `QUEUED` | Reassess critical-path value after Relations audit |

Planning documentation work does not create implementation authorization.

## 4. Critical-path/WIP rules

Current implementation WIP remains 0. Planning serializes shared contracts. Any future material implementation expansion follows:

`STOP → REASSESS → UPDATE IMPACT → RESCOPE OR SPLIT`

No active shared-surface implementation reservation exists.

## 5. Definition milestone truth preserved

- Definition identity, immutable Revision, Dependency edges and compiled cache are separate truths.
- Draft/current and published revisions can differ without mutating production semantics.
- historical revisions are immutable and not silently rewritten by migrators.
- current/published pointers must resolve to revisions belonging to the same Definition.
- portable identity is UUID/logical reference, never local numeric DB ID.
- explicit site/network scope remains security truth under shared PT-C storage.
- unknown future schema is degraded/read-only, not lossy-downgraded.
- module disable/Pro expiry preserves user configuration.
- import key collision never establishes identity automatically.
- archive/tombstone is not purge; purge has separate destructive/dependency/recovery gates.
- Backup/restore/clone/transfer preserves/remaps scope intentionally.
- cache/event success follows durable DB commit.
- D1/PT-C remains first benchmark baseline only; D2/D3/D4 remain evidence candidates.
- DEF-01…DEF-144 are documentation-only; executed 0/144; physical/runtime certification 0.

## 6. Current next safe action

Continue `P0-M00-WP16`: audit `docs/QUALITY/RELATIONS-P010-EXECUTABLE-EVIDENCE-PROTOCOL.md`, `docs/QUALITY/P010-RELATIONS-PHYSICAL-BENCHMARK-PROTOCOL.md`, ADR-0074/0093, relation physical/runtime architecture, exhaustive Relations spec, Query integration, content deletion/lifecycle, import/restore and Multisite scope behavior.

Do **not** create a second P-010 protocol unless the audit proves no canonical protocol exists. If material gaps exist, refine the existing canonical protocol in place and preserve existing fixture/workload traceability.

Production implementation remains blocked until explicit scoped owner consent is granted and recorded.