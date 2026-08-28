# WPEssential — Module Option Coverage & Maturity Ledger

Status: **Phase 0 planning source of truth / no development authorized**
Date: 2026-08-29
Expanded scope accepted by: ADR-0177 + ADR-0183…ADR-0188.

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
- Current canonical denominator: **48 module/platform surfaces**.
- Shared services such as S07 Product Discovery/Planning Orchestrator and S08 Market Intelligence Radar do **not** add denominator rows.
- Implementation authorization: **0/48**.
- Implemented/runtime verified: **0**.

Historical `31/31`, `43/43`, `0/31` and `0/43` statements remain historically correct for their earlier scope snapshots.

## Current product-option coverage — 48/48 Exhaustive

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
| 15 | Membership System | Exhaustive + accepted core semantics | `MEMBERSHIP-SYSTEM.md`; MBR/MB-F/PC-F evidence |
| 16 | Builder Widgets Builder | Exhaustive | `BUILDER-WIDGETS-EXHAUSTIVE-SPEC.md`; BW/CBP evidence |
| 17 | Forms & Workflow Builder | Exhaustive | `FORMS-WORKFLOW-EXHAUSTIVE-SPEC.md`; FM/WF evidence |
| 18 | Cron Job Builder | Exhaustive | `CRON-JOB-BUILDER-EXHAUSTIVE-SPEC.md`; JS evidence |
| 19 | Notification System | Exhaustive | `NOTIFICATION-SYSTEM-EXHAUSTIVE-SPEC.md`; NT evidence |
| 20 | Emails Builder | Exhaustive | `EMAILS-BUILDER-EXHAUSTIVE-SPEC.md`; EBR/ET-F evidence |
| 21 | Message & Chat System | Exhaustive | `MESSAGE-CHAT-EXHAUSTIVE-SPEC.md`; CH evidence |
| 22 | REST API Builder | Exhaustive | `REST-API-BUILDER-EXHAUSTIVE-SPEC.md`; REST evidence |
| 23 | Webhooks & Connections Manager | Exhaustive | `WEBHOOKS-CONNECTIONS-EXHAUSTIVE-SPEC.md`; WC/ICP-F evidence |
| 24 | Backup Manager | Exhaustive | `BACKUP-MANAGER-EXHAUSTIVE-SPEC.md`; BK/BPC-F evidence |
| 25 | Reset Manager | Exhaustive | `RESET-MANAGER-EXHAUSTIVE-SPEC.md`; RM evidence |
| 26 | Import / Export | Exhaustive | `IMPORT-EXPORT-EXHAUSTIVE-SPEC.md`; IM evidence |
| 27 | Protector | Exhaustive | `PROTECTOR-EXHAUSTIVE-SPEC.md`; PR/RLT evidence |
| 28 | Watermarker / Media Rules | Exhaustive | `WATERMARKER-MEDIA-RULES-EXHAUSTIVE-SPEC.md`; WM evidence |
| 29 | XML-RPC Manager | Exhaustive | `XML-RPC-MANAGER-EXHAUSTIVE-SPEC.md`; XR evidence |
| 30 | Role & Capability Manager | Exhaustive | `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md`; RA evidence |
| 31 | Platform Account / Docs / Support / Diagnostics | Exhaustive platform contract | `PLATFORM-SURFACES-SPEC.md`; PLT/RS/OA/TU evidence |
| 32 | Solution Blueprint & Application Composer | Exhaustive product behavior | `../SOLUTIONS/UNIVERSAL-FOUNDATIONS-EXHAUSTIVE-SPEC.md`; SBP 0/176 |
| 33 | Analytics, Event Tracking & Journey Intelligence | Exhaustive product behavior | universal foundation spec; ANL 0/176 |
| 34 | Search & Indexing Engine | Exhaustive product behavior | universal foundation spec; SRH 0/176 envelope |
| 35 | Decision, Formula, Scoring & Ranking Studio | Exhaustive product behavior | universal foundation spec; DEC 0/176 envelope |
| 36 | Ledger, Balance & Movement Engine | Exhaustive product behavior | universal foundation spec; LED 0/176 envelope |
| 37 | Resource Scheduling, Availability & Reservation Engine | Exhaustive product behavior | universal foundation spec; RSV 0/176 envelope |
| 38 | Experience Placement & Personalization Manager | Exhaustive product behavior | universal foundation spec; PLC 0/176 envelope |
| 39 | Experimentation & Feature Rollout Manager | Exhaustive product behavior | universal foundation spec; EXP 0/176 envelope |
| 40 | Documents, Records & Template Generation | Exhaustive product behavior | universal foundation spec; DOC 0/176 envelope |
| 41 | Data Sync, ETL & Integration Pipelines | Exhaustive product behavior | universal foundation spec; SYN 0/176 envelope |
| 42 | Geospatial, Location & Territory Engine | Exhaustive product behavior | universal foundation spec; GEO 0/176 envelope |
| 43 | AI Gateway, Knowledge & Copilot Studio | Exhaustive product behavior | universal foundation spec + `../AI/`; AIP 0/176 |
| 44 | URL Redirection & Routing Manager | **Exhaustive product behavior** | `URL-REDIRECTION-ROUTING-EXHAUSTIVE-SPEC.md`; RDR 0/176 |
| 45 | Search, Replace & Data Transformation Engine | **Exhaustive product behavior** | `SEARCH-REPLACE-DATA-TRANSFORMATION-EXHAUSTIVE-SPEC.md`; SRT 0/176 |
| 46 | Dummy Data, Synthetic Dataset & Fixture Studio | **Exhaustive product behavior** | `DUMMY-DATA-FIXTURE-GENERATOR-EXHAUSTIVE-SPEC.md`; DMY 0/176 |
| 47 | Link Health, Broken Link & Crawl Intelligence | **Exhaustive product behavior** | `LINK-HEALTH-BROKEN-LINK-CRAWLER-EXHAUSTIVE-SPEC.md`; LNK 0/176 |
| 48 | Database Maintenance, Cleanup & Storage Health | **Exhaustive product behavior** | `DATABASE-MAINTENANCE-CLEANUP-EXHAUSTIVE-SPEC.md`; DBM 0/176 |

## Current shared-service planning outside denominator

Accepted/shared architecture currently also includes:
- Simulation & Historical Replay Service;
- Transaction / Saga Coordination Contract;
- generalized Protected Asset Service;
- Context Resolver;
- Money / Decimal / Unit Type Library;
- Approval Policy Profile;
- **S07 Product Discovery & Pre-Development Planning Orchestrator**;
- **S08 Market Intelligence & Capability Radar**.

Domain/provider adapter packs such as the WooCommerce Commerce Domain Adapter also remain outside the module denominator.

## Current Multisite coverage

- surfaces 1–31: `MULTISITE-SCOPE-OPTION-MATRIX.md`;
- surfaces 32–43: `../SOLUTIONS/UNIVERSAL-FOUNDATIONS-MULTISITE-SCOPE-MATRIX.md`;
- surfaces 44–48: `MARKET-EXPANSION-MULTISITE-SCOPE-MATRIX.md`;
- combined logical scope coverage: **48/48**;
- runtime Multisite certification: **0**.

## Current AI Prompt coverage

- surfaces 1–43: `../AI/MODULE-AI-PROMPT-OPTION-STANDARD.md`;
- surfaces 44–48: `../AI/MARKET-EXPANSION-AI-PROMPT-MAPPING.md`;
- combined module-wide AI Prompt product mapping: **48/48**;
- AI Prompt/MCP executable evidence AIP: **0/176**;
- AIC/MCP runtime certifications: **0**.

## Exhaustive specification minimum

Every surface remains responsible for:
- list screens, filters, search, sort and bulk actions;
- editor tabs/sections and every known field/control/default;
- conditional visibility/dependencies;
- validation/sanitization/normalization;
- save/publish/archive/delete behavior;
- preview/test/run semantics;
- roles/capabilities/Policy/re-auth;
- revisions/import/export;
- health/observability/errors/degraded states;
- AI Prompt/gap-request behavior;
- REST/Abilities/MCP/CLI where applicable;
- Multisite scope/lifecycle;
- privacy/retention;
- performance/scale guardrails;
- destructive safeguards/recovery;
- future evidence namespace/protocol.

A newly discovered meaningful option must be added to the applicable product spec before or with the coherent planning change. `48/48 Exhaustive` never authorizes ad-hoc implementation semantics.

## Development gate

**Development remains prohibited until explicit scoped owner consent under ADR-0014. Current implementation authorization is 0/48.**
