# WPEssential

WPEssential is a modular WordPress application platform for structured data, automation, integrations, admin tooling, workflows, and AI-ready operations.

Project website: **https://wpessential.org**

> **Status:** Source development is active under explicit `GOV-OWNER-CONSENT-001` and remains milestone-gated. Production deployment, destructive live-provider operations, full runtime certification and product-parity certification are separate gates.

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
- Phase 2 Gate D / Admin Columns: **PASS for the certified bounded V1 baseline**
- Phase 2 Gate E / Dynamic Listings: **PASS for the certified bounded V1 baseline**
- Status Manager: **PASS for the certified bounded V1 baseline** via Issue #378 / merged PR #379.
- Active dependency gate: **Surface 7 — Custom Tables**.

README audited base anchor: `main @ 18165d325dd97d6346ad2982a8464b450eb8273d` on **2026-09-08**. Issue #441 is the current post-hardening shared-truth reconciliation. Repository, CI and machine-readable lifecycle files override this prose if it later becomes stale.

## Module implementation progress

This dashboard is mandatory AI-Native closeout truth. It is updated after meaningful completed work cycles.

**Progress percentages below measure the currently approved/certified bounded implementation milestone for that module, not full product parity.** A 100% row means its explicitly named bounded/native baseline is certified; it does **not** imply `PRODUCT_PARITY_CERTIFIED`, deployment or release readiness. Planning-only surfaces without a defined implementation baseline are intentionally not assigned fabricated percentages here.

| Module / Surface | Lifecycle | Progress | Latest evidence | Next gate |
|---|---|---:|---|---|
| Platform Foundation | PASS for module handoff | `██████████ 100%` | WP121 / foundation evidence | Broader platform work only when a dependent module requires it |
| Fields / Surface 3 | PASS — certified native V1 | `██████████ 100%` | Gate A | Broader provider/full parity remains gated |
| Relations / Surface 4 | PASS — certified native V1 | `██████████ 100%` | Gate B | Richer provider/full parity remains gated |
| Status Manager / Surface 5 | PASS — certified bounded V1 | `██████████ 100%` | Issue #378 / PR #379 | Workflow/provider/bulk parity remains gated |
| Query / Surface 6 | PASS — certified bounded V1 | `██████████ 100%` | Gate C | Public execution/full parity remains gated |
| Custom Tables / Surface 7 | ACTIVE / NOT PASS | `█████████░ 90%` | Issue #441 after merged PRs #437–#440 | Durable run-store integration + bounded metadata/recovery/Policy adapters; managed-table mutation remains blocked |
| Admin Columns / Surface 8 | PASS — certified bounded V1 | `██████████ 100%` | Gate D | No unbounded mass-edit/provider-wide parity claim |
| Dynamic Listings / Surface 9 | PASS — certified bounded V1 | `██████████ 100%` | Issue #343 / PR #344 | Richer async/builder parity remains gated |

The Custom Tables 90% value is a bounded-track progress indicator for the currently defined V1 foundation/composition runway; it is not a claim that 90% of all future Custom Tables product parity is implemented. The post-hardening audit does not invent a higher percentage because the repository defines no new percentage milestone for this wave.

## AI-Native work-cycle order

Every Supervisor/Worker cycle follows this mandatory order from `AUTO-AGENT.md` and `AGENTS.md`:

1. refresh exact current `main`;
2. inspect and triage **OPEN Issues first**;
3. inspect/fix/review/merge eligible **OPEN PRs/MRs second**;
4. re-read active deterministic claims and the coordination queue;
5. only then start or claim new dependency-ready development;
6. after the cycle reaches a stable final state, update this README status and module progress dashboard before reporting completion.

Issue #415 / merged PR #416 promoted this rule. This prevents accepted issue/PR work from being bypassed by newly invented branches and makes README progress reconciliation part of Definition of Done.

## Current Custom Tables state

Surface 7 is **ACTIVE / NOT PASS**. The following bounded foundations are promoted:

1. **Canonical table Definition + schema descriptor V1** — Issue #382 / merged PR #383.
2. **Observed schema normalization + pure Migration Plan V1** — Issue #385 / merged PR #387.
3. **Post-plan dependency audit** — Issue #388 / merged PR #390.
4. **Trusted CT1/PT-E physical identity + strictly read-only schema introspection V1** — Issue #389 / merged PR #391.
5. **Post-introspection next-lane audit V1** — Issue #392 / merged PR #394.
6. **Server-selected provider capability profile + pure DDL compiler preview V1** — Issue #393 / merged PR #395.
7. **Post-provider exact-main prerequisite audit V1** — Issue #397 / merged PR #398.
8. **Immutable Migration Run state machine V1** — Issue #399 / merged PR #402.
9. **Typed Precondition Contract V1** — Issue #400 / merged PR #403.
10. **Recovery + reviewed-source Revalidation V1** — Issue #401 / merged PR #404.
11. **Post-prerequisite exact-main audit V1** — Issue #405 / merged PR #406.
12. **Migration Run Repository Contract V1** — Issue #407 / merged PR #410.
13. **Precondition Evaluator V1** — Issue #408 / merged PR #411.
14. **Recovery Readiness V1** — Issue #409 / merged PR #412.
15. **Post-contract-wave exact-main audit V1** — Issue #413 / merged PR #414.
16. **AI-Native issue-first/PR-second + README closeout governance** — Issue #415 / merged PR #416.
17. **Migration Run Transition Service V1** — Issue #423 / merged PR #427.
18. **Precondition Probe Registry V1** — Issue #420 / merged PR #428.
19. **Recovery Evidence Source Contract V1** — Issue #421 / merged PR #429.
20. **Migration Execution Readiness V1** — Issue #422 / merged PR #430.
21. **Post-composition-wave exact-main audit + progress reconciliation** — Issue #431 / merged PR #432.
22. **Migration Run Persistence Record Codec V1** — merged PR #437.
23. **Precondition Read-Only Probe Plan V1** — merged PR #438.
24. **Recovery Evidence Binding/Freshness V1** — Issue #435 / merged PR #439.
25. **Execution Authorization Envelope V1** — Issue #436 / merged PR #440.
26. **Post-hardening-wave exact-main audit** — Issue #441; effective when its reconciliation promotes.

The provider compiler still produces deterministic MySQL/MariaDB statement previews and fingerprints with `execution_allowed=false`; generated statements remain review-only and are never dispatched to the database by the certified bounded code. Run persistence now has a deterministic record codec but no durable Custom Tables run repository yet. Precondition planning can describe bounded read-only probes but live row scans remain blocked. Recovery evidence can be bound to reviewed plan/provider freshness without invoking Backup side effects. Execution Authorization consumes bounded Policy/capability/confirmation facts but cannot dispatch statements or override R3/R4 denial.

The latest hardening wave was serialized through latest-main reconciliation and applicable exact-head CI. PR #439 promoted at `08f91e5c617b4e2b8de1f253568f3ddfc0802f96`; PR #440 was reconciled without force, passed Architecture Guards, PHP Quality Toolchain, Platform Compatibility Matrix and Distributable Package, and promoted at `18165d325dd97d6346ad2982a8464b450eb8273d`.

### Still blocked

The current Custom Tables foundation does **not** authorize:

- physical `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` execution against managed Custom Tables;
- generic managed-table DDL dispatch through `$wpdb->query()` or shared database mutation interfaces;
- row `INSERT`, `UPDATE`, `DELETE` or CRUD/Data Source runtime;
- R3/R4 managed-table execution;
- leases, retry workers or Action Scheduler migration execution;
- live row-count/null/duplicate/range/max-length precondition scans;
- Backup creation or restore side effects;
- backfill, deduplication, shadow-copy or swap flows;
- CT2/PT-D or CT3 runtime/topology conversion;
- external-table adoption;
- Custom Tables admin/REST/Ability/public mutation surfaces;
- `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release approval.

## Next dependency gate

Issue #441 authorizes, **only after its reconciliation promotes**, the following narrower next work. These are prerequisites for any future managed-table execution coordinator and do not authorize physical target-table mutation:

1. **Internal Migration Run Store Schema + WPDB Repository V1 — serialized Supervisor integration.** Durable Custom Tables run persistence may be implemented for an internal WPE metadata table only. Bootstrap/versioning must compose `frameworks/Platform/Database/Migrations/**`, repository behavior must preserve canonical create/get/CAS semantics and the promoted record codec, and supported MySQL/MariaDB integration must be tested. This lane may not execute a generated Custom Tables provider preview.
2. **Metadata-Only Precondition Probe Adapters V1 — parallel Worker.** Initial concrete read-only probes are limited to trusted schema/provider metadata facts such as table existence, schema/column metadata match and already-observable provider feature availability. No row scans, arbitrary SQL, payload reads or mutation.
3. **Recovery Verification Provider Port V1 — parallel Worker.** Read/verify-only provider integration may convert verification facts for an already-existing recovery artifact into the promoted bound/freshness evidence model. No snapshot creation, chargeable provider side effect, restore or secret payload persistence.
4. **Execution Authorization Policy Adapter V1 — parallel Worker.** Canonical WPE Policy/capability evaluation may be composed into the promoted authorization envelope so production code does not trust caller-supplied authorization booleans. It produces bounded facts only; no SQL, capability mutation, public mutation endpoint, jobs or R3/R4 override.

`frameworks/Platform/Database/Migrations/**` remains the canonical generic Platform migration infrastructure; Custom Tables must compose it rather than create a duplicate private migration engine. Another exact-main Supervisor audit is mandatory after these concrete adapters promote before an R1/R2-only managed-table execution coordinator may be opened.

## Planning / Bank snapshot

Planning certification and runtime implementation are separate lifecycle dimensions.

- Canonical modules planned: **56 / 56**
- Current Options Bank and Atomic Option lifecycle truth: `config/product/options-bank-progress.json` and `config/product/atomic-option-contract-progress.json`
- Current conflict-safe development queue: `config/coordination/agent-work-queue.json`
- No bounded module pass in this README implies full product parity or release readiness.

The Options Bank currently identifies Taxonomy, Fields, Relations, Status, Query, Custom Tables, Admin Columns, Dynamic Listings and Dashboard Widgets as Bank-reviewed surfaces, while CPT remains partially seeded. Machine-readable files are authoritative for current counts and state.

## Certified runtime / implementation gates

| Gate / Surface | Certified implementation state | Current boundary |
|---|---|---|
| A — Fields | **PASS — certified native V1 scope** | Broader provider/full parity remains gated |
| B — Relations | **PASS — certified native V1 baseline** | Richer provider/parity remains gated |
| C — Query | **PASS — certified bounded V1 baseline** | Public execution/full parity remains gated |
| D — Admin Columns | **PASS — certified bounded V1 baseline** | No unbounded mass-edit/provider-wide parity claim |
| E — Dynamic Listings | **PASS — certified bounded V1 baseline** | Richer async/builder parity remains gated |
| Status Manager | **PASS — certified bounded V1 baseline** | Workflow/provider/bulk parity remains gated |
| Custom Tables | **ACTIVE / NOT PASS — bounded runway 90%** | Definition/plan/introspection/provider preview, Run lifecycle/repository/transition/codec, Precondition contract/evaluator/registry/plan, Recovery/Revalidation/readiness/evidence binding and execution readiness/authorization are promoted; concrete persistence and bounded adapters are next |

`config/product/atomic-option-contract-progress.json` remains the authority for full-parity lifecycle flags. A bounded gate PASS must never be reported as `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` unless that machine state is explicitly promoted.

## Multi-agent development

WPEssential uses one Supervisor plus as many conflict-safe Workers as the dependency graph allows.

The authoritative queue is `config/coordination/agent-work-queue.json`.

### Supervisor

Start WPEssential Supervisor in AUTO mode.

Read `AUTO-AGENT.md` completely. Refresh exact current main, check OPEN Issues first, then OPEN PRs/MRs, then active deterministic claim branches and the coordination queue before starting new work. Take the highest-priority dependency-ready `SUPERVISOR_ONLY` slot first; if none exists, take the highest-priority valid `ANY` slot. The Supervisor owns shared truth, audit gates, merge serialization and end-of-cycle README progress reconciliation.

### Workers

Start WPEssential Worker in AUTO mode.

Read `AUTO-AGENT.md` completely. Refresh exact current main, inspect OPEN Issues first and OPEN PRs/MRs second, then inspect the coordination queue and claim only a dependency-ready free `ANY` slot using its deterministic remote branch. An existing claim branch means the slot is already owned. Workers must not invent speculative branches or edit Supervisor-owned shared truth. README progress changes are reported as an Integration Requirement when Workers cannot own shared truth.

Parallelism is encouraged only for non-overlapping dependency-safe lanes. Multiple workers must not independently modify the same runtime owner or bypass serialized safety gates.

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

Dynamic Listings final bounded closure: `docs/IMPLEMENTATION/DYNAMIC-LISTINGS-GATE-E-FINAL-CLOSURE-AUDIT-V3.md`.

Status Manager final bounded closure: `docs/IMPLEMENTATION/STATUS-MANAGER-FINAL-CLOSURE-AUDIT-V3.md`.

Custom Tables dependency evidence currently includes:

- `docs/IMPLEMENTATION/POST-STATUS-NEXT-GATE-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-DEFINITION-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-PLAN-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-INTROSPECTION-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-PROVIDER-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-PREREQUISITE-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-CONTRACT-WAVE-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-COMPOSITION-WAVE-AUDIT-V1.md`
- `docs/IMPLEMENTATION/CUSTOM-TABLES-POST-HARDENING-WAVE-AUDIT-V1.md`

Hosted CI provides architecture, PHP quality, WordPress/PHP/database compatibility and deterministic distributable-package evidence on applicable exact heads. WordPress.org release readiness remains a separate gate.

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