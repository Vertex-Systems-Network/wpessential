# WPEssential — Development Consent Gate

Status: **ACTIVE / MANDATORY**  
Owner instruction recorded: **2026-08-27**

## Rule

Production development must **not** begin until the project owner gives explicit consent in the active working conversation/request.

Planning completion, a green architecture review, an Accepted ADR, a ready roadmap, or an AI/engineer believing the project is ready **does not count as consent**.

Before the first production-development action, the AI/engineer must ask the owner for approval and receive an unambiguous instruction equivalent to:

- `development start karo`
- `implementation start karo`
- `code development shuru karo`
- another explicit statement that clearly authorizes implementation

Silence, `continue`, `proceed`, `research continue`, `planning continue`, or approval of a document/ADR alone does **not** authorize production development.

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
- merge an implementation PR.

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

Then request explicit owner consent before running or committing that spike.

## Consent scope

Consent should be interpreted narrowly:

- consent to one research spike does not authorize full development;
- consent to Phase 1 does not automatically authorize later destructive migrations/deployments if a separate high-risk approval is required;
- consent can be revoked by the owner at any time, after which work returns to planning/documentation-only mode.

## Start-development protocol after consent

Even after explicit consent, implementation starts only if the relevant Phase 0 gates are ready. Before the first code change:

1. verify the latest owner consent;
2. read `AGENTS.md`;
3. read `CHECKPOINT.md`;
4. read this file;
5. verify relevant specifications and ADR statuses;
6. identify unresolved blockers;
7. inspect branch/Git state;
8. create/confirm a safe implementation branch/checkpoint;
9. state the first bounded implementation milestone;
10. only then begin code.

If architecture blockers remain, report that consent exists but development is **not yet safe to start** and continue resolving planning blockers instead of bypassing them.

## Source of truth

This file is a project-governance constraint and must be read by every human/AI session before implementation.

Do not delete, weaken or reinterpret this gate without explicit owner instruction.
