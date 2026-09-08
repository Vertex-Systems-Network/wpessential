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

README reconciliation anchor: `main @ b0419289a7f4f205e1fa40dd5c3158b4927badd6` on **2026-09-08**. Repository, CI and machine-readable lifecycle files override this prose if it later becomes stale.

## Current Custom Tables state

Surface 7 is **ACTIVE / NOT PASS**. The following bounded foundations are promoted:

1. **Canonical table Definition + schema descriptor V1** — Issue #382 / merged PR #383.
2. **Observed schema normalization + pure Migration Plan V1** — Issue #385 / merged PR #387.
3. **Post-plan dependency audit** — Issue #388 / merged PR #390.
4. **Trusted CT1/PT-E physical identity + strictly read-only schema introspection V1** — Issue #389 / merged PR #391.
5. **Post-introspection next-lane audit V1** — Issue #392 / merged PR #394.
6. **Server-selected provider capability profile + pure DDL compiler preview V1** — Issue #393 / merged PR #395.
7. **Post-provider exact-main prerequisite audit V1** — Issue #397; this Supervisor reconciliation makes its bounded decision authoritative when promoted to `main`.

The promoted provider compiler consumes trusted CT1 identity, canonical desired schema and the reviewed Migration Plan to produce deterministic MySQL/MariaDB statement previews and fingerprints with `execution_allowed=false`. Blocked, recovery-required, R3/R4, finding-bearing and uncertified operation families fail closed. PR #395's final exact head passed PHP Quality Toolchain, Architecture Guards, Platform Compatibility Matrix and Distributable Package.

### Still blocked

The current Custom Tables foundation does **not** authorize:

- physical `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` execution;
- generic DDL dispatch through `$wpdb->query()` or shared database mutation interfaces;
- row `INSERT`, `UPDATE`, `DELETE` or CRUD/Data Source runtime;
- migration run/applied-generation persistence, leases or retry state;
- Backup/restore execution;
- data precondition scans, backfill, deduplication, shadow-copy or swap flows;
- CT2/PT-D or CT3 runtime/topology conversion;
- external-table adoption;
- Custom Tables admin/REST/Ability/public mutation surfaces;
- `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release approval.

## Next dependency gate

Once Issue #397's Supervisor reconciliation is promoted, three **execution-free, non-overlapping** prerequisite workers are authorized in parallel:

1. `custom-tables-migration-run-state-v1` — immutable Migration Run envelope/state machine only; no persistence/jobs/execution.
2. `custom-tables-precondition-contract-v1` — typed precondition descriptors and aggregate verdicts only; no database reads/scans or row values.
3. `custom-tables-recovery-revalidation-v1` — recovery classification plus reviewed-source fingerprint/revision revalidation only; no Backup provider calls or SQL execution.

These lanes are intentionally parallel because their namespaces and responsibilities do not overlap. Provider SQL remains review-only preview data and no generated statement may be dispatched to the database. A later exact-main Supervisor audit is mandatory before any execution, persistence, lease, scan/backfill or recovery-provider lane can open.

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
| Custom Tables | **ACTIVE / NOT PASS** | Definition, observed-plan, read-only CT1 introspection and pure provider DDL preview foundations promoted; execution-free run/precondition/recovery prerequisites are the next gated work |

`config/product/atomic-option-contract-progress.json` remains the authority for full-parity lifecycle flags. A bounded gate PASS must never be reported as `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` unless that machine state is explicitly promoted.

## Multi-agent development

WPEssential uses one Supervisor plus as many conflict-safe Workers as the dependency graph allows.

The authoritative queue is `config/coordination/agent-work-queue.json`.

### Supervisor

Start WPEssential Supervisor in AUTO mode.

Read `AUTO-AGENT.md` completely. Reconcile current `main`, open PRs/MRs, active deterministic claim branches and the coordination queue before changing files. Take the highest-priority dependency-ready `SUPERVISOR_ONLY` slot first; if none exists, take the highest-priority valid `ANY` slot. The Supervisor owns shared truth, audit gates and merge serialization.

### Workers

Start WPEssential Worker in AUTO mode.

Read `AUTO-AGENT.md` completely. Inspect exact current `main` and the coordination queue, then claim only a dependency-ready free `ANY` slot using its deterministic remote branch. An existing claim branch means the slot is already owned. Workers must not invent speculative branches or edit Supervisor-owned shared truth.

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
