# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation/evidence only. Accepted evidence decisions/refinements are preserved through **ADR-0135**. Architecture acceptance never implies runtime certification or owner development authorization.

All executable work remains blocked by ADR-0014 until explicit scoped owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002/0069/0075/0123 | compatibility + Multisite/site lifecycle — CF-01…CF-112 |
| D-002 | ADR-0005/0125 | UI/accessibility/RTL/scoped-assets/React isolation — UI-01…UI-104 |
| D-003 | ADR-0059/0068/0083/0119 | Job backend/Cron/DST/fairness/claims — JS-01…JS-106 |
| D-004 | ADR-0073/0092/0132 | Definition D1–D4 exact DDL/index/locking/migration — DEF-01…DEF-144 |
| D-005 | ADR-0048/0085/0124 | Vault crypto/envelope/rotation/recovery/security — VT-01…VT-128 |
| D-006 | ADR-0010/0070/0072/0076/0091/0128 | Free↔Pro package/API/schema boot compatibility — FP-01…FP-144 |
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
| D-030 | ADR-0098 | Admin Columns native hooks/batching/sort/filter/edit/export/Policy/N+1 — dedicated fixed evidence pending WP19 |

## B. Accepted paper/runtime-baseline summary

- Compatibility: WP 6.9 / PHP 8.3 minimum candidates; WP 7.1 planning reference; floor not certified.
- UI: WPE wrappers + WordPress-provided React; minimum WP cannot hard-depend on newer-only UI capability.
- Build: `@wordpress/build` first candidate, `@wordpress/scripts` fallback comparison; Vite only for proven unmet need.
- CI: provider-neutral FAST/FULL, secret isolation, baseline/flaky truth, artifact provenance; direct branch reads show main/planning unprotected; rulesets UNKNOWN.
- Definition: D1/PT-C first benchmark baseline; D2/D3/D4 comparisons; DEF fixed evidence.
- Query: QP1 WP-native, QP2 Custom Tables, QP3 Relations, QP4 remote; QRY fixed evidence.
- Relations: R1/PT-D first, R2/PT-E mandatory, R3 exceptional; REL fixed evidence.
- Field Storage: FS1 native WP; FS2 typed Custom Table; FS3 child rows; FS4 Relations; FS5 Vault; FS6 projection; FST fixed evidence.
- Custom Tables: CT1/PT-E first for site-owned; CT2/PT-D mandatory comparison; CT3 genuinely network-owned only. Typed desired-schema + Migration Plan; CM1 direct, CM2 backfill, CM3 shadow/swap, CM4 destructive recovery; CTB fixed evidence.
- Settings ST1/PT-A, ST2/PT-B, ST3 inheritance.
- Forms FRT1/PT-D first, FRT2/PT-E mandatory.
- Chat CRT1/PT-D first, CRT2/PT-E mandatory.
- Membership M1/PT-D first, M2/PT-E mandatory; Enrollment authoritative; protected file and billing certifications separate.
- Notification/Email NE1/PT-D first, NE2/PT-E mandatory.
- Event Inbox EI1/PT-D first, EI2/PT-E mandatory.
- Workflow WF1/PT-D first, WF2/PT-E mandatory.
- JobService J1/J2/J3; Action Scheduler candidate only.
- Import IR1/PT-D first, IR2/PT-E mandatory.
- Backup manifest-first; BR1/BR2/BR3; V0–V3 truth; BK fixed restore-first evidence.
- Vault V1/PT-C favored first; V2/PT-E comparison + separate network Vault.

All remain paper-only until their applicable executable evidence certifies them.

## C. Fixed evidence protocols and current execution truth

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
| FST | **0/176; FS runtime/profile certs 0** |
| CTB | **0/184; CT1/CT2/CT3 + CM1–CM4 certs 0; exact DDL open** |
| FM | **0/92** |
| NT | **0/142** |
| CH | **0/142** |
| WC | **0/156** |
| OA | **0/32** |
| TU | **0/44** |
| DW/AM/PR/RM/WM/FD/BW/SM/XR/ST/UP/RA/REST/IM | **all 0 executed at their documented fixture counts** |
| Email | **6 EE3 / 0 ET-certified** |
| Site Lifecycle | **0/40** |
| Multisite | **0 MS1+** |
| Remote privacy | **0/30** |

## D. Field Storage boundary retained after ADR-0134

- Field Definition, runtime value, editor control, storage adapter and presentation are distinct.
- Definition publish does not mean value migration complete.
- Q0–Q4 queryability cannot exceed physical evidence.
- hard uniqueness needs concurrency-safe proof.
- relationships/pivot/cardinality remain Relations-owned.
- secrets persist only as Vault references.
- projections remain rebuildable derived state.
- storage never automatically grants REST/Ability/export/cache access.

Current: **FST 0/176; all runtime/profile certifications 0**.

## E. Custom Tables boundary retained after ADR-0135

- Table Definition, observed schema, Migration Plan, Migration Run, applied fingerprint and runtime rows are distinct truths.
- raw DDL is not normal product configuration.
- `dbDelta()` is only an evidence-bounded compiler tool.
- CT1/PT-E is first ordinary site-owned baseline; CT2/PT-D mandatory comparison; CT3 network-owned only.
- CT2 requires trusted scope on every site-owned row operation.
- source fingerprint is revalidated before mutation.
- no silent truncation/lossy conversion.
- CM1–CM4 recovery/availability truth must be measured, not assumed.
- destructive Definition delete never auto-drops data.
- unknown drift is surfaced/classified rather than blindly overwritten.

Current: **CTB 0/184; CT/CM certifications 0; exact DDL open**.

## F. Current highest-priority planning blocker — Admin Columns

ADR-0098 already establishes AC1 whole-request execution planning and batch hydration, but no dedicated fixed executable protocol was found by repository search. WP19 must freeze evidence for:
- core + custom post/user/term/comment/media list-table hook integration;
- third-party adapter capability/degradation;
- column-set/audience/Policy resolution;
- whole-request data plan and bounded batch hydration;
- N+1 detection and lazy-fetch authorization;
- correct backend sorting/filtering instead of cosmetic client sort;
- typed rendering/escaping/media/relationship/query values;
- inline and bulk edit using owning Data Source/Field APIs;
- optimistic/conflict semantics where underlying source supports them;
- CSV/export formula injection and protected-field rules;
- screen options/column ordering/responsiveness/accessibility;
- Multisite/network list scope isolation;
- 100/1k/10k row list workloads and independent security/performance review.

## G. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine/refine bounded executable protocol when proof is required.
4. Prefer canonical in-place refinement over duplicates.
5. Do not execute code/build/DDL/migration/benchmark/provider/runtime/data mutations before explicit owner consent.
6. Never promote paper evidence to runtime/provider certification.
7. Keep checkpoint/ledger/readiness/ADR index/Draft PR synchronized.

## Next planning-only priorities

1. **Admin Columns operational executable-evidence refinement** — current `P0-M00-WP19`.
2. Dynamic Listings SSR/pagination/cache evidence.
3. Reassess remaining blockers after these data-consumer surfaces.

Production development authorization remains **NOT GRANTED**.