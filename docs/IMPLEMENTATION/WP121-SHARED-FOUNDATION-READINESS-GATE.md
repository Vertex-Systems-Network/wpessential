# WP121 — Shared Foundation Readiness Gate

Status: **OPEN / PARTIALLY_COMPLETE — module development not yet authorized**  
Date: **2026-08-30**  
Scope: WPEssential shared Platform foundation only

## Decision rule

WP121 may hand off to the first business-module tranche only when the applicable milestone-level FULL gate is executable and green. A green targeted implementation workflow is necessary evidence, but it is not sufficient when required quality categories remain absent.

This gate follows `docs/QUALITY-GATES.md`: planning claims are not executable evidence, FAST does not substitute for FULL, and unimplemented/untested required categories remain `Not Verified` rather than being inferred green.

## Verified accepted evidence

### Core architecture and platform runtime — VERIFIED

Exact head `8c2744faa722f89a0d78936cbc7053ef673224b0`, Architecture Guards run `33301396205` / #243 SUCCESS verified:

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

`compiled-registration-scale-v1` executed successfully in run `33301396205`:

- 10K: 0.031407 s, 31,457,280 B peak, 2,500 registrations per kind;
- 100K: 0.792474 s, 301,993,984 B peak, 25,000 registrations per kind;
- deterministic 10K checksum across reversed input order;
- manifest integrity/runtime cardinality PASS.

### Compatibility matrix — EXECUTING

Workflow `.github/workflows/platform-compatibility.yml` was added at source head `fb9246f309e4a6a19562652a8f61d93078583b2c`.

Required current matrix:

- WordPress 6.9 × PHP 8.2, 8.3, 8.4, 8.5 on MySQL 8.4;
- WordPress 7.1 × PHP 8.2, 8.3, 8.4, 8.5 on MySQL 8.4;
- MariaDB 10.11 baseline on WordPress 6.9 and 7.1 / PHP 8.4.

The matrix must be judged only from completed exact-head workflow evidence. Partial green jobs do not close this gate.

## Verified readiness blockers

### R1 — Canonical locked PHP development toolchain — BLOCKED

Repository root currently has `composer.json` but no committed `composer.lock`. The accepted implementation baseline requires reproducible locked PHP tooling and milestone FULL-gate categories rather than relying only on runner-global Composer/PHP.

Required closure:

- select/pin coding-standard, static-analysis and test-runner tooling under the accepted build contract;
- commit the generated Composer lockfile;
- expose canonical Composer commands;
- execute them in hosted CI.

Do not hand-write or fabricate a generated lockfile.

### R2 — JavaScript/TypeScript/admin build toolchain — BLOCKED

Repository root currently has no `package.json` or npm lockfile, and no canonical compiled admin application source/build graph is present. The Runtime Observatory is intentionally usable server-side, so this does not invalidate the accepted admin shell; it does mean the baseline JS/TS lint/typecheck/production-build category is not yet executable.

Required closure before a JS/TS business/admin surface depends on it:

- approved package graph and lockfile;
- formatting/lint/typecheck/build commands;
- deterministic admin asset output/manifest contract;
- hosted CI execution.

### R3 — Unit and browser E2E suites — NOT VERIFIED

Current `tests/` contains `Smoke/` and `Integration/` only. No dedicated unit or browser E2E suite is currently present.

Required closure:

- canonical unit test runner/suite for deterministic domain rules;
- browser E2E baseline before critical UI/user workflows are claimed ready;
- accessibility automation may augment but not replace required manual/keyboard evidence where applicable.

### R4 — Dependency/security and package artifact gates — NOT VERIFIED

Current hosted workflows are Architecture Guards and Platform Compatibility Matrix. A canonical dependency/security audit and distributable package/build-artifact gate are not yet represented as milestone evidence.

Required closure:

- dependency/advisory/license checks for distributed dependencies;
- deterministic distributable plugin package validation;
- no secrets/dev-only/test artifacts in release package;
- version/text-domain/Free-vs-Pro boundary checks when applicable.

### R5 — Multisite-specific runtime matrix — NOT VERIFIED

Current snapshots expose site/network/Multisite context, but the accepted WP121 exclusions still include Multisite-specific AJAX/queue worker switching and network-admin behavior. Do not convert generic site/network-aware data structures into a Multisite runtime certification claim.

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

Reason: core runtime, admin observability and scale evidence are green, but the milestone-level reproducible development/quality/package toolchain is incomplete and the compatibility matrix is still executing.

## Ordered closure path

1. finish and evaluate the exact-head WordPress/PHP/MySQL/MariaDB compatibility matrix;
2. close R1 reproducible PHP quality toolchain;
3. close R2 JS/TS/admin build baseline before depending on JS admin surfaces;
4. establish canonical unit/E2E and dependency/package gates at the minimum depth required for first module development;
5. rerun the complete WP121 FULL readiness gate;
6. only then change WP121 to PASS and authorize the first business-module tranche.

No business module is authorized by this document while the result remains `BLOCKED_FOR_MODULE_HANDOFF`.
