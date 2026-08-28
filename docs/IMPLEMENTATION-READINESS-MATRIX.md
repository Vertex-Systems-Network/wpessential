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
| WP/PHP/DB/Multisite compatibility | ADR-0002/0069/0075/0123 | CF-01…CF-112 / P-001 |
| UI/design system | ADR-0005/0125 | UI-01…UI-104 / P-002 |
| Job/Action Scheduler/Cron | ADR-0059/0068/0083/0119 | JS-01…JS-106 / P-003 |
| Definition Repository | ADR-0073/0092/0132 | DEF-01…DEF-144 / P-004; final D1–D4/DDL evidence-gated |
| Vault | ADR-0048/0085/0124 | VT-01…VT-128 / P-005 |
| Free↔Pro/Product License package boundary | ADR-0010/0070/0072/0076/0091/0128 | FP-01…FP-144 / P-006 |
| OAuth Account Link | ADR-0034/0101 | OA-01…OA-32 |
| Pro updater TUF | ADR-0044/0102 | TU-01…TU-44 |
| CI | ADR-0011/0127 | CI-01…CI-120 / P-007 |
| Build | ADR-0012/0126 | BT-01…BT-112 / P-008 |
| Query | ADR-0086/0131 | QRY-01…QRY-168 / P-009 |
| Relations | ADR-0074/0093 | existing P-010 evidence; completeness audit current |
| Workflow | ADR-0082/0118 | WF-01…WF-116 / P-011 |
| Membership | ADR-0013/0015/0016/0019/0020/0057/0062/0066/0078/0090/0129 | MBR-01…MBR-160 / P-012 + MB/PC profile certification |
| Backup | ADR-0021/0033/0043/0053/0056/0061/0064/0065/0084/0100/0130 | BK-01…BK-180 / P-013 + exact C0–C4/V3 certification |
| Notification | ADR-0026/0079/0120 | NT-01…NT-142 + NE1/NE2/channel evidence |
| Message & Chat | ADR-0027/0077/0121 | CH-01…CH-142 + CRT1/CRT2/private-asset/search/transport evidence |
| Webhooks/Connections/Event Inbox | ADR-0040/0055/0080/0122 | WC-01…WC-156 + I0–I5 + EI1/EI2 evidence |
| Admin Columns | ADR-0098 AC1 | dedicated runtime/list-table evidence |
| Dynamic Listings | ADR-0099 DL1 | dedicated SSR/cache/pagination evidence |
| Dashboard Widgets | ADR-0103 | DW-01…DW-36 |
| Admin Menu | ADR-0104 | AM-01…AM-40 |
| Protector | ADR-0105 | PR-01…PR-44 |
| Reset Manager | ADR-0106 | RM-01…RM-48 |
| Watermarker / Media | ADR-0107 | WM-01…WM-48 |
| Frontend Dashboard | ADR-0108 | FD-01…FD-48 |
| Builder Widgets adapters | ADR-0109 | BW-01…BW-50 + BC0…BC4 certification |
| Status Manager | ADR-0110 | SM-01…SM-48 |
| XML-RPC Manager | ADR-0111 | XR-01…XR-48 |
| Settings Page | ADR-0112 | ST-01…ST-48 |
| User Profile | ADR-0113 | UP-01…UP-48 |
| Role & Capability | ADR-0114 | RA-01…RA-48 |
| REST API Builder | ADR-0115 | REST-01…REST-52 |
| Import / Export | ADR-0116 | IM-01…IM-56 |
| Forms Runtime | ADR-0025/0077/0117 | FM-01…FM-92 + FRT1/FRT2 topology evidence |
| Owner consent | ADR-0014 | blocks all executable work |

## Per-surface readiness

| # | Surface | Product maturity | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration + Definition D1/PT-C | CF/P-001 + DEF/P-004 + UI/P-002 + BT/P-008 + CI + rewrite evidence | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration + Definition D1/PT-C | CF/P-001 + DEF/P-004 + UI/P-002 + BT/P-008 + CI + rewrite evidence | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0087 FS1–FS6 | storage/index/projection/migration/VT-P-005 Vault | No |
| 4 | Relations Builder | Exhaustive | R1/PT-D vs R2/PT-E + fixed P-010 | P-010 completeness audit then execution | No |
| 5 | Status Manager | Exhaustive | ADR-0038/0110 split Post Status/domain-state evidence | SM-01…SM-48 | No |
| 6 | Custom Query Builder | Exhaustive | ADR-0086 QP1–QP4 + ADR-0131 fixed evidence | QRY-01…QRY-168 execution; QP1–QP4 certifications | No |
| 7 | Custom Tables Builder | Exhaustive | CT1/PT-E vs CT2/PT-D | DDL/migration/locking/backfill/recovery | No |
| 8 | Admin Columns Builder | Exhaustive | ADR-0098 AC1 whole-request batch plan | hooks/batch budgets/sort/filter/edit/export/N+1 evidence | No |
| 9 | Dynamic Listings/Templates | Exhaustive | ADR-0099 DL1 auth-aware Query + SSR | cursor/count/cache/refill/nesting/builder/SEO evidence + QRY | No |
| 10 | Dashboard Widgets Manager | Exhaustive | ADR-0051/0103 trusted structured-content evidence | DW-01…DW-36 + UI/BT/CI | No |
| 11 | Custom Admin Menu Builder | Exhaustive | ADR-0037/0104 transform + native safe-mode fallback | AM-01…AM-40 + UI/BT/CI | No |
| 12 | Settings Page Builder | Exhaustive | ADR-0036/0089/0112 ST1/ST2/ST3 | ST-01…ST-48 + VT + UI/BT/CI | No |
| 13 | Frontend Dashboard Builder | Exhaustive | ADR-0031/0108 Dashboard + Blueprint runtime | FD-01…FD-48 + UI/BT/CI + MBR where Membership protected | No |
| 14 | User Profile Builder | Exhaustive | ADR-0030/0096/0113 native WP identity authority | UP-01…UP-48 + UI/BT/CI | No |
| 15 | Membership System | Exhaustive | ADR-0013/0015/0016/0019/0020 + M1/M2 + PD/PC profiles + ADR-0129 | MBR-01…MBR-160; MB provider + PC profile certification remain separate | No |
| 16 | Builder Widgets Builder | Exhaustive | ADR-0035/0109 shared Component Blueprint adapters | BW-01…BW-50; BC0…BC4 + BT/CI | No |
| 17 | Forms & Workflow Builder | Exhaustive | Forms FRT1/FRT2 + Workflow WF1/WF2 | FM-01…FM-92 + WF-01…WF-116 + JS/P-003; final topology evidence | No |
| 18 | Cron Job Builder | Exhaustive | JobService + J1/J2/J3 | JS-01…JS-106 / P-003 | No |
| 19 | Notification System | Exhaustive | NE1/PT-D vs NE2/PT-E | NT-01…NT-142 + JS/WF + provider evidence | No |
| 20 | Emails Builder | Exhaustive | Email IR + provider profiles | renderer + ET certification + VT | No |
| 21 | Message & Chat | Exhaustive | CRT1/PT-D vs CRT2/PT-E | CH-01…CH-142 + MBR/Protected Asset + search/realtime/transport cert | No |
| 22 | REST API Builder | Exhaustive | RE1 + RI1/RI2 | REST-01…REST-52 + QRY where Query-backed | No |
| 23 | Webhooks & Connections | Exhaustive | Safe HTTP + verified Gateway + durable Event Inbox | WC-01…WC-156 + VT; I0–I5; Safe HTTP/EI runtime | No |
| 24 | Backup Manager | Exhaustive | manifest/crypto/providers + ADR-0100 + ADR-0130 | BK-01…BK-180; provider C0–C4/V3; VT/MBR recovery interaction | No |
| 25 | Reset Manager | Exhaustive | Plan + restore point + durable journal | RM-01…RM-48 + certified Backup boundary where required | No |
| 26 | Import / Export | Exhaustive | IR1/PT-D vs IR2/PT-E | IM-01…IM-56 + DEF package/revision interactions | No |
| 27 | Protector | Exhaustive | request gate + atomic rate-limit | PR-01…PR-44 | No |
| 28 | Watermarker / Media Rules | Exhaustive | non-destructive derivative pipeline | WM-01…WM-48 | No |
| 29 | XML-RPC Manager | Exhaustive | layered method/endpoint policy | XR-01…XR-48 | No |
| 30 | Role & Capability Manager | Exhaustive | native WP authority + anti-lockout | RA-01…RA-48 + MBR role-sync provenance interaction | No |
| 31 | Account/Docs/Support/Diagnostics | Exhaustive | OAuth/Product License/TUF/remote-service architecture | FP/OA/TU/privacy/service runtime + VT/UI/BT/CI | No |

## Current evidence/certification counters

- Compatibility P-001 / CF: **0/112; floor not certified; ADR-0002 Proposed**.
- UI P-002 / UI: **0/104; runtime certification none; ADR-0005 Proposed**.
- Job P-003 / JS: **0/106; backend/Cron-DST certifications none**.
- Definition P-004 / DEF: **0/144; physical/runtime certifications 0; final D1–D4/DDL open; independent review not executed**.
- Vault P-005 / VT: **0/128; runtime/crypto certifications none; security review not executed**.
- Free↔Pro P-006 / FP: **0/144; certified artifact pairs 0; ADR-0010 Proposed**.
- CI P-007 / CI: **0/120; CI runtime certification none; workflows not verified; direct branch reads show `main` + `planning/master-architecture` unprotected; repository rulesets UNKNOWN due 403 plan/access limitation**.
- Build P-008 / BT: **0/112; toolchain certification none; ADR-0012 Proposed; canonical tool not selected**.
- Query P-009 / QRY: **0/168; runtime certifications 0; QP1/QP2/QP3/QP4 certifications all 0; cost/cache/cursor defaults open**.
- Relations P-010: **0 executed; canonical/supplementary evidence completeness audit current**.
- Workflow P-011 / WF: **0/116; runtime certification none**.
- Membership P-012 / MBR: **0/160; runtime certification none; M1/M2 physical benchmarks 0; 4 BE3 / 0 MB-certified; 0 PC1+; independent security review not executed**.
- Backup P-013 / BK: **0/180; runtime certification none; 34 targets / 0 C-certified / 0 C3 Supported; V3 restore certifications 0; independent disaster-recovery/security review not executed**.
- Notification: **0/142 NT**.
- Message & Chat: **0/142 CH; runtime/realtime/search cert 0**.
- Webhooks/Connections/Event Inbox: **0/156 WC; I4/I5 0; Safe HTTP/EI runtime unverified**.
- Forms Runtime: **0/92 FM**.
- Dashboard Widgets: **0/36 DW**.
- Admin Menu: **0/40 AM**.
- Protector: **0/44 PR**.
- Reset Manager: **0/48 RM**.
- Watermarker / Media: **0/48 WM**.
- Frontend Dashboard: **0/48 FD**.
- Builder Widgets adapters: **0/50 BW; runtime certifications 0**.
- Status Manager: **0/48 SM**.
- XML-RPC Manager: **0/48 XR**.
- Settings Page: **0/48 ST**.
- User Profile: **0/48 UP**.
- Role & Capability: **0/48 RA**.
- REST API Builder: **0/52 REST**.
- Import / Export: **0/56 IM**.
- OAuth Account Link: **0/32 OA**.
- Pro updater TUF: **0/44 TU**.
- Email: **6 EE3 / 0 ET-certified**.
- Remote privacy: **0/30**.
- Product License API/service executions: **0**.
- Site Lifecycle: **0/40**.
- Multisite: **0 MS1+**.

## Recommended implementation order after future consent

1. P-001/CF + P-002/UI + P-008/BT + P-007/CI + P-003/JS + P-004/DEF + P-005/VT + P-006/FP shared foundations;
2. Kernel/Scope/Site Lifecycle/Registry/Definition/Policy/Abilities/Audit/Vault/Job + build/UI/assets + safe Free/Pro bootstrap;
3. CPT/Taxonomy;
4. Fields → Relations P-010 → Query P-009/QRY → Custom Tables → Admin Columns → Blueprint/Listings;
5. admin/site UX modules using UI/BT/CI and surface gates;
6. User/Profile + Role security;
7. Membership P-012/MBR + protected files + MB certification;
8. Forms/WF/JS → Notification + Email/channel certification;
9. REST/Connections/Event Inbox/Import using REST/IM/WC/VT/QRY;
10. Backup P-013/BK + restore certification → Reset/Protector/destructive operations;
11. Chat after MBR/Protected Asset/Job/Notification prerequisites;
12. OAuth Account Link + Product License service;
13. TUF updater only after verifier/key-ops bar;
14. AI only over certified scoped Abilities.

## Current conclusion

**Architecture/evidence contracts/refinements accepted through ADR-0132; runtime/toolchain/blocker ADRs remain unverified until authorized evidence executes.**  
**31/31 Exhaustive. 0/31 Authorized.**  
**Implemented: none. Runtime verified: none.**

Current planning work: **P0-M00-WP16 — P-010 Relations evidence completeness / physical proof audit**.

Planning/research/documentation only remains allowed until explicit owner development consent.