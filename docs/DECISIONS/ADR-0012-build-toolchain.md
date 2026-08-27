# ADR-0012 — Canonical Build Toolchain

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27  
Static research refreshed: 2026-08-27

## Context

The legacy WPEssential repository contains both Laravel Mix scripts and Vite packages. A new platform should have one explicit, reproducible build path rather than carrying two frontend ecosystems.

WPEssential also needs WordPress-aware dependency handling, TypeScript, React, route/module code splitting, scoped styles, asset metadata, translations and production packaging.

Since the earlier planning pass, current WordPress documentation provides an important new option: `@wordpress/build`, an opinionated build system specifically for WordPress plugins. It uses esbuild and includes WordPress package/React externalization plus generated asset/PHP registration metadata.

This materially weakens the earlier assumption that generic Vite should be the default first choice.

Static evidence is recorded in `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`.

## PHP toolchain direction

- Composer-managed PSR-4 autoloading;
- one root `WPEssential\` namespace with coherent directory mapping;
- WordPress-compatible coding standards and static analysis;
- production artifact excludes dev dependencies;
- Composer platform requirement must match plugin header/CI through automated consistency checks;
- no legacy namespace/path typo or mixed dependency model is carried forward.

## Updated frontend build candidates

### Candidate 1 — `@wordpress/build` — **preferred first spike candidate**

Current official documentation describes it as a build system designed for WordPress plugins with:

- TypeScript/JSX transpilation;
- esbuild;
- CommonJS/ESM/browser outputs;
- SCSS/CSS Modules;
- LTR/RTL generation;
- generated PHP registration files;
- WordPress asset metadata;
- third-party plugin namespace support;
- automatic externalization of `@wordpress/*` packages;
- automatic externalization of React and common WordPress-provided vendors;
- watch mode.

This aligns directly with WPEssential's biggest runtime risk: avoiding duplicate/mismatched WordPress and React runtimes.

Important boundary: `@wordpress/build` admin `pages`, routes and widgets features are currently documented as experimental. WPEssential must **not** make its routing/module architecture depend on those experimental facilities unless a later ADR accepts them.

The stable build/transpile/externalization capabilities should be evaluated independently of experimental pages/routes/widgets.

### Candidate 2 — `@wordpress/scripts`

Mature WordPress-tailored tooling with:
- build/watch;
- WordPress dependency extraction;
- lint/format;
- unit/E2E tooling;
- plugin ZIP;
- block metadata workflows.

It remains the primary fallback/comparison baseline.

### Candidate 3 — Vite

Vite remains a technically strong generic frontend tool, but it should now be used only if evidence shows WPEssential has requirements that the WordPress-native options cannot satisfy cleanly.

A custom Vite path means WPEssential must own and continuously verify:
- WordPress package externalization;
- React runtime compatibility;
- asset dependency metadata;
- PHP enqueue manifest integration;
- translation/build packaging integration.

Given WordPress 7.1's real mixed-React compatibility issues, generic bundler ergonomics are secondary to correct WordPress runtime integration.

### Laravel Mix

Rejected as a preferred new architecture. The legacy project provides no WordPress-specific reason to preserve it, and retaining Mix alongside newer tools would reproduce the historical toolchain drift we are trying to eliminate.

## Current proposed evaluation order

1. `@wordpress/build` using only stable build capabilities;
2. `@wordpress/scripts` on the same representative fixture;
3. Vite only if the first two fail material WPEssential requirements;
4. reject Laravel Mix for the new codebase unless extraordinary evidence appears.

## WordPress/React externalization requirement

WordPress 7.1 uses React 18.3. React 19 was punted because mixing runtime/JSX versions caused real plugin crashes.

Therefore the accepted toolchain must prove:
- WPEssential does not ship a competing React runtime on normal wp-admin screens;
- `@wordpress/*` dependencies resolve to the correct WordPress-provided handles/modules where appropriate;
- generated chunks preserve dependency loading order;
- plugin assets remain compatible with the minimum supported WordPress version.

This is a hard acceptance requirement, not an optimization.

## Asset Manifest / Registry contract

Regardless of chosen tool:

- PHP code must never guess hashed filenames;
- every entry/chunk must have deterministic registration metadata;
- dependencies and versions must be machine-generated;
- module assets are loadable only on exact required screens/runtime paths;
- shared chunks load once;
- production source maps follow an explicit release policy;
- translations/RTL outputs are part of the release artifact contract.

The exact metadata format can use WordPress-generated `.asset.php`/registration files where suitable rather than inventing a second custom manifest unnecessarily.

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
- bundle analysis;
- release artifact verification.

No Node runtime is allowed to be required on end-user WordPress installations.

## Acceptance spike — NOT AUTHORIZED YET

A future bounded executable spike must compare the candidate tools on the same representative WPEssential fixture:

1. React/TypeScript admin entry;
2. multiple module entries;
3. lazy chunk;
4. `@wordpress/components`, `@wordpress/dataviews`, `@wordpress/theme`;
5. WPEssential wrapper component;
6. icon abstraction;
7. CSS Module/scoped style;
8. RTL output;
9. translations;
10. generated WordPress dependency metadata;
11. no duplicate React/WordPress runtime;
12. PHP enqueue/registration integration;
13. production ZIP without Node/dev source;
14. bundle-size report;
15. minimum/current WordPress compatibility.

Compare:
- build correctness;
- watch speed;
- chunking;
- WordPress dependency handling;
- maintenance/configuration burden;
- package size;
- release ergonomics;
- test integration.

Under ADR-0014 this executable spike **requires explicit owner consent before any scaffold/package install/build is created or run**.

## Consequences if `@wordpress/build` is accepted

- WPEssential uses a WordPress-owned build/externalization model rather than maintaining generic-bundler compatibility glue;
- stable `@wordpress/build` capabilities become the canonical build layer;
- experimental page/router/widget features remain unused unless separately accepted;
- legacy Mix files/config are not migrated;
- generated Gutenberg/block packages can share the same WordPress-aware ecosystem more naturally;
- toolchain changes still remain isolated from domain modules through Asset Registry and UI wrapper contracts.

## Current recommendation

**Evaluate `@wordpress/build` first, `@wordpress/scripts` second, and use Vite only if executable evidence proves a real unmet requirement.**

This ADR remains Proposed until the owner authorizes and the project executes the comparison spike.
