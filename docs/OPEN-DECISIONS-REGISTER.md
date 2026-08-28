# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register tracks unresolved runtime/physical/provider/evidence decisions. Accepted evidence decisions/refinements are preserved through **ADR-0157**. Architecture or protocol acceptance never implies runtime certification or owner development authorization.

All executable work remains blocked by ADR-0014 until explicit scoped owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002/0123 | compatibility floor — CF-01…CF-112 |
| D-002 | ADR-0005/0125 | UI/accessibility/RTL/scoped assets/React isolation — UI-01…UI-104 |
| D-003 | ADR-0059/0068/0083/0119 | Job backend/Cron/DST/fairness/claims — JS-01…JS-106 |
| D-004 | ADR-0073/0092/0132 | Definition D1–D4 exact DDL/index/locking/migration — DEF-01…DEF-144 |
| D-005 | ADR-0048/0085/0124 | Vault crypto/envelope/rotation/recovery — VT-01…VT-128 |
| D-006 | ADR-0010/0070/0072/0076/0091/0128 | Free↔Pro package/API/schema/entitlement compatibility — FP-01…FP-144 |
| D-007 | ADR-0011/0127 | CI/runtime/release enforcement — CI-01…CI-120; branches directly observed unprotected; rulesets UNKNOWN (403) |
| D-008 | ADR-0012/0126 | build/externalization/toolchain — BT-01…BT-112 |
| D-009 | ADR-0086/0131 | Query compiler/cost/cache/security/providers — QRY-01…QRY-168; QP certs separate |
| D-010 | ADR-0074/0093/0133 | Relations topology/cardinality/concurrency/lifecycle — REL-01…REL-160; final R/E/PV/DDL open |
| D-011 | ADR-0082/0118 | Workflow runtime — WF-01…WF-116 |
| D-012 | ADR-0013…0090/0129 | Membership runtime/providers/protected files — MBR-01…MBR-160 + MB/PC certs |
| D-013 | ADR-0021…0100/0130 | Backup artifact/crypto/provider/restore — BK-01…BK-180 + C0–C4/V3 certs |
| D-014 | ADR-0044/0102 | TUF verifier/key custody/package staging — TU-01…TU-44 |
| D-015 | ADR-0031/0108 | Frontend Dashboard — FD-01…FD-48 |
| D-016 | ADR-0035/0109 | Builder adapter certification — BW-01…BW-50 + BC0…BC4 |
| D-017 | ADR-0038/0110 | Status runtime/concurrency/history/migration — SM-01…SM-48 |
| D-018 | ADR-0052/0111 | XML-RPC compatibility/security — XR-01…XR-48 |
| D-019 | ADR-0036/0089/0112 | Settings scope/inheritance/Vault/cache/import — ST-01…ST-48 |
| D-020 | ADR-0030/0096/0113 | User Profile identity/security/privacy — UP-01…UP-48; WP41 canonical refinement current |
| D-021 | ADR-0032/0097/0114/0157 | Role/capability native authority/anti-lockout/recovery/cache/Multisite — RA-01…RA-176 |
| D-022 | ADR-0028/0094/0115/0155 | REST auth/scope/schema/idempotency/RLT/CAC/CORS/async/privacy/versioning — REST-01…REST-176 |
| D-023 | ADR-0041/0095/0116/0156 | Import/Export package trust/plan/checkpoint/remap/rollback/privacy/scale — IM-01…IM-176 |
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
| D-035 | ADR-0069/0071/0075/0141 | Multisite scope/isolation + lifecycle — MSI-01…MSI-160 + LC-01…LC-96; runtime certs 0 |
| D-036 | ADR-0081/0142 | Audit/Observability — AUD-01…AUD-176; AU1/PT-D first baseline; exact physical/integrity choices open |
| D-037 | ADR-0003/0004/0010/0143 | Kernel/Registry/Policy/Abilities/Events/SDK — KPA-01…KPA-176 |
| D-038 | ADR-0144 | local privacy/data lifecycle — PDL-01…PDL-176; RS remote privacy separate |
| D-039 | ADR-0145 | error taxonomy/failure UX — ERR-01…ERR-176 |
| D-040 | ADR-0035/0039/0099/0146 | Component Blueprint core — CBP-01…CBP-176; BW/BC separate |
| D-041 | ADR-0147 | shared cross-version/deprecation/migrator evidence — VER-01…VER-176 |
| D-042 | ADR-0148 | enable/disable/dependency loss/expiry/uninstall/recovery — MLC-01…MLC-176 |
| D-043 | ADR-0149 | Entity/Data Source schema/capability/Policy/query/write/transaction/scope/version — DSR-01…DSR-176 |
| D-044 | ADR-0150 | Asset Registry descriptor/dependency/scope/WP-handle/build/loading/security — ASR-01…ASR-176 |
| D-045 | ADR-0151 | Conditional Logic typed operators/value sources/Policy/cache/consumer parity — CLG-01…CLG-176 |
| D-046 | ADR-0152 | Dynamic Value resolver source/typing/escaping/Policy/cache/consumer parity — DVR-01…DVR-176 |
| D-047 | ADR-0045/0153 | Shared Rate Limit identity/atomicity/window/bypass/failure/Multisite/scale — RLT-01…RLT-176 |
| D-048 | ADR-0154 | Shared Cache key/auth/generation/TTL/stampede/backend/privacy/Multisite — CAC-01…CAC-176 |

## B. Accepted paper/runtime-baseline summary

- Compatibility: WP 6.9 / PHP 8.3 minimum candidates; WP 7.1 planning reference; floor not certified.
- UI: WPE wrappers + WordPress-provided React; minimum WP cannot hard-depend on newer-only UI capability.
- Build: `@wordpress/build` first candidate, `@wordpress/scripts` comparison/fallback; final toolchain unverified.
- CI: provider-neutral FAST/FULL, secret isolation, baseline/flaky truth and provenance; branches directly observed unprotected; rulesets UNKNOWN.
- Definition D1/PT-C; Query QP1–QP4; Relations R1/PT-D first + R2 mandatory; physical profiles remain evidence-gated.
- Field Storage FS1–FS6; Custom Tables CT1–CT3 + CM1–CM4; Admin Columns AC1; Dynamic Listings DL1 remain unexecuted.
- Platform trust domains remain separate: Account/OAuth/Entitlement/Allocation/package/update/support/diagnostics/docs.
- Multisite current blog is never durable ownership; MS0–MS4 and SL0–SL4 remain runtime-evidence gated.
- Audit local DB/hash evidence is not called tamper-proof without certified threat/evidence model.
- Free owns one kernel/registry family; Capability + target Policy apply regardless of UI/REST/CLI/Workflow/AI.
- Local live erase ≠ backup erase ≠ remote deletion.
- Error machine codes/retry/disclosure/partial-failure remain explicit.
- Component Blueprint core and builder adapter certifications remain separate.
- Product/API/schema/runtime/Ability/Event/SDK/package version equality never substitutes for explicit compatibility/migrator evidence.
- module disable/expiry/uninstall/cleanup/privacy erase remain separate lifecycle operations.
- readable Data Source never implies write/delete/transaction capability.
- asset declaration/registration/enqueue/fetch/execution remain separate.
- condition `true`, DVR success, RLT allow and CAC hit never grant authorization.
- WordPress remains Role/Capability authority; WPE plans/snapshots are guard/control-plane metadata.
- Import package checksum/signature status never authorizes import and numeric IDs are not portable identity authority.

## C. Fixed evidence execution truth

| Protocol | State |
|---|---|
| CF | **0/112** |
| UI | **0/104** |
| JS | **0/106** |
| DEF | **0/144** |
| VT | **0/128** |
| FP | **0/144** |
| CI | **0/120** |
| BT | **0/112** |
| QRY | **0/168** |
| REL | **0/160** |
| WF | **0/116** |
| MBR | **0/160** |
| BK | **0/180** |
| FST | **0/176** |
| CTB | **0/184** |
| AC | **0/176** |
| DL | **0/176** |
| CPTX | **0/176** |
| EBR | **0/176** |
| PLT | **0/176** |
| MSI | **0/160; 0 MS1+** |
| LC | **0/96; 0 SL runtime certs** |
| AUD | **0/176** |
| KPA | **0/176** |
| PDL | **0/176** |
| ERR | **0/176** |
| CBP | **0/176** |
| VER | **0/176** |
| MLC | **0/176** |
| DSR | **0/176** |
| ASR | **0/176** |
| CLG | **0/176** |
| DVR | **0/176** |
| RLT | **0/176** |
| CAC | **0/176** |
| REST | **0/176** |
| IM | **0/176** |
| RA | **0/176** |
| UP | **0/48; WP41 refinement current** |
| FM | **0/92** |
| NT | **0/142** |
| CH | **0/142** |
| WC | **0/156** |
| OA | **0/32** |
| TU | **0/44** |
| DW/AM/PR/RM/WM/FD/BW/SM/XR/ST | **all 0 executed at documented counts** |
| Email transport/provider | **6 EE3 / 0 ET-certified** |
| Membership billing/protected files | **4 BE3 / 0 MB-certified; 0 PC1+** |
| Backup providers | **34 targets / 0 C-certified / 0 C3; V3 0** |
| Connection adapters | **0 I4/I5** |
| Remote privacy RS | **0/30** |

## D. Current highest-priority planning blocker — User Profile

`P0-M00-WP41` must reconcile the existing UP-01…UP-48 protocol against current FST/DSR/KPA/RA/PDL/ERR/CAC/VER/MLC and Multisite evidence. The refinement must preserve:
- native WordPress identity/auth authority;
- generic profile fields cannot mutate passwords, roles/caps, sessions, Application Passwords, Membership entitlements, Vault/provider secrets or protected security internals;
- identity-change actions are separate high-risk flows with current auth/recent auth/revalidation where required;
- global user identity and site-scoped profile/role/Membership facts remain distinct in Multisite;
- public projection is authorization/privacy-aware and cache-safe;
- native/third-party/user-meta ownership and mass-assignment boundaries remain explicit.

## E. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine/refine bounded executable protocol when proof is required.
4. Prefer canonical in-place refinement over duplicates.
5. Do not execute code/build/DDL/migration/benchmark/provider/runtime/data mutations before explicit owner consent.
6. Never promote paper evidence to runtime/provider certification.
7. Keep checkpoint/ledger/readiness/open-decisions/ADR index/Draft PR synchronized.

## Next planning-only priorities

1. **User Profile canonical evidence refinement — current `P0-M00-WP41`.**
2. Reassess remaining shallow legacy evidence protocols by security/critical-path value.

Production development authorization remains **NOT GRANTED**.