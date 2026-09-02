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
- Platform Foundation: **WP121 DONE / PASS FOR MODULE HANDOFF**
- Owner engineering contract: **ADR-0216**
- Atomic compiled-registration persistence: **ADR-0217**
- Definition + Audit persistence: **ADR-0218**
- WordPress.org metadata + direct-access security: **ADR-0219**
- Real WordPress AJAX/nonce/Policy integration: **ADR-0220**
- Phase 2 Gate A / Fields: **PASS for the certified native V1 scope**
- Phase 2 Gate B / Relations: **IN PROGRESS** — canonical definition lifecycle, durable edge persistence, and transactional connect/disconnect foundation are merged; Gate B closure criteria remain open
- Phase 2 Gates C–E / Query → Admin Columns → Dynamic Listings: **dependency-gated after Relations**
- Status runtime: **blocked until Gates A–E are complete**

Audit anchor for this README reconciliation: `main @ ecf18c2e0cab9bd4a9cfd689d1b016babf9f09c0` on **2026-09-02**. Repository/machine evidence remains authoritative if this prose later becomes stale.

## Module progress dashboard

The **56-module master plan is complete**. This dashboard tracks each canonical module/surface through the Master Options Bank certification lifecycle; it does **not** claim runtime implementation or production release completion.

**Progress model:** `UNSEEDED = 0%` → `BANK_SURFACE_SEEDED = 25%` → `NATIVE_AUDITED = 50%` → `MARKET_AUDITED = 75%` → `BANK_REVIEWED = 100%`.

- Canonical modules planned: **56 / 56 (100%)**
- Modules with Bank work started: **8 / 56**
- Fully Bank-reviewed modules: **6 / 56**
- Current Bank records: **1,571**
- Weighted Bank-readiness snapshot: **11.6%**
- Current certified Bank checkpoint: **6 surfaces BANK_REVIEWED** — Fields, Relations, Status, Custom Tables, Admin Columns, Dashboard Widgets
- Estimated Bank-review program completion: **~2026-10-26**

> **Date meaning:** “Bank Review Date” is a working estimate for completing planning/research certification for that surface, not a promise of runtime implementation, release, or production deployment. Dates are re-baselined when research, compatibility work, or CI exposes additional gaps.

| # | Module / Surface | Progress | Status | Bank Review Date |
|---:|---|---|---|---|
| 1 | CPT | `██░░░░░░░░ 25%` | 🟠 BANK_SURFACE_SEEDED | ~2026-09-03 |
| 2 | Taxonomy | `██░░░░░░░░ 25%` | 🟠 BANK_SURFACE_SEEDED | ~2026-09-04 |
| 3 | Fields / Field Groups | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-01** (complete) |
| 4 | Relations | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-01** (complete) |
| 5 | Status | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-01** (complete) |
| 6 | Query Builder | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-06 |
| 7 | Custom Tables / Content Tables | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-02** (complete) |
| 8 | Admin Columns | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-01** (complete) |
| 9 | Listings | `░░░░░░░░░░ 0%` | ⚪ UNSEEDED | ~2026-09-09 |
| 10 | Dashboard Widgets | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-01** (complete) |
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

### Plan vs implementation critical path

Bank certification and runtime implementation are separate gates. Current audited critical-path truth at the README audit anchor is:

| Gate / Surface | Planning / contract state | Runtime / implementation state | Next blocking work |
|---|---|---|---|
| A — Fields | Bank reviewed; detailed atomic inventory exists | **PASS for certified native V1 scope**; not full product-parity certification | Preserve fail-closed owner boundaries; broader parity remains separately gated |
| B — Relations | Bank reviewed; Relations Atomic Option Contract complete | **IN PROGRESS**; definition lifecycle, durable edge persistence, transactional connect/disconnect foundation merged | Remaining object/provider adapters where owned, Query/Data Source integration, admin UX, import/export/diagnostics, reference/performance/scale evidence, final Gate B closure |
| C — Query | Atomic inventory exists; separate stale planning branch contains unintegrated Bank/audit work | **NOT STARTED / BLOCKED by Gate B** | Rebase/sync planning work from current main, certify Bank lifecycle, then implement typed Query AST/data-source/policy/performance contract after Relations is stable |
| D — Admin Columns | Bank reviewed; historical Atomic/UX work exists but current shared atomic lifecycle does not certify full UX/runtime completion | **BLOCKED by Query runtime** | Preserve Query ownership of backend semantics; certify edit/export/performance/accessibility only after dependencies |
| E — Dynamic Listings | Atomic inventory exists; separate stale planning branch contains unintegrated listing/UX research | **NOT STARTED / BLOCKED by Query + shared renderer/data-source contracts** | Rebase/sync planning work, certify Bank lifecycle, then implement safe renderer/query/field/relation integration |
| Status | Bank reviewed | **RUNTIME BLOCKED** | Start only after Gates A–E complete |
| Custom Tables | Bank reviewed at 165 records | **Planning-only certification**; no runtime DDL/migration execution authorized by the Bank review merge | Enter a separately approved runtime/migration gate before executable table schema work |

`config/product/atomic-option-contract-progress.json` separately reports 56/56 atomic inventories, only Relations at `OPTION_CONTRACT_COMPLETE`, and zero surfaces at full-parity runtime/product certification. A bounded runtime gate such as Fields Gate A must not be misreported as full `PRODUCT_PARITY_CERTIFIED` completion.

## Multi-agent work command

### 1. Sabse pehle sirf ek Supervisor start karo

Is message ko Agent 1 ko do:

Start WPEssential Supervisor in AUTO mode.

Read AUTO-AGENT.md and follow it completely.

Reconcile current main, open PRs/MRs, active claim branches and config/coordination/agent-work-queue.json before changing files.

Take the highest-priority valid SUPERVISOR_ONLY slot first; if none exists, take the highest-priority valid ANY slot.

Coordinate submitted workers, shared writes and merge order while working on your own claimed slot.

### 2. Uske baad jitne additional agents chaho start karo

Agent 2, Agent 3, Agent 4... sab ko exactly same Worker command do:

Start WPEssential Worker in AUTO mode.

Read AUTO-AGENT.md and follow it completely.

Autonomously inspect current main and config/coordination/agent-work-queue.json, then claim the highest-priority valid free ANY slot using its deterministic remote claim branch.

Do not ask me which module to work on unless repository evidence contains a genuine unresolved decision.

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

See `CONTRIBUTING.md` for contribution and WordPress.org release rules. The mandatory WordPress.org/Plugin Check policy is `docs/QUALITY/WORDPRESS-ORG-PLUGIN-CHECK-COMPLIANCE.md`. `readme.txt` is the WordPress.org-facing plugin documentation draft for the current development line.

## Current foundation evidence

The shared Platform Foundation has passed module handoff, and Surface 3 Fields Gate A has passed for its certified native V1 scope. Surface 4 Relations is now the active serialized runtime critical path: merged evidence includes the Relations Atomic Option Contract, canonical definition lifecycle, durable edge persistence, and transactional connect/disconnect foundation. Gate B is not yet complete; Query runtime must not start until the Relations contract is stable and its required closure evidence is satisfied.

Hosted CI continues to provide architecture, PHP quality, WordPress/PHP/database compatibility, distributable-package and browser/accessibility evidence on certified exact heads where applicable. WordPress.org release readiness remains a separate gate and now additionally requires the official Plugin Check / Directory compliance policy referenced above.

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
