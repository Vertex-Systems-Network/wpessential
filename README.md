# WPEssential

WPEssential is a modular WordPress application platform for structured data, automation, integrations, admin tooling, workflows, and AI-ready operations.

Project website: **https://wpessential.org**

> **Status:** Phase 0 planning is complete. Source development is active under explicit `GOV-OWNER-CONSENT-001` and remains milestone-gated. Production deployment and separately privileged destructive/live-provider operations are not implied by source-development approval.

## Current lifecycle

- Accepted product scope: **56/56 Exhaustive**
- Multisite planning: **56/56**
- AI Prompt planning: **56/56**
- Planning closure/integration authority: through **ADR-0213**
- Implementation Baseline: **WP119 / ADR-0214 PASS**
- Machine-enforced architecture guards: **WP120 / ADR-0215 PASS**
- Platform Foundation: **WP121 CURRENT / IMPLEMENTING**
- Owner engineering contract: **ADR-0216**
- Atomic compiled-registration persistence: **ADR-0217**
- Definition + Audit persistence: **ADR-0218**
- WordPress.org metadata + direct-access security: **ADR-0219**
- Real WordPress AJAX/nonce/Policy integration: **ADR-0220**

The current implementation branch is `implementation/baseline-adoption-gate`. Draft PR #2 carries the Platform Foundation work.

## Module progress dashboard

The **56-module master plan is complete**. This dashboard tracks each canonical module/surface through the Master Options Bank certification lifecycle; it does **not** claim runtime implementation or production release completion.

**Progress model:** `UNSEEDED = 0%` → `BANK_SURFACE_SEEDED = 25%` → `NATIVE_AUDITED = 50%` → `MARKET_AUDITED = 75%` → `BANK_REVIEWED = 100%`.

- Canonical modules planned: **56 / 56 (100%)**
- Modules with Bank work started: **4 / 56**
- Fully Bank-reviewed modules: **1 / 56**
- Current Bank records: **938**
- Weighted Bank-readiness snapshot: **3.6%**
- Current active surface: **Relations — Market Audit V1**
- Estimated Bank-review program completion: **~2026-10-26**

> **Date meaning:** “Bank Review Date” is a working estimate for completing planning/research certification for that surface, not a promise of runtime implementation, release, or production deployment. Dates are re-baselined when research, compatibility work, or CI exposes additional gaps.

| # | Module / Surface | Progress | Status | Bank Review Date |
|---:|---|---|---|---|
| 1 | CPT | `██░░░░░░░░ 25%` | 🟠 BANK_SURFACE_SEEDED | ~2026-09-03 |
| 2 | Taxonomy | `██░░░░░░░░ 25%` | 🟠 BANK_SURFACE_SEEDED | ~2026-09-04 |
| 3 | Fields / Field Groups | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-01** (complete) |
| 4 | Relations | `█████░░░░░ 50%` | 🟡 NATIVE_AUDITED | ~2026-09-02 |
| 5 | Status | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-05 |
| 6 | Query Builder | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-06 |
| 7 | Custom Tables / Content Tables | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-07 |
| 8 | Admin Columns | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-08 |
| 9 | Listings | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-09 |
| 10 | Dashboard Widgets | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-10 |
| 11 | Admin Menu | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-11 |
| 12 | Settings / Options Pages | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-12 |
| 13 | Frontend Dashboards | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-13 |
| 14 | User Profiles | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-14 |
| 15 | Membership | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-15 |
| 16 | Builder Widgets / Dynamic Components | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-16 |
| 17 | Forms / Workflows | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-17 |
| 18 | Cron / Schedules | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-18 |
| 19 | Notifications | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-19 |
| 20 | Emails | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-20 |
| 21 | Chat | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-21 |
| 22 | REST API Builder | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-22 |
| 23 | Connections | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-23 |
| 24 | Backup | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-24 |
| 25 | Reset | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-25 |
| 26 | Import / Export | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-26 |
| 27 | Protector | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-27 |
| 28 | Media | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-28 |
| 29 | XML-RPC | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-29 |
| 30 | Roles | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-30 |
| 31 | Platform | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-01 |
| 32 | Solutions | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-02 |
| 33 | Analytics | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-03 |
| 34 | Search | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-04 |
| 35 | Decision / Rules | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-05 |
| 36 | Ledger / Activity Audit | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-06 |
| 37 | Reservations | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-07 |
| 38 | Placement | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-08 |
| 39 | Experiments | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-09 |
| 40 | Documents | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-10 |
| 41 | Sync | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-11 |
| 42 | Geo | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-12 |
| 43 | AI Gateway | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-13 |
| 44 | Redirects | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-14 |
| 45 | Transform | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-15 |
| 46 | Fixtures | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-16 |
| 47 | Link Health | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-17 |
| 48 | Database Maintenance | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-18 |
| 49 | Admin Theme | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-19 |
| 50 | Safe Script | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-20 |
| 51 | Content Order | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-21 |
| 52 | Security Scanner | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-22 |
| 53 | Fonts | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-23 |
| 54 | User Stores | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-24 |
| 55 | Staging | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-25 |
| 56 | Theme Workspace | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-10-26 |

The machine-readable source of truth for current counts and lifecycle status is `config/product/options-bank-progress.json`. The dates above are README planning estimates and intentionally remain separate from the machine-certified lifecycle state.

## Multi-agent work command

Use the following prompt when assigning work to any AI/coding/planning agent. It is intentionally **not module-only**: the agent must first determine whether the request is a surface/module task, existing milestone/work package, shared-foundation task, integration, fix, research/audit, release/QA or recovery work.

Replace only the text inside `<REQUESTED WORK>` when starting another agent.

```text
Work on the WPEssential repository from current repository truth.

Repository:
https://github.com/Vertex-Systems-Network/wpessential

Before doing anything, read and follow root AGENTS.md, CONTRIBUTING.md, current CHECKPOINT.md, project-state/adoption docs, approval/work lifecycle ledger, engineering execution governance, canonical ownership/dependency contracts, and all files relevant to the requested work.

Requested work:
<REQUESTED WORK>

Do not assume this is necessarily a single module task.

First autonomously determine the correct work mode from repository evidence:
- SURFACE_WORK
- MILESTONE_WORK
- WORK_PACKAGE
- SHARED_FOUNDATION
- INTEGRATION_WORK
- REGRESSION_FIX
- RESEARCH_AUDIT
- RELEASE_QA
- RECOVERY

Resolve the exact current plan, milestone/work-package/task ID, canonical owner(s), dependencies, lifecycle state, completed work, active blockers, allowed write scope, and shared/integrator-owned files before making changes.

If this request corresponds to an existing approved plan/work package, continue that existing plan from its latest verified checkpoint. Do not create a competing plan, restart completed work, or force it into a module workflow.

If it is surface/module Options Bank work, follow:
Audit → current WordPress/native research → current market/provider research → Options Bank → ownership/duplicate resolution → Native Audit → Market Audit → Bank Review → UX projection → implementation contract.

If it is implementation or shared/integration work, follow the repository's approved milestone/work-package lifecycle and dependency order instead of unnecessarily running the Options Bank lifecycle again.

Multi-agent rules are mandatory:
- dedicated branch from an explicit current main SHA
- one primary writer per assigned work scope
- no overlapping active write ownership
- canonical owner/no-bypass rules
- no peer-private storage/runtime access
- module-local workers do not race on shared/global files
- shared changes become Integration Requirements unless this agent is the designated integrator
- stale branch must sync with latest main before final certification
- exact-head applicable CI only
- never weaken valid tests or invent completion

Do not ask the repository owner to restate ordinary architecture, UI, options, plan details, or module boundaries when repository evidence can resolve them.

Ask/escalate only if there is a genuinely unresolved product decision, conflicting authoritative contracts, missing authorization/credentials, or another privileged/irreversible action.

Final report must state:
- detected work mode
- plan/milestone/work-package/task ID if applicable
- canonical owner/surfaces affected
- base SHA and branch
- work completed
- files changed
- lifecycle/status changes
- Integration Requirements
- tests/CI with exact head SHA
- unresolved items
- next safe action
```

Examples for `<REQUESTED WORK>`:

- `Continue Relations from the latest certified repository checkpoint.`
- `Continue the current Platform Foundation milestone.`
- `Continue P2-M4-WP3 from where it stopped.`
- `Fix the current Options Bank schema problem.`
- `Audit and implement the approved shared Job Service work package.`

The detailed parallel-development rules remain canonical in root `AGENTS.md` and `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`; this README prompt is only the reusable task-entry command.

## What WPEssential is

WPEssential is designed as one governed platform rather than a collection of unrelated mini-frameworks. Business modules compose shared contracts for:

- canonical data and semantic ownership;
- definitions and compiled WordPress registrations;
- capability and Policy authorization;
- Abilities and typed events;
- scoped persistence and migrations;
- jobs and external integrations;
- audit and diagnostics;
- WordPress bridges, AJAX, nonce and runtime security;
- Multisite isolation;
- AI/MCP-safe invocation boundaries.

Core rule: every business semantic has one canonical owner. UI, REST, Workflow, Cron, CLI and AI are invocation channels and cannot create private duplicate engines or bypass the canonical owner Policy/Ability/storage path.

## Engineering contract

Production implementation must preserve:

- namespace `WPEssential`;
- canonical PSR-4 source root `frameworks/`;
- global functions `wpessential_*`;
- constants `WPE_*`;
- exact custom filters `wpesential/apply_*`;
- custom actions `wpessential/hook_*`;
- one typed allowlisted AJAX gateway;
- centralized nonce operation handling;
- compile-on-write runtime registrations;
- bounded/redacted Runtime Observatory diagnostics;
- direct-access `ABSPATH` guards on shipped PHP source.

The asymmetric `wpesential` filter spelling is intentional public API.

See `CONTRIBUTING.md` for contribution and WordPress.org release rules. `readme.txt` is the WordPress.org-facing plugin documentation draft for the current development line.

## Current foundation evidence

Hosted CI currently covers Composer metadata, canonical architecture/engineering guards, PHP syntax, smoke tests, MySQL integration fixtures, and a pinned real WordPress 7.1 AJAX/nonce/Policy fixture. Accepted runtime evidence includes atomic compiled registrations, scoped Definition/Audit persistence, WordPress.org/direct-access security invariants, and the canonical Ability-backed AJAX path.

Current next Platform Foundation target is **Action Scheduler coexistence/packaging/backend evidence**, followed by durable Job persistence, Platform/Runtime Observatory admin surfaces, 10K/100K compiled-registration performance evidence, and the final shared-foundation readiness gate before business-module development.

## Canonical planning maps

- `docs/ARCHITECTURE/CANONICAL-56-SURFACE-OWNERSHIP-REGISTRY.md`
- `docs/ARCHITECTURE/CROSS-MODULE-OPTION-OWNERSHIP-AND-NO-BYPASS-CONTRACT.md`
- `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`
- `docs/UI/ADMIN-INFORMATION-ARCHITECTURE-V2-56-SURFACES.md`
- `docs/SOLUTIONS/SYSTEM-PATTERN-TO-CANONICAL-SURFACE-MAP.md`
- `docs/ARCHITECTURE/CANONICAL-56-SURFACE-DEPENDENCY-RELATION-MATRIX.md`
- `docs/ARCHITECTURE/PER-SURFACE-CAPABILITY-ABILITY-EVENT-REGISTRY-32-56.md`
- `docs/ARCHITECTURE/DATA-OWNERSHIP-LIFECYCLE-REGISTRY-32-56.md`
- `docs/QUALITY/POST-P0-MODULE-OPTION-UI-SYSTEM-INTEGRITY-AUDIT.md`

Repository evidence and accepted ADRs override stale conversational summaries.
