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
| `P0-M00-WP09` | P-002 UI + P-008 build evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | UI/React/assets/build | ADR-0125 UI-01…UI-104 + ADR-0126 BT-01…BT-112; 0 executed. |
| `P0-M00-WP10` | P-007 CI / Quality Matrix evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | CI + FAST/FULL + provenance | ADR-0127; CI-01…CI-120; 0 executed. |
| `P0-M00-WP11` | P-006 Free↔Pro compatibility / boot evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Free/Pro + Platform API + schema + entitlement | ADR-0128; FP-01…FP-144; 0 executed. |
| `P0-M00-WP12` | P-012 Membership evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Membership/Policy/protected assets/providers | ADR-0129; MBR-01…MBR-160; 0 executed. |
| `P0-M00-WP13` | P-013 Backup/Restore evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Backup/crypto/providers/Vault/Restore | ADR-0130; BK-01…BK-180; 0 executed. |
| `P0-M00-WP14` | P-009 Query evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Query/Policy/Data Sources/cache/Multisite | ADR-0131; QRY-01…QRY-168; 0 executed. |
| `P0-M00-WP15` | P-004 Definition Repository audit/refinement | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Definition/revisions/dependencies/migrations | ADR-0132; DEF-01…DEF-144; 0 executed. |
| `P0-M00-WP16` | P-010 Relations audit/refinement | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Relations/Query/Policy/Fields/Multisite | ADR-0133; REL-01…REL-160; 0 executed. |
| `P0-M00-WP17` | Field Storage / Custom Fields evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Field Schema/FS1–FS6/Query/Relations/Vault | ADR-0134; FST-01…FST-176; 0 executed. |
| `P0-M00-WP18` | Custom Tables physical/DDL/migration evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | CT1–CT3/CM1–CM4/Query/Fields/Relations/Backup/Multisite | ADR-0135; CTB-01…CTB-184; 0 executed. |
| `P0-M00-WP19` | Admin Columns operational evidence refinement | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | WP list tables/Query/Fields/Relations/Policy/export | ADR-0136; AC-01…AC-176; 0 executed. |
| `P0-M00-WP20` | Dynamic Listings SSR/cache/pagination evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Query/Policy/Fields/Relations/Component Blueprint/cache/builders | ADR-0137; DL-01…DL-176; 0 executed. |
| `P0-M00-WP21` | Free CPT + Taxonomy runtime registration/rewrite evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | WP registration/rewrite/REST/Definition/Policy/Multisite | ADR-0138; CPTX-01…CPTX-176; 0 executed; all CPTX certifications 0. |
| `P0-M00-WP22` | Emails Builder renderer/composition evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Email IR/templates/Vault/Policy/Notification/providers/Multisite | ADR-0139; EBR-01…EBR-176; 0 executed; renderer certifications 0; ET transport truth remains separate. |
| `P0-M00-WP23` | Platform Account / Docs / Support / Diagnostics consolidated evidence reassessment | `SPECIFICATION` | `SHARED_CONTRACT` | `SERIALIZE` | Product Account/OAuth/License/TUF/Support/Docs/Diagnostics/Remote Service/Vault/Privacy | Current planning work; verify existing per-domain evidence before creating or refining one consolidated fixed platform-surface protocol. |

No production implementation work package is active.

## 3. Current planning queue

| Order | Planning item | Current state | Dependency / note |
|---:|---|---|---|
| 1 | Platform Account / Docs / Support / Diagnostics evidence reassessment | `SPECIFICATION` current | Reconcile existing OA/TU/FP/privacy/service architecture and platform exhaustive spec; avoid duplicating already-fixed sub-protocols |
| 2 | Remaining unresolved shared/surface blockers | `QUEUED` | Reassess by critical-path value after WP23 |

Planning documentation work does not create implementation authorization.

## 4. Critical-path/WIP rules

Current implementation WIP remains 0. Planning serializes shared contracts. Material expansion follows:

`STOP → REASSESS → UPDATE IMPACT → RESCOPE OR SPLIT`

No active shared-surface implementation reservation exists.

## 5. Foundation/consumer truth preserved

- Definition: DEF **0/144**; Query: QRY **0/168**; Relations: REL **0/160**.
- Field Storage: FST **0/176**; Custom Tables: CTB **0/184**.
- Admin Columns: AC **0/176**; displayed cells do not imply real sort/filter/edit/export capability.
- Dynamic Listings: DL **0/176**; rendered HTML does not imply authorized count/cursor/cache/client-transition correctness.
- Free CPT/Taxonomy: CPTX **0/176**; stored Definition does not equal effective WordPress registration or rewrite/REST/editor state.
- Emails Builder: EBR **0/176**; rendered message generation does not imply transport submission, receiving-server delivery, inbox placement, human read or provider certification.
- Email transport remains **6 EE3 / 0 ET-certified**; EBR does not supersede ET0–ET5.
- published CPT/taxonomy keys are migration-class identities.
- rewrite flush is controlled/dirty-generation based, never every request.
- Definition disable/delete preserves posts/terms/relationships/meta by default.
- external discovery/collision does not establish WPE ownership.
- network templates do not make site content shared.
- destructive schema/data work requires truthful verified recovery boundaries.

## 6. Current next safe action

Continue `P0-M00-WP23`: audit Platform Account/Docs/Support/Diagnostics exhaustive surface against Product License, OAuth Account Link, TUF updater, Remote Service, privacy/retention, support-ticket authority, Docs/Changelog/System Status contracts, Multisite scope and existing OA/TU/FP evidence. Prefer existing canonical sub-protocols; create a consolidated platform-surface protocol only for uncovered cross-surface behavior.

Production implementation remains blocked until explicit scoped owner consent is granted and recorded.