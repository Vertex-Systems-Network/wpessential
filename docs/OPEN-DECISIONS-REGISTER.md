# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation/evidence only. Accepted evidence decisions/refinements are preserved through **ADR-0141**. Architecture acceptance never implies runtime certification or owner development authorization.

All executable work remains blocked by ADR-0014 until explicit scoped owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002/0123 | compatibility floor — CF-01…CF-112 |
| D-002 | ADR-0005/0125 | UI/accessibility/RTL/scoped-assets/React isolation — UI-01…UI-104 |
| D-003 | ADR-0059/0068/0083/0119 | Job backend/Cron/DST/fairness/claims — JS-01…JS-106 |
| D-004 | ADR-0073/0092/0132 | Definition D1–D4 exact DDL/index/locking/migration — DEF-01…DEF-144 |
| D-005 | ADR-0048/0085/0124 | Vault crypto/envelope/rotation/recovery/security — VT-01…VT-128 |
| D-006 | ADR-0010/0070/0072/0076/0091/0128 | Free↔Pro package/API/schema/entitlement compatibility — FP-01…FP-144 |
| D-007 | ADR-0011/0127 | CI/runtime/release enforcement — CI-01…CI-120; branches directly observed unprotected; rulesets UNKNOWN (403) |
| D-008 | ADR-0012/0126 | build/externalization/toolchain — BT-01…BT-112 |
| D-009 | ADR-0086/0131 | Query compiler/cost/cache/security/provider — QRY-01…QRY-168; QP1–QP4 certs separate |
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
| D-020 | ADR-0030/0096/0113 | User Profile identity/security/privacy — UP-01…UP-48 |
| D-021 | ADR-0032/0097/0114 | Role/capability anti-lockout/recovery — RA-01…RA-48 |
| D-022 | ADR-0028/0094/0115 | REST auth/scope/schema/idempotency/rate/cache/CORS — REST-01…REST-52 |
| D-023 | ADR-0041/0095/0116 | Import/Export plan/checkpoint/rollback/scale — IM-01…IM-56 |
| D-024 | ADR-0025/0077/0117 | Forms runtime — FM-01…FM-92 |
| D-025 | ADR-0026/0079/0120 | Notification fan-out/dedupe/preferences/channel truth — NT-01…NT-142 |
| D-026 | ADR-0027/0077/0121 | Chat authorization/assets/search/realtime/privacy — CH-01…CH-142 |
| D-027 | ADR-0040/0055/0080/0122 | Connections/Safe HTTP/Event Inbox/provider I0–I5 — WC-01…WC-156 |
| D-028 | ADR-0022/0087/0134 | Field Storage FS1–FS6 type/storage/migration/uniqueness/privacy — FST-01…FST-176 |
| D-029 | ADR-0023/0088/0135 | Custom Tables CT1–CT3 + CM1–CM4 exact DDL/migration/recovery — CTB-01…CTB-184 |
| D-030 | ADR-0098/0136 | Admin Columns hooks/batching/sort/filter/edit/export/Policy/N+1 — AC-01…AC-176 + target certification |
| D-031 | ADR-0039/0099/0137 | Dynamic Listings auth/pagination/count/cache/hydration/interaction/builders/SEO — DL-01…DL-176 |
| D-032 | ADR-0138 | Free CPT/Taxonomy native registration/rewrite/capability/REST/editor/lifecycle/Multisite — CPTX-01…CPTX-176 |
| D-033 | ADR-0029/0058/0063/0067/0079/0139 | Emails Builder render/composition — EBR-01…EBR-176; ET0–ET5 transport separate |
| D-034 | ADR-0034/0044/0050/0054/0060/0070/0072/0076/0091/0101/0102/0128/0140 | Platform Account/Docs/Support/Diagnostics composition — PLT-01…PLT-176 + FP/OA/TU/RS dependencies |
| D-035 | ADR-0069/0071/0075/0141 | Multisite scope/isolation + lifecycle — MSI-01…MSI-160 + LC-01…LC-96; MS/SL runtime certification 0 |
| D-036 | ADR-0081 + shared Audit architecture | Audit/Observability retention/integrity/correlation/redaction/export/Multisite/failure evidence — WP25 current reassessment |

## B. Accepted paper/runtime-baseline summary

- Compatibility: WP 6.9 / PHP 8.3 minimum candidates; WP 7.1 planning reference; floor not certified.
- UI: WPE wrappers + WordPress-provided React; minimum WP cannot hard-depend on newer-only UI capability.
- Build: `@wordpress/build` first candidate, `@wordpress/scripts` comparison/fallback; Vite only for proven unmet need.
- CI: provider-neutral FAST/FULL, secret isolation, baseline/flaky truth and provenance; main/planning directly observed unprotected; rulesets UNKNOWN.
- Definition D1/PT-C first benchmark baseline; Query QP1–QP4; Relations R1/PT-D first + R2 mandatory; all remain evidence-gated.
- Field Storage FS1–FS6; Custom Tables CT1–CT3 + CM1–CM4; Admin Columns AC1; Dynamic Listings DL1; all fixed protocols remain 0 executed.
- Free CPT/Taxonomy effective WP registration/rewrite/REST/editor state remains distinct from Definition intent; CPTX fixed evidence.
- Email Definition → compiled descriptor → authorized context → Email IR → rendered message → transport handoff; EBR fixed evidence; ET separate.
- Platform trust domains remain separate: onboarding, Account, OAuth, Product Entitlement, Site Allocation, package compatibility, update trust, Support authority, Diagnostics transmission and Docs cache are not one state; PLT fixed evidence.
- Multisite: explicit scope + target authorization; current blog is not ownership; MS0–MS4 retained; MSI fixed evidence.
- Site Lifecycle: provisioning/restrict/reactivate/teardown/clone/transfer/disaster are journaled domain-aware states; SL0–SL4 retained; LC fixed evidence.
- Forms FRT1/PT-D first + FRT2 mandatory; Chat CRT1/PT-D first + CRT2 mandatory; Membership M1/PT-D first + M2 mandatory; Notification/Email NE1/PT-D first + NE2 mandatory; Event Inbox EI1/PT-D first + EI2 mandatory; Workflow WF1/PT-D first + WF2 mandatory.
- JobService J1/J2/J3; Action Scheduler candidate only.
- Import IR1/PT-D first + IR2 mandatory; Backup manifest-first BR1/BR2/BR3; Vault V1/PT-C favored first + V2 comparison.

All remain paper-only until applicable executable evidence certifies them.

## C. Fixed evidence protocols and execution truth

| Protocol | Evidence state |
|---|---|
| CF | **0/112** |
| UI | **0/104** |
| JS | **0/106** |
| DEF | **0/144; final D1–D4/DDL open** |
| VT | **0/128** |
| FP | **0/144** |
| CI | **0/120** |
| BT | **0/112** |
| QRY | **0/168; QP1–QP4 certs 0** |
| REL | **0/160; R/E/PV/DDL open** |
| WF | **0/116** |
| MBR | **0/160; 4 BE3 / 0 MB-certified; 0 PC1+** |
| BK | **0/180; 34 targets / 0 C-certified / 0 C3; V3 0** |
| FST | **0/176** |
| CTB | **0/184** |
| AC | **0/176** |
| DL | **0/176** |
| CPTX | **0/176** |
| EBR | **0/176** |
| PLT | **0/176** |
| MSI | **0/160; 0 surfaces MS1+** |
| LC | **0/96; SL runtime certs 0** |
| FM | **0/92** |
| NT | **0/142** |
| CH | **0/142** |
| WC | **0/156** |
| OA | **0/32** |
| TU | **0/44** |
| DW/AM/PR/RM/WM/FD/BW/SM/XR/ST/UP/RA/REST/IM | **all 0 executed at documented fixture counts** |
| Email transport/provider | **6 EE3 / 0 ET-certified** |
| Remote privacy RS | **0/30** |

## D. Multisite boundary retained after ADR-0141

- site/network ownership never falls back to current blog context;
- numeric blog ID, WPE site UUID, network/install identity and commercial Allocation ID stay distinct;
- Site Admin cannot acquire Network authority through request coordinates;
- `switch_to_blog()` is context management only;
- caches/jobs/workflows/provider actions stay target-scope bound;
- ordinary site Query/Relation/REST/Ability cannot become arbitrary cross-site operation;
- network secret delegation does not reveal/copy plaintext ownership;
- global WP user identity does not propagate site roles/Membership access;
- site lifecycle reauthorizes delayed side effects;
- site deletion is not global-user deletion/billing cancellation/shared-secret deletion/privacy erasure;
- clone/restore does not resurrect stale allocation/OAuth/provider/access authority;
- MS0/SL0 static mapping is not runtime certification;
- scale claims require measured executed environments.

Current: **MSI 0/160; LC 0/96; 0 MS1+ surfaces; 0 SL runtime certifications**.

## E. Current highest-priority planning blocker — Audit & Observability

WP25 must reconcile accepted Audit/AU1/PT-D architecture and all consumers without duplicating domain history or Diagnostics. Evidence should cover:
- audit event identity/version/category/action/outcome and actor/subject/target scope;
- correlation across Ability/REST/Job/Workflow/provider/import/backup/destructive operations;
- append/history semantics, retention, pruning and immutable-history truth;
- before/after summaries without secret/private payload leakage;
- Vault/password/token/private-content redaction;
- sensitive audit read/export authorization and anti-IDOR;
- Multisite site/network isolation and network aggregate access;
- timestamps/order/concurrency/duplicate event/idempotency behavior;
- storage topology/index/performance/retention benchmark;
- integrity/tamper-evidence claims without pretending local DB is tamper-proof;
- privacy export/erasure/legal-retention boundaries;
- degraded audit-write behavior for security/high-risk mutations;
- support/diagnostics export and safe correlation IDs;
- observability metrics/logs/traces separation from canonical Audit.

## F. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine/refine bounded executable protocol when proof is required.
4. Prefer canonical in-place refinement over duplicates.
5. Do not execute code/build/DDL/migration/benchmark/provider/runtime/data mutations before explicit owner consent.
6. Never promote paper evidence to runtime/provider certification.
7. Keep checkpoint/ledger/readiness/ADR index/Draft PR synchronized.

## Next planning-only priorities

1. **Audit & Observability executable-evidence reassessment** — current `P0-M00-WP25`.
2. Reassess remaining unresolved shared/surface blockers after WP25.

Production development authorization remains **NOT GRANTED**.