# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: 2026-08-28

## Global rule

A surface can be Exhaustive and architecturally Accepted while still technically unverified and unauthorized. Implementation requires accepted semantics, executable evidence, quality/security gates, platform/toolchain compatibility and **explicit owner consent under ADR-0014**.

Current owner consent: **NOT GRANTED**. Therefore **0/31 Authorized**.

## Shared blockers

| Area | Current paper state | Required evidence |
|---|---|---|
| WP/PHP/DB/Multisite compatibility | ADR-0002/0069/0075/0123 | CF-01…CF-112 / P-001 |
| UI/design system | ADR-0005/0125 | UI-01…UI-104 / P-002 |
| Job/Action Scheduler/Cron | ADR-0059/0068/0083/0119 | JS-01…JS-106 / P-003 |
| Definition Repository | ADR-0073/0092/0132 | DEF-01…DEF-144 / P-004; final D1–D4/DDL evidence-gated |
| Vault | ADR-0048/0085/0124 | VT-01…VT-128 / P-005 |
| Free↔Pro/Product License boundary | ADR-0010/0070/0072/0076/0091/0128 | FP-01…FP-144 / P-006 |
| OAuth Account Link | ADR-0034/0101 | OA-01…OA-32 |
| Pro updater TUF | ADR-0044/0102 | TU-01…TU-44 |
| CI | ADR-0011/0127 | CI-01…CI-120 / P-007 |
| Build | ADR-0012/0126 | BT-01…BT-112 / P-008 |
| Query | ADR-0086/0131 | QRY-01…QRY-168 / P-009 |
| Relations | ADR-0074/0093/0133 | REL-01…REL-160 / P-010; final R/E/PV/DDL evidence-gated |
| Workflow | ADR-0082/0118 | WF-01…WF-116 / P-011 |
| Membership | ADR-0013/0015/0016/0019/0020/0057/0062/0066/0078/0090/0129 | MBR-01…MBR-160 / P-012 + MB/PC certification |
| Backup | ADR-0021/0033/0043/0053/0056/0061/0064/0065/0084/0100/0130 | BK-01…BK-180 / P-013 + C0–C4/V3 certification |
| Field Storage / Custom Fields | ADR-0022/0087/0134 | FST-01…FST-176; FS1–FS6 certification boundaries |
| Custom Tables | ADR-0023/0088/0135 | CTB-01…CTB-184; CT1–CT3 + CM1–CM4 certification |
| Notification | ADR-0026/0079/0120 | NT-01…NT-142 + NE1/NE2/channel evidence |
| Message & Chat | ADR-0027/0077/0121 | CH-01…CH-142 + CRT1/CRT2/private-asset/search/transport evidence |
| Webhooks/Connections/Event Inbox | ADR-0040/0055/0080/0122 | WC-01…WC-156 + I0–I5 + EI1/EI2 evidence |
| Admin Columns | ADR-0098 AC1 | dedicated runtime/list-table evidence — WP19 current |
| Dynamic Listings | ADR-0099 DL1 | dedicated SSR/cache/pagination evidence |
| Dashboard Widgets | ADR-0103 | DW-01…DW-36 |
| Admin Menu | ADR-0104 | AM-01…AM-40 |
| Protector | ADR-0105 | PR-01…PR-44 |
| Reset Manager | ADR-0106 | RM-01…RM-48 |
| Watermarker / Media | ADR-0107 | WM-01…WM-48 |
| Frontend Dashboard | ADR-0108 | FD-01…FD-48 |
| Builder Widgets adapters | ADR-0109 | BW-01…BW-50 + BC0…BC4 |
| Status Manager | ADR-0110 | SM-01…SM-48 |
| XML-RPC Manager | ADR-0111 | XR-01…XR-48 |
| Settings Page | ADR-0112 | ST-01…ST-48 |
| User Profile | ADR-0113 | UP-01…UP-48 |
| Role & Capability | ADR-0114 | RA-01…RA-48 |
| REST API Builder | ADR-0115 | REST-01…REST-52 |
| Import / Export | ADR-0116 | IM-01…IM-56 |
| Forms Runtime | ADR-0025/0077/0117 | FM-01…FM-92 + FRT1/FRT2 evidence |
| Owner consent | ADR-0014 | blocks all executable work |

## Per-surface readiness

| # | Surface | Product maturity | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration + Definition D1/PT-C | CF + DEF + UI + BT + CI + rewrite runtime evidence | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration + Definition D1/PT-C | CF + DEF + UI + BT + CI + rewrite runtime evidence | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0087 FS1–FS6 + ADR-0134 fixed evidence | FST-01…FST-176; FS1–FS6 runtime/profile certification | No |
| 4 | Relations Builder | Exhaustive | R1/PT-D vs R2/PT-E + ADR-0133 | REL-01…REL-160; final physical/locking profile | No |
| 5 | Status Manager | Exhaustive | ADR-0038/0110 | SM-01…SM-48 | No |
| 6 | Custom Query Builder | Exhaustive | ADR-0086 QP1–QP4 + ADR-0131 | QRY-01…QRY-168; QP1–QP4 certification | No |
| 7 | Custom Tables Builder | Exhaustive | ADR-0023 typed migrations + ADR-0088 CT1/CT2/CT3 + ADR-0135 | CTB-01…CTB-184; CT/CM profile certification; exact DDL | No |
| 8 | Admin Columns Builder | Exhaustive | ADR-0098 AC1 whole-request batch plan | fixed hook/batch/sort/filter/edit/export/N+1 evidence — WP19 | No |
| 9 | Dynamic Listings/Templates | Exhaustive | ADR-0099 DL1 auth-aware Query + SSR | cursor/count/cache/refill/nesting/builder/SEO evidence + QRY/FST/REL | No |
| 10 | Dashboard Widgets Manager | Exhaustive | ADR-0051/0103 | DW-01…DW-36 + UI/BT/CI | No |
| 11 | Custom Admin Menu Builder | Exhaustive | ADR-0037/0104 | AM-01…AM-40 + UI/BT/CI | No |
| 12 | Settings Page Builder | Exhaustive | ADR-0036/0089/0112 | ST-01…ST-48 + VT + UI/BT/CI | No |
| 13 | Frontend Dashboard Builder | Exhaustive | ADR-0031/0108 | FD-01…FD-48 + UI/BT/CI + MBR when protected | No |
| 14 | User Profile Builder | Exhaustive | ADR-0030/0096/0113 | UP-01…UP-48 + UI/BT/CI | No |
| 15 | Membership System | Exhaustive | ADR-0013…0090 + ADR-0129 | MBR-01…MBR-160 + MB/PC certification | No |
| 16 | Builder Widgets Builder | Exhaustive | ADR-0035/0109 | BW-01…BW-50 + BC0…BC4 + BT/CI | No |
| 17 | Forms & Workflow Builder | Exhaustive | FRT1/FRT2 + WF1/WF2 | FM + WF + JS execution/topology evidence | No |
| 18 | Cron Job Builder | Exhaustive | JobService + J1/J2/J3 | JS-01…JS-106 | No |
| 19 | Notification System | Exhaustive | NE1/PT-D vs NE2/PT-E | NT-01…NT-142 + JS/WF/provider evidence | No |
| 20 | Emails Builder | Exhaustive | Email IR + provider profiles | renderer + ET certification + VT | No |
| 21 | Message & Chat | Exhaustive | CRT1/PT-D vs CRT2/PT-E | CH-01…CH-142 + MBR/private-asset/search/realtime cert | No |
| 22 | REST API Builder | Exhaustive | RE1 + RI1/RI2 | REST-01…REST-52 + QRY where Query-backed | No |
| 23 | Webhooks & Connections | Exhaustive | Safe HTTP + Gateway + Event Inbox | WC-01…WC-156 + VT + I0–I5 + EI runtime | No |
| 24 | Backup Manager | Exhaustive | manifest/crypto/providers + ADR-0130 | BK-01…BK-180 + C0–C4/V3 certification | No |
| 25 | Reset Manager | Exhaustive | Plan + verified restore point + journal | RM-01…RM-48 + certified Backup boundary | No |
| 26 | Import / Export | Exhaustive | IR1/PT-D vs IR2/PT-E | IM-01…IM-56 + DEF/FST/CTB package interactions | No |
| 27 | Protector | Exhaustive | request gate + atomic rate-limit | PR-01…PR-44 | No |
| 28 | Watermarker / Media Rules | Exhaustive | non-destructive derivative pipeline | WM-01…WM-48 | No |
| 29 | XML-RPC Manager | Exhaustive | layered method/endpoint policy | XR-01…XR-48 | No |
| 30 | Role & Capability Manager | Exhaustive | native WP authority + anti-lockout | RA-01…RA-48 + MBR role-sync interaction | No |
| 31 | Account/Docs/Support/Diagnostics | Exhaustive | OAuth/Product License/TUF/remote architecture | FP/OA/TU/privacy/service runtime + VT/UI/BT/CI | No |

## Current evidence/certification counters

- CF **0/112**; compatibility floor not certified.
- UI **0/104**; runtime certification 0.
- JS **0/106**; Job backend/Cron-DST certification 0.
- DEF **0/144**; final D1–D4/DDL open.
- VT **0/128**; crypto/runtime certification 0.
- FP **0/144**; certified Free↔Pro artifact pairs 0.
- CI **0/120**; workflow implementation unverified; direct branch reads show `main` + planning unprotected; rulesets UNKNOWN (403).
- BT **0/112**; canonical toolchain not selected.
- QRY **0/168**; QP1–QP4 certifications 0.
- REL **0/160**; R1/R2/R3 final profile unverified.
- WF **0/116**.
- MBR **0/160**; 4 BE3 / 0 MB-certified; 0 PC1+.
- BK **0/180**; 34 provider targets / 0 C-certified / 0 C3; V3 0.
- FST **0/176**; Field Storage runtime/profile certifications 0.
- CTB **0/184**; CT1/CT2/CT3 + CM1/CM2/CM3/CM4 certifications 0; exact DDL open.
- FM **0/92**; NT **0/142**; CH **0/142**; WC **0/156**.
- OA **0/32**; TU **0/44**.
- DW **0/36**; AM **0/40**; PR **0/44**; RM **0/48**; WM **0/48**; FD **0/48**; BW **0/50**; SM **0/48**; XR **0/48**; ST **0/48**; UP **0/48**; RA **0/48**; REST **0/52**; IM **0/56**.
- Email **6 EE3 / 0 ET-certified**.
- Connection adapters **0 I4/I5**.
- Site Lifecycle **0/40**.
- Multisite **0 MS1+**.
- Remote privacy **0/30**.

## Recommended implementation order after future consent

1. CF/UI/BT/CI/JS/DEF/VT/FP shared foundations;
2. Kernel/Scope/Site Lifecycle/Registry/Definition/Policy/Abilities/Audit/Vault/Job + safe build/UI/Free↔Pro bootstrap;
3. CPT/Taxonomy;
4. Field Storage/FST → Relations/REL → Query/QRY → Custom Tables/CTB → Admin Columns → Listings;
5. remaining admin/site UX modules;
6. User/Profile + Role security;
7. Membership + protected files/provider certification;
8. Forms/Workflow/Jobs → Notification/Email;
9. REST/Connections/Event Inbox/Import;
10. Backup certification → Reset/destructive operations;
11. Chat after Membership/protected assets/jobs/notification;
12. OAuth/Product License service;
13. TUF updater after verifier/key-ops bar;
14. AI only over certified scoped Abilities.

## Current conclusion

**Architecture/evidence contracts/refinements accepted through ADR-0135; all applicable runtime/toolchain/profile decisions remain unverified until authorized evidence executes.**  
**31/31 Exhaustive. 0/31 Authorized. Implemented: none. Runtime verified: none.**

Current planning work: **`P0-M00-WP19` — Admin Columns operational executable-evidence refinement**.

Planning/research/documentation only remains allowed until explicit owner development consent.