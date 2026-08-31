# ADR-0012 — Canonical Build Toolchain

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27  
Static research refreshed: 2026-08-28

## Context

WPEssential needs one explicit, reproducible WordPress-aware build path for TypeScript/React administration, module/route assets, scoped styles, dependency metadata, translations and release packaging.

### Current repository baseline correction

The currently accessible repository baseline does **not** contain an active frontend runtime/toolchain to preserve:

- `main` currently exposes only the repository README and LICENSE;
- `planning/master-architecture` contains planning/governance/documentation and no root `package.json`;
- no current active Laravel Mix, Vite or other Node build manifest has been verified on these authoritative branches.

Earlier references to a “legacy repository containing both Laravel Mix and Vite” are therefore treated as **historical/unverified context**, not current implementation truth. A future adoption audit may recover legacy artifacts elsewhere, but WP09 must not design around them unless repository evidence is actually found.

This strengthens the requirement to choose the future build tool by controlled evidence rather than accidental legacy retention.

Current WordPress documentation provides `@wordpress/build`, an opinionated build system specifically for WordPress plugins. Its stable build/transpile/externalization capabilities are a strong first candidate. Its current page/route/widget facilities are experimental and must not become WPEssential architecture dependencies merely because they are convenient.

Static evidence is recorded in `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`.

## PHP toolchain direction

- Composer-managed PSR-4 autoloading;
- one root `WPEssential\` namespace with coherent directory mapping;
- WordPress-compatible coding standards and static analysis;
- production artifact excludes dev dependencies/tools unless explicitly runtime-required and licensed;
- Composer platform requirement, plugin headers, P-001 support metadata and CI matrix must be automatically consistency-checked;
- no legacy namespace/path typo or mixed dependency model is carried forward merely for compatibility with unverified historical code.

## Frontend build candidates

### Candidate 1 — `@wordpress/build` — preferred first evidence candidate

Evaluate stable capabilities including:
- TypeScript/JSX transpilation;
- esbuild-based build pipeline;
- CommonJS/ESM/browser output where applicable;
- SCSS/CSS Modules;
- LTR/RTL output;
- generated PHP/WordPress asset registration metadata;
- third-party plugin namespace support;
- externalization of `@wordpress/*`, React and other WordPress-provided vendors according to supported profiles;
- watch/incremental behavior.

Hard boundary:
- experimental admin page/route/widget features are **not** required by WPEssential's routing/module architecture;
- adoption of any experimental facility requires separate evidence/decision and a safe fallback/migration path.

### Candidate 2 — `@wordpress/scripts`

Use as the mature WordPress-tailored comparison baseline for:
- build/watch;
- WordPress dependency extraction;
- lint/format/test integration;
- plugin ZIP/package workflows;
- block metadata where applicable.

### Candidate 3 — Vite

Evaluate only if the WordPress-native candidates fail a material, documented WPEssential requirement.

A custom Vite path would make WPEssential responsible for continuously proving:
- WordPress package externalization;
- React/JSX-runtime compatibility;
- asset dependency metadata;
- PHP enqueue/manifest integration;
- translations/RTL packaging;
- minimum/current WordPress package mapping;
- release artifact determinism.

Generic development ergonomics alone are not enough to justify this maintenance burden.

### Laravel Mix

Not a candidate for the new architecture unless future repository evidence and an extraordinary WordPress-specific requirement justify reopening it. Current authoritative branches do not prove an active Mix build to preserve.

## Current proposed evaluation order

1. `@wordpress/build` using only stable build capabilities;
2. `@wordpress/scripts` on the identical representative fixture;
3. identify material unmet requirements, if any;
4. only then evaluate Vite against those explicit gaps;
5. do not introduce a second production build system merely because one candidate handles one fixture differently.

## Minimum/current WordPress package mapping

The build cannot assume that the latest npm package APIs equal the accepted minimum WordPress runtime.

Future P-008 evidence must:
- record package/runtime versions for the minimum WordPress profile and current/reference profile;
- use official WordPress package version/dist-tag guidance where applicable;
- verify every imported public `@wordpress/*` API actually exists/behaves on the supported runtime;
- prevent a package used during compilation from silently creating a runtime dependency newer than the declared WordPress minimum;
- support capability-gated UI behavior from ADR-0005, including a minimum-version path that does not require WordPress 7.1-only `wp-theme` capabilities while WP 6.9 remains the candidate floor.

## WordPress/React externalization requirement

WordPress 7.1 continues React 18.3, and Core's React 19 transition was punted after mixed runtime/JSX compatibility problems.

Therefore acceptance requires machine-verifiable proof that:
- WPEssential does not ship a competing React or React DOM runtime on normal wp-admin surfaces;
- JSX/runtime helpers do not smuggle a second incompatible React runtime into chunks;
- `@wordpress/*` dependencies resolve to the correct WordPress-provided runtime handles/modules where intended;
- generated chunks preserve dependency loading order;
- shared external/runtime dependencies are not duplicated across module entries;
- plugin assets remain compatible with the accepted minimum/current WordPress profiles.

A duplicate/mismatched React runtime is a stop-the-line failure, not a bundle-size optimization.

## Asset Manifest / Registry contract

Regardless of tool choice:

- PHP never guesses hashed filenames;
- every entry/chunk has deterministic machine-generated registration metadata;
- dependency/version metadata is machine-generated where supported rather than manually duplicated;
- module assets load only on exact required admin/frontend/editor runtime paths;
- unrelated wp-admin/site screens receive no WPE module bundle/styles;
- shared chunks register/load once;
- lazy chunks and failure states are deterministic/observable;
- production source maps follow an explicit release/privacy policy;
- translations and RTL outputs are release-artifact requirements;
- third-party builder/adapter assets remain isolated to the relevant integration context;
- experimental `@wordpress/build` routing/widgets are not required by the Asset Registry.

Use native `.asset.php`/generated PHP registration metadata where it cleanly satisfies the contract rather than inventing another manifest format without need.

## Reproducibility and dependency governance

Future selected toolchain must define:
- one package manager/lockfile policy;
- exact Node/tool versions for development/CI, not end-user runtime;
- dependency/license inventory;
- deterministic clean-install command;
- production build from a clean checkout;
- cross-platform path/case behavior where supported development environments matter;
- dependency update policy and generated-artifact review rules;
- release artifact verification from the actual ZIP, not just source tree;
- documented nondeterministic fields, if any, excluded or normalized for reproducibility comparisons.

No Node runtime may be required on end-user WordPress installations.

## Development/release commands required after implementation is authorized

The final toolchain must expose documented commands for:
- dependency install;
- development/watch;
- typecheck;
- JS/style lint;
- production build;
- unit tests;
- E2E tests;
- plugin ZIP/package;
- bundle/dependency analysis;
- release artifact verification.

These commands do not exist merely because they are listed here; P-008/P-007 evidence must prove them after authorization.

## Acceptance spike — NOT AUTHORIZED YET

A future fixed P-008 evidence protocol must compare candidates on the same controlled fixture containing at least:

1. React/TypeScript admin entry using WordPress-provided runtime;
2. multiple module entries;
3. shared chunk;
4. lazy/dynamic chunk;
5. minimum-version-compatible WordPress component/DataViews imports;
6. optional capability-gated WordPress 7.1+ theme integration;
7. WPEssential wrapper component;
8. icon abstraction;
9. CSS Module/scoped styles;
10. LTR + RTL output;
11. localization/string extraction;
12. generated WordPress dependency metadata/PHP registration;
13. exact-route enqueue and unrelated-screen absence;
14. production source-map policy;
15. production ZIP excluding Node/dev/test-only material;
16. bundle/dependency duplicate report;
17. minimum/current WordPress compatibility;
18. clean/repeated build reproducibility comparison.

Compare:
- correctness;
- React/WordPress externalization;
- build/watch speed;
- chunking/lazy-loading behavior;
- route asset isolation;
- maintenance/configuration burden;
- output/package size;
- translation/RTL integration;
- PHP registration complexity;
- cross-platform behavior;
- release ergonomics;
- test/CI integration.

Vite is evaluated only if a documented unmet requirement survives the two WordPress-native candidates.

Under ADR-0014 this executable spike **requires explicit owner consent before any scaffold, package manifest, dependency install, build, lint, test or ZIP is created/executed**.

## Acceptance consequences

If `@wordpress/build` wins:
- stable build/externalization capabilities become canonical;
- experimental page/router/widget features remain excluded unless separately accepted;
- domain modules stay insulated through UI wrappers and Asset Registry.

If `@wordpress/scripts` wins:
- the same externalization, asset, route, RTL/localization, artifact and reproducibility gates still apply.

If Vite wins:
- the ADR must record the exact unmet WordPress-native requirements that justified owning custom WordPress integration, plus regression tests for that custom integration.

No result permits two competing canonical production build systems without a separate architectural justification.

## Current recommendation

**Evaluate `@wordpress/build` first and `@wordpress/scripts` second on the same controlled fixture. Evaluate Vite only for a proven unmet requirement. Treat historical Mix/Vite references as unverified until repository evidence exists.**

This ADR remains **Proposed** until authorized executable P-008 evidence is completed.
