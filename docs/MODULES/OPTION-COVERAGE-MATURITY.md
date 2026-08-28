# WPEssential — Module Option Coverage & Maturity Ledger

Status: **Phase 0 planning source of truth / no development authorized**  
Date: 2026-08-29  
Current scope accepted through ADR-0197; exact planning evidence accepted through **ADR-0208**.

## Purpose

The owner requires every module's smallest practical option to be planned before development. Maturity levels remain:
1. Inventory
2. Behavioral
3. Exhaustive option spec
4. Accepted semantics
5. Implementation-ready technically
6. Authorized
7. Implemented
8. Verified

A surface can be **Exhaustive** while still technically blocked, unimplemented and unauthorized.

## Scope history

- Original product scope: **31/31 Exhaustive**.
- ADR-0177 universal-system expansion: **43/43 Exhaustive**.
- ADR-0183…ADR-0188 market expansion: **48/48 Exhaustive**.
- ADR-0189…ADR-0194 access/admin/media/code expansion: **50/50 Exhaustive**.
- ADR-0195 second competitive expansion: **55/55 Exhaustive**.
- ADR-0197 third competitive expansion: current **56/56 Exhaustive**.
- Current canonical denominator: **56 module/platform surfaces**.
- Implementation authorization: **0/56**.
- Implemented/runtime verified: **0**.

Historical denominators remain historically correct for their earlier scope snapshots.

## Current product-option coverage — 56/56 Exhaustive

| # | Surface | Option maturity | Primary product specification / evidence direction |
|---:|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | `FREE-CPT-TAXONOMY-EXHAUSTIVE-SPEC.md`; CPTX evidence |
| 2 | Taxonomy Builder | Exhaustive | `FREE-CPT-TAXONOMY-EXHAUSTIVE-SPEC.md`; CPTX evidence |
| 3 | Custom Fields Builder | Exhaustive | `CUSTOM-FIELDS-EXHAUSTIVE-SPEC.md`; FST evidence |
| 4 | Relations Builder | Exhaustive | `RELATIONS-STATUS-EXHAUSTIVE-SPEC.md`; REL evidence |
| 5 | Status Manager | Exhaustive | `RELATIONS-STATUS-EXHAUSTIVE-SPEC.md`; SM evidence |
| 6 | Custom Query Builder | Exhaustive | `QUERY-BUILDER-EXHAUSTIVE-SPEC.md`; QRY evidence |
| 7 | Custom Tables Builder | Exhaustive | `CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md`; CTB evidence |
| 8 | Admin Columns Builder | Exhaustive | `CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md`; AC evidence |
| 9 | Dynamic Listings / Templates | Exhaustive | `DYNAMIC-LISTINGS-EXHAUSTIVE-SPEC.md`; DL/CBP evidence |
| 10 | Dashboard Widgets Manager | Exhaustive | `ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md`; DW evidence |
| 11 | Custom Admin Menu Builder | Exhaustive | `ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md`; AM evidence |
| 12 | Settings Page Builder | Exhaustive | `ADMIN-DASHBOARD-MENU-SETTINGS-EXHAUSTIVE-SPEC.md`; ST evidence |
| 13 | Frontend Dashboard Builder | Exhaustive | `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md`; FD evidence |
| 14 | User Profile Builder | Exhaustive | `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md`; UP evidence |
| 15 | Membership System | Exhaustive + parity expansion | `MEMBERSHIP-SYSTEM.md` + parity addenda; MBR/MB-F/PC-F/MPR evidence |
| 16 | Builder Widgets Builder | Exhaustive | `BUILDER-WIDGETS-EXHAUSTIVE-SPEC.md`; BW/CBP evidence |
| 17 | Forms & Workflow Builder | Exhaustive | `FORMS-WORKFLOW-EXHAUSTIVE-SPEC.md`; FM/WF evidence |
| 18 | Cron Job Builder | Exhaustive | `CRON-JOB-BUILDER-EXHAUSTIVE-SPEC.md`; JS evidence |
| 19 | Notification System | Exhaustive | `NOTIFICATION-SYSTEM-EXHAUSTIVE-SPEC.md`; NT evidence |
| 20 | Emails Builder | Exhaustive | `EMAILS-BUILDER-EXHAUSTIVE-SPEC.md`; EBR/ET-F evidence |
| 21 | Message & Chat System | Exhaustive | `MESSAGE-CHAT-EXHAUSTIVE-SPEC.md`; CH evidence |
| 22 | REST API Builder | Exhaustive | `REST-API-BUILDER-EXHAUSTIVE-SPEC.md`; REST evidence |
| 23 | Webhooks & Connections Manager | Exhaustive | `WEBHOOKS-CONNECTIONS-EXHAUSTIVE-SPEC.md`; WC/ICP-F evidence |
| 24 | Backup Manager | Exhaustive | `BACKUP-MANAGER-EXHAUSTIVE-SPEC.md`; BK/BPC-F/BKX evidence |
| 25 | Reset Manager | Exhaustive | `RESET-MANAGER-EXHAUSTIVE-SPEC.md`; RM/RSX evidence |
| 26 | Import / Export | Exhaustive | `IMPORT-EXPORT-EXHAUSTIVE-SPEC.md`; IM evidence |
| 27 | Protector | Exhaustive | `PROTECTOR-EXHAUSTIVE-SPEC.md`; PR/RLT evidence |
| 28 | Watermarker / Media Rules + Performance Delivery | Exhaustive + parity expansion | media specs; WM/MDP evidence |
| 29 | XML-RPC Manager | Exhaustive | `XML-RPC-MANAGER-EXHAUSTIVE-SPEC.md`; XR evidence |
| 30 | Role & Capability Manager | Exhaustive + parity expansion | role specs; RA/RPR evidence |
| 31 | Platform Account / Docs / Support / Diagnostics | Exhaustive platform contract | `PLATFORM-SURFACES-SPEC.md`; PLT/RS/OA/TU evidence |
| 32 | Solution Blueprint & Application Composer | Exhaustive | universal foundation spec; SBP exact 176/176 documented / 0 executed |
| 33 | Analytics, Event Tracking & Journey Intelligence | Exhaustive | ANL exact 176/176 / 0 |
| 34 | Search & Indexing Engine | Exhaustive | SRH exact 176/176 / 0 |
| 35 | Decision, Formula, Scoring & Ranking Studio | Exhaustive | DEC exact 176/176 / 0 |
| 36 | Ledger, Balance & Movement Engine | Exhaustive | LED exact 176/176 / 0 |
| 37 | Resource Scheduling, Availability & Reservation Engine | Exhaustive | RSV exact 176/176 / 0 |
| 38 | Experience Placement & Personalization Manager | Exhaustive | PLC exact 176/176 / 0 |
| 39 | Experimentation & Feature Rollout Manager | Exhaustive | EXP exact 176/176 / 0 |
| 40 | Documents, Records & Template Generation | Exhaustive | DOC exact 176/176 / 0 |
| 41 | Data Sync, ETL & Integration Pipelines | Exhaustive | SYN exact 176/176 / 0 |
| 42 | Geospatial, Location & Territory Engine | Exhaustive | GEO exact 176/176 / 0 |
| 43 | AI Gateway, Knowledge & Copilot Studio | Exhaustive | AIP exact 176/176 / 0 |
| 44 | URL Redirection & Routing Manager | Exhaustive | **RDR exact 176/176 documented / 0 executed — ADR-0208** |
| 45 | Search, Replace & Data Transformation Engine | Exhaustive | **SRT exact 176/176 / 0 — ADR-0208** |
| 46 | Dummy Data, Synthetic Dataset & Fixture Studio | Exhaustive | **DMY exact 176/176 / 0 — ADR-0208** |
| 47 | Link Health, Broken Link & Crawl Intelligence | Exhaustive | **LNK exact 176/176 / 0 — ADR-0208** |
| 48 | Database Maintenance, Cleanup & Storage Health | Exhaustive | **DBM exact 176/176 / 0 — ADR-0208** |
| 49 | Admin Theme, Branding & Experience Manager | Exhaustive | ATM exact expansion pending WP114 |
| 50 | Safe Script, Tag & Code Injection Manager | Exhaustive | STM exact expansion pending WP114; no PHP/eval |
| 51 | Content Order & Sequence Manager | Exhaustive | ORD exact expansion pending WP115 |
| 52 | Security Integrity, Malware & Vulnerability Scanner | Exhaustive | SEC exact expansion pending WP115 |
| 53 | Font Library, Typography & Delivery Manager | Exhaustive | FNT/UAF exact expansions pending WP115/WP116 |
| 54 | User Data Stores, Favorites & Collections | Exhaustive | UDS exact expansion pending WP115 |
| 55 | Staging, Clone & Migration Manager | Exhaustive | STG/MIG exact expansions pending WP115/WP116 |
| 56 | Theme Workspace, Child Theme & Theme Customization Manager | Exhaustive | THM exact expansion pending WP116; no arbitrary live PHP execution |

## Shared planning services outside denominator

S07 Product Discovery & Planning Orchestrator and S08 Market Intelligence Radar remain shared services rather than numbered product surfaces. Under ADR-0208:
- PDO 176/176 exact documented / 0 executed;
- MIR 176/176 exact documented / 0 executed.

Other shared services/adapters retain their accepted ownership and evidence contracts.

## Current Multisite and AI coverage

- combined logical Multisite scope coverage: **56/56**;
- combined module-wide AI Prompt product mapping: **56/56**;
- runtime Multisite certification: **0**;
- AI Prompt/MCP runtime certifications: **0**.

## Remaining exact evidence planning

ADR-0207 identified 5,808 missing exact supplemental fixture definitions. ADR-0208 closed 1,232 Market Expansion definitions.

Remaining:
- **WP114 CURRENT** — MPR/RPR/ATM/MDP/STM = 880;
- WP115 — ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC = 1,936;
- WP116 — UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX = 1,760;
- total **4,576 / 26 namespaces**.

## Exhaustive specification minimum

Every surface remains responsible for list/editor UX, every known option/default, conditional behavior, validation, Policy, lifecycle/failure/recovery, dependencies/assets, import/export/revisions, AI/REST/Abilities/MCP/CLI where applicable, Multisite, privacy, performance, destructive safeguards and executable evidence.

A newly discovered meaningful option must be added to the applicable product spec before or with the coherent planning change. `56/56 Exhaustive` never authorizes ad-hoc implementation semantics.

## Development gate

**Development remains prohibited until explicit scoped owner consent under ADR-0014. Current implementation authorization is 0/56.**