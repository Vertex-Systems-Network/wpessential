# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: 2026-08-28

## Global rule

A surface can be Exhaustive and architecturally Accepted while still being technically unverified and unauthorized.

Implementation requires exhaustive option specification, accepted semantics, physical/runtime/security/performance evidence, acceptance tests, platform/toolchain gates, explicit owner consent under ADR-0014 and a bounded implementation checkpoint.

Current owner consent: **NOT GRANTED**. Therefore **0/31 Authorized**.

## Shared blockers

| Area | State | Protocol |
|---|---|---|
| WP/PHP/DB compatibility | ADR-0002 Proposed | P-001 |
| UI/design system | ADR-0005 Proposed | P-002 |
| Job Service concrete backend | ADR-0059 + ADR-0068 Accepted semantics/packaging; Action Scheduler runtime Proposed | P-003 |
| Definition Repository physical DDL/indexes | ADR-0049/0069/0071 + ADR-0073 D1 baseline; exact DDL pending | P-004 |
| Secrets Vault implementation | ADR-0048 + ADR-0069 hierarchy/scope accepted; exact envelope pending | P-005 |
| Free↔Pro / Product License runtime | ADR-0010 + ADR-0070/0072/0076 paper contract; runtime/service pending | P-006 |
| CI | ADR-0011 Proposed | P-007 |
| Build toolchain | ADR-0012 Proposed | P-008 |
| Relations physical topology | ADR-0074 R1/PT-D baseline; R2/PT-E mandatory comparison | P-010 |
| Multisite lifecycle/runtime | ADR-0069/0071/0075 accepted; MS0–MS4 protocol, 0 fixtures | P-001 + module gates |
| Owner consent | ADR-0014 Accepted, consent absent | blocks all |

## Per-surface readiness

All 31 surfaces remain **Exhaustive / Unauthorized**. ADR-0073 and ADR-0074 are future benchmark baselines only. ADR-0075 is lifecycle coordination only. ADR-0076 is a future API contract only.

| # | Surface | Product options | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration + ADR-0069 + Definition D1 ADR-0073 + lifecycle ADR-0075 | compatibility, P-004 DDL, UI/build, provisioning/site-network fixtures | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration + ADR-0069 + Definition D1 ADR-0073 + lifecycle ADR-0075 | compatibility, P-004 DDL, UI/build, provisioning/rewrite fixtures | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0022 + ADR-0069/0071/0075 | storage topology/scale/query/revision/migration/Vault/lifecycle | No |
| 4 | Relations Builder | Exhaustive | typed edge semantics + ADR-0069/0071 + R1/PT-D ADR-0074 + lifecycle ADR-0075 | R1 vs R2 indexes/cardinality/concurrency/high-degree/site cleanup — P-010 | No |
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
| 15 | Membership System | Exhaustive | ADRs 0013/15/16/19/20/24 + 0057/0062/0066/0069 + PT-D ADR-0071 + lifecycle ADR-0075 | Enrollment schema/cache/files, MB0–MB5, Multisite/teardown — P-012 | No |
| 16 | Builder Widgets Builder | Exhaustive | ADR-0035/0069 | renderer/nesting/bindings/assets/accessibility/site-network library | No |
| 17 | Forms & Workflow Builder | Exhaustive | ADR-0025 + 0059/0068/0069/0071/0075 | Entry topology/Workflow/Job backend/lifecycle — P-003/P-011 | No |
| 18 | Cron Job Builder | Exhaustive | ADR-0059/0068/0069/0071/0075 | AS backend/runner/DST/overlap/fairness/network/lifecycle — P-003 | No |
| 19 | Notification System | Exhaustive | ADR-0026 + 0058/0059/0063/0067/0068/0069/0071/0075 | schema/fan-out/dedupe/ET provider/Job/lifecycle | No |
| 20 | Emails Builder | Exhaustive | ADR-0029 + 0058/0063/0067/0069/0071/0075 | renderer/client + Delivery/Attempt/Event + ET0–ET5 + lifecycle | No |
| 21 | Message & Chat System | Exhaustive | ADR-0027/0069 + PT-D/PT-E ADR-0071 + lifecycle ADR-0075 | indexes/search/transport/private assets/revocation/topology | No |
| 22 | REST API Builder | Exhaustive | ADR-0028/0069 | compiler/auth/rate/CORS/cache/fuzz/cross-site IDOR/scale | No |
| 23 | Webhooks & Connections | Exhaustive | ADR-0040/0055/0059/0068/0069/0071/0075 | I0–I5/Event Inbox DDL/Job/shared connection cleanup | No |
| 24 | Backup Manager | Exhaustive | ADR-0021/0033/0043/0053/0056/0059/0061/0064/0065/0068/0069/0071/0075 | provider C0–C4, recovery point, site/network restore — P-003/P-013 | No |
| 25 | Reset Manager | Exhaustive | ADR-0047/0059/0068/0069/0071/0075 | recovery schema/checkpoints/crash/Job/site-network lifecycle | No |
| 26 | Import / Export | Exhaustive | ADR-0041/0059/0068/0069/0071/0075 | run schema/checkpoints/source/media/scope/transfer remap | No |
| 27 | Protector | Exhaustive | ADR-0045/0069 | hook/atomic rate/proxy/login/header/network-floor | No |
| 28 | Watermarker / Media Rules | Exhaustive | ADR-0046/0059/0068/0069/0075 | registry/image/offload/concurrency/Job/media lifecycle | No |
| 29 | XML-RPC Manager | Exhaustive | ADR-0052/0069 | methods/hooks/parser/complete-deny/Jetpack/multisite | No |
| 30 | Role & Capability Manager | Exhaustive | ADR-0032/0069/0075 | classifier/anti-lockout/Super Admin/site-removal/CLI recovery | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | ADR-0034/0042/0044/0050/0054/0060/0069/0070/0072/0075/**0076** + PT-F ADR-0071 | exact OpenAPI schemas/OAuth/idempotency/ETag/TUF/privacy/allocation/clone-transfer/service evidence | No |

## Cross-cutting accepted architecture that still needs evidence

- Definition Repository: PT-C + D1 future baseline; exact P-004 evidence absent.
- Relations: R1/PT-D future baseline + R2/PT-E comparison; P-010 absent.
- Site Lifecycle Coordinator: paper architecture only; 0 hooks/fixtures.
- Product License: identity/resource/state + HTTP/OpenAPI principles accepted; **0 API/service fixtures**.
- Backup: 34 targets, **0 C-certified**.
- Membership Billing: 4 profiles, **0 MB-certified**.
- Email: 6 profiles, **0 ET-certified**.
- Multisite: 31/31 product scopes mapped, **0 MS1+**.
- Remote Service privacy: 30 fixtures documented, **0 executed**.
- Action Scheduler: reviewed candidate only, P-003 unexecuted.

## Recommended implementation order after future consent

1. P-001/P-003/P-004/P-010 platform physical evidence;
2. Kernel/Scope/Site Lifecycle/Registry/Definition/Policy/Abilities/Assets/Audit/Vault/JobService;
3. CPT/Taxonomy;
4. Fields → Relations → Query → Tables/Columns → Blueprint → Listings/Status;
5. Admin/site UX modules;
6. Membership + MB certification;
7. Forms/Workflow → Notifications → Email certification;
8. REST/Connections/Event Inbox/Import;
9. Backup/provider/site-network restore → destructive/security operations;
10. Chat after PT-D/PT-E evidence;
11. Account/Support/Updater/Product License HTTP client/service under ADR-0076;
12. AI only over certified scope-aware Abilities/Blueprints;
13. ecosystem/large-network scale.

## Current conclusion

**Product specification:** 31/31 Exhaustive.  
**Architecture:** accepted decisions through ADR-0076; physical/runtime evidence incomplete.  
**Multisite runtime certification:** 0 MS1+.  
**Definition D1 benchmark execution:** 0.  
**Relations R1 benchmark execution:** 0.  
**Site Lifecycle runtime fixtures:** 0.  
**Product License API/service fixtures:** 0.  
**Implemented:** none.  
**Verified runtime:** none.  
**Authorized:** 0/31.

Allowed work remains planning/research/documentation only until explicit owner development consent.
