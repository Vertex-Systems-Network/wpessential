# WP121 — Shared Foundation Readiness Gate

Status: **OPEN / PARTIALLY_COMPLETE — module development not yet authorized**  
Date: **2026-08-30**  
Scope: WPEssential shared Platform foundation only

## Decision rule

WP121 may hand off to the first business-module tranche only when the applicable milestone-level FULL gate is executable and green. A green targeted implementation workflow is necessary evidence, but it is not sufficient when required quality categories remain absent.

This gate follows `docs/QUALITY-GATES.md`: planning claims are not executable evidence, FAST does not substitute for FULL, and unimplemented/untested required categories remain `Not Verified` rather than being inferred green.

## Verified accepted evidence

### Core architecture and platform runtime — VERIFIED

Authoritative exact-source foundation head: **`f15fe7b2d9d0067c90a7e9f23746c002265f8560`**.

Architecture Guards run **33301807573 / #249 SUCCESS** explicitly checked out that SHA, verified exact source identity and required a tracked-clean tree before executing:

- Composer metadata;
- machine architecture contracts;
- engineering contracts and direct-access guards;
- PHP syntax across production/smoke/integration PHP;
- Kernel and Platform core smoke;
- minimal Platform admin bootstrap + server-rendered Runtime Observatory;
- Audit/Jobs, Vault/Assets/Integrations, migrations and WordPress Ability bridges;
- compiled-registration persistence;
- executable compiled-registration scale contract;
- MySQL compiled-registration persistence;
- Definition/Audit MySQL persistence;
- real WordPress AJAX/nonce/Policy integration;
- Action Scheduler 3.9.3/4.1.0 coexistence/backend integration;
- real WordPress durable JobService persistence/lease integration.

### Compiled-registration scale — VERIFIED

`compiled-registration-scale-v1` executed successfully on the exact source head in run `33301807573`:

- 10K: 0.042631 s, 31,457,280 B peak, 2,500 registrations per kind;
- 100K: 0.948230 s, 301,993,984 B peak, 25,000 registrations per kind;
- deterministic 10K checksum across reversed input order;
- manifest integrity/runtime cardinality PASS.

### Compatibility matrix — VERIFIED

Platform Compatibility Matrix run **33301807593 / #6 SUCCESS** independently verified the same exact source head before every job.

Verified **10/10** combinations:
- WordPress 6.9 × PHP 8.2 / 8.3 / 8.4 / 8.5 on MySQL 8.4;
- WordPress 7.1 × PHP 8.2 / 8.3 / 8.4 / 8.5 on MySQL 8.4;
- MariaDB 10.11 × WordPress 6.9 / 7.1 on PHP 8.4.

Every matrix job completed SUCCESS. The MySQL jobs include source-contract validation, complete smoke, persistence, real WordPress AJAX/nonce/Policy and durable JobService execution. The MariaDB jobs cover persistence and WordPress integration baselines.

## Verified readiness blockers

### R1 — Canonical locked PHP development toolchain — BLOCKED

Repository root currently has `composer.json` but no committed `composer.lock`. ADR-0214 requires Composer 2.x with a committed lockfile plus the canonical PHP quality stack: PHPCS + WordPress Coding Standards, WordPress-aware PHPStan and PHPUnit.

Required closure:
- select/pin the accepted toolchain under the existing build contract;
- generate and commit the Composer lockfile from Composer, never hand-author it;
- expose canonical Composer commands;
- execute them in hosted CI.

### R2 — JavaScript/TypeScript/admin build toolchain — BLOCKED

Repository root currently has no `package.json` or npm lockfile, and no canonical compiled admin application source/build graph is present. ADR-0214 requires Node 24 LTS, npm lockfile and `@wordpress/scripts` as the initial WordPress-oriented tooling baseline.

The Runtime Observatory remains valid because it is intentionally usable server-side. However, the JS/TS formatting, lint, typecheck and production-build categories remain not executable.

Required closure before a JS/TS business/admin surface depends on it:
- approved Node 24/npm package graph and lockfile;
- WordPress scripts/format/lint/typecheck/build commands;
- deterministic admin asset output/manifest contract;
- hosted CI execution.

### R3 — Unit and browser E2E suites — NOT VERIFIED

Current `tests/` contains `Smoke/` and `Integration/` only. ADR-0214 requires PHPUnit and Playwright categories; no dedicated unit or browser E2E suite is currently present.

Required closure:
- canonical PHPUnit unit test runner/suite for deterministic domain rules;
- browser E2E baseline before critical UI/user workflows are claimed ready;
- accessibility automation may augment but not replace required manual/keyboard evidence where applicable.

### R4 — Dependency/security and package artifact gates — NOT VERIFIED

A canonical dependency/advisory/license gate and distributable package/build-artifact validation are not yet represented as milestone evidence.

Required closure:
- Composer/npm dependency and advisory checks;
- distributed dependency license inventory where applicable;
- deterministic distributable plugin package validation;
- no secrets/dev-only/test artifacts in release package;
- version/text-domain/Free-vs-Pro boundary checks when applicable.

### R5 — Multisite-specific runtime matrix — NOT VERIFIED

Current snapshots expose site/network/Multisite context, but the accepted WP121 exclusions still include Multisite-specific AJAX/queue worker switching and network-admin behavior. Generic site/network-aware data structures are not a Multisite runtime certification.

## Non-blocking later capability work

The following are still real planned work but do not automatically become WP121 source-foundation blockers unless their owning business/release stage requires them:
- live production DB migration/rollback;
- WordPress.org stable submission;
- final public Action Scheduler distribution/vendoring mechanism;
- provider-specific/live external acceptance;
- Audit viewer legal-hold/export product surfaces;
- high-concurrency fairness/resource admission beyond the bounded foundation contracts.

## Current gate result

`PARTIALLY_COMPLETE / BLOCKED_FOR_MODULE_HANDOFF`

Reason: core runtime, Runtime Observatory, 10K/100K scale evidence and the exact-source compatibility matrix are VERIFIED, but the milestone-level reproducible development/quality/package toolchain is incomplete.

## Ordered closure path

1. close R1 reproducible PHP quality toolchain;
2. close R2 JS/TS/admin build baseline before depending on JS admin surfaces;
3. establish canonical unit/E2E evidence and dependency/package gates at the minimum depth required for first module development;
4. close or explicitly stage the Multisite-specific runtime baseline under its accepted ownership;
5. rerun the complete WP121 FULL readiness gate;
6. only then change WP121 to PASS and authorize the first business-module tranche.

No business module is authorized by this document while the result remains `BLOCKED_FOR_MODULE_HANDOFF`.
