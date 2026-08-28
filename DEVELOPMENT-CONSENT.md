# WPEssential — Development Consent Gate

Status: **ACTIVE / MANDATORY**  
Owner instruction recorded: **2026-08-27**

## Rule

Production development must **not** begin until the project owner gives explicit consent in the active working conversation/request and the approval is recorded in `docs/APPROVAL-LEDGER.md`.

Planning completion, a green architecture review, an Accepted ADR, a ready roadmap, or an AI/engineer believing the project is ready **does not count as consent**.

Before the first production-development action, the AI/engineer must receive an unambiguous instruction equivalent to:
- `development start karo`
- `implementation start karo`
- `code development shuru karo`
- another explicit statement clearly authorizing implementation.

Silence, `continue`, `proceed`, `research continue`, `planning continue`, or approval of a document/ADR alone does **not** authorize production development.

## Approval scope hierarchy

Every implementation approval is one of `TASK`, `MODULE`, `MILESTONE`, `PHASE`, `PROJECT` and records included/excluded scope, risk exceptions, status and evidence.

Do not repeatedly ask approval for ordinary reversible decisions inside an ACTIVE documented milestone. Ask again when scope/risk materially changes or separately privileged/destructive production action is required.

## Current consent state

Project development approval: **NOT GRANTED / PENDING**  
Implementation authorization: **0/50 module/platform surfaces**  
Execution mode: **PLANNER_ONLY**

Historical `0/31`, `0/43` and `0/48` counters refer to earlier scope snapshots only.

The durable current ledger is `docs/APPROVAL-LEDGER.md`.

## Allowed before consent

Planning/research activities may continue, including repository/documentation inspection, official/market research, module/product specifications, screen/option/state inventories, threat models, architecture/ADRs, paper data/API/Ability design, test/acceptance planning, roadmaps/backlogs, dependency/license/security evaluation, UX flows, migration/rollback design, performance budgets, release/support planning, governance documentation and documentation-only Git/PR changes.

## Prohibited before consent

Without explicit owner consent, do **not**:
- create/modify production PHP/React/TypeScript runtime source;
- create plugin bootstrap/runtime loaders;
- create/execute production DB migrations or real schema/tables;
- install/add/remove production Composer/npm dependencies;
- create/modify production build configuration;
- scaffold implementation source;
- implement REST endpoints, Abilities, Jobs, modules, screens or integrations;
- write/execute implementation tests or benchmarks tied to production source;
- package/install/activate/deploy production WPEssential;
- merge an implementation PR;
- install/enable scheduled market-research GitHub workflows;
- execute crawlers, Search/Replace, fixture generation or DB cleanup against runtime;
- mutate WordPress users/roles/memberships or run Administrator Rescue;
- apply admin themes/branding;
- collect media field metrics or rewrite image priority/lazy/responsive output;
- inject browser scripts/tags/CSS/HTML into a runtime;
- execute arbitrary PHP/eval/SQL/shell through a content-managed tool.

## Research spike boundary

Any research spike that writes executable code counts as development unless separately authorized. If executable evidence is needed, document why static research is insufficient, exact scope/environment, success/failure criteria, security/data risks, cleanup/rollback and requested approval scope before execution.

## Existing-project approval adoption

Do not retroactively invalidate clearly authorized historical work. Recover/backfill evidence where possible and mark uncertain scope PENDING/UNKNOWN rather than inventing consent. WPEssential currently has no production implementation to adopt.

## Start-development protocol after consent

Even after explicit consent:
1. verify ACTIVE approval ledger entry;
2. read `AGENTS.md`, `CHECKPOINT.md`, this file and project-state/adoption governance;
3. verify relevant specifications/ADRs/evidence gates;
4. identify unresolved blockers;
5. inspect VCS/workspace/baseline;
6. establish Implementation Baseline / Adoption Gate and safe branch/checkpoint;
7. confirm bounded milestone ID, change budget, critical-path/parallelism and FAST/FULL gates;
8. only then begin code.

Consent does not bypass technical/security/recovery blockers.

## Revocation

Owner may revoke approval at any time. Stop new affected implementation, preserve evidence, reach a safe checkpoint, record `REVOKED`, and return affected work to planning/blocked state.

## Source of truth

This file plus `docs/APPROVAL-LEDGER.md` are mandatory governance constraints. Do not delete, weaken or reinterpret this gate without explicit owner instruction.