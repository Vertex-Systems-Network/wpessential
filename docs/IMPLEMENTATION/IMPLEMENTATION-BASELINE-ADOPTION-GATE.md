# WPEssential — Implementation Baseline / Adoption Gate

Status: **WP119 / PASS WITH CONTROLLED IMPLEMENTATION ENTRY**  
Date: **2026-08-29**  
Approval: **GOV-OWNER-CONSENT-001 ACTIVE**  
Implementation branch: `implementation/baseline-adoption-gate`

## 1. Purpose

This gate converts WPEssential from planning-only governance into a verified implementation starting point without inventing legacy runtime state or bypassing the accepted architecture.

This document does **not** authorize production deployment or destructive/live provider operations. Those remain separately privileged.

## 2. Authority

Implementation must preserve:
- accepted architecture through ADR-0213;
- 56/56 canonical surface ownership;
- 56/56 option/UI/system ownership mappings;
- P01–P40 system-pattern containment;
- shared-service/no-bypass rules;
- `DEVELOPMENT-CONSENT.md` and `docs/APPROVAL-LEDGER.md`;
- `AGENTS.md`, `docs/QUALITY-GATES.md`, security/recovery governance and current checkpoint.

## 3. Repository adoption finding

### Default branch
The repository default/main branch contains no WPEssential production plugin implementation baseline. At the time of this gate it contains only the repository license and a minimal README.

### Planning/implementation authority
The accepted product/architecture exists on `planning/master-architecture`. The implementation branch was created from that authority **after** `GOV-OWNER-CONSENT-001` was recorded.

### Runtime/source inventory at gate entry
At the starting revision the implementation branch has:
- no `composer.json`;
- no `composer.lock`;
- no `package.json`;
- no npm lockfile;
- no production PHP/React/TypeScript source tree;
- no executable database migrations;
- no PHPUnit test runtime;
- no browser E2E suite;
- no production build output;
- no `.github/workflows` implementation CI.

Therefore the implementation classification is:

**`GREENFIELD_IMPLEMENTATION_WITH_EXISTING_ACCEPTED_PLAN`**

This is not a legacy-code rewrite and there is no existing production implementation to preserve/adopt.

## 4. Compatibility baseline

### WordPress
- **Minimum supported WordPress: 6.9**.
- Current stable at gate time: **WordPress 7.1**.
- Reason: WordPress 6.9 is the first version containing the Abilities API that is part of WPEssential's canonical cross-context operation architecture.
- CI must cover minimum supported WordPress and current stable WordPress; current beta/nightly may be an allowed-failure forward-compatibility signal.

### PHP
- **Minimum supported PHP: 8.2**.
- **Recommended production PHP: 8.3 or newer compatible line**.
- Initial CI target lines: PHP 8.2, 8.3, 8.4 and 8.5 where the selected WordPress matrix supports them.
- WPEssential intentionally does not carry PHP 7.x/8.0/8.1 compatibility into this new platform runtime.

### Database
Certified baseline targets:
- **MySQL 8.0+**;
- **MariaDB 10.11+**.

WPE code must still use WordPress database abstractions and cannot casually depend on vendor-specific SQL features when a supported WPE profile requires portable semantics. Any narrower module/database dependency requires a separate compatibility decision/evidence profile.

### HTTPS
Production support assumes HTTPS consistent with current WordPress requirements. Development/test environments may use local trusted/non-public tooling as documented.

## 5. PHP/runtime toolchain

### Composer
Adopt **Composer 2.x** as the canonical PHP dependency/build metadata tool.

Required rules:
- commit `composer.json` and `composer.lock`;
- root runtime namespace: `WPEssential\\`;
- PSR-4 source mapping from `src/`;
- development-only analysis/test dependencies must not leak into production package unless explicitly required;
- production package is built from locked dependencies;
- dependency licenses/security advisories are checked in FULL gate;
- no dependency is introduced merely to avoid writing a small stable platform primitive.

### Dependency injection
Do **not** adopt a third-party DI container at baseline.

Milestone 1 uses a small WPE-owned service registry/container contract sufficient for explicit service construction and testing. A third-party container requires a later ADR proving material value, lifecycle cost and interoperability.

### Job backend
The WPE `JobService` contract is canonical. Action Scheduler remains the preferred concrete backend candidate from accepted architecture, but **dependency adoption is deferred until the Milestone 1 bounded Action Scheduler coexistence/packaging spike passes**.

Modules may never depend directly on Action Scheduler internals.

## 6. Frontend/admin build baseline

### Node
- development/build baseline: **Node.js 24 LTS**;
- package manager baseline: **npm** with committed lockfile;
- CI uses the locked package graph and supported LTS line.

### WordPress JavaScript stack
Use WordPress packages and APIs where they provide the canonical integration surface.

Adopt `@wordpress/scripts` as the initial build/tooling baseline because it provides maintained WordPress-oriented lint/build/test conventions and requires an LTS Node line.

Admin architecture remains:
- one WPEssential application shell;
- module/suite route manifests;
- route/module code splitting;
- WordPress packages externalized where appropriate;
- no parallel Pro admin application;
- no global wp-admin DOM monkey-patching.

TypeScript is the default language for new admin application source where practical.

## 7. Source/package layout baseline

Initial implementation layout:

```text
wpessential.php
composer.json
composer.lock
package.json
package-lock.json
src/
  Bootstrap/
  Kernel/
  Contracts/
  Platform/
    Modules/
    Definitions/
    Auth/
    Capabilities/
    Abilities/
    Events/
    Jobs/
    Audit/
    Secrets/
    Assets/
    Integrations/
    Diagnostics/
  Domain/
  Modules/
admin-ui/
  src/
config/
  architecture/
tools/
  architecture/
tests/
  Unit/
  Integration/
  E2E/
build/
```

`build/` is generated output and packaging policy decides which generated artifacts are committed/distributed. Runtime source of truth remains source + locked build inputs, not hand-edited generated files.

## 8. Quality/tooling baseline

The repository must expose canonical commands for both FAST and FULL gates.

### PHP
- WordPress Coding Standards via PHPCS;
- PHP static analysis via PHPStan with WordPress-aware configuration where required;
- PHPUnit for deterministic unit tests and WordPress integration tests;
- Composer audit/security checks.

### JavaScript/TypeScript
- formatting/lint through WordPress-oriented tooling;
- ESLint;
- TypeScript typecheck;
- production build;
- npm dependency audit as an evidence signal, classified rather than blindly auto-fixed.

### Browser/E2E
- Playwright for critical browser workflows;
- WordPress Playground is preferred for fast/versioned compatibility scenarios where faithful;
- a second real WordPress/database environment remains required for behavior Playground cannot certify, especially DB/filesystem/provider/queue/restore cases.

## 9. CI gate baseline

### FAST
Runs on bounded implementation changes as applicable:
- architecture manifest validation;
- formatting/coding standards;
- PHP static analysis;
- JS/TS lint/typecheck;
- targeted unit/integration tests;
- affected production build;
- targeted security/negative checks.

### FULL
Runs at milestone/release boundaries:
- all architecture validators;
- full unit/integration suites;
- WordPress 6.9 minimum compatibility;
- current stable WordPress compatibility;
- PHP support matrix;
- Multisite tests where applicable;
- E2E;
- migration/upgrade/recovery tests;
- security/authorization regression;
- dependency/security audit;
- package artifact validation;
- broader performance/regression evidence.

No hidden developer-machine-only quality gate is accepted.

## 10. Versioning/package baseline

- semantic product/package versioning;
- plugin header `Requires at least: 6.9`;
- plugin header `Requires PHP: 8.2`;
- reproducible package assembled from a tagged/identified source revision plus locked dependencies/build inputs;
- generated/vendor/node development content excluded from distribution unless explicitly required;
- package manifest/checksum validation in FULL gate;
- Free platform plugin is the owner of shared kernel/services; Pro later registers into the same kernel and may not fork platform services.

## 11. Security/data baseline

At baseline there are no production DB schemas, secrets or live external connections.

Rules before introducing them:
- every table/option/file namespace has one declared owner;
- migrations are versioned and tested;
- secrets use Vault contracts, never ordinary options/export payloads;
- capabilities + resource Policy gate mutations;
- typed Abilities are the mutation vocabulary across UI/REST/CLI/Workflow/AI;
- external/provider facts retain explicit authority/reconciliation semantics;
- destructive operations require impact/recovery contracts.

## 12. Baseline failures and UNKNOWNs

### No inherited implementation failures
There is no prior plugin runtime/build/test implementation whose red/green state can be inherited. Missing build/tests/source are expected greenfield absence, not baseline failures.

### UNKNOWN / pending execution evidence
Until implementation tooling exists and runs:
- exact CI runtime durations;
- package artifact behavior;
- WordPress/PHP matrix runtime results;
- Action Scheduler coexistence;
- real database migration behavior;
- browser E2E runtime;
- provider/integration certification.

These are not claimed PASS by documentation.

## 13. Gate decision

**WP119 PASS** for controlled implementation entry because:
- owner development approval is ACTIVE;
- repository/VCS baseline is known;
- no hidden legacy implementation exists;
- accepted architecture is current through ADR-0213;
- exact compatibility/toolchain/build/test baseline is now defined;
- live/destructive/provider privileges remain separately gated.

WP119 does **not** authorize skipping the next structural enforcement step.

## 14. Next work package

**WP120 — Machine-enforced Architecture Guards**

Before ordinary feature/module code, implement machine-readable ownership manifests and validators for:
1. 56 canonical surfaces;
2. option semantic owners;
3. exactly-once admin route owners;
4. dependency graph/cycle/private-import constraints;
5. Ability ownership/exposure by UI/REST/CLI/Workflow/AI;
6. storage/table/options ownership;
7. P01–P40 Blueprint owner resolution;
8. Multisite scope semantics;
9. cache/index invalidation ownership;
10. provider/source-of-truth/reconciliation boundaries;
11. destructive/recovery contracts;
12. AI/MCP exposure/allowlist rules.

Only after WP120 FAST/FULL architecture validation passes may Milestone 1 ordinary Platform Foundation runtime source proceed.