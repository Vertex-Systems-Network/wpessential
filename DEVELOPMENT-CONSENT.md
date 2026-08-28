# WPEssential — Development Consent Gate

Status: **ACTIVE / MANDATORY**  
Owner instruction recorded: **2026-08-27**

## Rule

Production development must **not** begin until the project owner gives explicit consent in the active working conversation/request and the approval is recorded in `docs/APPROVAL-LEDGER.md`.

Planning completion, a green architecture review, an Accepted ADR, a ready roadmap, or an AI/engineer believing the project is ready **does not count as consent**.

Before the first production-development action, the AI/engineer must ask the owner for approval and receive an unambiguous instruction equivalent to:

- `development start karo`
- `implementation start karo`
- `code development shuru karo`
- another explicit statement that clearly authorizes implementation

Silence, `continue`, `proceed`, `research continue`, `planning continue`, or approval of a document/ADR alone does **not** authorize production development.

## Approval scope hierarchy

Every implementation approval is recorded as one of:

- `TASK`
- `MODULE`
- `MILESTONE`
- `PHASE`
- `PROJECT`

Prefer meaningful milestone-level approval for substantial systems.

Approval records must identify:
- stable work/scope ID where assigned;
- included scope;
- excluded scope/non-goals;
- high-risk/destructive exceptions;
- approval status (`ACTIVE`, `REVOKED`, `EXHAUSTED`, etc.);
- evidence/reference.

Do not repeatedly ask for approval for ordinary reversible decisions already inside an ACTIVE documented milestone scope.

Ask again only when scope/risk materially changes or a separately privileged/destructive production action requires authorization.

## Current consent state

Project development approval: **NOT GRANTED / PENDING**  
Implementation authorization: **0/48 module/platform surfaces**  
Execution mode: **PLANNER_ONLY**

Historical `0/31` and `0/43` counters refer to earlier pre-expansion scope snapshots only.

The durable current ledger is `docs/APPROVAL-LEDGER.md`.

## Allowed before consent

The following are Phase 0 planning/research activities and may continue without development consent:

- inspect repositories, documentation, architecture and Git history;
- public/official-source research;
- competitor and market research;
- module/product specifications;
- screen, option, state, validation and permission inventories;
- threat modeling;
- architecture diagrams/specifications;
- ADRs and decision registers;
- data-model design on paper/specification only;
- API/Ability contract design on paper/specification only;
- test-strategy and acceptance-criteria planning;
- roadmap, milestone and backlog planning;
- dependency/license/security evaluation;
- UX flows and wireframe-level specifications;
- migration/rollback design;
- performance budgets and benchmark plans;
- release/distribution/support planning;
- project-state/adoption baseline and governance documentation;
- approval/work-lifecycle planning;
- documentation-only Git commits and documentation-only PR updates.

## Prohibited before consent

Without explicit owner consent, do **not** perform any production-development action, including:

- create or modify plugin runtime PHP source;
- create or modify React/TypeScript application source;
- create plugin bootstrap/runtime loaders;
- create production database migrations;
- create real schema/tables in a WordPress/database environment;
- install/add/remove production Composer or npm dependencies;
- create or modify production build configuration for implementation;
- scaffold production plugin/module source directories as implementation;
- implement REST endpoints, Abilities, jobs, modules, UI screens or integrations;
- write implementation tests tied to production source;
- execute implementation migrations;
- package/install/activate a production WPEssential build;
- deploy code;
- merge an implementation PR;
- install/enable scheduled market-research GitHub workflows;
- execute crawlers, Search/Replace transformations, fixture generation or DB cleanup against a runtime.

## Research spike boundary

A research spike that writes executable code also counts as **development** for this project unless the owner separately authorizes that spike.

Therefore, before consent, resolve Proposed ADRs using documentation, official-source research, architecture analysis and benchmark/test **plans** only.

If executable evidence is materially required to resolve a blocker, record:

1. what executable spike is needed;
2. why static research is insufficient;
3. exact scope/files/environment;
4. what will be discarded vs retained;
5. success/failure criteria;
6. security/data risks;
7. rollback/cleanup;
8. approval scope (`TASK` or other appropriate level).

Then request explicit owner consent before running or committing that spike.

## Existing-project approval adoption

When this governance is applied to an already active project:
- do not retroactively invalidate work that repository/project evidence shows was already clearly authorized;
- backfill durable approval evidence where possible;
- mark uncertain scope PENDING/UNKNOWN instead of inventing consent;
- request new approval only for materially new scope or risk.

WPEssential currently has no production implementation to adopt, so no retrospective implementation approval exists.

## Consent scope

Consent should be interpreted according to the durable approval record:

- consent to one research spike does not authorize full development;
- consent to a TASK does not authorize an unrelated MODULE;
- consent to one MILESTONE does not silently expand its included scope;
- consent to Phase 1 does not automatically authorize later destructive migrations/deployments if a separate high-risk approval is required;
- consent can be revoked by the owner at any time, after which affected work returns to planning/blocked mode.

For ambiguous conflicts, the narrower/newer explicit owner instruction governs the same scope, while destructive/security restrictions remain unless explicitly lifted.

## Start-development protocol after consent

Even after explicit consent, implementation starts only if the relevant technical entry gates are ready. Before the first code change:

1. verify the latest owner consent and ACTIVE approval ledger entry;
2. read `AGENTS.md`;
3. read `CHECKPOINT.md`;
4. read this file;
5. read `docs/PROJECT-STATE-AND-ADOPTION.md` and refresh capabilities;
6. verify relevant specifications/ADRs/evidence gates;
7. identify unresolved blockers;
8. inspect branch/VCS/working-tree state where available;
9. create/confirm the Implementation Baseline / Adoption Gate;
10. create/confirm a safe implementation branch/checkpoint;
11. assign/confirm the first bounded milestone/work package ID;
12. define its change budget, critical-path class, parallelism class and FAST/FULL gates;
13. only then begin code.

If architecture/evidence blockers remain, report that consent exists but development is **not yet safe to start** and continue resolving planning blockers instead of bypassing them.

## Revocation

The owner may revoke approval at any time.

After revocation:
- stop new affected implementation;
- preserve work/evidence;
- reach a safe non-destructive checkpoint when possible;
- mark the ledger entry `REVOKED`;
- return affected work to planning/blocked state.

## Source of truth

This file plus `docs/APPROVAL-LEDGER.md` are project-governance constraints and must be read by every human/AI session before implementation.

Do not delete, weaken or reinterpret this gate without explicit owner instruction.
