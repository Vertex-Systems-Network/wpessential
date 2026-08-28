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
| `P0-M00-WP10` | P-007 CI / Quality Matrix evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | CI + FAST/FULL + provenance | ADR-0127; CI-01…CI-120; 0 executed; workflows unverified; direct branch reads show main/planning unprotected; rulesets UNKNOWN (403). |
| `P0-M00-WP11` | P-006 Free↔Pro compatibility / boot evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Free/Pro + Platform API + schema + entitlement | ADR-0128; FP-01…FP-144; 0 executed. |
| `P0-M00-WP12` | P-012 Membership evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Membership/Policy/protected assets/providers | ADR-0129; MBR-01…MBR-160; 0 executed; 4 BE3 / 0 MB-certified; 0 PC1+. |
| `P0-M00-WP13` | P-013 Backup/Restore evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Backup/crypto/providers/Vault/Restore | ADR-0130; BK-01…BK-180; 0 executed; 34 targets / 0 C-certified / 0 C3; V3 cert 0. |
| `P0-M00-WP14` | P-009 Query evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Query/Policy/Data Sources/cache/Multisite | ADR-0131; QRY-01…QRY-168; 0 executed; QP1–QP4 certifications 0. |
| `P0-M00-WP15` | P-004 Definition Repository audit/refinement | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Definition/revisions/dependencies/migrations | ADR-0132; canonical ADR-0092 protocol refined to DEF-01…DEF-144; 0 executed. |
| `P0-M00-WP16` | P-010 Relations audit/refinement | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Relations/Query/Policy/Fields/Multisite | ADR-0133; canonical ADR-0093 protocol refined to REL-01…REL-160; 0 executed; R1/PT-D first baseline only. |
| `P0-M00-WP17` | Field Storage / Custom Fields evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Field Schema/FS1–FS6/Query/Relations/Vault | ADR-0134; FST-01…FST-176; 0 executed; all adapter certifications 0. |
| `P0-M00-WP18` | Custom Tables physical/DDL/migration evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | CT1–CT3/CM1–CM4/Query/Fields/Relations/Backup/Multisite | ADR-0135; CTB-01…CTB-184; 0 executed; all CT/CM certifications 0. |
| `P0-M00-WP19` | Admin Columns operational evidence refinement | `SPECIFICATION` | `SHARED_CONTRACT` | `SERIALIZE` | WP list tables/Query/Fields/Relations/Policy/export | Current planning work; ADR-0098 operational profile exists but no dedicated fixed executable protocol found. |

No production implementation work package is active.

## 3. Current planning queue

| Order | Planning item | Current state | Dependency / note |
|---:|---|---|---|
| 1 | Admin Columns operational evidence refinement | `SPECIFICATION` current | Query/Fields/Relations foundations now have fixed protocols; freeze list-table hooks, batching, sort/filter/edit/export/security evidence |
| 2 | Dynamic Listings SSR/cache/pagination evidence | `QUEUED` | Reuses QRY/FST/REL and Component Blueprint |
| 3 | Remaining unresolved shared/surface blockers | `QUEUED` | Reassess by critical-path value after WP19 |

Planning documentation work does not create implementation authorization.

## 4. Critical-path/WIP rules

Current implementation WIP remains 0. Planning serializes shared contracts. Material expansion follows:

`STOP → REASSESS → UPDATE IMPACT → RESCOPE OR SPLIT`

No active shared-surface implementation reservation exists.

## 5. Data-foundation truth preserved

- Definition identity/revision/dependency/cache are separate truths; DEF **0/144**.
- Query uses typed AST/provider capabilities; QRY **0/168**; QP1–QP4 certs 0.
- Relations runtime is separate from Relation Definition; REL **0/160**; R1 first baseline, R2 mandatory.
- Field Definition/editor/storage/presentation/runtime value are separate; FST **0/176**; FS1–FS6 certifications 0.
- Table Definition/observed schema/Migration Plan/Migration Run/applied fingerprint/runtime rows are separate; CTB **0/184**.
- CT1/PT-E is first site-owned Custom Tables baseline; CT2/PT-D mandatory comparison; CT3 only genuinely network-owned.
- `dbDelta()` is not WPE's source-of-truth migration language.
- Definition publish never implies physical/value migration completion.
- destructive schema/data work requires truthful verified recovery boundaries.

## 6. Current next safe action

Continue `P0-M00-WP19`: audit `docs/ARCHITECTURE/ADMIN-COLUMNS-OPERATIONAL-PROFILE.md`, ADR-0098, Data & Query exhaustive specifications and Query/Field/Relations/Policy integration. If no dedicated equivalent exists, create one bounded Admin Columns executable evidence protocol; otherwise refine canonical evidence in place.

Production implementation remains blocked until explicit scoped owner consent is granted and recorded.