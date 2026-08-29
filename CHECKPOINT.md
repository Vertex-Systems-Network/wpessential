# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-29**  
Implementation branch: `implementation/baseline-adoption-gate`  
Planning authority: `planning/master-architecture` through ADR-0213  
Project classification: **`GREENFIELD_IMPLEMENTATION_WITH_EXISTING_ACCEPTED_PLAN`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Lifecycle: **`IMPLEMENTING_ARCHITECTURE_GUARDS`**  
Development approval: **GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56**

## Approval boundary

Owner explicitly authorized:

`Implementation Baseline / Adoption Gate → Machine-enforced architecture guards → Milestone 1 Platform Foundation → module development`.

Source implementation, development/test tooling, CI and milestone-scoped schemas/tests are authorized. Production deployment, destructive live-site/customer-data operations and chargeable/irreversible real-provider side effects remain separately privileged and are not authorized by this grant.

## Current product truth

Accepted product scope remains **56/56 Exhaustive**, Multisite **56/56**, AI Prompt **56/56**, with no known planning or semantic-owner gap after WP118 / ADR-0213.

## WP119 — Implementation Baseline / Adoption Gate

**DONE / PASS / ADR-0214**.

Verified:
- default/main branch has no production WPEssential implementation baseline;
- implementation branch started with docs/governance only and no Composer/npm/source/tests/CI;
- classification is greenfield implementation against the accepted plan, not a legacy rewrite;
- minimum WordPress **6.9**;
- minimum PHP **8.2**, recommended **8.3+**;
- certified DB baseline MySQL **8.0+** / MariaDB **10.11+**;
- Composer 2.x + PSR-4 `WPEssential\\`;
- Node 24 LTS + npm + initial `@wordpress/scripts` direction;
- PHPCS/WPCS, PHPStan, PHPUnit, TypeScript/ESLint/build, Playwright and compatibility matrix are mandatory quality categories;
- no third-party DI container at baseline;
- Action Scheduler remains a preferred JobService backend candidate but runtime adoption is deferred to a bounded coexistence spike.

Canonical evidence:
- `docs/IMPLEMENTATION/IMPLEMENTATION-BASELINE-ADOPTION-GATE.md`;
- `docs/DECISIONS/ADR-0214-implementation-baseline-build-compatibility-and-toolchain.md`.

## WP120 — Machine-enforced Architecture Guards

**CURRENT — IMPLEMENTED / INDEPENDENT INVARIANTS PASS / HOSTED CI INFRA_BLOCKED**.

Implemented:
- `config/architecture/surfaces.json`;
- `config/architecture/ownership-contracts.json`;
- `config/architecture/system-patterns.json`;
- `config/architecture/operation-guards.json`;
- `tools/architecture/validate.php`;
- `composer.json` architecture validation command;
- `.github/workflows/architecture-guards.yml`.

Independent invariant verification passed for 56 surfaces, unique keys, exactly-once suite coverage, P01–P40 pattern range and critical semantic owners.

Hosted GitHub Actions is currently blocked before runner assignment. Multiple attempts including explicit `ubuntu-24.04` produced an immediate failed job with no assigned runner, no steps and no logs. Therefore repository validator execution is **NOT certified green** and the failure is classified `EXTERNAL CI RUNNER DISPATCH FAILURE / INFRA_BLOCKED`, not a validator-code failure.

Canonical status: `docs/IMPLEMENTATION/MACHINE-ARCHITECTURE-GUARDS.md`.

## Stop line

Ordinary Milestone 1 Platform Foundation runtime source does **not** begin until WP120's exact repository validator executes successfully in a faithful environment and WP120 is recorded PASS.

Current safe action:
1. restore a working runner/faithful checkout;
2. execute `php tools/architecture/validate.php`;
3. fix any real guard failure;
4. record WP120 PASS;
5. begin Milestone 1 Platform Foundation.

Repository evidence overrides conversational memory.