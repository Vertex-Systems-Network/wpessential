# ADR-0012 — Canonical Build Toolchain

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27

## Context

The legacy WPEssential repository contains both Laravel Mix scripts and Vite packages. A new platform should have one explicit, reproducible build path rather than carrying two frontend ecosystems.

WPEssential also needs WordPress-aware dependency handling, route/module code splitting, TypeScript, React, asset manifests and production packaging.

## Proposed decision

### PHP
- Composer-managed PSR-4 autoloading;
- one root `WPEssential\` namespace with coherent directory mapping;
- WordPress-compatible coding standards + static analysis;
- production artifact excludes dev dependencies;
- Composer platform requirement must match plugin header/CI through an automated consistency check.

### Admin frontend
Preferred direction: **Vite + React + TypeScript** for the WPEssential admin application, subject to a WordPress package externalization spike.

Why Vite is preferred over carrying forward Laravel Mix:
- modern maintained development/build path;
- straightforward ES modules/code splitting;
- strong React/TypeScript ecosystem;
- no Laravel-specific reason exists in the new WordPress repository;
- easier explicit asset-manifest strategy than preserving legacy Mix assumptions.

### WordPress packages
Do not blindly bundle duplicate React/WordPress runtimes. The acceptance spike must determine which WordPress packages are externalized to WordPress-provided handles and which application libraries are bundled, while preserving the selected minimum WordPress version.

### Asset manifest
Build outputs must produce a deterministic manifest containing entry/chunk/dependency/hash metadata consumed by the Asset Registry. PHP code must not guess hashed filenames.

### Development structure
Admin source lives outside built distributable assets, with documented commands for:
- install dependencies;
- development/watch;
- typecheck/lint;
- production build;
- test;
- package.

No source-map or dev-server assumption may be required in a production plugin.

## Alternatives

### Laravel Mix
Rejected as the preferred new architecture unless the spike uncovers a WordPress-specific blocker to Vite. It exists in the legacy project but adds no product value and would preserve historical toolchain drift.

### `@wordpress/scripts` for everything
A serious alternative because it aligns with WordPress tooling, especially block packages. It may be preferable for Gutenberg-specific generated packages, but the large modular admin SPA needs a spike comparing flexibility, code splitting, dependency externalization and maintenance.

### Custom Webpack configuration
Possible but not preferred unless required by WordPress externals/build constraints; it creates more maintenance surface.

## Acceptance spike

Before Accepted:
1. minimal React/TS WPEssential admin page;
2. route-level lazy chunk;
3. import Lucide + one approved MIT Untitled UI component;
4. import stable WordPress components/DataViews;
5. verify no duplicate incompatible React runtime;
6. generate/read PHP asset manifest;
7. verify scoped CSS does not leak to unrelated wp-admin;
8. production package works with no Node runtime;
9. measure baseline admin JS/CSS size;
10. compare Vite vs `@wordpress/scripts` on the same spike.

## Consequences if Vite is accepted

- legacy Mix files/config are not migrated unless they contain separately valuable logic;
- block-builder output may still use WordPress-native build tooling where generated extension packages require it, but core repository scripts must clearly separate those tasks;
- frontend build/version consistency becomes part of CI/release artifact validation.
