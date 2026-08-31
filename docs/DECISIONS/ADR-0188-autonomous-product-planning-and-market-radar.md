# ADR-0188 — Autonomous Product Planning Orchestrator & Market Intelligence Radar

Status: **Accepted planning architecture / evidence pending / no development authorization**
Date: 2026-08-29

## Context

The owner requested that future planning not require manually repeating the same pre-development process. A minimal instruction such as “ABC system add karna hai” should trigger repository audit, public research, competitor analysis, capability mapping, gap classification, exhaustive product planning, AI/MCP/Multisite/security/evidence planning and governance-ready Draft artifacts. The owner also requested a daily ~24h market-research Git job that discovers new options/modules/systems and audits them against WPE.

## Decision 1 — S07 Planning Orchestrator

Accept non-sellable shared platform service:

**S07 Product Discovery & Pre-Development Planning Orchestrator**.

Canonical contract:
`docs/AI/AUTONOMOUS-PRODUCT-DISCOVERY-PLANNING-ORCHESTRATOR.md`

S07 composes F01 Solution Blueprint, F12 AI Gateway/Prompt Runtime, Requirement Compiler, Capability Gap Request, repository connectors, public research adapters and governance docs.

It produces planning artifacts only by default; it does not authorize or start source/runtime development.

## Decision 2 — S08 Market Radar

Accept non-sellable shared service:

**S08 Market Intelligence & Capability Radar**.

Canonical contract:
`docs/AI/MARKET-INTELLIGENCE-CAPABILITY-RADAR.md`

It monitors WordPress.org/plugin/core/provider/standard sources, extracts capability signals, dedupes them against WPE and routes high-value candidates into S07 Draft audits.

## Decision 3 — Daily GitHub job

Accept the disabled implementation plan in:
`docs/OPERATIONS/MARKET-INTELLIGENCE-DAILY-GITHUB-JOB.md`

Planned cadence: daily using GitHub Actions `schedule`, plus manual `workflow_dispatch`.

Default mode is report-only with least privilege. Optional docs-only Draft PR mode is separately permissioned and disabled by default.

**No executable `.github/workflows` file is enabled by this ADR.** Scheduled workflows run from the default branch and may be delayed, so the truthful contract is “daily”, not an exact 24-hour SLA.

## Decision 4 — Scope expansion

ADR-0183…ADR-0187 accept five new user-facing surfaces:
1. URL Redirection & Routing Manager;
2. Search, Replace & Data Transformation;
3. Dummy Data & Fixture Studio;
4. Link Health & Crawl Intelligence;
5. Database Maintenance & Cleanup.

Therefore current planned module/platform surface denominator becomes:
- original scope: 31;
- ADR-0177 universal foundations: +12;
- August 2026 market-expansion modules: +5;
- **current total: 48 module/platform surfaces**.

S07/S08 are shared services and do not add denominator rows.

Implementation authorization remains **0/48**.

## Decision 5 — Market audit reuse rules

Accept `docs/RESEARCH/MARKET-GAP-AUDIT-2026-08.md`.

Market research also concludes:
- Query Monitor-like deep diagnostics → expand existing Platform Diagnostics/Audit, not new module;
- Health Check/Troubleshooting Mode → Platform Diagnostics shared-service expansion;
- User Switching → controlled Support Impersonation option under User Profile/Role/Platform Support;
- WP Crontrol → existing Cron Job/JobService enhancement;
- activity/history → existing Audit/Observability;
- media replace/thumbnail regeneration → existing Watermarker/Media expansion;
- generic Code Snippets arbitrary execution → rejected by ADR-0004; use typed SDK/declarative mechanisms instead.

## Decision 6 — Evidence

Accept fixed future evidence namespaces from:
`docs/QUALITY/MARKET-EXPANSION-EXECUTABLE-EVIDENCE-MASTER-PLAN.md`

Current counters:
- RDR 0/176;
- SRT 0/176;
- DMY 0/176;
- LNK 0/176;
- DBM 0/176;
- PDO 0/176;
- MIR 0/176.

No fixture is executed.

## AI Prompt / MCP

All five new module surfaces inherit the shared ADR-0178 Prompt Runtime and must be mapped in the module AI Prompt standard. S07/S08 themselves may expose planning/read abilities through MCP, but canonical-scope acceptance, runtime code, merge/deploy and development consent remain separate privileges.

## Safety

The market system must not:
- auto-copy competitor code;
- auto-add every popular feature as a module;
- invent market facts;
- expose secrets;
- execute discovered plugin code;
- auto-merge canonical architecture by default;
- write runtime/source implementation;
- turn a market score into development authorization.

## Development gate

ADR-0188 is planning-only. No crawler, database transform, dummy-data generation, cleanup, market-scan script, scheduled GitHub workflow, AI research job or production implementation is authorized.
