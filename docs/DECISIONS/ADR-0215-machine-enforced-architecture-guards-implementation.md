# ADR-0215 — Machine-enforced Architecture Guards Implementation

Status: **ACCEPTED**  
Date: **2026-08-29**

## Context

ADR-0213 required WPEssential's post-P0 ownership/no-bypass architecture to become machine-enforced before ordinary platform/module implementation. WP119 / ADR-0214 established the greenfield implementation baseline and toolchain direction.

WP120 implemented machine-readable architecture manifests, an executable PHP validator, Composer FAST integration and a GitHub Actions gate.

GitHub-hosted Actions repeatedly failed before runner assignment (`steps = null`, no logs), including retry and explicit `ubuntu-24.04` pin. That infrastructure failure prevented hosted execution but did not provide evidence of validator failure.

## Decision

Accept WP120 as **DONE / PASS with hosted CI degraded**.

Accepted executable guard surfaces:
- `config/architecture/surfaces.json`;
- `config/architecture/ownership-contracts.json`;
- `config/architecture/system-patterns.json`;
- `config/architecture/operation-guards.json`;
- `tools/architecture/validate.php`;
- Composer `architecture:validate` / FAST chain;
- `.github/workflows/architecture-guards.yml`.

The implementation-branch manifest contents and repository validator logic were faithfully materialized into an isolated PHP 8.4.23 execution environment. The validator returned PASS for:
- 56/56 canonical surfaces;
- unique keys/routes/suite ownership;
- semantic-owner + competitive-overlay no-bypass rules;
- P01..P40 system routing;
- Ability/storage/Multisite/invalidation/provider/destructive/AI guards.

A separate invariant check agreed with the result.

## Hosted CI classification

Hosted GitHub Actions remains:

`EXTERNAL CI RUNNER DISPATCH FAILURE / INFRA_DEGRADED`

This is not called green. Once a runner is assigned, the hosted workflow must execute its real steps. Any code/manifest failure found then becomes a stop-line regression.

## Consequences

1. WP121 Platform Foundation may begin in bounded source tranches.
2. Architecture guards remain mandatory and MUST NOT be weakened for CI convenience.
3. The hosted CI degradation remains visible in milestone/release readiness.
4. Production deployment and destructive/live-provider privileges remain separately gated.
5. Competitive overlays, system blueprints, UI/REST/Workflow/CLI/AI paths and modules remain bound to the canonical owner manifests.

## Evidence

- `docs/IMPLEMENTATION/MACHINE-ARCHITECTURE-GUARDS.md`
- Draft implementation PR #2
- GitHub Actions run `33247682122` demonstrating pre-step runner-dispatch failure
- WP121 local PHP syntax/kernel smoke evidence remains separate from this ADR.
