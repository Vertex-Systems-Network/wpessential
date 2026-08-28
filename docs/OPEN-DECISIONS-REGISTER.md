# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation/evidence only. Accepted evidence decisions are preserved through **ADR-0130**. Architecture acceptance never implies runtime certification or owner development authorization.

All executable work remains blocked by ADR-0014 until explicit scoped owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002/0069/0075/0123 | WP/PHP/DB compatibility + Multisite/site lifecycle — CF-01…CF-112 / P-001 |
| D-002 | ADR-0005/0125 | UI runtime/WP-min capability/accessibility/RTL/scoped-assets/React isolation — UI-01…UI-104 / P-002 |
| D-003 | ADR-0059/0068/0083/0119 | Job backend/Action Scheduler/Cron/DST/fairness/claims/Multisite — JS-01…JS-106 / P-003 |
| D-004 | ADR-0073/0092 | Definition D1/D2/D3/D4 exact DDL/index/locking/migration — P-004 |
| D-005 | ADR-0048/0085/0124 | Vault crypto/AAD/envelope/rotation/recovery/redaction/Multisite/security review — VT-01…VT-128 / P-005 |
| D-006 | ADR-0010/0070/0072/0076/0091/0128 | Free↔Pro package/Platform API/schema boot compatibility — FP-01…FP-144 / P-006; Product License/OAuth remain separately gated |
| D-007 | ADR-0011/0127 | executable CI matrix, provider-neutral gates, artifact provenance, branch/release enforcement — CI-01…CI-120 / P-007; direct branch reads show `main` + planning branch unprotected; repository rulesets UNKNOWN due 403 |
| D-008 | ADR-0012/0126 | build/externalization/toolchain/package comparison — BT-01…BT-112 / P-008 |
| D-009 | ADR-0086 | Query compiler/cost/cache/security/storage-adapter evidence — P-009; dedicated fixed protocol pending WP14 |
| D-010 | ADR-0074/0093 | Relations DDL/cardinality/concurrency/scale — P-010 |
| D-011 | ADR-0082/0118 | Workflow revision/dedupe/concurrency/waits/approvals/recovery/scale — WF-01…WF-116 / P-011 |
| D-012 | ADR-0013/0015/0016/0019/0020/0057/0062/0066/0078/0090/0129 | Membership runtime/cache/revoke/teams/provider/protected-files/Multisite — MBR-01…MBR-160 / P-012; MB/PC certifications separate |
| D-013 | ADR-0021/0033/0043/0053/0056/0061/0064/0065/0084/0100/0130 | Backup artifact/crypto/Remote Copy/provider/restore evidence — BK-01…BK-180 / P-013; provider C0–C4/V3 certifications remain runtime-gated |
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

- Compatibility: WP 6.9 / PHP 8.3 current minimum candidates; WP 7.1 planning reference; CF protocol does not certify the floor.
- UI: WPE wrappers + WordPress-provided React; WP 6.9 must not hard-depend on WP 7.1-only theme/UI capabilities; experimental WordPress UI/routes/widgets are not foundational.
- Build: `@wordpress/build` first candidate, `@wordpress/scripts` comparison/fallback, Vite only for proven unmet requirements; BT fixed evidence.
- CI: layered provider-neutral FAST/FULL gates; untrusted PR secret isolation; BASELINE FAILURE/flaky truth; exact artifact provenance; CI fixed evidence. Direct GitHub branch reads currently show `main` and `planning/master-architecture` unprotected; repository rulesets remain UNKNOWN because ruleset reads are plan/access restricted.
- Free↔Pro: Free kernel + separately distributed Pro; Platform API/schema/package compatibility separate from Product Entitlement/service state; FP fixed evidence.
- Definition: D1/PT-C first; D2/D3/D4 comparisons.
- Relations: R1/PT-D first; R2/PT-E mandatory.
- Query: QP1 native-WP; QP2 Custom Table; QP3 Relations; QP4 remote. Fixed P-009 adversarial evidence protocol still pending.
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
- Backup: manifest-first multipart logical bundle; BR1/BR2/BR3 Remote Copy profiles; H-B1 SHA-256 stored-byte integrity; CMP0 fallback/CMP1 gzip comparison; ZIP convenience only; BK fixed restore-first evidence.
- Vault: V1/PT-C favored first; V2/PT-E + separate network Vault mandatory; VT fixed evidence.
- User/Profile and Role/Capability remain native WordPress identity/authorization authority with WPE security workflows.

All are paper-only until their applicable executable evidence certifies them.

## C. Fixed evidence protocols and current execution truth

| Protocol | Evidence state |
|---|---|
| P-001 Compatibility CF | **0/112; floor not certified** |
| P-002 UI | **0/104; runtime certification 0** |
| P-003 Job/Cron JS | **0/106; backend/Cron-DST certifications 0** |
| P-004 Definition | **0 executed** |
| P-005 Vault VT | **0/128; runtime/crypto certifications 0; security review not executed** |
| P-006 Free↔Pro FP | **0/144; certified artifact pairs 0** |
| P-007 CI | **0/120; workflow implementation not verified; direct branch reads show main + planning unprotected; repository rulesets UNKNOWN (403)** |
| P-008 Build BT | **0/112; canonical toolchain not selected** |
| P-009 Query | **0 executed; dedicated fixed protocol pending** |
| P-010 Relations | **0 executed** |
| P-011 Workflow WF | **0/116; runtime certification 0** |
| P-012 Membership MBR | **0/160; runtime certification 0; 4 BE3 / 0 MB-certified; 0 PC1+** |
| P-013 Backup BK | **0/180; runtime certification 0; 34 provider targets / 0 C-certified / 0 C3 Supported; V3 certifications 0** |
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

## D. P-009 Query — current highest-priority open blocker

Accepted paper architecture in ADR-0086 already defines:
- QP1 WordPress-native query path as first baseline;
- QP2 Custom Table adapter for owned physical tables;
- QP3 Relations adapter for relationship predicates/traversal;
- QP4 remote source path only through separately certified adapters;
- typed Query AST/compiler direction rather than arbitrary SQL;
- cost/cache/index behavior as evidence-gated, not assumed.

Still open for a fixed P-009 executable evidence contract:
- AST/schema validation and version/revision pinning;
- server-side Policy placement before query/result disclosure;
- data-source/adapter capability negotiation and unsupported-operator failure;
- parameter/identifier/operator safety and no raw SQL escape hatch;
- cost estimation, hard budgets, time/row/scan limits and hostile-complexity rejection;
- deterministic ordering, tie-breakers, cursor/offset semantics and pagination drift;
- aggregate/count/facet leakage boundaries;
- cache-key scope, auth context, invalidation dependencies and stale protected-result prevention;
- native WP/meta/tax/user/term semantics;
- Custom Table typed columns/indexes/null/collation behavior;
- Relations direction/cardinality/pivot metadata/traversal and N+1 avoidance;
- remote-source pagination/rate/unknown-outcome/secret boundaries;
- Multisite site/network scope isolation;
- revision/concurrency/change-between-pages behavior;
- scale/query-plan/latency/memory benchmarks and negative requirements.

Current Query truth: **P-009 executed 0; dedicated fixed protocol not yet accepted**.

## E. Backup boundary retained after ADR-0130

- generated/uploaded ≠ restore-ready;
- V2 Remote Verified ≠ V3 Restore Tested;
- required missing/corrupt data cannot be shown as fully verified;
- provider success/checksum does not replace WPE manifest/integrity/restore truth;
- static SE evidence never grants C certification;
- the only disaster-recovery key cannot exist solely beside/inside ciphertext;
- hostile archive/parser/path input is bounded before destructive restore;
- remote commit/delete unknown outcomes require reconciliation;
- restore/clone reauthorizes Vault/provider/commercial state and cannot resurrect stale Membership access;
- Reset/migration/destructive flows requiring a restore point must verify the configured tier before commit.

Current: **BK 0/180; 34 targets / 0 C-certified / 0 C3 Supported; V3 certifications 0**.

## F. Membership boundary retained after ADR-0129

- provider billing status/event is source fact, never direct access authority;
- stale allow after committed revoke/hard deny is a security defect;
- ordinary access check makes no provider API call;
- Role sync remains optional/provenance-safe;
- direct origin byte bypass defeats a protected-file claim;
- BE3 static evidence is not MB certification;
- one protected-file PC profile does not certify another.

Current: **MBR 0/160; 4 BE3 / 0 MB-certified; 0 PC1+**.

## G. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine bounded executable protocol when proof is required.
4. Do not install, compile, migrate, benchmark, test, contact providers/services, send mail, run queues, generate signing/Vault/backup keys, execute OAuth/crypto, create/extract backup archives, mutate runtime data or transfer data before explicit owner consent.
5. Never promote paper/static evidence to runtime/provider certification.
6. Keep governance, checkpoint and Draft PR synchronized.

## Next planning-only priorities

1. **P-009 Query compiler/cost/cache/security evidence refinement** — current `P0-M00-WP14`.
2. Reassess P-004 Definition exact physical evidence and P-010 Relations evidence after Query protocol closes.
3. Keep all fixed protocols and certification boundaries intact.

Production development authorization remains **NOT GRANTED**.