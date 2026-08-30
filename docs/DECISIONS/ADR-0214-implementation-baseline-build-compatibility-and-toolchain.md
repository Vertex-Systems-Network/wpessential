# ADR-0214 — Implementation Baseline, Build, Compatibility & Toolchain

Status: **Accepted implementation-entry decision**  
Date: **2026-08-29**  
Work package: **WP119 — Implementation Baseline / Adoption Gate**  
Approval: **GOV-OWNER-CONSENT-001 ACTIVE**

## Context

WPEssential completed Phase 0 product/architecture planning and the post-P0 structural-integrity audit through ADR-0213. The owner has now explicitly authorized implementation in this order:

`Implementation Baseline / Adoption Gate → Machine-enforced architecture guards → Milestone 1 Platform Foundation → module development`.

Repository inspection shows no existing production plugin implementation on the default branch and no runtime/build/test/CI source on the implementation starting branch. WPEssential therefore needs an explicit greenfield implementation baseline rather than pretending to adopt a legacy runtime.

Current public WordPress facts checked for this decision:
- WordPress 7.1 is the current stable release at decision time;
- the WordPress Abilities API is available from WordPress 6.9 onward;
- current WordPress hosting requirements recommend PHP 8.3+, MySQL 8.0+ or MariaDB 10.11+, and HTTPS;
- `@wordpress/scripts` is the WordPress-maintained reusable scripts baseline and requires an LTS Node line;
- Node.js 24 is an LTS release line at decision time.

## Decision 1 — implementation classification

Accept:

**`GREENFIELD_IMPLEMENTATION_WITH_EXISTING_ACCEPTED_PLAN`**.

No legacy production runtime is assumed. Planning/ADR/module/evidence contracts remain binding and are implemented incrementally; they are not rewritten from scratch merely because the source tree is greenfield.

## Decision 2 — compatibility floor

Accept:
- minimum WordPress: **6.9**;
- minimum PHP: **8.2**;
- recommended PHP: **8.3+ compatible supported line**;
- certified database baseline: **MySQL 8.0+ / MariaDB 10.11+**;
- production HTTPS assumption.

Rationale:
- WordPress 6.9 is the first core version with the Abilities API required by WPE's canonical typed-operation architecture;
- this new platform will not carry new technical debt for PHP 7.x/8.0/8.1;
- PHP 8.2 still provides a useful compatibility floor while PHP 8.3+ is the current WordPress-recommended runtime;
- database implementation must remain WordPress-aware and cannot casually bind all surfaces to one vendor-specific extension.

## Decision 3 — PHP dependency/runtime foundation

Adopt Composer 2.x and PSR-4:
- root namespace `WPEssential\\`;
- `src/` as canonical PHP source root;
- committed `composer.json` + lockfile;
- locked/reviewed production dependencies;
- dependency licenses/advisories evaluated;
- development dependencies separated from production packaging.

Do **not** adopt a third-party DI container at baseline. Milestone 1 starts with a small WPE-owned explicit service registry/container contract. Any third-party DI framework requires a later ADR.

## Decision 4 — Jobs / Action Scheduler

The WPE `JobService` abstraction remains canonical.

Action Scheduler remains the preferred concrete backend candidate from accepted architecture but is **not adopted merely by this ADR**. Milestone 1 must run the bounded coexistence/packaging/capability spike before its package is introduced as a runtime dependency.

No module may directly depend on Action Scheduler classes, tables, statuses or admin UI.

## Decision 5 — admin/frontend build

Adopt:
- Node.js **24 LTS** development/build baseline;
- npm with committed lockfile;
- `@wordpress/scripts` as the initial WordPress-oriented build/tooling baseline;
- TypeScript-first new admin source where practical;
- one shared WPE admin application shell;
- route/module code splitting;
- supported WordPress packages/APIs rather than duplicate framework runtimes where appropriate.

No separate Pro admin application is permitted.

## Decision 6 — source layout

Accept the initial layered implementation layout from the WP119 baseline:
- plugin bootstrap;
- `src/Bootstrap`, `src/Kernel`, `src/Contracts`;
- platform services under `src/Platform`;
- domain primitives under `src/Domain`;
- product modules under `src/Modules`;
- `admin-ui/src`;
- machine architecture manifests under `config/architecture`;
- architecture validators under `tools/architecture`;
- unit/integration/E2E tests under `tests`.

Exact subfolders can evolve within the accepted layer ownership without a new ADR when semantics remain unchanged.

## Decision 7 — mandatory quality stack

Canonical categories:
- PHPCS + WordPress Coding Standards;
- PHPStan static analysis with WordPress-aware setup;
- PHPUnit unit + WordPress integration testing;
- WordPress-oriented JS formatting/lint/build;
- ESLint + TypeScript checking;
- Playwright E2E;
- Composer/npm dependency audit signals;
- package artifact validation;
- WordPress/PHP compatibility matrix.

`docs/QUALITY-GATES.md` FAST/FULL policy remains controlling.

## Decision 8 — CI compatibility matrix direction

Minimum matrix must cover:
- WordPress 6.9;
- current stable WordPress;
- PHP 8.2 minimum;
- current recommended PHP;
- newest WordPress-compatible PHP included in the supported matrix.

Forward nightly/beta compatibility may be allowed-failure signal unless a milestone promotes it to blocking.

WordPress Playground may accelerate compatible tests, but it is not accepted as proof for runtime behavior it cannot faithfully model. Real WordPress/database/filesystem/provider environments remain required where applicable.

## Decision 9 — packaging/versioning

- semantic package versions;
- plugin header `Requires at least: 6.9`;
- plugin header `Requires PHP: 8.2`;
- reproducible build from identified source revision + lockfiles;
- production package excludes development-only assets/dependencies;
- package/checksum validation in FULL gate;
- Free platform owns shared kernel/services; Pro later registers into the same kernel.

## Decision 10 — machine enforcement before feature runtime

ADR-0213 structural ownership is not allowed to remain paper-only once implementation begins.

Therefore **WP120 Machine-enforced Architecture Guards is mandatory before Milestone 1 ordinary feature/module runtime code**.

The guards must encode and validate canonical Surface, Option, Route, Dependency, Ability, Storage, Blueprint, Multisite, Invalidation, Provider, Destructive/Recovery and AI/MCP ownership boundaries.

## Alternatives rejected

- minimum WordPress below 6.9 with a parallel WPE pseudo-Abilities API;
- PHP 7.x/8.0/8.1 compatibility for a new platform runtime;
- separate Pro kernel/admin application;
- generic third-party framework/DI container before a demonstrated need;
- direct module dependency on Action Scheduler;
- undocumented build tools or machine-local-only gates;
- starting feature modules before architecture ownership is machine-enforced.

## Consequences

Positive:
- native WordPress Abilities architecture can be used directly;
- greenfield implementation avoids legacy runtime compromises;
- compatibility and packaging are explicit before source scaffolding;
- architecture drift can be caught before module code compounds it.

Costs:
- support excludes older WordPress/PHP installations;
- implementation must establish build/test/CI infrastructure before visible product features;
- Action Scheduler requires a separate executable compatibility decision before adoption.

## Gate result

ADR-0214 accepts WP119 as **PASS WITH CONTROLLED IMPLEMENTATION ENTRY**.

Next current work:

**WP120 — Machine-enforced Architecture Guards**.

This ADR does not authorize live production deployment or separately privileged external/destructive operations.