# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register tracks unresolved runtime/physical/provider/evidence decisions. Accepted evidence decisions/refinements are preserved through **ADR-0173**. Architecture/protocol acceptance never implies runtime certification or owner development authorization.

All executable work remains blocked by ADR-0014 until explicit scoped owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002/0123 | compatibility floor — CF-01…CF-112 |
| D-002 | ADR-0005/0125 | UI/accessibility/RTL/scoped assets/React isolation — UI-01…UI-104 |
| D-003 | ADR-0059/0068/0083/0119 | Job backend/Cron/DST/fairness/claims — JS-01…JS-106 |
| D-004 | ADR-0073/0092/0132 | Definition exact DDL/index/locking/migration — DEF-01…DEF-144 |
| D-005 | ADR-0048/0085/0124 | Vault crypto/envelope/rotation/recovery — VT-01…VT-128 |
| D-006 | ADR-0010/0070/0072/0076/0091/0128 | Free↔Pro package/API/schema/entitlement compatibility — FP-01…FP-144 |
| D-007 | ADR-0011/0127 | CI/runtime/release enforcement — CI-01…CI-120; branches observed unprotected; rulesets UNKNOWN |
| D-008 | ADR-0012/0126 | build/externalization/toolchain — BT-01…BT-112 |
| D-009 | ADR-0086/0131 | Query compiler/cost/cache/security/providers — QRY-01…QRY-168 |
| D-010 | ADR-0074/0093/0133 | Relations topology/cardinality/concurrency/lifecycle — REL-01…REL-160 |
| D-011 | ADR-0082/0118 | Workflow runtime — WF-01…WF-116 |
| D-012 | ADR-0013…0090/0129/0173 | Membership core + billing providers + protected files — MBR-01…MBR-160 + MB-F001…MB-F176/MB0–MB5 + PC |
| D-013 | ADR-0021…0100/0130 | Backup artifact/crypto/provider/restore — BK-01…BK-180 + C0–C4/V3 |
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
| D-027 | ADR-0040/0055/0080/0122 | Connections/Safe HTTP/Event Inbox/provider I0–I5 — WC-01…WC-156 |
| D-028 | ADR-0022/0087/0134 | Field Storage FS1–FS6 type/storage/migration/uniqueness/privacy — FST-01…FST-176 |
| D-029 | ADR-0023/0088/0135 | Custom Tables CT1–CT3 + CM1–CM4 DDL/migration/recovery — CTB-01…CTB-184 |
| D-030 | ADR-0098/0136 | Admin Columns batching/sort/filter/edit/export/Policy/N+1 — AC-01…AC-176 |
| D-031 | ADR-0039/0099/0137 | Dynamic Listings auth/pagination/count/cache/hydration/builders/SEO — DL-01…DL-176 |
| D-032 | ADR-0138 | Free CPT/Taxonomy registration/rewrite/capability/REST/editor/lifecycle — CPTX-01…CPTX-176 |
| D-033 | ADR-0029/0058/0063/0067/0079/0139 | Emails Builder render/composition — EBR-01…EBR-176; ET transport separate |
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
| D-050 | ADR-0058/0063/0067/0172 | Email Transport exact-profile submission/delivery/feedback/reconciliation/lifecycle certification — ET-F001…ET-F176 + ET0–ET5; 6 EE3 / 0 ET-certified |

## B. Additional canonical refinements now fixed but unexecuted

- ADR-0159 — Protector **PR 0/176**.
- ADR-0160 — XML-RPC **XR 0/176**.
- ADR-0161 — Reset Manager **RM 0/176**.
- ADR-0162 — Settings Page **ST 0/176**.
- ADR-0163 — Frontend Dashboard **FD 0/176**.
- ADR-0164 — Admin Menu **AM 0/176**.
- ADR-0165 — Dashboard Widgets **DW 0/176**.
- ADR-0166 — Status Manager **SM 0/176**.
- ADR-0167 — Builder Widgets adapters **BW 0/176**, BC0…BC4 certifications 0.
- ADR-0168 — Watermarker/Media **WM 0/176**.
- ADR-0169 — Pro Updater TUF **TU 0/176**.
- ADR-0170 — OAuth Account-Link **OA 0/176**.
- ADR-0171 — Remote Service Privacy / Retention **RS 0/176**.
- ADR-0172 — Email Transport / Provider Certification **ET-F 0/176; 6 EE3 / 0 ET-certified**.
- ADR-0173 — Membership Billing Provider Certification **MB-F 0/176; 4 BE3 / 0 MB-certified**.

## C. Accepted paper/runtime-baseline summary

- Compatibility: WP/PHP/DB floor remains unverified.
- UI/build/CI: wrappers + WordPress-provided React; final runtime/build/CI certification pending.
- Definition/Query/Relations/Field/Custom Table physical profiles remain evidence-gated.
- Free owns one kernel/registry family; every channel remains Capability + target Policy bound.
- WordPress remains native identity/auth and Role/Capability authority.
- Local live erase ≠ backup erase ≠ remote deletion.
- Account/OAuth/Entitlement/Site Allocation/TUF update trust are distinct domains.
- TUF Root trust cannot be added by Account/API/CDN; target bytes require trusted metadata graph + hash/length + staging/compatibility evidence.
- OAuth first profile uses public-client PKCE S256/exact redirect/issuer binding; link success does not grant WordPress/Membership/Product entitlement authority.
- Component Blueprint remains canonical over builder-private representations.
- Watermarker original source bytes/checksum remain immutable.
- menu/widget/route/control visibility never becomes authorization.
- module disable/expiry/uninstall/cleanup/privacy erase remain distinct lifecycle operations.
- current-blog context never becomes durable ownership/authorization.
- email renderer success ≠ transport attempt ≠ provider acceptance ≠ receiving-server delivery ≠ complaint/suppression ≠ engagement; open/click ≠ human read.
- billing provider commercial fact ≠ Enrollment ≠ Membership Entitlement ≠ Product Entitlement ≠ WordPress Role.
- webhook/hook freshness and Job retries never become provider/Membership authority shortcuts.

## D. Fixed evidence execution truth

- CF **0/112**; UI **0/104**; JS **0/106**; DEF **0/144**; VT **0/128**; FP **0/144**; CI **0/120**; BT **0/112**.
- QRY **0/168**; REL **0/160**; WF **0/116**; MBR **0/160**; BK **0/180**.
- FST **0/176**; CTB **0/184**; AC/DL/CPTX/EBR/PLT/AUD/KPA/PDL/ERR/CBP/VER/MLC/DSR/ASR/CLG/DVR/RLT/CAC are all **0/176**.
- REST/IM/RA/UP/PR/XR/RM/ST/FD/AM/DW/SM/BW/WM/TU/OA/RS are all **0/176**.
- MSI **0/160; 0 MS1+**; LC **0/96; 0 SL runtime certs**.
- FM **0/92**; NT **0/142**; CH **0/142**; WC **0/156**.
- Email transport ET-F **0/176**; provider profiles **6 EE3 / 0 ET-certified**; ET0…ET5 runtime certifications **0 each**.
- Membership billing MB-F **0/176**; provider profiles **4 BE3 / 0 MB-certified**; MB0…MB5 runtime certifications **0 each**; protected files **0 PC1+**.
- Backup providers **34 targets / 0 C-certified / 0 C3; V3 0**.
- Connection adapters **0 I4/I5**.

## E. Current highest-priority planning blocker

`P0-M00-WP57` — **Protected-file provider/delivery certification reassessment**.

Reason: Membership billing provider certification evidence is now fixed by ADR-0173, while protected Membership files remain **0 PC1+**. Reassess PC0–PC4 and private local/accelerated/object-delivery profiles against current Membership Entitlement/Policy, Vault, Safe HTTP/Asset/Media boundaries, privacy, ERR, VER, RLT, Multisite, Site Lifecycle, Backup and revoke-safe delivery semantics. Preserve storage possession ≠ authorization and signed URL issuance ≠ durable entitlement.

## F. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine/refine bounded executable protocol when proof is required.
4. Prefer canonical in-place refinement over duplicates.
5. Do not execute code/build/DDL/migration/benchmark/provider/runtime/data mutations before explicit owner consent.
6. Never promote paper evidence to runtime/provider certification.
7. Keep checkpoint/ledger/readiness/open-decisions/ADR index/Draft PR synchronized.

Production development authorization remains **NOT GRANTED**.