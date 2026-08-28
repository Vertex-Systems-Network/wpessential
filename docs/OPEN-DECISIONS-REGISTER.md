# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation/evidence only. Accepted evidence decisions/refinements are preserved through **ADR-0132**. Architecture acceptance never implies runtime certification or owner development authorization.

All executable work remains blocked by ADR-0014 until explicit scoped owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002/0069/0075/0123 | WP/PHP/DB compatibility + Multisite/site lifecycle — CF-01…CF-112 / P-001 |
| D-002 | ADR-0005/0125 | UI runtime/WP-min capability/accessibility/RTL/scoped-assets/React isolation — UI-01…UI-104 / P-002 |
| D-003 | ADR-0059/0068/0083/0119 | Job backend/Action Scheduler/Cron/DST/fairness/claims/Multisite — JS-01…JS-106 / P-003 |
| D-004 | ADR-0073/0092/0132 | Definition D1/D2/D3/D4 exact DDL/index/locking/migration/import/retention/recovery — DEF-01…DEF-144 / P-004 |
| D-005 | ADR-0048/0085/0124 | Vault crypto/AAD/envelope/rotation/recovery/redaction/Multisite/security review — VT-01…VT-128 / P-005 |
| D-006 | ADR-0010/0070/0072/0076/0091/0128 | Free↔Pro package/Platform API/schema boot compatibility — FP-01…FP-144 / P-006; Product License/OAuth remain separately gated |
| D-007 | ADR-0011/0127 | executable CI matrix, provider-neutral gates, artifact provenance, branch/release enforcement — CI-01…CI-120 / P-007; direct branch reads show `main` + planning branch unprotected; repository rulesets UNKNOWN due 403 |
| D-008 | ADR-0012/0126 | build/externalization/toolchain/package comparison — BT-01…BT-112 / P-008 |
| D-009 | ADR-0086/0131 | Query compiler/cost/cache/security/provider evidence — QRY-01…QRY-168 / P-009; QP1–QP4 certifications separate |
| D-010 | ADR-0074/0093 | Relations DDL/cardinality/concurrency/lifecycle/scale — P-010; completeness audit current |
| D-011 | ADR-0082/0118 | Workflow revision/dedupe/concurrency/waits/approvals/recovery/scale — WF-01…WF-116 / P-011 |
| D-012 | ADR-0013/0015/0016/0019/0020/0057/0062/0066/0078/0090/0129 | Membership runtime/cache/revoke/teams/provider/protected-files/Multisite — MBR-01…MBR-160 / P-012; MB/PC certifications separate |
| D-013 | ADR-0021/0033/0043/0053/0056/0061/0064/0065/0084/0100/0130 | Backup artifact/crypto/Remote Copy/provider/restore evidence — BK-01…BK-180 / P-013; provider C0–C4/V3 certifications separate |
| D-014 | ADR-0044/0102 | Pro updater TUF verifier/key custody/metadata/package staging — TU-01…TU-44 |
| D-015 | ADR-0031/0108 | Frontend Dashboard routing/IDOR/cache/assets/permalink/Multisite — FD-01…FD-48 |
| D-016 | ADR-0035/0109 | Builder adapter registration/render/version/upgrade certification — BW-01…BW-50 |
| D-017 | ADR-0038/0110 | Post Status + domain-state execution/concurrency/history/migration — SM-01…SM-48 |
| D-018 | ADR-0052/0111 | XML-RPC method/parser/rate/compatibility/Multisite — XR-01…XR-48 |
| D-019 | ADR-0036/0089/0112 | Settings site/network/inheritance/Vault/REST/cache/import — ST-01…ST-48 |
| D-020 | ADR-0030/0096/0113 | User Profile identity/protected-binding/email/session/privacy/Multisite — UP-01…UP-48 |
| D-021 | ADR-0032/0097/0114 | Role/capability mutation/anti-lockout/recovery/Super Admin/cache — RA-01…RA-48 |
| D-022 | ADR-0028/0094/0115 | REST route/auth/scope/schema/idempotency/rate/cache/CORS/fuzz — REST-01…REST-52 |
| D-023 | ADR-0041/0095/0116 | Import/Export source/archive/map/checkpoint/rollback/export/scale — IM-01…IM-56 |
| D-024 | ADR-0025/0077/0117 | Forms revision/access/storage/idempotency/files/actions/Workflow/privacy/FRT topology — FM-01…FM-92 |
| D-025 | ADR-0026/0079/0120 | Notification rule/fan-out/dedupe/preferences/inbox/channel truth/NE topology — NT-01…NT-142 |
| D-026 | ADR-0027/0077/0121 | Chat authorization/revocation/idempotency/private-assets/search/realtime/privacy/CRT topology — CH-01…CH-142 |
| D-027 | ADR-0040/0055/0080/0122 | Connection/Vault/OAuth/Safe-HTTP/signature/replay/Event Inbox/provider I0–I5/EI topology — WC-01…WC-156 |

## B. Accepted paper/runtime-baseline summary

- Compatibility: WP 6.9 / PHP 8.3 current minimum candidates; WP 7.1 planning reference; CF does not certify floor.
- UI: WPE wrappers + WordPress-provided React; minimum WP cannot hard-depend on newer-only UI/theme capabilities.
- Build: `@wordpress/build` first candidate, `@wordpress/scripts` comparison/fallback, Vite only for proven unmet requirements.
- CI: layered provider-neutral FAST/FULL; untrusted PR secret isolation; BASELINE FAILURE/flaky truth; exact artifact provenance. Direct branch reads show `main` and planning branch unprotected; repository rulesets UNKNOWN because endpoint is plan/access restricted.
- Free↔Pro: Free kernel + separately distributed Pro; package/API/schema compatibility separate from Product Entitlement/service state.
- Definition: **D1/PT-C first benchmark baseline; D2/D3/D4 comparisons; DEF-01…DEF-144 fixed canonical evidence after ADR-0132.**
- Relations: R1/PT-D first; R2/PT-E mandatory; P-010 completeness audit current.
- Query: QP1 native-WP; QP2 Custom Table; QP3 Relations; QP4 remote; **QRY-01…QRY-168 fixed evidence under ADR-0131**.
- Field Storage: FS1–FS6.
- Custom Tables: CT1/PT-E first; CT2/PT-D mandatory; CT3 network-owned only.
- Settings: ST1/PT-A; ST2/PT-B; ST3 inheritance.
- Forms: FRT1/PT-D first; FRT2/PT-E mandatory.
- Chat: CRT1/PT-D first; CRT2/PT-E mandatory.
- Membership: M1/PT-D first; M2/PT-E mandatory; Enrollment authoritative, Entitlements derived; MBR fixed evidence.
- Protected files: PD1 correctness baseline; PD2/PD3 capability profiles; PC0–PC4 profile-scoped certifications.
- Notification/Email: NE1/PT-D first; NE2/PT-E mandatory.
- Event Inbox: EI1/PT-D first; EI2/PT-E mandatory.
- Workflow: WF1/PT-D first; WF2/PT-E mandatory.
- JobService: J1/J2/J3 physical profiles; Action Scheduler remains candidate only.
- Import: IR1/PT-D first; IR2/PT-E mandatory.
- Backup: manifest-first multipart logical bundle; BR1/BR2/BR3; H-B1 SHA-256; CMP0 fallback/CMP1 gzip comparison; ZIP convenience only; BK fixed restore-first evidence.
- Vault: V1/PT-C favored first; V2/PT-E + separate network Vault mandatory; VT fixed evidence.
- User/Profile and Role/Capability remain native WordPress identity/authorization authority with WPE security workflows.

All remain paper-only until applicable executable evidence certifies them.

## C. Fixed evidence protocols and current execution truth

| Protocol | Evidence state |
|---|---|
| P-001 Compatibility CF | **0/112; floor not certified** |
| P-002 UI | **0/104; runtime certification 0** |
| P-003 Job/Cron JS | **0/106; backend/Cron-DST certifications 0** |
| P-004 Definition DEF | **0/144; physical/runtime certifications 0; final D1–D4/DDL open** |
| P-005 Vault VT | **0/128; runtime/crypto certifications 0; security review not executed** |
| P-006 Free↔Pro FP | **0/144; certified artifact pairs 0** |
| P-007 CI | **0/120; workflows not verified; main + planning branches unprotected; repo rulesets UNKNOWN (403)** |
| P-008 Build BT | **0/112; canonical toolchain not selected** |
| P-009 Query QRY | **0/168; runtime certifications 0; QP1/QP2/QP3/QP4 certifications 0** |
| P-010 Relations | **0 executed; completeness audit current** |
| P-011 Workflow WF | **0/116; runtime certification 0** |
| P-012 Membership MBR | **0/160; runtime certification 0; 4 BE3 / 0 MB-certified; 0 PC1+** |
| P-013 Backup BK | **0/180; runtime certification 0; 34 targets / 0 C-certified / 0 C3 Supported; V3 certifications 0** |
| Forms FM | **0/92** |
| Notification NT | **0/142** |
| Chat CH | **0/142; realtime/search certifications 0** |
| Webhooks/Connections/Event Inbox WC | **0/156; I4/I5 0; Safe HTTP/EI runtime unverified** |
| OAuth OA | **0/32** |
| TUF updater TU | **0/44** |
| Dashboard Widgets DW | **0/36** |
| Admin Menu AM | **0/40** |
| Protector PR | **0/44** |
| Reset RM | **0/48** |
| Watermarker/Media WM | **0/48** |
| Frontend Dashboard FD | **0/48** |
| Builder Widgets BW | **0/50; runtime certifications 0** |
| Status Manager SM | **0/48** |
| XML-RPC XR | **0/48** |
| Settings ST | **0/48** |
| User Profile UP | **0/48** |
| Role/Capability RA | **0/48** |
| REST Builder REST | **0/52** |
| Import/Export IM | **0/56** |
| Email provider evidence | **6 EE3 / 0 ET-certified** |
| Site Lifecycle | **0/40** |
| Multisite | **0 MS1+** |
| Remote privacy | **0/30** |

## D. P-010 Relations — current highest-priority open blocker

Accepted architecture already includes:
- explicit Relation Definition with direction/cardinality/endpoint types;
- R1/PT-D first physical benchmark baseline;
- R2/PT-E mandatory comparison;
- relation edges are runtime data, not Definition payload EAV;
- reverse lookup/cardinality/uniqueness/delete semantics require physical enforcement or deterministic reconciliation;
- Query P-009 QP3 consumes Relations but does not replace Relations' own integrity/concurrency evidence.

WP16 must audit the existing canonical/supplementary P-010 protocols against current architecture for:
- one-to-one/one-to-many/many-to-many cardinality and race enforcement;
- duplicate edge prevention and deterministic identity;
- direction/symmetric semantics;
- pivot typed data and schema evolution;
- ordered relation collision/reordering semantics;
- endpoint deletion/detach/orphan/reconciliation;
- bulk attach/detach/import idempotency;
- Relation Definition revision/change impact on existing edges;
- Query/batch traversal/N+1 boundaries;
- authorization and field/endpoint visibility;
- cache/invalidation/reverse Used-by dependencies;
- Site Backup/restore/clone/transfer;
- Multisite site/network scope and cross-site denial;
- 100k/1M+ edge scale and hot/high-degree endpoints;
- migration/physical profile recovery and independent security/data-integrity review.

Current Relations truth: **P-010 executed 0; existing protocol audit pending**.

## E. Definition boundary retained after ADR-0132

- Definition identity ≠ Revision ≠ Dependency edge ≠ compiled cache.
- Draft/current and published revisions may differ safely.
- historical revisions are immutable.
- pointers must remain same-Definition valid.
- portable identity uses UUID/logical refs, never local numeric IDs.
- explicit site/network scope remains security truth under PT-C.
- unknown future schema becomes degraded/read-only.
- module disable/Pro expiry preserves configuration.
- import key collision does not prove identity.
- archive/tombstone ≠ purge.
- Backup/restore/clone/transfer scope must be preserved/remapped intentionally.
- event/cache success follows durable commit.

Current: **DEF 0/144; final D1–D4 profile/DDL open**.

## F. Query boundary retained after ADR-0131

- no raw SQL/arbitrary callback/eval in normal Query AST;
- parameters are typed/untrusted values; identifiers are registered references;
- unsupported provider semantics fail before execution;
- row/resource/field/count/scope authorization stays server-side;
- protected persistent cache must model authorization/invalidation dependencies;
- stale protected cache after committed revoke is a security failure;
- cursor is untrusted and revision/provider/scope/order/parameter/auth bound;
- QP4 remote results reauthorize locally;
- normal relation/list N+1 is stop-line.

Current: **QRY 0/168; QP1–QP4 certifications 0**.

## G. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine/refine bounded executable protocol when proof is required.
4. Prefer in-place canonical refinement over duplicate protocol documents.
5. Do not install, compile, migrate, benchmark, test, contact providers/services, send mail, run queues, generate signing/Vault/backup keys, execute OAuth/crypto, create/extract backup archives, mutate runtime data or transfer data before explicit owner consent.
6. Never promote paper/static evidence to runtime/provider certification.
7. Keep governance, checkpoint and Draft PR synchronized.

## Next planning-only priorities

1. **P-010 Relations evidence completeness / physical proof audit** — current `P0-M00-WP16`.
2. Reassess remaining unresolved shared/surface blockers after Relations.
3. Keep all fixed protocols and certification boundaries intact.

Production development authorization remains **NOT GRANTED**.