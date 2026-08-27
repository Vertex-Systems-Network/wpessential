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
| Job Service backend/history | ADR-0059/0068 + ADR-0083 J1/J2/J3 paper mapping | P-003 |
| Definition Repository | ADR-0073 D1/PT-C + ADR-0092 exact evidence protocol | P-004 |
| Secrets Vault | ADR-0048 + ADR-0085 V1/V2 | P-005 |
| Free↔Pro / Product License | ADR-0070/0072/0076/0091 paper contract | P-006 |
| CI | ADR-0011 Proposed | P-007 |
| Build toolchain | ADR-0012 Proposed | P-008 |
| Query | ADR-0086 QP1/QP2/QP3/QP4 | P-009 |
| Relations | ADR-0074 R1/R2 + ADR-0093 exact evidence protocol | P-010 |
| Workflow | ADR-0082 WF1/PT-D; WF2/PT-E | P-011 |
| Membership | ADR-0078 M1/M2 + ADR-0090 protected delivery | P-012 |
| Backup | ADR-0084 BR1/BR2/BR3 | P-013 |
| Field Storage | ADR-0087 FS1–FS6 | adapter/migration evidence |
| Custom Tables | ADR-0088 CT1/PT-E vs CT2/PT-D | exact DDL/migration evidence |
| Settings | ADR-0089 ST1/ST2/ST3 | option/autoload/concurrency evidence |
| REST | ADR-0094 RE1/RI1/RI2 | runtime/security/idempotency/rate/cache evidence |
| Import/Export | ADR-0095 IR1/PT-D vs IR2/PT-E | physical/recovery/source-adapter evidence |
| User Profile | ADR-0096 UP1/UP2/UP3 + UE1 | core-adapter/security/recent-auth/privacy evidence |
| Role & Capability | ADR-0097 RA1/RA2 + anti-lockout/recovery | native mutation/Multisite/recovery/cache evidence |
| Forms/Chat | ADR-0077 FRT1/CRT1 | domain evidence |
| Notification/Email | ADR-0079 NE1/NE2 | runtime + ET evidence |
| Event Inbox | ADR-0080 EI1/EI2 | runtime + I4/I5 evidence |
| Audit | ADR-0081 AU1/PT-D | exact DDL/retention/integrity evidence |
| Multisite/Site Lifecycle | ADR-0069/0071/0075 | P-001 + module gates |
| Owner consent | ADR-0014 accepted, consent absent | blocks all executable work |

## Per-surface readiness

All 31 surfaces remain **Exhaustive / Unauthorized**.

| # | Surface | Product options | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration + Definition D1 + Multisite/lifecycle | P-001/P-004 execution, UI/build, rewrite/provision fixtures | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration + Definition D1 + Multisite/lifecycle | P-001/P-004 execution, UI/build, rewrite fixtures | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0087 FS1/FS2/FS3/FS4/FS5 | adapter/queryability/uniqueness/migration evidence | No |
| 4 | Relations Builder | Exhaustive | ADR-0074 R1/R2 + ADR-0093 protocol | execute P-010 | No |
| 5 | Status Manager | Exhaustive | ADR-0038 + scoped lifecycle | WP UI/migration/state history/concurrency | No |
| 6 | Custom Query Builder | Exhaustive | ADR-0086 QP1/QP2/QP3/QP4 | execute P-009 | No |
| 7 | Custom Tables Builder | Exhaustive | ADR-0088 CT1/PT-E vs CT2/PT-D | exact DDL/locking/backfill/shadow/recovery/scale | No |
| 8 | Admin Columns Builder | Exhaustive | typed list/source semantics | N+1/source/sort/filter/write renderer operational profile/evidence | No |
| 9 | Dynamic Listings/Templates | Exhaustive | Blueprint + authorized Query → SSR | protected pagination/cache/SSR/site-network evidence | No |
| 10 | Dashboard Widgets Manager | Exhaustive | trusted structured content | Site/Network/remote schema/iframe/XSS/CSP evidence | No |
| 11 | Custom Admin Menu Builder | Exhaustive | transformation/safe mode | site/network hook conflict/recovery/performance | No |
| 12 | Settings Page Builder | Exhaustive | ADR-0089 ST1/ST2/ST3 | option/autoload/stale-write/REST/Vault/network evidence | No |
| 13 | Frontend Dashboard Builder | Exhaustive | Dashboard + Blueprint runtime | routing/multisite/IDOR/cache/assets/builder | No |
| 14 | User Profile Builder | Exhaustive | ADR-0096 UP1 native WP authority + UP2 Field Storage + protected binding + UE1 | current core adapter behavior, recent-auth, email/password/session/Application Password, public privacy, Multisite | No |
| 15 | Membership System | Exhaustive | ADR-0078 M1/M2 + ADR-0090 PD/PC | P-012 + MB + protected-file certification | No |
| 16 | Builder Widgets Builder | Exhaustive | shared Blueprint | renderer/nesting/bindings/assets/accessibility/site-network | No |
| 17 | Forms & Workflow Builder | Exhaustive | ADR-0077 FRT + ADR-0082 WF | Form topology + P-011 + P-003 | No |
| 18 | Cron Job Builder | Exhaustive | JobService + ADR-0083 | P-003 | No |
| 19 | Notification System | Exhaustive | ADR-0079 + JobService | fan-out/dedupe/unknown outcome/ET/Job/lifecycle | No |
| 20 | Emails Builder | Exhaustive | Email IR + ADR-0079 | renderer/client + ET0–ET5 | No |
| 21 | Message & Chat System | Exhaustive | ADR-0077 CRT1/CRT2 | indexes/search/transport/private assets/revocation | No |
| 22 | REST API Builder | Exhaustive | ADR-0094 RE1/RI operational | route/auth/fuzz/idempotency/rate/CORS/cache/cross-site | No |
| 23 | Webhooks & Connections | Exhaustive | Safe HTTP/I0–I5 + ADR-0080 | provider signature/replay/routing/retention certification | No |
| 24 | Backup Manager | Exhaustive | manifest/crypto/provider + ADR-0084 | P-013 + C0–C4 | No |
| 25 | Reset Manager | Exhaustive | destructive Plan/journal/recovery | Job/recovery/crash/lifecycle | No |
| 26 | Import / Export | Exhaustive | ADR-0095 IR1/IR2 | Run/Map/Journal DDL, crash/retry/source/media/rollback/scale | No |
| 27 | Protector | Exhaustive | request gate/rate-limit security | hook/atomic rate/proxy/login/header/network-floor | No |
| 28 | Watermarker / Media Rules | Exhaustive | derivative pipeline | registry/image/offload/concurrency/Job/lifecycle | No |
| 29 | XML-RPC Manager | Exhaustive | layered enforcement | method/parser/complete-deny/Jetpack/Multisite | No |
| 30 | Role & Capability Manager | Exhaustive | ADR-0097 RA1 native WP authority + Change Plan/anti-lockout/recovery | native mutation/meta-caps/third-party roles/self-lockout/Super Admin/WP-CLI/recovery/cache evidence | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | OAuth/entitlement/TUF + ADR-0076/0091 | actual OpenAPI/OAuth/TUF/service evidence | No |

## Current evidence counters

- P-004 Definition protocol: **0 executed**.
- P-010 Relations protocol: **0 executed**.
- P-009 Query: **0 executed**.
- Field/Custom Tables/Settings: **0 executed**.
- REST: **0 executed**.
- Import: **0 executed**.
- User Profile security: **0 executed**.
- Role/Capability security: **0 executed**.
- Workflow P-011: **0 executed**.
- Job P-003: **0 executed**.
- Membership P-012: **0 executed**; **4 BE3 / 0 MB-certified**; protected delivery **0 PC1+**.
- Email: **6 EE3 / 0 ET-certified**.
- Event adapters: **0 I4/I5**.
- Audit: **0**.
- Backup: **34 / 0 C-certified**.
- Vault: **0**.
- Site Lifecycle: **40 / 0 executed**.
- Multisite: **0 MS1+**.
- Remote privacy: **30 / 0 executed**.
- Product License API/service: **0**.

## Recommended implementation order after future consent

1. P-001 compatibility/Multisite, P-003 Job, P-004 Definition, P-005 Vault;
2. Kernel/Scope/Site Lifecycle/Registry/Definition/Policy/Abilities/Assets/Audit/Vault/JobService;
3. CPT/Taxonomy;
4. Fields → Relations P-010 → Query P-009 → Custom Tables/Columns → Blueprint/Listings/Status;
5. Settings/Admin UX + User Profile ADR-0096 + Role ADR-0097;
6. Membership P-012 + MB + protected-file PC certification;
7. Forms + Workflow P-011 → Notifications/Email ET;
8. REST ADR-0094 → Connections/Event Inbox → Import ADR-0095;
9. Backup P-013 + Restore → destructive/security operations;
10. Chat after CRT evidence;
11. remote Account/Support/Updater/Product License;
12. AI only over certified scoped Abilities/Blueprints;
13. ecosystem/large-network scale.

## Current conclusion

**Product specification:** 31/31 Exhaustive.  
**Architecture:** accepted decisions through ADR-0097.  
**Authorized:** 0/31.  
**Implemented:** none.  
**Verified runtime:** none.  
**All physical/compiler/security/delivery/evidence profiles:** paper-only.  
**Multisite:** 0 MS1+.  
**Membership:** 0 MB / 0 PC1+.  
**Email:** 0 ET.  
**Event adapters:** 0 I4/I5.  
**Backup:** 0 C.  
**User/Profile + Role security fixtures:** 0.  
**Product License API/service:** 0.

Allowed work remains planning/research/documentation only until explicit owner development consent.