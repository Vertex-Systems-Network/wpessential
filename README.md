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
- Phase 2 Gate B / Relations: **PASS for the certified native V1 baseline**
- Phase 2 Gate C / Query: **PASS for the certified bounded V1 baseline**
- Phase 2 Gate D / Admin Columns: **PASS for the certified bounded V1 baseline** — final exact-main reference/closure evidence was promoted by PR #287 / Issue #285 after the bounded single-row and visible-page bulk Fields editing path, export safety, no-N+1/performance and accessibility evidence were certified. This is not `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`, does not claim provider-wide mutation parity, and does not authorize unbounded mass editing.
- Phase 2 Gate E / Dynamic Listings: **ACTIVE at dependency-safe V1 start** — exact-main prerequisite audit PR #291 identified the missing shared render/value/component plane; shared Renderer + Dynamic Value + Component Blueprint contracts V1 were promoted by PR #293 / Issue #292 with PHP Quality, Architecture Guards, Platform Compatibility Matrix and Distributable Package green. Listings definition/compiler and Query-binding/result-envelope are the next disjoint bounded workers; composed SSR remains later and must consume those shared contracts.
- Status runtime: **blocked until Gate E is complete**

Audit anchor for this README reconciliation: `main @ ca8954f3294c9d98a822c67b98eb7f8069a354c1` on **2026-09-08**. Repository/machine evidence remains authoritative if this prose later becomes stale.

## Module progress dashboard

The **56-module master plan is complete**. This dashboard tracks each canonical module/surface through the Master Options Bank certification lifecycle; it does **not** claim runtime implementation or production release completion.

**Progress model:** `UNSEEDED = 0%` → `BANK_SURFACE_SEEDED = 25%` → `NATIVE_AUDITED = 50%` → `MARKET_AUDITED = 75%` → `BANK_REVIEWED = 100%`.

- Canonical modules planned: **56 / 56 (100%)**
- Modules with Bank work started: **10 / 56**
- Fully Bank-reviewed modules: **9 / 56**
- Current Bank records: **1,890**
- Weighted Bank-readiness snapshot: **16.5%**
- Current certified Bank checkpoint: **9 surfaces BANK_REVIEWED** — Taxonomy, Fields, Relations, Status, Query, Custom Tables, Admin Columns, Dynamic Listings, Dashboard Widgets
- CPT remains **BANK_SURFACE_SEEDED / 107**
- Estimated Bank-review program completion: **~2026-10-26**

> **Date meaning:** “Bank Review Date” is a working estimate for completing planning/research certification for that surface, not a promise of runtime implementation, release, or production deployment. Dates are re-baselined when research, compatibility work, or CI exposes additional gaps. Machine lifecycle state takes precedence over an old estimate.

| # | Module / Surface | Progress | Status | Bank Review Date |
|---:|---|---|---|---|
| 1 | CPT | `██░░░░░░░░ 25%` | 🟠 BANK_SURFACE_SEEDED | in progress |
| 2 | Taxonomy | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-02** (complete by current machine snapshot) |
| 3 | Fields / Field Groups | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-01** (complete) |
| 4 | Relations | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-01** (complete) |
| 5 | Status | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-01** (complete) |
| 6 | Query Builder | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-02** (complete by current machine snapshot) |
| 7 | Custom Tables / Content Tables | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-02** (complete) |
| 8 | Admin Columns | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-01** (complete) |
| 9 | Listings | `██████████ 100%` | ✅ BANK_REVIEWED | **2026-09-02** (complete by current machine snapshot) |
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
| B — Relations | Bank reviewed; Relations Atomic Option Contract complete | **PASS for certified native V1 baseline** | Preserve the accepted public Query/Data Source consumer seam and owner boundaries; provider/richer parity remains separately gated |
| C — Query | Bank reviewed / 169; typed bounded contract and canonical admin route are merged | **PASS for certified bounded V1 baseline** | Preserve Query ownership of backend semantics; public execution/full parity remain separate gates |
| D — Admin Columns | Bank reviewed / 214; **UX_CONTRACT_COMPLETE** with 41 Atomic Options | **PASS for certified bounded V1 baseline**; final closure evidence through PR #287 is promoted | Preserve Query/source-owner authorization and mutation ownership; broader provider/unbounded parity remains separately gated |
| E — Dynamic Listings | Bank reviewed / 150; prerequisite audit #291 complete | **ACTIVE / bounded V1 start authorized after shared prerequisite #293** | Run definition/compiler and Query-binding/result-envelope as disjoint workers; compose server-first SSR only after both are promoted |
| Status | Bank reviewed / 129 | **RUNTIME BLOCKED** | Start only after Gate E completes |
| Custom Tables | Bank reviewed at 165 records | **Planning-only certification**; no runtime DDL/migration execution authorized by the Bank review merge | Enter a separately approved runtime/migration gate before executable table schema work |

`config/product/atomic-option-contract-progress.json` separately reports 56/56 atomic inventories, Relations at `OPTION_CONTRACT_COMPLETE`, Admin Columns at `UX_CONTRACT_COMPLETE`, and zero surfaces at full-parity `RUNTIME_CERTIFIED` / `PRODUCT_PARITY_CERTIFIED`. Bounded Gate A/B/C/D runtime passes must not be misreported as full product-parity completion.

## Multi-agent work command

The conflict-safe queue is `config/coordination/agent-work-queue.json`. Current reconciliation records Gate D final evidence #287, the Gate E prerequisite audit #291 and shared render/value/component contracts #293 as promoted. After the Supervisor reconciliation is merged, the next two valid dependency-ready worker lanes are intentionally path-disjoint: Issue #294 (Listings definition + compiler descriptor V1) and Issue #295 (Listings Query binding + authorized result envelope V1). They may be claimed concurrently on deterministic remote branches. Workers must not start composed SSR, Status, shared-truth edits or product-parity work early.

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

The shared Platform Foundation has passed module handoff. Surface 3 Fields Gate A, Surface 4 Relations Gate B, Surface 6 Query Gate C and Surface 8 Admin Columns Gate D have each passed explicitly bounded V1 baseline gates; none of those passes means full product parity. Gate D final closure evidence is promoted through PR #287 / Issue #285. Dynamic Listings Gate E prerequisite audit #291 classified Query/Data Source/Policy/Assets foundations as usable while identifying the missing shared Renderer/Dynamic Value/Component Blueprint plane. PR #293 / Issue #292 then promoted that shared plane with exact-head PHP Quality, Architecture Guards, Platform Compatibility Matrix and Distributable Package all green.

The next code tranches are therefore two disjoint bounded Gate E workers: Listings definition/compiler descriptor V1 (#294) and Listings Query-binding/authorized result-envelope V1 (#295). Neither may implement a private renderer, raw provider query language, direct Fields/meta reads, private builder document, composed SSR, Status runtime or product-parity claim. Composed server-first Listings SSR becomes eligible only after both workers are promoted and must consume the shared contracts rather than invent duplicate ownership.

Hosted CI continues to provide architecture, PHP quality, WordPress/PHP/database compatibility, distributable-package and browser/accessibility evidence on certified exact heads where applicable. WordPress.org release readiness remains a separate gate and additionally requires the official Plugin Check / Directory compliance policy referenced above.

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
