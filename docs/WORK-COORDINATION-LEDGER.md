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
| `P0-M00-WP10` | P-007 CI / Quality Matrix evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | CI lanes + FAST/FULL + artifact provenance | ADR-0127; CI-01…CI-120; 0 executed; workflows not verified; protections UNKNOWN. |
| `P0-M00-WP11` | P-006 Free↔Pro compatibility / boot evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Free core + Pro add-on + Platform API + entitlement + migrations/update ordering | ADR-0128; FP-01…FP-144; 0 executed; ADR-0010 remains Proposed. |
| `P0-M00-WP12` | P-012 Membership runtime/access/protected-files/provider evidence refinement | `SPECIFICATION` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Membership + Policy + Entitlement + protected assets + billing source facts + Jobs/Notifications + Multisite | Current planning work; reconcile M1/M2, Enrollment/Entitlement, protected-file and provider evidence without runtime execution. |

No production implementation work package is active.

## 3. Current planning queue

| Order | Planning item | Current state | Dependency / note |
|---:|---|---|---|
| 1 | P-012 Membership runtime/access/protected-files/provider evidence refinement | `SPECIFICATION` current | Security-critical shared access foundation; billing/provider facts must never directly become access truth |
| 2 | P-013 Backup executable evidence refinement | `BLOCKED` by sequence | Recovery/destructive-operation foundation; start after Membership planning closes unless new evidence changes priority |
| 3 | Remaining unresolved shared/surface blockers | `BLOCKED` by sequence | Reassess after P-013 planning |

Planning documentation work does not create implementation authorization.

## 4. Critical-path/WIP rules

Current implementation WIP remains 0. Planning serializes shared contracts. Any future material implementation expansion follows:

`STOP → REASSESS → UPDATE IMPACT → RESCOPE OR SPLIT`

No active shared-surface implementation reservation exists.

## 5. Shared truth boundaries currently reserved for planning

WP12 must preserve:
- Role ≠ Membership ≠ billing Subscription/Purchase ≠ Product Entitlement;
- provider billing events are source facts, not direct access truth;
- Enrollment is authoritative membership lifecycle state;
- effective Entitlement is derived/policy-controlled and revocation-safe;
- protected file delivery must reauthorize at request time and prevent origin bypass;
- cache invalidation/access generation must close stale-access windows;
- site/network/user/team scope must be explicit;
- Free/Pro Product License state cannot become Membership authorization.

## 6. Current next safe action

Continue `P0-M00-WP12`: reconcile ADR-0013/0015/0016/0019/0020, Membership M1/M2 runtime baselines, protected-file PD/PC profiles, billing-provider evidence and generic P-012 spike into a fixed adversarial executable-evidence protocol if no dedicated equivalent exists.

Production implementation remains blocked until explicit scoped owner consent is granted and recorded.