# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: 2026-08-28

## Global rule

A surface can be Exhaustive and architecturally Accepted while still technically unverified and unauthorized. Implementation requires accepted semantics, executable evidence, quality/security gates, platform/toolchain compatibility and **explicit owner consent under ADR-0014**.

Current owner consent: **NOT GRANTED**. Therefore **0/31 Authorized**.

## Shared blockers

| Area | Current paper state | Required evidence |
|---|---|---|
| WP/PHP/DB compatibility | ADR-0002/0123 | CF-01…CF-112 |
| Multisite Scope/Isolation | ADR-0069/0071/0141 | MSI-01…MSI-160; MS0–MS4 runtime certification |
| Site Lifecycle | ADR-0075/0141 | LC-01…LC-96; SL0–SL4 runtime certification |
| UI/design system | ADR-0005/0125 | UI-01…UI-104 |
| Job/Action Scheduler/Cron | ADR-0059/0068/0083/0119 | JS-01…JS-106 |
| Definition Repository | ADR-0073/0092/0132 | DEF-01…DEF-144; final D1–D4/DDL evidence-gated |
| Vault | ADR-0048/0085/0124 | VT-01…VT-128 |
| Free↔Pro/Product License | ADR-0010/0070/0072/0076/0091/0128 | FP-01…FP-144 |
| OAuth Account Link | ADR-0034/0101 | OA-01…OA-32 |
| Pro updater TUF | ADR-0044/0102 | TU-01…TU-44 |
| CI | ADR-0011/0127 | CI-01…CI-120 |
| Build | ADR-0012/0126 | BT-01…BT-112 |
| Query | ADR-0086/0131 | QRY-01…QRY-168; QP1–QP4 separate |
| Relations | ADR-0074/0093/0133 | REL-01…REL-160; final R/E/PV/DDL open |
| Workflow | ADR-0082/0118 | WF-01…WF-116 |
| Membership | ADR-0013…0090/0129 | MBR-01…MBR-160 + MB/PC certification |
| Backup | ADR-0021…0100/0130 | BK-01…BK-180 + C0–C4/V3 certification |
| Field Storage / Custom Fields | ADR-0022/0087/0134 | FST-01…FST-176; FS1–FS6 |
| Custom Tables | ADR-0023/0088/0135 | CTB-01…CTB-184; CT1–CT3 + CM1–CM4 |
| Notification | ADR-0026/0079/0120 | NT-01…NT-142 + NE1/NE2/channel evidence |
| Message & Chat | ADR-0027/0077/0121 | CH-01…CH-142 + CRT/private-asset/search/transport evidence |
| Webhooks/Connections/Event Inbox | ADR-0040/0055/0080/0122 | WC-01…WC-156 + I0–I5 + EI evidence |
| Admin Columns | ADR-0098/0136 | AC-01…AC-176 |
| Dynamic Listings | ADR-0039/0099/0137 | DL-01…DL-176 |
| Free CPT + Taxonomy | ADR-0138 | CPTX-01…CPTX-176 |
| Emails Builder render/composition | ADR-0029/0139 | EBR-01…EBR-176; ET0–ET5 separate |
| Platform Account/Docs/Support/Diagnostics | ADR-0140 | PLT-01…PLT-176 + FP/OA/TU/RS/Vault/UI prerequisites |
| Audit / Observability | ADR-0081/0142 | AUD-01…AUD-176; AU1/PT-D first baseline only |
| Kernel/Registry/Policy/Abilities/Events/SDK | ADR-0003/0004/0010/0143 | KPA-01…KPA-176 |
| Local Privacy / Data Lifecycle | ADR-0144 | PDL-01…PDL-176; RS separate |
| Error Taxonomy / Failure UX | ADR-0145 | ERR-01…ERR-176 |
| Component Blueprint Core | ADR-0035/0039/0099/0146 | CBP-01…CBP-176; BW/BC separate |
| Contract Versioning / Deprecation | ADR-0147 | VER-01…VER-176 |
| Module Lifecycle / Uninstall / Recovery | ADR-0148 | MLC-01…MLC-176 |
| Entity / Data Source Registry | ADR-0149 | DSR-01…DSR-176 |
| Asset Registry / Scoped Loader | ADR-0150 | ASR-01…ASR-176 |
| Conditional Logic Engine | ADR-0151 | CLG-01…CLG-176 |
| Dynamic Value / Token Resolver | ADR-0152 | DVR-01…DVR-176 |
| Shared Rate Limit / Abuse Control | ADR-0045/0153 | RLT-01…RLT-176; consumer certifications separate |
| Shared Cache / Invalidation | ADR-0154 | CAC-01…CAC-176; consumer certifications separate |
| Dashboard Widgets | ADR-0103 | DW-01…DW-36 |
| Admin Menu | ADR-0104 | AM-01…AM-40 |
| Protector | ADR-0045/0105 | PR-01…PR-44 + RLT/CAC/KPA/ERR/VER/MLC; WP42 reassessment current |
| Reset Manager | ADR-0106 | RM-01…RM-48 |
| Watermarker / Media | ADR-0107 | WM-01…WM-48 |
| Frontend Dashboard | ADR-0108 | FD-01…FD-48 |
| Builder Widgets adapters | ADR-0109 | BW-01…BW-50 + BC0…BC4 |
| Status Manager | ADR-0110 | SM-01…SM-48 |
| XML-RPC Manager | ADR-0111 | XR-01…XR-48 |
| Settings Page | ADR-0112 | ST-01…ST-48 |
| User Profile | ADR-0030/0096/0113/0158 | UP-01…UP-176 |
| Role & Capability | ADR-0032/0097/0114/0157 | RA-01…RA-176 |
| REST API Builder | ADR-0028/0094/0115/0155 | REST-01…REST-176 |
| Import / Export | ADR-0041/0095/0116/0156 | IM-01…IM-176 |
| Forms Runtime | ADR-0025/0077/0117 | FM-01…FM-92 + FRT1/FRT2 |
| Owner consent | ADR-0014 | blocks all executable work |

## Per-surface readiness

| # | Surface | Product maturity | Accepted/paper architecture | Remaining technical blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration + Definition + ADR-0138 | CF/DEF/KPA/UI/BT/CI + CPTX + VER/MLC/DSR/CAC + MSI/LC | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration + Definition + ADR-0138 | CF/DEF/KPA/UI/BT/CI + CPTX + VER/MLC/DSR/CAC + MSI/LC | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0087/0134 | FST + DSR/CLG/DVR/CAC + KPA/PDL/ERR/VER/MLC + MSI/LC | No |
| 4 | Relations Builder | Exhaustive | ADR-0074/0093/0133 | REL + DSR/CLG/DVR/CAC + final physical/locking + KPA/PDL/ERR/VER + MSI/LC | No |
| 5 | Status Manager | Exhaustive | ADR-0038/0110 | SM + CLG/DVR/CAC + KPA/ERR/VER/MLC + MSI/LC | No |
| 6 | Custom Query Builder | Exhaustive | ADR-0086/0131 | QRY + DSR/CLG/DVR/CAC + QP certification + KPA/PDL/ERR/VER + MSI | No |
| 7 | Custom Tables Builder | Exhaustive | ADR-0023/0088/0135 | CTB + DSR + CT/CM certification + PDL/ERR/VER/MLC + MSI/LC | No |
| 8 | Admin Columns Builder | Exhaustive | ADR-0098/0136 | AC + DSR/CLG/DVR/CAC + KPA/PDL/ERR + MSI | No |
| 9 | Dynamic Listings/Templates | Exhaustive | ADR-0039/0099/0137/0146 | DL + CBP + DSR/CLG/DVR/ASR/CAC + authorization/builder/SEO/MSI | No |
| 10 | Dashboard Widgets Manager | Exhaustive | ADR-0051/0103 | DW + KPA/CBP/CLG/DVR/ASR/CAC/UI/BT/CI + MSI | No |
| 11 | Custom Admin Menu Builder | Exhaustive | ADR-0037/0104 | AM + KPA/CLG/DVR/ASR/UI/BT/CI + MLC/MSI | No |
| 12 | Settings Page Builder | Exhaustive | ADR-0036/0089/0112 | ST + VT + DSR/CLG/DVR/CAC + KPA/PDL/ERR/VER + UI/BT/CI + MSI | No |
| 13 | Frontend Dashboard Builder | Exhaustive | ADR-0031/0108/0146 | FD + CBP + DSR/CLG/DVR/ASR/CAC + UI/BT/CI + MBR/MSI | No |
| 14 | User Profile Builder | Exhaustive | ADR-0030/0096/0113/0158 | UP 0/176 + FST/DSR/KPA/RA/PDL/ERR/CAC/VER/MLC + UI/BT/CI + MSI | No |
| 15 | Membership System | Exhaustive | ADR-0013…0090/0129 | MBR + MB/PC + KPA/PDL/ERR/VER/MLC/CLG/DVR/CAC/RA + MSI/LC | No |
| 16 | Builder Widgets Builder | Exhaustive | ADR-0035/0109/0146 | CBP + BW/BC + DSR/CLG/DVR/ASR/CAC + BT/CI + MSI | No |
| 17 | Forms & Workflow Builder | Exhaustive | FRT1/FRT2 + WF1/WF2 | FM + WF + JS + DSR/CLG/DVR/RLT/CAC + KPA/PDL/ERR/VER/MLC + MSI/LC | No |
| 18 | Cron Job Builder | Exhaustive | JobService J1/J2/J3 | JS + KPA/ERR/VER/MLC + MSI/LC | No |
| 19 | Notification System | Exhaustive | NE1/NE2 | NT + JS/WF/DSR/CLG/DVR/CAC + KPA/PDL/ERR/provider + MSI/LC | No |
| 20 | Emails Builder | Exhaustive | Email IR + ADR-0139 | EBR + ET + VT + DSR/CLG/DVR/ASR/CAC + KPA/PDL/ERR + MSI | No |
| 21 | Message & Chat | Exhaustive | CRT1/CRT2 | CH + DSR/CLG/DVR/CAC + MBR/private-asset/search/realtime + KPA/PDL/ERR + MSI/LC | No |
| 22 | REST API Builder | Exhaustive | ADR-0028/0094/0115/0155 | REST 0/176 + QRY/DSR/CLG/DVR/RLT/CAC/KPA/PDL/ERR/VER + MSI/provider profiles | No |
| 23 | Webhooks & Connections | Exhaustive | Safe HTTP + Gateway + Event Inbox | WC + VT + DSR/CLG/DVR/RLT/CAC + KPA/PDL/ERR/VER + I0–I5/EI + MSI/LC | No |
| 24 | Backup Manager | Exhaustive | ADR-0130 | BK + C0–C4/V3 + KPA/PDL/ERR/VER/MLC + MSI/LC | No |
| 25 | Reset Manager | Exhaustive | Plan + verified restore point + journal | RM + Backup + KPA/PDL/ERR/MLC + MSI/LC | No |
| 26 | Import / Export | Exhaustive | ADR-0041/0095/0116/0156 | IM 0/176 + DEF/FST/REL/CTB/DSR/VER/CAC/PDL/ERR/KPA + MSI/LC | No |
| 27 | Protector | Exhaustive | ADR-0045/0105 | PR 0/44 + RLT/CAC/KPA/ERR/VER/MLC + REST/XR/WC compatibility + MSI network-floor evidence; WP42 refinement current | No |
| 28 | Watermarker / Media Rules | Exhaustive | non-destructive derivatives | WM + DSR/DVR/ASR/CAC + KPA/PDL/ERR/MLC + MSI | No |
| 29 | XML-RPC Manager | Exhaustive | layered method/endpoint policy | XR + RLT/KPA/ERR/VER/MLC + MSI | No |
| 30 | Role & Capability Manager | Exhaustive | ADR-0032/0097/0114/0157 | RA 0/176 + KPA/CAC/ERR/VER/MLC + MBR role-sync + MSI | No |
| 31 | Account/Docs/Support/Diagnostics | Exhaustive | ADR-0140 | PLT + FP/OA/TU/RS/VT/KPA/PDL/ERR/VER/MLC/ASR/DVR/CAC/UI/BT/CI + MSI/LC | No |

## Current evidence/certification counters

- CF **0/112**; UI **0/104**; JS **0/106**; DEF **0/144**; VT **0/128**; FP **0/144**; CI **0/120**; BT **0/112**.
- QRY **0/168**; REL **0/160**; WF **0/116**; MBR **0/160**; BK **0/180**.
- FST **0/176**; CTB **0/184**; AC **0/176**; DL **0/176**; CPTX **0/176**; EBR **0/176**; PLT **0/176**; AUD **0/176**.
- KPA **0/176**; PDL **0/176**; ERR **0/176**; CBP **0/176**.
- VER **0/176**; MLC **0/176**; DSR **0/176**; ASR **0/176**; CLG **0/176**; DVR **0/176**.
- RLT **0/176**; CAC **0/176**; REST **0/176**; IM **0/176**; RA **0/176**; UP **0/176**.
- FM **0/92**; NT **0/142**; CH **0/142**; WC **0/156**; OA **0/32**; TU **0/44**.
- DW **0/36**; AM **0/40**; PR **0/44**; RM **0/48**; WM **0/48**; FD **0/48**; BW **0/50**; SM **0/48**; XR **0/48**; ST **0/48**.
- Email transport/provider **6 EE3 / 0 ET-certified**.
- Membership Billing **4 BE3 / 0 MB-certified**; protected files **0 PC1+**.
- Backup **34 provider targets / 0 C-certified / 0 C3; V3 0**.
- Connection adapters **0 I4/I5**; Multisite **0 MS1+**; Site Lifecycle runtime certs **0**; Remote privacy RS **0/30**.

## Recommended implementation order after future consent

1. CF/UI/BT/CI/JS/DEF/VT/FP/KPA/MSI/LC shared foundations;
2. VER/MLC/DSR/ASR/CLG/DVR/RLT/CAC + ERR/PDL/AUD cross-cutting runtime contracts;
3. CPT/Taxonomy → Field Storage → Relations → Query → Custom Tables;
4. Role/User Profile security foundations before broad identity-dependent UX;
5. Admin Columns/Blueprint/Listings/admin UX;
6. Membership + protected files/provider certification;
7. Forms/Workflow/Jobs → Notification/Email;
8. REST/Connections/Event Inbox/Import after their shared dependencies certify;
9. Protector/XML-RPC and other request-surface security profiles after RLT/KPA/ERR/MLC compatibility evidence;
10. Backup certification → Reset/destructive operations;
11. Chat after Membership/protected assets/jobs/notification;
12. OAuth/Product License/Platform service surfaces; TUF updater after verifier/key-ops bar;
13. AI only over certified scoped Abilities.

## Current conclusion

**Architecture/evidence contracts/refinements accepted through ADR-0158; all applicable runtime/toolchain/provider/Multisite decisions remain unverified until authorized evidence executes.**  
**31/31 Exhaustive. 0/31 Authorized. Implemented: none. Runtime verified: none.**

Current planning work: **`P0-M00-WP42` — Protector canonical evidence refinement**.

Planning/research/documentation only remains allowed until explicit owner development consent.