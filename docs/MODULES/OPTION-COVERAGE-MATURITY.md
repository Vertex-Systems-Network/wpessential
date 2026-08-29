# WPEssential — Module Option Coverage & Maturity Ledger

Status: **Phase 0 planning source of truth / no development authorized**  
Date: 2026-08-29  
Current scope accepted through ADR-0197; exact planning evidence accepted through **ADR-0209**.

## Current maturity

- Current denominator: **56 module/platform surfaces**.
- Product-option maturity: **56/56 Exhaustive**.
- Logical Multisite mapping: **56/56**.
- Module-wide AI Prompt mapping: **56/56**.
- Implementation authorization: **0/56**.
- Implemented/runtime verified: **0**.

Historical scope lineage: 31 → 43 → 48 → 50 → 55 → 56. Historical denominators remain correct for their snapshots.

## Current surface ledger

| # | Surface | Maturity / current exact evidence direction |
|---:|---|---|
| 1 | Custom Post Types Builder | Exhaustive; CPTX later WP116 supplement |
| 2 | Taxonomy Builder | Exhaustive; CPTX later WP116 supplement |
| 3 | Custom Fields Builder | Exhaustive; FST existing evidence |
| 4 | Relations Builder | Exhaustive; REL existing evidence |
| 5 | Status Manager | Exhaustive; SM existing evidence |
| 6 | Custom Query Builder | Exhaustive; QRY existing evidence |
| 7 | Custom Tables Builder | Exhaustive; CTB existing evidence |
| 8 | Admin Columns Builder | Exhaustive; AC existing evidence |
| 9 | Dynamic Listings / Templates | Exhaustive; DL/CBP existing evidence |
| 10 | Dashboard Widgets Manager | Exhaustive; DW evidence |
| 11 | Custom Admin Menu Builder | Exhaustive; AM evidence |
| 12 | Settings Page Builder | Exhaustive; ST evidence |
| 13 | Frontend Dashboard Builder | Exhaustive; FD evidence |
| 14 | User Profile Builder | Exhaustive; PBX supplement current WP115 |
| 15 | Membership System | Exhaustive + parity; **MPR exact 176/176 / 0 — ADR-0209** |
| 16 | Builder Widgets Builder | Exhaustive; BW/CBP evidence |
| 17 | Forms & Workflow Builder | Exhaustive; FM/WF evidence |
| 18 | Cron Job Builder | Exhaustive; JS evidence |
| 19 | Notification System | Exhaustive; NT evidence |
| 20 | Emails Builder | Exhaustive; EBR/ET-F evidence |
| 21 | Message & Chat System | Exhaustive; CH evidence |
| 22 | REST API Builder | Exhaustive; REST evidence |
| 23 | Webhooks & Connections Manager | Exhaustive; WC/ICP-F evidence |
| 24 | Backup Manager | Exhaustive; BK/BPC-F plus BKX supplement current WP115 |
| 25 | Reset Manager | Exhaustive; RSX supplement WP116 |
| 26 | Import / Export | Exhaustive; IM evidence |
| 27 | Protector | Exhaustive; PR/RLT evidence |
| 28 | Watermarker / Media Rules + Performance Delivery | Exhaustive + parity; **MDP exact 176/176 / 0 — ADR-0209**; MRL current WP115 |
| 29 | XML-RPC Manager | Exhaustive; XR evidence |
| 30 | Role & Capability Manager | Exhaustive + parity; **RPR exact 176/176 / 0 — ADR-0209** |
| 31 | Platform Account / Docs / Support / Diagnostics | Exhaustive platform contract |
| 32 | Solution Blueprint & Application Composer | Exhaustive; SBP exact 176/176 / 0 |
| 33 | Analytics, Event Tracking & Journey Intelligence | Exhaustive; ANL exact 176/176 / 0 |
| 34 | Search & Indexing Engine | Exhaustive; SRH exact 176/176 / 0 |
| 35 | Decision, Formula, Scoring & Ranking Studio | Exhaustive; DEC exact 176/176 / 0 |
| 36 | Ledger, Balance & Movement Engine | Exhaustive; LED exact 176/176 / 0 |
| 37 | Resource Scheduling, Availability & Reservation Engine | Exhaustive; RSV exact 176/176 / 0 |
| 38 | Experience Placement & Personalization Manager | Exhaustive; PLC exact 176/176 / 0 |
| 39 | Experimentation & Feature Rollout Manager | Exhaustive; EXP exact 176/176 / 0 |
| 40 | Documents, Records & Template Generation | Exhaustive; DOC exact 176/176 / 0 |
| 41 | Data Sync, ETL & Integration Pipelines | Exhaustive; SYN exact 176/176 / 0 |
| 42 | Geospatial, Location & Territory Engine | Exhaustive; GEO exact 176/176 / 0 |
| 43 | AI Gateway, Knowledge & Copilot Studio | Exhaustive; AIP exact 176/176 / 0 |
| 44 | URL Redirection & Routing Manager | Exhaustive; RDR exact / ADR-0208 |
| 45 | Search, Replace & Data Transformation Engine | Exhaustive; SRT exact / ADR-0208 |
| 46 | Dummy Data, Synthetic Dataset & Fixture Studio | Exhaustive; DMY exact / ADR-0208 |
| 47 | Link Health, Broken Link & Crawl Intelligence | Exhaustive; LNK exact / ADR-0208; LHX supplement current WP115 |
| 48 | Database Maintenance, Cleanup & Storage Health | Exhaustive; DBM exact / ADR-0208 |
| 49 | Admin Theme, Branding & Experience Manager | Exhaustive; **ATM exact 176/176 / 0 — ADR-0209** |
| 50 | Safe Script, Tag & Code Injection Manager | Exhaustive; **STM exact 176/176 / 0 — ADR-0209; no PHP/eval/server code**; HFC current WP115 |
| 51 | Content Order & Sequence Manager | Exhaustive; ORD exact expansion current WP115 |
| 52 | Security Integrity, Malware & Vulnerability Scanner | Exhaustive; SEC current WP115 |
| 53 | Font Library, Typography & Delivery Manager | Exhaustive; FNT current WP115; UAF WP116 |
| 54 | User Data Stores, Favorites & Collections | Exhaustive; UDS current WP115 |
| 55 | Staging, Clone & Migration Manager | Exhaustive; STG current WP115; MIG WP116 |
| 56 | Theme Workspace, Child Theme & Theme Customization Manager | Exhaustive; THM WP116; no arbitrary live PHP execution |

## Shared services outside denominator

S07 PDO and S08 MIR are shared services, both exact 176/176 documented / 0 executed under ADR-0208.

## Exact supplemental evidence progress

ADR-0207 identified **5,808 missing exact definitions / 33 namespaces**.

Completed:
- WP113 / ADR-0208 — 1,232 / 7;
- WP114 / ADR-0209 — **880 / 5**.

Remaining:
- **WP115 CURRENT** — ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC = **1,936**;
- WP116 — UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX = **1,760**;
- total **3,696 / 21 namespaces**.

## Development gate

`56/56 Exhaustive` and exact evidence design never authorize implementation. Production development remains prohibited until explicit scoped owner consent under ADR-0014. Current authorization: **0/56**.