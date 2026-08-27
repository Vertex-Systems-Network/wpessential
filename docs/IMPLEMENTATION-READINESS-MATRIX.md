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
| Job Service backend/history | ADR-0059/0068 + ADR-0083 J1/J2/J3 paper mapping; backend/runtime unverified | P-003 |
| Definition Repository | ADR-0073 D1/PT-C + ADR-0092 exact paper protocol; exact DDL evidence pending | P-004 |
| Secrets Vault | ADR-0048 hierarchy + ADR-0085 V1/V2 physical profile; crypto/runtime unverified | P-005 |
| Free↔Pro / Product License | ADR-0070/0072/0076/0091 paper contract; runtime/service pending | P-006 |
| CI | ADR-0011 Proposed | P-007 |
| Build toolchain | ADR-0012 Proposed | P-008 |
| Query | ADR-0086 QP1–QP4; compiler/cache/cost/security evidence pending | P-009 |
| Relations | ADR-0074 R1/PT-D + ADR-0093 exact evidence protocol; R2/PT-E mandatory | P-010 |
| Workflow | ADR-0082 WF1/PT-D; WF2/PT-E mandatory | P-011 |
| Membership | ADR-0078 M1/PT-D; M2/PT-E mandatory | P-012 |
| Backup | ADR-0084 BR1/BR2/BR3; provider/artifact/runtime unverified | P-013 |
| Forms/Chat | ADR-0077 FRT1/CRT1 PT-D, PT-E comparisons | domain evidence |
| Notification/Email | ADR-0079 NE1/PT-D, NE2/PT-E | runtime + ET evidence |
| Event Inbox | ADR-0080 EI1/PT-D, EI2/PT-E | runtime + I4/I5 evidence |
| Audit | ADR-0081 AU1/PT-D favored | exact DDL/retention/integrity evidence |
| REST | ADR-0094 RE1 + RI1/RI2 | route/rate/idempotency/cache evidence |
| Import | ADR-0095 IR1/PT-D + IR2/PT-E | exact runtime/recovery evidence |
| User/Profile | ADR-0096 native WordPress authority + WPE security workflow layer | identity/session/privacy evidence |
| Role/Capability | ADR-0097 native WordPress authority + WPE anti-lockout/recovery layer | effective-cap/recovery evidence |
| Multisite/Site Lifecycle | ADR-0069/0071/0075; 40-fixture protocol | P-001 + module gates |
| Owner consent | ADR-0014 Accepted, consent absent | blocks all executable work |

## Per-surface readiness

All 31 surfaces remain **Exhaustive / Unauthorized**. Paper baseline acceptance is not final schema approval, implementation or certification.

| # | Surface | Product options | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration + Definition D1 + Multisite/lifecycle | P-001/P-004, UI/build, provisioning/rewrite fixtures | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration + Definition D1 + Multisite/lifecycle | P-001/P-004, UI/build, rewrite fixtures | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0087 FS1–FS6 | field adapter index/projection/migration/Vault evidence | No |
| 4 | Relations Builder | Exhaustive | ADR-0074 R1/PT-D vs R2/PT-E + ADR-0093 protocol | P-010 DDL/cardinality/concurrency/high-degree/lifecycle | No |
| 5 | Status Manager | Exhaustive | ADR-0038 + scoped lifecycle | WP UI/migration/state history/concurrency | No |
| 6 | Custom Query Builder | Exhaustive | ADR-0086 Query compiler matrix + Multisite | P-009 compiler/cost/cache/IDOR/storage-adapter scale | No |
| 7 | Custom Tables Builder | Exhaustive | ADR-0088 CT1/PT-E vs CT2/PT-D | compiler/version/locking/backfill/recovery/migration | No |
| 8 | Admin Columns Builder | Exhaustive | typed list/source semantics | dedicated N+1/batching/sort/filter/write/export evidence pending | No |
| 9 | Dynamic Listings/Templates | Exhaustive | Blueprint + Query → SSR architecture | dedicated protected pagination/cache/SSR operational profile pending | No |
| 10 | Dashboard Widgets Manager | Exhaustive | trusted structured-content architecture | Site/Network Dashboard, remote schema/iframe/XSS/CSP evidence | No |
| 11 | Custom Admin Menu Builder | Exhaustive | transformation/safe-mode architecture | site/network hook conflict/recovery/performance | No |
| 12 | Settings Page Builder | Exhaustive | ADR-0089 ST1/PT-A + ST2/PT-B + ST3 | autoload/inheritance/concurrency/Vault/REST evidence | No |
| 13 | Frontend Dashboard Builder | Exhaustive | Dashboard + Blueprint runtime model | routing/multisite/IDOR/cache/assets/builder | No |
| 14 | User Profile Builder | Exhaustive | ADR-0096 native WordPress identity/auth authority | protected-meta/re-auth/session/App Password/public-profile/Multisite evidence | No |
| 15 | Membership System | Exhaustive | ADR-0078 M1/M2 + ADR-0090 PD/PC | P-012 schema/cache/locking/files/MB certification/revoke/restore | No |
| 16 | Builder Widgets Builder | Exhaustive | shared Blueprint architecture | renderer/nesting/bindings/assets/accessibility/site-network library | No |
| 17 | Forms & Workflow Builder | Exhaustive | ADR-0077 FRT1/FRT2 + ADR-0082 WF1/WF2 | Form exact topology + P-011 Workflow + P-003 Job backend | No |
| 18 | Cron Job Builder | Exhaustive | JobService semantics + ADR-0083 J1/J2/J3 | P-003 backend/claims/fairness/DST/recurrence/Multisite | No |
| 19 | Notification System | Exhaustive | ADR-0079 NE1/NE2 + JobService | exact fan-out/dedupe/unknown-outcome/ET/Job/lifecycle | No |
| 20 | Emails Builder | Exhaustive | Email IR architecture + ADR-0079 | renderer/client + provider evidence + ET0–ET5 | No |
| 21 | Message & Chat System | Exhaustive | ADR-0077 CRT1/PT-D vs CRT2/PT-E | indexes/search/transport/private assets/revocation/retention | No |
| 22 | REST API Builder | Exhaustive | ADR-0094 RE1 + compiled endpoint | auth/rate/CORS/cache/idempotency/fuzz/cross-site IDOR | No |
| 23 | Webhooks & Connections | Exhaustive | Safe HTTP/I0–I5 + ADR-0080 EI1/EI2 | signature/replay/claim/routing/retention/runtime certification | No |
| 24 | Backup Manager | Exhaustive | manifest/crypto/provider model + ADR-0084 BR1/BR2/BR3 | P-013 artifact/physical/crypto/provider/restore C0–C4 evidence | No |
| 25 | Reset Manager | Exhaustive | destructive Plan/journal/recovery architecture | Job backend, recovery schema, crash/site-network lifecycle | No |
| 26 | Import / Export | Exhaustive | ADR-0095 IR1/PT-D vs IR2/PT-E | exact Run/Map/Journal/recovery/source/media/Job evidence | No |
| 27 | Protector | Exhaustive | request gate/rate-limit security architecture | hook/atomic rate/proxy/login/header/network-floor evidence | No |
| 28 | Watermarker / Media Rules | Exhaustive | derivative pipeline architecture | registry/image/offload/concurrency/Job/media lifecycle | No |
| 29 | XML-RPC Manager | Exhaustive | layered enforcement architecture | method/parser/complete-deny/Jetpack/Multisite evidence | No |
| 30 | Role & Capability Manager | Exhaustive | ADR-0097 native WordPress authority + WPE Change Plan/anti-lockout | effective-cap/Super Admin/site-removal/CLI recovery/snapshot evidence | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | OAuth/entitlement/TUF/Product License schema architecture | exact OpenAPI/OAuth/TUF/idempotency/privacy/allocation/service evidence | No |

## Cross-cutting current state

- **Definition D1:** 0 executable benchmarks; P-004 protocol fixed.
- **Relations R1:** 0 P-010 benchmarks; exact protocol fixed.
- **Query QP1/QP2/QP3/QP4:** 0 executable evidence.
- **Field/Custom Tables/Settings:** 0 executable evidence.
- **Forms FRT1 / Chat CRT1:** 0 physical benchmarks.
- **Workflow WF1:** 0 P-011 benchmarks.
- **Job J1/J2/J3 + Action Scheduler:** 0 P-003 evidence.
- **Membership M1/M2:** 0 P-012 benchmarks; **4 BE3 / 0 MB-certified**; protected PC1+ 0.
- **Notification/Email NE1/NE2:** 0 physical benchmarks; **6 EE3 / 0 ET-certified**.
- **Event Inbox EI1/EI2:** 0 physical benchmarks; **0 I4/I5 certified**.
- **Audit AU1:** 0 physical/integrity benchmarks.
- **REST RE1/RI1/RI2:** 0 route/runtime evidence.
- **Import IR1/IR2:** 0 physical/recovery evidence.
- **Backup BR1/BR2/BR3:** 0 physical/provider runtime benchmarks; **34 targets / 0 C-certified**.
- **Vault V1/V2:** 0 crypto/physical evidence.
- **User/Profile ADR-0096:** 0 runtime evidence.
- **Role/Capability ADR-0097:** 0 runtime/recovery evidence.
- **Site Lifecycle:** 40 fixtures documented / 0 executed.
- **Multisite:** 31/31 scopes mapped / 0 MS1+.
- **Remote privacy:** 30 fixtures / 0 executed.
- **Product License API/service:** 0 fixtures.

## Recommended implementation order after future consent

1. P-001 compatibility/Multisite, P-003 Job, P-004 Definition, P-005 Vault;
2. Kernel/Scope/Site Lifecycle/Registry/Definition/Policy/Abilities/Assets/Audit/Vault/JobService;
3. CPT/Taxonomy;
4. Fields → Relations P-010 → Query P-009 → Custom Tables/Columns → Blueprint/Listings/Status;
5. admin/site UX modules;
6. User/Profile and Role security fixtures before exposing privileged account-management surfaces;
7. Membership P-012 + MB certification;
8. Forms + Workflow P-011 → Notifications/Email ET;
9. REST/Connections/Event Inbox I-certification/Import;
10. Backup P-013 + provider/site-network Restore → destructive/security operations;
11. Chat after CRT evidence;
12. remote Account/Support/Updater/Product License runtime;
13. AI only over certified scoped Abilities/Blueprints;
14. ecosystem/large-network scale.

## Current conclusion

**Product specification:** 31/31 Exhaustive.  
**Architecture:** accepted decisions through ADR-0097.  
**Authorized:** 0/31.  
**Implemented:** none.  
**Verified runtime:** none.  
**All physical/compiler/security profiles:** paper-only unless explicitly stated.  
**Multisite:** 0 MS1+.  
**Membership:** 0 MB-certified.  
**Email:** 0 ET-certified.  
**Event adapters:** 0 I4/I5.  
**Backup:** 0 C-certified.  
**Vault crypto/runtime:** 0 executed.  
**User/Profile + Role runtime evidence:** 0 executed.

Allowed work remains planning/research/documentation only until explicit owner development consent.