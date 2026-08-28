# WPEssential — Work Coordination Ledger

Status: **Active governance ledger**
Last reviewed: 2026-08-29

## 1. Current execution state

Project state: `PLANNED_EXISTING_PROJECT`
Execution mode: `PLANNER_ONLY`
Current planning lifecycle: `SPECIFICATION`
Production implementation WIP: **0**
Active implementation approvals: **0**
Current planned module/platform surfaces: **48**
Authorized module/platform surfaces: **0/48**
Current logical Multisite mappings: **48/48**
Current AI Prompt product mappings: **48/48**

Planning/documentation/research is allowed. Executable/source/runtime work remains blocked by ADR-0014.

Historical denominators: 31 original; 43 after ADR-0177; current 48 after ADR-0188.

## 2. Historical planning work

Work packages `P0-M00-WP01…WP59` remain DONE and retain their original evidence/ADR semantics. They are planning completion records, not implementation/runtime claims.

## 3. Universal-system work packages

| Work ID | Scope | Lifecycle | Class | Parallelism | Evidence / note |
|---|---|---|---|---|---|
| `P0-M00-WP60` | Solution Blueprint + universal systems + 12 foundations + Woo adapter expansion | DONE | SHARED_CONTRACT | SERIALIZE | ADR-0177; 43-surface milestone; 160 curated systems; 40 patterns; 268,800 raw primary combinations |
| `P0-M00-WP61` | Module-wide AI Prompt / Requirement Compiler / MCP / gap request | DONE | SHARED_CONTRACT | SERIALIZE | ADR-0178/0179; AIP 0/176 |
| `P0-M00-WP62` | Universal foundations + Woo adapter evidence master plan | DONE | BLOCKING_FOUNDATION | SERIALIZE | ADR-0180; SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA 0/176 |
| `P0-M00-WP63` | F01 Solution Blueprint detailed evidence | DONE | BLOCKING_FOUNDATION | SERIALIZE | ADR-0181; SBP 0/176 |
| `P0-M00-WP64` | F02 Analytics/Event/Journey detailed evidence | DONE | BLOCKING_FOUNDATION | SERIALIZE | ADR-0182; ANL 0/176 |
| `P0-M00-WP65` | F03 Search & Indexing detailed evidence | SPECIFICATION | BLOCKING_FOUNDATION | SERIALIZE | SRH envelope 0/176; **current resume point** after owner-requested market audit |

## 4. Reserved follow-on universal work IDs

These IDs were reserved before the market-expansion interrupt and must not be reused:
- WP66 — F04 Decision/Formula/Scoring (`DEC`)
- WP67 — F05 Ledger/Balance/Movement (`LED`)
- WP68 — F06 Resource Scheduling/Reservation (`RSV`)
- WP69 — F07 Placement/Personalization (`PLC`)
- WP70 — F08 Experimentation/Rollout (`EXP`)
- WP71 — F09 Documents/Records (`DOC`)
- WP72 — F10 Sync/ETL (`SYN`)
- WP73 — F11 Geo/Territory (`GEO`)
- WP74 — WooCommerce Commerce Domain Adapter (`WCA`)

Planning order can be reassessed but stable work IDs are not repurposed.

## 5. Owner-requested market-expansion interrupt — DONE

| Work ID | Scope | Lifecycle | Class | Evidence / result |
|---|---|---|---|---|
| `P0-M00-WP75` | Redirection/Better Search Replace/fixture + broader WordPress market gap audit | DONE | RESEARCH/SHARED_CONTRACT | market audit; reuse/new-module decisions; source provenance |
| `P0-M00-WP76` | URL Redirection & Routing Manager | DONE | SHARED_CONTRACT | ADR-0183; RDR 0/176 |
| `P0-M00-WP77` | Search, Replace & Data Transformation | DONE | HIGH_RISK_DATA | ADR-0184; SRT 0/176 |
| `P0-M00-WP78` | Dummy Data & Fixture Studio | DONE | DEVELOPER_TOOL | ADR-0185; DMY 0/176 |
| `P0-M00-WP79` | Link Health & Crawl Intelligence | DONE | INTEGRATION/HTTP | ADR-0186; LNK 0/176 |
| `P0-M00-WP80` | Database Maintenance & Cleanup | DONE | HIGH_RISK_DATA | ADR-0187; DBM 0/176 |
| `P0-M00-WP81` | S07 Product Discovery & Pre-Development Planning Orchestrator | DONE | SHARED_CONTRACT | ADR-0188; PDO 0/176 |
| `P0-M00-WP82` | S08 Market Intelligence Radar + daily Git job design | DONE | SHARED_CONTRACT | ADR-0188; MIR 0/176; executable workflow NOT installed |

## 6. Current scope/evidence expansion

New module surfaces accepted by ADR-0183…0188:
- 44 URL Redirection & Routing — RDR 0/176;
- 45 Search/Replace & Data Transformation — SRT 0/176;
- 46 Dummy Data & Fixture Studio — DMY 0/176;
- 47 Link Health & Crawl Intelligence — LNK 0/176;
- 48 Database Maintenance & Cleanup — DBM 0/176.

Shared services outside denominator:
- S07 Planning Orchestrator — PDO 0/176;
- S08 Market Radar — MIR 0/176.

All are planning-only; no fixture is executed.

## 7. Market-driven existing-surface enhancements

Planned without new work-ID/module denominator inflation:
- deep developer request/query/hook/REST/asset diagnostics → Platform Diagnostics/Audit;
- isolated Troubleshooting Session Mode → Platform Diagnostics shared service;
- controlled Support Impersonation → User Profile/Role/Platform Support;
- native WP-Cron inspector → Cron/JobService;
- human-readable Activity History → Audit;
- media replace/regenerate derivatives → Media Rules;
- generic Code Snippets arbitrary execution → rejected under ADR-0004.

Detailed source: `docs/MODULES/MARKET-RESEARCH-EXISTING-SURFACE-ENHANCEMENTS.md`.

## 8. Shared-surface planning reservations

- F03 Search owns SRH planning during WP65.
- Redirect Manager owns request-time Redirect Definitions/routing, not general crawling.
- Link Health owns crawl/link inventory/issues, not source mutations.
- Search/Replace owns planned deterministic transforms; owning module APIs remain mutation authority.
- Dummy Data owns synthetic fixture Runs/cleanup ownership; it does not become Reset Manager.
- DB Maintenance owns owner-aware retention/cleanup Plans; no arbitrary SQL cleanup.
- S07 owns pre-development planning orchestration; S08 owns market signal discovery.
- AI Prompt Runtime remains shared; no new module gets a private provider stack.

Implementation shared-surface reservations remain **0**.

## 9. Daily market Git job truth

The exact planned GitHub Actions YAML lives in:
`docs/OPERATIONS/MARKET-INTELLIGENCE-DAILY-GITHUB-JOB.md`.

It is intentionally documentation-only. No executable `.github/workflows/market-intelligence*.yml` file is installed/enabled before explicit development authorization and CI/security review.

## 10. Current next safe action

Resume **P0-M00-WP65 — F03 Search & Indexing detailed executable-evidence specification**.

Do not begin production code, install packages, create DB tables, execute Search/Replace/cleanup, create dummy data, crawl URLs, enable scheduled Actions, collect market data automatically, call AI providers/MCP or mutate WordPress runtime.
