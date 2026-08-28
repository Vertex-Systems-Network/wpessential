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
| `P0-M00-WP21` | Free CPT + Taxonomy runtime registration/rewrite evidence | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | WP registration/rewrite/REST/Definition/Policy/Multisite | ADR-0138; CPTX-01…CPTX-176; 0 executed. |
| `P0-M00-WP22` | Emails Builder renderer/composition evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Email IR/templates/Vault/Policy/Notification/providers/Multisite | ADR-0139; EBR-01…EBR-176; 0 executed; ET separate. |
| `P0-M00-WP23` | Platform Account / Docs / Support / Diagnostics evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Product Account/OAuth/License/TUF/Support/Docs/Diagnostics/Remote Service/Vault/Privacy | ADR-0140; PLT-01…PLT-176; 0 executed; FP/OA/TU/RS remain separate prerequisites. |
| `P0-M00-WP24` | Multisite Scope/Isolation + Site Lifecycle evidence refinement | `DONE` | `BLOCKING_FOUNDATION` | `SERIALIZE` | Scope/Policy/Jobs/Cache/Vault/Membership/Backup/Product License/lifecycle | ADR-0141; MSI-01…MSI-160 + LC-01…LC-96; 0 executed; MS0–MS4/SL0–SL4 preserved. |
| `P0-M00-WP25` | Audit & Observability evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Audit/AU1/PT-D/retention/integrity/diagnostics/security/event correlation | ADR-0142; AUD-01…AUD-176; 0 executed; AU1/PT-D remains first future baseline only. |
| `P0-M00-WP26` | Kernel / Module Registry / Capability-Policy / Abilities / Event Registry / Extension SDK evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | bootstrap/kernel/module registry/policy/abilities/events/extension registries/SDK/Free↔Pro/Multisite | ADR-0143; KPA-01…KPA-176; 0 executed. |
| `P0-M00-WP27` | Local Privacy / Data Lifecycle evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | privacy/classification/retention/export-erasure/backups/Multisite | ADR-0144; PDL-01…PDL-176; 0 executed; RS remote privacy remains separate. |
| `P0-M00-WP28` | Error Taxonomy & Failure UX evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | error envelopes/retry/conflict/partial failure/UI/REST/Ability/Jobs/accessibility | ADR-0145; ERR-01…ERR-176; 0 executed. |
| `P0-M00-WP29` | Component Blueprint Core Runtime evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | compiler/bindings/Policy/renderer/slots/styles/assets/cache/accessibility/Multisite | ADR-0146; CBP-01…CBP-176; 0 executed; BW/BC adapter certification remains separate. |
| `P0-M00-WP30` | Contract Versioning & Deprecation evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | Product/Platform API/Definition/runtime schema/Ability/Event/adapter/package/SDK versions and migrations | ADR-0147; VER-01…VER-176; 0 executed; domain migration protocols remain separate. |
| `P0-M00-WP31` | Module Lifecycle / Disable / Uninstall / Recovery evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | module states/dependencies/migrations/security enforcement/cleanup/recovery/Multisite | ADR-0148; MLC-01…MLC-176; 0 executed; disable/expiry/uninstall/privacy erase remain separate. |
| `P0-M00-WP32` | Entity / Data Source Registry evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | data-source adapters/schema/capabilities/Policy/query/write/transactions/Multisite | ADR-0149; DSR-01…DSR-176; 0 executed; readable never implies writable. |
| `P0-M00-WP33` | Asset Registry & Scoped Loader evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | asset descriptors/dependency graph/WordPress handles/routes/build manifest/loading/security/Multisite | ADR-0150; ASR-01…ASR-176; 0 executed; UI/BT/CBP/BW certification remains separate. |
| `P0-M00-WP34` | Conditional Logic Engine evidence | `DONE` | `SHARED_CONTRACT` | `SERIALIZE` | typed conditions/operators/value sources/Policy/cache/consumers/Multisite | ADR-0151; CLG-01…CLG-176; 0 executed; condition truth never grants action authorization. |
| `P0-M00-WP35` | Dynamic Value / Token Resolver evidence reassessment | `SPECIFICATION` | `SHARED_CONTRACT` | `SERIALIZE` | Data Source/Fields/Relations/Query/Policy/renderer/escaping/cache/Blueprint/Email/Notification/Forms | Current planning work; architecture defines one shared resolver but repository search found no dedicated fixed resolver protocol. Reconcile overlap before freezing evidence. |

No production implementation work package is active.

## 3. Current planning queue

| Order | Planning item | Current state | Dependency / note |
|---:|---|---|---|
| 1 | Dynamic Value / Token Resolver executable-evidence reassessment | `SPECIFICATION` current | Shared renderer/value resolver is canonical architecture; isolate resolution, escaping, authorization, dependency/cache and cross-consumer semantics without duplicating DSR/FST/QRY/REL/CBP/EBR/NT/FM. |
| 2 | Remaining unresolved shared/surface blockers | `QUEUED` | Reassess by critical-path value after WP35. |

Planning documentation work does not create implementation authorization.

## 4. Critical-path/WIP rules

Current implementation WIP remains 0. Planning serializes shared contracts. Material expansion follows:

`STOP → REASSESS → UPDATE IMPACT → RESCOPE OR SPLIT`

No active shared-surface implementation reservation exists.

## 5. Foundation/consumer truth preserved

- Definition DEF **0/144**; Query QRY **0/168**; Relations REL **0/160**.
- Field Storage FST **0/176**; Custom Tables CTB **0/184**; Admin Columns AC **0/176**; Dynamic Listings DL **0/176**.
- Free CPT/Taxonomy CPTX **0/176**; stored Definition does not equal effective WP registration/rewrite/REST/editor state.
- Emails Builder EBR **0/176**; render success never implies ET provider/delivery success.
- Platform PLT **0/176**; Account connection, entitlement, allocation, update trust, support authority and diagnostics transmission remain separate.
- Multisite MSI **0/160**; runtime-certified surfaces at MS1+ remain 0.
- Site Lifecycle LC **0/96**; SL runtime certification remains 0.
- Audit AUD **0/176**; AU1/PT-D is only a future baseline; local Audit/hash evidence is not tamper-proof truth.
- KPA **0/176**; one shared Free-owned kernel/registry family remains platform authority; every invocation channel remains Capability + Policy bound.
- PDL **0/176**; live erase is distinct from backup/remote deletion.
- ERR **0/176**; machine codes/retry/disclosure/partial-failure semantics remain explicit.
- CBP **0/176**; core Blueprint runtime remains separate from BW **0/50** builder adapter certification.
- VER **0/176**; cross-version compatibility/deprecation is explicit and never inferred from package version alone.
- MLC **0/176**; module disable ≠ data delete ≠ privacy erase ≠ uninstall.
- DSR **0/176**; source readability, queryability and mutability are independent declared capabilities.
- ASR **0/176**; registered/enqueued/fetched/executed/certified asset states remain distinct.
- CLG **0/176**; a true condition never authorizes an otherwise denied action.
- current-blog context is never durable ownership or authorization.
- site deletion does not imply global-user deletion/billing cancellation/shared-secret deletion/privacy erasure.
- clone/restore cannot silently resurrect stale commercial/provider/access authority.
- destructive schema/data work requires truthful verified recovery boundaries.

## 6. Current next safe action

Continue `P0-M00-WP35`: reconcile the architecture's shared Dynamic Value / Token Resolver against DSR/FST/QRY/REL/Policy/CBP/EBR/NT/FM and consumer-specific evidence. Define only genuinely shared behavior: stable typed token/value descriptors; source resolution without side effects; explicit render/escaping contexts (HTML text/attribute/URL/JSON/plain/email); Policy/privacy before disclosure; null/missing/error semantics; resolver dependency graph/cycles/budgets; request batching; cache identity/invalidation; locale/timezone/versioning; secret denial; cross-consumer parity; Multisite target scope and scale.

Production implementation remains blocked until explicit scoped owner consent is granted and recorded.