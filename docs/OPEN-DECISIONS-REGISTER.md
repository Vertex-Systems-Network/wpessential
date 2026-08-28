# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation/evidence only. Accepted evidence decisions/refinements are preserved through **ADR-0139**. Architecture acceptance never implies runtime certification or owner development authorization.

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
| D-030 | ADR-0098/0136 | Admin Columns hooks/batching/sort/filter/edit/export/Policy/N+1 — AC-01…AC-176 + target capability certification |
| D-031 | ADR-0039/0099/0137 | Dynamic Listings authorization/pagination/count/cache/hydration/interaction/builders/SEO — DL-01…DL-176 |
| D-032 | ADR-0138 | Free CPT/Taxonomy native registration/rewrite/capability/REST/editor/lifecycle/Multisite — CPTX-01…CPTX-176 + CPTX certifications |
| D-033 | ADR-0029/0058/0063/0067/0079/0139 | Emails Builder compile/context/IR/render/plaintext/adapter/handoff — EBR-01…EBR-176; transport delivery truth remains ET0–ET5 |
| D-034 | ADR-0034/0044/0050/0054/0060/0070/0072/0076/0091/0101/0102/0128 | Platform Account/Docs/Support/Diagnostics cross-surface service/auth/privacy/status/cache/error/degradation evidence — WP23 current reassessment |

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
- Admin Columns: AC1 whole-request Column Execution Plan + bounded batch hydration; capabilities certified independently; AC fixed evidence.
- Dynamic Listings: DL1 authorization-aware Query + batched hydration + Component Blueprint SSR; DL-A1 preferred, DL-A2 bounded/evidence-gated, DL-A3 unsupported context; DL fixed evidence.
- Free CPT/Taxonomy: Definition intent, effective registration, rewrite/query state, REST/editor state and persisted content remain separate truths; CPTX fixed evidence.
- Email Builder: Definition → compiled descriptor → authorized context → Email IR → HTML/plaintext Rendered Message → delivery handoff; EBR fixed evidence; transport/provider truth remains separate ET certification.
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
| AC | **0/176; AC-R/S/F/Q/E/B/X/M/P certs 0** |
| DL | **0/176; DL-A1/A2/A3 + DL-R/A/P/F/H/C/I/B/S/M/O certs 0** |
| CPTX | **0/176; all CPTX certifications 0** |
| EBR | **0/176; all renderer/composition certifications 0** |
| FM | **0/92** |
| NT | **0/142** |
| CH | **0/142** |
| WC | **0/156** |
| OA | **0/32** |
| TU | **0/44** |
| DW/AM/PR/RM/WM/FD/BW/SM/XR/ST/UP/RA/REST/IM | **all 0 executed at their documented fixture counts** |
| Email transport/provider | **6 EE3 / 0 ET-certified** |
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

## F. Admin Columns boundary retained after ADR-0136

- Column definition, compiled plan, adapter capability, hydrated data, displayed output, writable source and export capability are distinct.
- AC1 whole-request planning and source-grouped batch hydration remain first baseline.
- real sorting/filtering/search execute before pagination in authoritative backend; browser-only behavior is not equivalent.
- protected data is Policy-gated before hydration.
- inline/bulk edits use owning APIs and stale-write/conflict semantics.
- current-page and all-filtered bulk selection are different contracts.
- export authorization is independent of screen visibility and spreadsheet injection is an explicit security fixture.
- third-party/Woo/core/DataViews support is version/storage-specific evidence.

Current: **AC 0/176; all target/capability certifications 0**.

## G. Dynamic Listings boundary retained after ADR-0137

- Listing definition, published revision, compiled descriptor, candidate set, authorized visible set, count/cursor metadata, rendered HTML, cache artifact and client state are distinct.
- DL1 authorization-aware Query + batched hydration + Component Blueprint SSR remains first baseline.
- DL-A1 is preferred; DL-A2 must stay bounded and metadata-truthful; DL-A3 blocks/degrades unsupported secure pageable contexts.
- protected totals/page counts/facets/cursors/cache cannot reveal inaccessible rows.
- nested listings require depth/result/query budgets and N+1 diagnostics.
- public persistent cache is only for genuinely public deterministic output; protected cache includes scope/access generations.
- stale authorization cannot survive revocation where fail-closed semantics apply.
- enhanced/client transitions use the same compiled server Query/Policy contract.
- builder adapters reference the canonical Listing rather than creating a second product schema.

Current: **DL 0/176; all strategy/capability certifications 0**.

## H. Free CPT + Taxonomy boundary retained after ADR-0138

- Draft Definition ≠ Published Revision ≠ validated registration descriptor ≠ effective WordPress registration ≠ rewrite/query state ≠ REST/editor state ≠ persisted posts/terms ≠ migration state ≠ certification.
- external runtime discovery/collision does not establish WPE ownership.
- published CPT/taxonomy keys are migration-class identities.
- rewrite changes use dirty-generation + controlled safe flush, never every request.
- disable/delete preserves posts/terms/relationships/meta by default.
- capability changes require impact/anti-lockout evidence.
- CPT↔taxonomy registration association must remain consistent on both surfaces.
- network template distribution never makes posts/terms network-shared.

Current: **CPTX 0/176; all CPTX certifications 0**.

## I. Emails Builder boundary retained after ADR-0139

- Template/Layout Definition, compiled descriptor, authorized render context, Email IR, rendered HTML, plaintext, envelope metadata, Rendered Message and Transport Attempt are separate truths.
- renderer success does not imply transport accepted, receiving-server delivered, inbox placed, opened/read or human viewed.
- arbitrary browser/frontend builder markup, PHP, JS, iframes and unsafe raw headers are not canonical Email Builder inputs.
- token providers are typed, privacy-classified and Policy-authorized for the recipient/render context.
- generic tokens never expose credentials, password/reset/session internals or Vault plaintext.
- plaintext generation is deterministic from Email IR, not naive HTML stripping.
- WordPress/third-party overrides require semantic certified adapters and safe fallback; global string-matching interception is not accepted.
- network templates/layouts do not imply shared sender credentials, recipients or delivery state.
- ET0–ET5 provider/transport certification remains independent.

Current: **EBR 0/176; all renderer/composition certifications 0; Email transport 6 EE3 / 0 ET-certified**.

## J. Current highest-priority planning blocker — Platform surfaces

WP23 must reconcile the Account/Docs/Support/Diagnostics surface without duplicating already-canonical FP/OA/TU protocols. Cross-surface evidence must cover only gaps such as:
- account/link/license/catalog/support/docs/status trust-domain boundaries;
- authentication/session expiry, unlink/relink and wrong-account/site behavior;
- offline/stale/degraded cache truth and no false entitlement/support/status claims;
- support ticket create/update/attachment/redaction/idempotency authority;
- docs/changelog/version/locale cache and integrity behavior;
- diagnostics consent, minimization, redaction and remote transmission boundaries;
- remote service rate/error/schema/version degradation;
- Multisite network/site allocation and site-context isolation;
- privacy/retention/export/erasure interactions;
- Vault usage without secret exposure;
- TUF/update trust remaining separate from general account/service APIs;
- service unavailable/partial-outage/support escalation UX;
- no hidden telemetry from Free activation or ordinary local use.

## K. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine/refine bounded executable protocol when proof is required.
4. Prefer canonical in-place refinement over duplicates.
5. Do not execute code/build/DDL/migration/benchmark/provider/runtime/data mutations before explicit owner consent.
6. Never promote paper evidence to runtime/provider certification.
7. Keep checkpoint/ledger/readiness/ADR index/Draft PR synchronized.

## Next planning-only priorities

1. **Platform Account / Docs / Support / Diagnostics consolidated evidence reassessment** — current `P0-M00-WP23`.
2. Reassess remaining unresolved shared/surface blockers by critical-path value after WP23.

Production development authorization remains **NOT GRANTED**.