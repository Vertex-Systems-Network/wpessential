# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: 2026-08-28

## Global rule

A surface can be Exhaustive and architecturally Accepted while still be technically unverified and unauthorized.

Implementation requires exhaustive option specification, accepted semantics, physical/runtime/security/performance evidence, acceptance tests, platform/toolchain gates, explicit owner consent under ADR-0014 and a bounded implementation checkpoint.

Current owner consent: **NOT GRANTED**. Therefore **0/31 Authorized**.

## Shared blockers

| Area | State | Protocol |
|---|---|---|
| WP/PHP/DB compatibility | ADR-0002 Proposed | P-001 |
| UI/design system | ADR-0005 Proposed | P-002 |
| Job Service concrete backend/history | ADR-0059 + ADR-0068 semantics/packaging Accepted; physical/runtime unverified | P-003 |
| Definition Repository physical DDL/indexes | ADR-0049/0069/0071 + ADR-0073 D1 baseline | P-004 |
| Secrets Vault implementation | ADR-0048 + ADR-0069 hierarchy/scope accepted; exact envelope/storage pending | P-005 |
| Free↔Pro / Product License runtime | ADR-0010 + ADR-0070/0072/0076 paper contract | P-006 |
| CI | ADR-0011 Proposed | P-007 |
| Build toolchain | ADR-0012 Proposed | P-008 |
| Query compiler/runtime | Query AST accepted, execution evidence pending | P-009 |
| Relations physical topology | ADR-0074 R1/PT-D baseline; R2/PT-E mandatory comparison | P-010 |
| Workflow runtime | PT-D candidate, exact run/step/wait profile pending | P-011 |
| Membership runtime | ADR-0078 M1/PT-D baseline; M2/PT-E mandatory comparison | P-012 |
| Backup runtime/providers | logical/crypto/provider architecture accepted; runtime evidence absent | P-013 |
| Forms/Chat physical topology | ADR-0077 FRT1/CRT1 PT-D; FRT2/CRT2 PT-E mandatory comparisons | domain evidence gates |
| Notification/Email operational topology | ADR-0079 NE1/PT-D; NE2/PT-E mandatory comparison | runtime + ET evidence |
| Event Inbox physical topology | ADR-0080 EI1/PT-D; EI2/PT-E mandatory comparison | runtime + I4/I5 evidence |
| Audit | ADR-0081 AU1/PT-D favored; exact DDL/retention/integrity evidence absent | runtime evidence |
| Multisite lifecycle/runtime | ADR-0069/0071/0075 accepted; MS0–MS4 protocol, 0 fixtures | P-001 + module gates |
| Owner consent | ADR-0014 Accepted, consent absent | blocks all executable work |

## Per-surface readiness

All 31 surfaces remain **Exhaustive / Unauthorized**. Benchmark baseline acceptance never means final schema approval or runtime verification.

| # | Surface | Product options | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration + ADR-0069 + Definition D1 ADR-0073 + lifecycle ADR-0075 | compatibility, P-004 DDL, UI/build, provisioning/site-network fixtures | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration + ADR-0069 + Definition D1 ADR-0073 + lifecycle ADR-0075 | compatibility, P-004 DDL, UI/build, provisioning/rewrite fixtures | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0022 + ADR-0069/0071/0075 | storage topology/scale/query/revision/migration/Vault/lifecycle | No |
| 4 | Relations Builder | Exhaustive | typed edge semantics + ADR-0069/0071 + R1/PT-D ADR-0074 + lifecycle | R1 vs R2 exact indexes/cardinality/concurrency/high-degree/site cleanup — P-010 | No |
| 5 | Status Manager | Exhaustive | ADR-0038 + ADR-0069/0075 | WP UI/migration/state history/concurrency/site lifecycle | No |
| 6 | Custom Query Builder | Exhaustive | Query AST + ADR-0069/0071 | compiler/cost/cache/security/network aggregate/storage scale — P-009 | No |
| 7 | Custom Tables Builder | Exhaustive | ADR-0023 + ADR-0069/0071/0075 | PT-D/PT-E compiler/version/locking/backfill/recovery/teardown | No |
| 8 | Admin Columns Builder | Exhaustive | list/source + ADR-0069 | adapters/N+1/performance/write/site-template proof | No |
| 9 | Dynamic Listings/Templates | Exhaustive | ADR-0035/0039/0069 | Query/protected pagination/cache/site-network fixtures | No |
| 10 | Dashboard Widgets Manager | Exhaustive | ADR-0051/0069 | Site vs Network Dashboard/remote schema/iframe/XSS/CSP | No |
| 11 | Custom Admin Menu Builder | Exhaustive | ADR-0037/0069 | site/network hook conflicts/recovery/performance | No |
| 12 | Settings Page Builder | Exhaustive | ADR-0036/0069 + PT-A/PT-B ADR-0071 | physical storage/autoload/concurrency/inheritance/Vault/REST | No |
| 13 | Frontend Dashboard Builder | Exhaustive | ADR-0031/0035/0069 | routing/domain/multisite/IDOR/cache/assets/builder | No |
| 14 | User Profile Builder | Exhaustive | ADR-0030 + ADR-0069/0071/0075 | global identity vs site fields/credential/session/removal | No |
| 15 | Membership System | Exhaustive | Membership ADRs + ADR-0069/0071/0075 + **ADR-0078 M1/PT-D, M2/PT-E** | exact Enrollment/Entitlement/generation/locking/files, MB0–MB5, revoke/restore/scale — P-012 | No |
| 16 | Builder Widgets Builder | Exhaustive | ADR-0035/0069 | renderer/nesting/bindings/assets/accessibility/site-network library | No |
| 17 | Forms & Workflow Builder | Exhaustive | ADR-0025 + Job/Multisite + **ADR-0077 FRT1/PT-D vs FRT2/PT-E**; Workflow PT-D candidate | Form exact topology + Workflow run/step/wait/Job backend/lifecycle — P-003/P-011 | No |
| 18 | Cron Job Builder | Exhaustive | ADR-0059/0068/0069/0071/0075 | AS/backend, Job physical history, runner/DST/overlap/fairness/network/lifecycle — P-003 | No |
| 19 | Notification System | Exhaustive | ADR-0026 + Job/Multisite/provider semantics + **ADR-0079 NE1/PT-D vs NE2/PT-E** | exact schema/fan-out/dedupe/unknown-outcome/ET provider/Job/lifecycle | No |
| 20 | Emails Builder | Exhaustive | ADR-0029/0058/0063/0067 + **ADR-0079** | renderer/client + transport/evidence exact schema + ET0–ET5 + restore/lifecycle | No |
| 21 | Message & Chat System | Exhaustive | ADR-0027/0069 + **ADR-0077 CRT1/PT-D vs CRT2/PT-E** | exact indexes/search/transport/private assets/revocation/noisy-neighbor/retention | No |
| 22 | REST API Builder | Exhaustive | ADR-0028/0069 | compiler/auth/rate/CORS/cache/fuzz/cross-site IDOR/scale | No |
| 23 | Webhooks & Connections | Exhaustive | ADR-0040/0055 + Job/Multisite + **ADR-0080 EI1/PT-D vs EI2/PT-E** | I0–I5 provider evidence, exact Event Inbox claim/dedupe/retention/routing/lifecycle | No |
| 24 | Backup Manager | Exhaustive | ADR-0021/0033/0043/0053/0056/0061/0064/0065 + Multisite/lifecycle | Remote Copy physical schema, provider C0–C4, site/network restore — P-003/P-013 | No |
| 25 | Reset Manager | Exhaustive | ADR-0047/0059/0068/0069/0071/0075 | recovery schema/checkpoints/crash/Job/site-network lifecycle | No |
| 26 | Import / Export | Exhaustive | ADR-0041/0059/0068/0069/0071/0075 | run schema/checkpoints/source/media/scope/transfer remap | No |
| 27 | Protector | Exhaustive | ADR-0045/0069 | hook/atomic rate/proxy/login/header/network-floor | No |
| 28 | Watermarker / Media Rules | Exhaustive | ADR-0046/0059/0068/0069/0075 | registry/image/offload/concurrency/Job/media lifecycle | No |
| 29 | XML-RPC Manager | Exhaustive | ADR-0052/0069 | methods/hooks/parser/complete-deny/Jetpack/multisite | No |
| 30 | Role & Capability Manager | Exhaustive | ADR-0032/0069/0075 | classifier/anti-lockout/Super Admin/site-removal/CLI recovery | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | ADR-0034/0042/0044/0050/0054/0060/0069/0070/0072/0075/0076 + PT-F ADR-0071 | exact OpenAPI/OAuth/idempotency/ETag/TUF/privacy/allocation/clone-transfer/service evidence | No |

## Cross-cutting accepted architecture that still needs evidence

- Definition Repository: PT-C + D1 future baseline; exact P-004 evidence absent.
- Relations: R1/PT-D + R2/PT-E; P-010 absent.
- Forms/Chat: FRT1/CRT1 PT-D + FRT2/CRT2 PT-E; exact physical benchmarks absent.
- Membership: M1/PT-D + M2/PT-E; exact P-012 evidence absent; **4 BE3 / 0 MB-certified**.
- Notification/Email: NE1/PT-D + NE2/PT-E; **6 EE3 / 0 ET-certified**.
- Event Inbox: EI1/PT-D + EI2/PT-E; **0 I4/I5 event certifications**.
- Audit: AU1/PT-D favored; exact DDL/retention/failure/integrity evidence absent and local DB is not a tamper-proof claim.
- Site Lifecycle: paper architecture + 40 fixture protocol; **0 lifecycle fixtures**.
- Product License: identity/resource/state/HTTP principles accepted; **0 API/service fixtures**.
- Backup: **34 targets / 0 C-certified / 0 C3 Supported**.
- Multisite: **31/31 scopes mapped / 0 MS1+**.
- Remote Service privacy: **30 fixtures / 0 executed**.
- Action Scheduler: reviewed candidate only; **P-003 0 executed**.

## Recommended implementation order after future consent

1. P-001 compatibility/Multisite + P-003 Job physical/backend + P-004 Definition + P-005 Vault evidence;
2. Kernel/Scope/Site Lifecycle/Registry/Definition/Policy/Abilities/Assets/Audit/Vault/JobService;
3. CPT/Taxonomy;
4. Fields → Relations P-010 → Query P-009 → Tables/Columns → Blueprint → Listings/Status;
5. admin/site UX modules;
6. Membership P-012 + MB certification;
7. Forms + Workflow P-011 → Notification/Email NE evidence + ET certification;
8. REST/Connections/Event Inbox EI evidence/Import;
9. Backup core/Remote Copy/provider/site-network restore P-013 → destructive/security operations;
10. Chat after CRT1/CRT2 evidence;
11. Account/Support/Updater/Product License HTTP client/service under ADR-0076;
12. AI only over certified scope-aware Abilities/Blueprints;
13. ecosystem/large-network scale.

## Current conclusion

**Product specification:** 31/31 Exhaustive.  
**Architecture:** accepted decisions through ADR-0081; physical/runtime evidence incomplete.  
**Authorized:** 0/31.  
**Implemented:** none.  
**Verified runtime:** none.  
**Multisite runtime certification:** 0 MS1+.  
**Definition D1 / Relations R1 / Forms FRT1 / Chat CRT1 / Membership M1 / Notification NE1 / Event Inbox EI1 / Audit AU1 executable benchmarks:** 0.  
**Membership billing:** 0 MB-certified.  
**Email:** 0 ET-certified.  
**Event adapters:** 0 I4/I5 certified.  
**Backup:** 0 C-certified.  
**Site Lifecycle fixtures:** 0/40.  
**Product License API/service fixtures:** 0.

Allowed work remains planning/research/documentation only until explicit owner development consent.