# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: 2026-08-28

## Global rule

A surface can be Exhaustive and architecturally Accepted while still technically unverified and unauthorized.

Implementation requires accepted semantics, executable evidence, quality/security gates, platform/toolchain compatibility, and **explicit owner consent under ADR-0014**.

Current owner consent: **NOT GRANTED**. Therefore **0/31 Authorized**.

## Shared blockers

| Area | Current paper state | Required evidence |
|---|---|---|
| WP/PHP/DB/Multisite | ADR-0002/0069/0075 | P-001 |
| UI/design system | ADR-0005 | P-002 |
| Job/Action Scheduler | ADR-0059/0068/0083 | P-003 |
| Definition Repository | ADR-0073/0092 | P-004 |
| Vault | ADR-0048/0085 | P-005 |
| Free↔Pro/Product License | ADR-0070/0072/0076/0091 | P-006 |
| OAuth Account Link | ADR-0034/0101 | OA-01…OA-32 |
| Pro updater TUF | ADR-0044/0102 | TU-01…TU-44 |
| CI | ADR-0011 | P-007 |
| Build | ADR-0012 | P-008 |
| Query | ADR-0086 | P-009 |
| Relations | ADR-0074/0093 | P-010 |
| Workflow | ADR-0082 | P-011 |
| Membership | ADR-0078/0090 | P-012 |
| Backup | ADR-0084/0100 | P-013 |
| Admin Columns | ADR-0098 AC1 | dedicated runtime/list-table evidence |
| Dynamic Listings | ADR-0099 DL1 | dedicated SSR/cache/pagination evidence |
| Owner consent | ADR-0014 | blocks all executable work |

## Per-surface readiness

| # | Surface | Product maturity | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration + Definition D1 | P-001/P-004/UI/build/rewrite | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration + Definition D1 | P-001/P-004/UI/build/rewrite | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0087 FS1–FS6 | storage/index/projection/migration/Vault | No |
| 4 | Relations Builder | Exhaustive | R1/PT-D vs R2/PT-E + fixed P-010 | P-010 execution | No |
| 5 | Status Manager | Exhaustive | WP Status + domain-state split | UI/migration/history/concurrency | No |
| 6 | Custom Query Builder | Exhaustive | ADR-0086 QP1–QP4 | P-009 compiler/cost/cache/security | No |
| 7 | Custom Tables Builder | Exhaustive | CT1/PT-E vs CT2/PT-D | DDL/migration/locking/backfill/recovery | No |
| 8 | Admin Columns Builder | Exhaustive | ADR-0098 AC1 whole-request batch plan | hooks/batch budgets/sort/filter/edit/export/N+1 evidence | No |
| 9 | Dynamic Listings/Templates | Exhaustive | ADR-0099 DL1 auth-aware Query + SSR | cursor/count/cache/refill/nesting/builder/SEO evidence | No |
| 10 | Dashboard Widgets Manager | Exhaustive | trusted structured-content architecture | refresh/cache/iframe/remote/source evidence | No |
| 11 | Custom Admin Menu Builder | Exhaustive | transform + native safe-mode fallback | hook conflicts/order/performance/recovery | No |
| 12 | Settings Page Builder | Exhaustive | ADR-0089 ST1/ST2/ST3 | autoload/inheritance/concurrency/Vault/REST | No |
| 13 | Frontend Dashboard Builder | Exhaustive | Dashboard + Blueprint runtime | routing/IDOR/cache/assets/builders | No |
| 14 | User Profile Builder | Exhaustive | ADR-0096 native WP identity authority | re-auth/email/session/App Password/privacy/Multisite | No |
| 15 | Membership System | Exhaustive | M1/M2 + PD/PC protected files | P-012/MB/files/cache/revoke/restore | No |
| 16 | Builder Widgets Builder | Exhaustive | shared Component Blueprint | renderer/nesting/bindings/assets/builders | No |
| 17 | Forms & Workflow Builder | Exhaustive | FRT1/FRT2 + WF1/WF2 | Form storage + P-011 + P-003 | No |
| 18 | Cron Job Builder | Exhaustive | JobService + J1/J2/J3 | P-003 recurrence/DST/claims/fairness | No |
| 19 | Notification System | Exhaustive | NE1/NE2 + JobService | fan-out/dedupe/delivery/ET/lifecycle | No |
| 20 | Emails Builder | Exhaustive | Email IR + provider profiles | renderer + 0 ET certification | No |
| 21 | Message & Chat | Exhaustive | CRT1/PT-D vs CRT2/PT-E | indexes/search/transport/private assets/revoke | No |
| 22 | REST API Builder | Exhaustive | ADR-0094 RE1 + RI1/RI2 | route/rate/CORS/cache/idempotency/fuzz | No |
| 23 | Webhooks & Connections | Exhaustive | Safe HTTP + Event Inbox | signature/replay/routing/provider certification | No |
| 24 | Backup Manager | Exhaustive | manifest/crypto/providers + ADR-0100 artifact profile | P-013/archive/chunks/crypto/C0–C4 restore evidence | No |
| 25 | Reset Manager | Exhaustive | Plan + restore point + durable journal | recovery runtime/crash/Job/lifecycle | No |
| 26 | Import / Export | Exhaustive | IR1/PT-D vs IR2/PT-E | Run/Map/Journal/recovery/source/media | No |
| 27 | Protector | Exhaustive | request gate + atomic rate-limit architecture | trusted-proxy/rate/login/header/runtime | No |
| 28 | Watermarker / Media Rules | Exhaustive | non-destructive derivative pipeline | image/offload/Job/concurrency/media lifecycle | No |
| 29 | XML-RPC Manager | Exhaustive | layered method/endpoint policy | method/parser/Jetpack/Multisite evidence | No |
| 30 | Role & Capability Manager | Exhaustive | ADR-0097 native WP authority + anti-lockout | effective-cap/Super Admin/recovery/snapshot evidence | No |
| 31 | Account/Docs/Support/Diagnostics | Exhaustive | OAuth/Product License/TUF/remote-service architecture | OA/TU/P-006/privacy/service runtime | No |

## Current evidence/certification counters

- Definition P-004: **0 executed**.
- Relations P-010: **0 executed**.
- Query P-009: **0 executed**.
- Job P-003: **0 executed**.
- Workflow P-011: **0 executed**.
- Vault P-005: **0 executed**.
- Membership P-012: **0 executed**; billing **4 BE3 / 0 MB-certified**; protected files **0 PC1+**.
- Admin Columns AC1: **0 runtime cases**.
- Dynamic Listings DL1: **0 runtime cases**.
- REST/Import: **0 runtime cases**.
- Email: **6 EE3 / 0 ET-certified**.
- Event adapters: **0 I4/I5**.
- Backup: **34 targets / 0 C-certified**; archive/hash/compression/restore **0**.
- User/Profile: **0 runtime cases**.
- Role/Capability: **0 runtime cases**.
- OAuth Account Link: **0/32 OA fixtures**.
- Pro updater TUF: **0/44 TU fixtures**.
- Remote privacy: **0/30**.
- Product License API/service: **0**.
- Site Lifecycle: **0/40**.
- Multisite: **0 MS1+**.

## Recommended implementation order after future consent

1. P-001 compatibility + P-003 Job + P-004 Definition + P-005 Vault;
2. Kernel/Scope/Site Lifecycle/Registry/Definition/Policy/Abilities/Audit/Vault/Job;
3. CPT/Taxonomy;
4. Fields → Relations P-010 → Query P-009 → Custom Tables → Admin Columns → Blueprint/Listings;
5. admin/site UX modules;
6. User/Profile + Role security evidence;
7. Membership P-012 + protected files + MB certification;
8. Forms/Workflow → Notifications/Email;
9. REST/Connections/Event Inbox/Import;
10. Backup P-013 + restore certification → Reset/Protector/other destructive operations;
11. Chat after storage/search/transport evidence;
12. OAuth Account Link OA protocol + Product License service;
13. TUF updater TU protocol only after verifier/key-ops security bar is met;
14. AI only over certified scoped Abilities.

## Current conclusion

**Architecture accepted through ADR-0102.**  
**31/31 Exhaustive. 0/31 Authorized.**  
**Implemented: none. Runtime verified: none.**

Planning/research/documentation only remains allowed until explicit owner development consent.