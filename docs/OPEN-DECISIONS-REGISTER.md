# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-29

This register tracks unresolved runtime/physical/provider/evidence decisions. Accepted planning/evidence decisions now extend through **ADR-0181**. Architecture/protocol acceptance never implies runtime certification or owner development authorization.

Current canonical product scope after ADR-0177: **43 surfaces**.  
Authorized: **0/43**.  
All executable work remains blocked by ADR-0014.

## A. Established platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002/0123 | compatibility floor — CF-01…CF-112 |
| D-002 | ADR-0005/0125 | UI/accessibility/RTL/scoped assets/React isolation — UI-01…UI-104 |
| D-003 | ADR-0059/0068/0083/0119 | Job backend/Cron/DST/fairness/claims — JS-01…JS-106 |
| D-004 | ADR-0073/0092/0132 | Definition exact DDL/index/locking/migration — DEF-01…DEF-144 |
| D-005 | ADR-0048/0085/0124 | Vault crypto/envelope/rotation/recovery — VT-01…VT-128 |
| D-006 | ADR-0010/0070/0072/0076/0091/0128 | Free↔Pro package/API/schema/entitlement compatibility — FP-01…FP-144 |
| D-007 | ADR-0011/0127 | CI/runtime/release enforcement — CI-01…CI-120; rulesets UNKNOWN where access unavailable |
| D-008 | ADR-0012/0126 | build/externalization/toolchain — BT-01…BT-112 |
| D-009 | ADR-0086/0131 | Query compiler/cost/cache/security/providers — QRY-01…QRY-168 |
| D-010 | ADR-0074/0093/0133 | Relations topology/cardinality/concurrency/lifecycle — REL-01…REL-160 |
| D-011 | ADR-0082/0118 | Workflow runtime — WF-01…WF-116 |
| D-012 | ADR-0013…0090/0129/0173/0174 | Membership core + billing providers + protected files — MBR 0/160 + MB-F 0/176 + PC-F 0/176 + MB0–MB5 + PC0–PC4 |
| D-013 | ADR-0021…0100/0130/0175 | Backup artifact/crypto/provider/restore — BK 0/180 + BPC-F 0/176 + C0–C4/V3 |
| D-014 | ADR-0044/0102/0169 | TUF verifier/key custody/metadata/package/staging/recovery — TU-01…TU-176 |
| D-015 | ADR-0031/0108/0163 | Frontend Dashboard routing/auth/cache/browser/Multisite — FD-01…FD-176 |
| D-016 | ADR-0035/0109/0167 | Builder adapter version/capability/upgrade certification — BW-01…BW-176 + BC0…BC4 |
| D-017 | ADR-0038/0110/0166 | Status WP adapter/generic engine/concurrency/history/migration — SM-01…SM-176 |
| D-018 | ADR-0052/0111/0160 | XML-RPC endpoint/method/auth/rate/parser/compatibility — XR-01…XR-176 |
| D-019 | ADR-0036/0089/0112/0162 | Settings scope/inheritance/Vault/cache/import/adapters — ST-01…ST-176 |
| D-020 | ADR-0030/0096/0113/0158 | User Profile identity/security/privacy/cache/Multisite — UP-01…UP-176 |
| D-021 | ADR-0032/0097/0114/0157 | Role/capability native authority/anti-lockout/recovery/cache/Multisite — RA-01…RA-176 |
| D-022 | ADR-0028/0094/0115/0155 | REST auth/scope/schema/idempotency/RLT/CAC/CORS/async/privacy/versioning — REST-01…REST-176 |
| D-023 | ADR-0041/0095/0116/0156 | Import/Export package trust/checkpoint/remap/rollback/privacy/scale — IM-01…IM-176 |
| D-024 | ADR-0025/0077/0117 | Forms runtime — FM-01…FM-92 |
| D-025 | ADR-0026/0079/0120 | Notification fan-out/dedupe/preferences/channel truth — NT-01…NT-142 |
| D-026 | ADR-0027/0077/0121 | Chat authorization/assets/search/realtime/privacy — CH-01…CH-142 |
| D-027 | ADR-0040/0055/0080/0122/0176 | Connections/Safe HTTP/Event Inbox/provider I0–I5 — WC 0/156 + ICP-F 0/176 |
| D-028 | ADR-0022/0087/0134 | Field Storage FS1–FS6 type/storage/migration/uniqueness/privacy — FST-01…FST-176 |
| D-029 | ADR-0023/0088/0135 | Custom Tables CT1–CT3 + CM1–CM4 DDL/migration/recovery — CTB-01…CTB-184 |
| D-030 | ADR-0098/0136 | Admin Columns batching/sort/filter/edit/export/Policy/N+1 — AC-01…AC-176 |
| D-031 | ADR-0039/0099/0137 | Dynamic Listings auth/pagination/count/cache/hydration/builders/SEO — DL-01…DL-176 |
| D-032 | ADR-0138 | Free CPT/Taxonomy registration/rewrite/capability/REST/editor/lifecycle — CPTX-01…CPTX-176 |
| D-033 | ADR-0029/0058/0063/0067/0079/0139 | Emails Builder render/composition — EBR-01…EBR-176; ET separate |
| D-034 | ADR-0140 | Platform Account/Docs/Support/Diagnostics composition — PLT-01…PLT-176 + FP/OA/TU/RS prerequisites |
| D-035 | ADR-0069/0071/0075/0141 | Multisite scope/isolation + lifecycle — MSI-01…MSI-160 + LC-01…LC-96 |
| D-036 | ADR-0081/0142 | Audit/Observability — AUD-01…AUD-176; exact physical/integrity choices open |
| D-037 | ADR-0003/0004/0010/0143 | Kernel/Registry/Policy/Abilities/Events/SDK — KPA-01…KPA-176 |
| D-038 | ADR-0144 | local privacy/data lifecycle — PDL-01…PDL-176; RS remote privacy separate |
| D-039 | ADR-0145 | error taxonomy/failure UX — ERR-01…ERR-176 |
| D-040 | ADR-0035/0039/0099/0146 | Component Blueprint core — CBP-01…CBP-176; builder adapters separate |
| D-041 | ADR-0147 | cross-version/deprecation/migrator evidence — VER-01…VER-176 |
| D-042 | ADR-0148 | enable/disable/dependency loss/expiry/uninstall/recovery — MLC-01…MLC-176 |
| D-043 | ADR-0149 | Entity/Data Source schema/capability/Policy/query/write/transaction/scope/version — DSR-01…DSR-176 |
| D-044 | ADR-0150 | Asset Registry descriptor/dependency/scope/WP-handle/build/loading/security — ASR-01…ASR-176 |
| D-045 | ADR-0151 | Conditional Logic typed operators/value sources/Policy/cache/consumer parity — CLG-01…CLG-176 |
| D-046 | ADR-0152 | Dynamic Value resolver source/typing/escaping/Policy/cache/consumer parity — DVR-01…DVR-176 |
| D-047 | ADR-0045/0153 | Shared Rate Limit identity/atomicity/window/bypass/failure/Multisite/scale — RLT-01…RLT-176 |
| D-048 | ADR-0154 | Shared Cache key/auth/generation/TTL/stampede/backend/privacy/Multisite — CAC-01…CAC-176 |
| D-049 | ADR-0060/0171 | Remote service purpose/minimization/retention/clone/disconnect/deletion/backup/Multisite — RS-01…RS-176 |
| D-050 | ADR-0058/0063/0067/0172 | Email Transport exact-profile submission/delivery/feedback/reconciliation/lifecycle — ET-F 0/176 + ET0–ET5; 6 EE3 / 0 ET-certified |

## B. Universal system / AI expansion blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-051 | ADR-0177/0180/0181 | F01 Solution Blueprint install/upgrade/drift/security/package/Multisite — SBP-001…SBP-176; executed 0/176 |
| D-052 | ADR-0177/0180 | F02 Analytics/Event/Journey event store/identity/privacy/metrics/funnels/attribution/scale — ANL-001…ANL-176; detailed fixture specification current WP64 |
| D-053 | ADR-0177/0180 | F03 Search/Index backend/security/relevance/invalidation/scale — SRH-001…SRH-176 |
| D-054 | ADR-0177/0180 | F04 Decision/Formula/Scoring typed compiler/decimal/unit/determinism/performance — DEC-001…DEC-176 |
| D-055 | ADR-0177/0180 | F05 Ledger transaction/idempotency/holds/rebuild/reconciliation — LED-001…LED-176 |
| D-056 | ADR-0177/0180 | F06 Reservation calendar/DST/atomic hold/capacity/concurrency — RSV-001…RSV-176 |
| D-057 | ADR-0177/0180 | F07 Placement/Personalization slots/context/frequency/cache/privacy/adapters — PLC-001…PLC-176 |
| D-058 | ADR-0177/0180 | F08 Experiments assignment/exposure/statistics/rollout/cache/privacy — EXP-001…EXP-176 |
| D-059 | ADR-0177/0180 | F09 Documents renderer/fonts/assets/private delivery/record integrity — DOC-001…DOC-176 |
| D-060 | ADR-0177/0180 | F10 Sync/ETL cursor/checkpoint/conflicts/idempotency/provider drift/scale — SYN-001…SYN-176 |
| D-061 | ADR-0177/0180 | F11 Geo/Territory spatial storage/query/provider/privacy/scale — GEO-001…GEO-176 |
| D-062 | ADR-0177/0178/0179/0180 | F12 AI Gateway + shared Prompt/MCP runtime — AIP-001…AIP-176; AIC and MCP runtime certifications 0 |
| D-063 | ADR-0177/0180 | WooCommerce Commerce Domain Adapter HPOS/cart/checkout/Blocks/order/stock/shipping/payment/provider/version evidence — WCA-001…WCA-176 |
| D-064 | ADR-0178/0179 | Module-wide Prompt coverage execution across 43/43 surfaces, Requirement/Plan IR, capability gaps, MCP exposure, prompt-injection and provider/model regression — AIP-001…AIP-176 |

## C. Accepted refinements fixed but unexecuted

Historical ADR-0159…ADR-0176 refinements remain unchanged and unexecuted, including PR/XR/RM/ST/FD/AM/DW/SM/BW/WM/TU/OA/RS, ET-F, MB-F, PC-F, BPC-F and ICP-F. No static/provider evidence is promoted to runtime certification.

New accepted planning/evidence:
- ADR-0177 — Solution Blueprint/universal foundations/Woo adapter architecture; 43 surfaces; 160 curated systems; 40 patterns; 268,800 raw primary Blueprint combinations.
- ADR-0178 — WordPress-native AI Prompt/Requirement Compiler + optional MCP architecture; 43/43 Prompt product mapping.
- ADR-0179 — AIP-001…AIP-176; executed 0/176; AIC/MCP certs 0.
- ADR-0180 — universal foundations/Woo adapter master evidence envelopes; each 0/176.
- ADR-0181 — F01 SBP-001…SBP-176 explicit fixtures; executed 0/176.

## D. Current evidence execution truth

Established evidence remains unexecuted as recorded in the Readiness Matrix/Checkpoint. Expanded counters:
- SBP 0/176
- ANL 0/176
- SRH 0/176
- DEC 0/176
- LED 0/176
- RSV 0/176
- PLC 0/176
- EXP 0/176
- DOC 0/176
- SYN 0/176
- GEO 0/176
- AIP 0/176
- WCA 0/176

No expanded runtime certification exists.

## E. Current planning priority

Current work package: **`P0-M00-WP64` — F02 Analytics, Event Tracking & Journey Intelligence detailed executable-evidence specification**.

Planned sequence afterward: F03 Search → F04 Decision/Formula → F05 Ledger → F06 Reservation → F07 Placement → F08 Experiments → F09 Documents → F10 Sync → F11 Geo → Woo Adapter detailed evidence → expanded-scope consistency audit.

This is planning order only. It authorizes no execution.

## F. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine/refine bounded executable protocol when proof is required.
4. Prefer canonical in-place refinement over duplicates.
5. Do not execute code/build/DDL/migration/benchmark/provider/AI/MCP/runtime/data mutations before explicit owner consent.
6. Never promote paper evidence to runtime/provider certification.
7. Keep checkpoint/ledger/readiness/open-decisions/ADR index/Draft PR synchronized.

Production development authorization remains **NOT GRANTED / 0/43**.