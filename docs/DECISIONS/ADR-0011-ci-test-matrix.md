# ADR-0011 — CI and Compatibility Test Matrix

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27

## Context

WPEssential spans PHP, React/TypeScript, WordPress native APIs, database migrations, REST, background jobs, page-builder adapters, Free/Pro compatibility and destructive operations. A single “latest PHP + latest WordPress” CI job would not prove production compatibility.

WordPress Playground provides official workflows for clean WordPress environments, PHPUnit and Playwright-style E2E testing and is a useful matrix tool, but some filesystem/database/provider behavior may still need conventional containers/services.

References:
- https://developer.wordpress.org/playground/handbook/guides/phpunit-testing/
- https://developer.wordpress.org/playground/handbook/guides/e2e-testing-with-playwright/

## Proposed decision

Adopt layered CI rather than running every expensive integration on every commit.

### Tier A — every pull request

- PHP formatting/coding standards
- PHP static analysis
- JavaScript/TypeScript formatting/lint
- TypeScript strict check
- production frontend build
- PHP unit tests
- focused WordPress integration tests
- REST/authorization regression tests for touched modules
- dependency vulnerability audit
- Free artifact package check
- plugin/composer/package version consistency
- optional-module asset isolation smoke tests

### Tier B — merge/main matrix

Test combinations based on accepted compatibility ADR:
- minimum WordPress + minimum PHP
- minimum WordPress + recommended/current PHP
- current WordPress + minimum PHP
- current WordPress + recommended/current PHP
- current WordPress + newest supported PHP

Add multisite fixture for modules/platform code that claim multisite support.

### Tier C — scheduled/nightly

- WordPress trunk/nightly compatibility signal
- page-builder integration versions
- WooCommerce adapter versions
- large-data/performance fixtures
- long-running background job tests
- Free×Pro compatibility matrix
- import/export round trips
- migration historical fixtures
- accessibility browser suite

### Tier D — provider/release certification

For external storage/API providers:
- authenticated sandbox/staging integration tests
- upload/download/checksum/delete
- token refresh/revoke
- timeout/rate/failure behavior
- restore verification where applicable

Secrets are provided through CI secret management and never available to untrusted pull-request code.

## Test environment strategy

Use WordPress Playground where it gives fast, reproducible environments. Use containerized MySQL/MariaDB/PHP/WordPress or dedicated provider test sites when the behavior requires real filesystem, networking, cron, database locking or third-party credentials.

## Free / Pro matrix

Once Pro exists, CI must include:
- current Free + current Pro
- newest Free + previous supported Pro
- previous supported Free + newest Pro where compatibility is claimed
- intentionally incompatible pair verifies safe degraded UX rather than fatal

## Artifact gates

Free package must prove:
- no Pro source/assets
- no development-only dependencies/files
- plugin header/version/min requirements match Composer/docs
- compiled assets referenced by manifest exist
- translations/text domain valid
- no secrets/local config

Pro package receives equivalent checks plus platform API compatibility metadata.

## Required reports

CI failures must expose actionable logs without leaking secrets. Performance regressions use retained benchmark summaries rather than binary pass/fail alone.

## Acceptance work

Before accepting:
1. accept ADR-0002 compatibility floor;
2. choose PHP/JS tools and build system;
3. prove a minimal Playground PHPUnit + Playwright pipeline;
4. prove a conventional database/container integration job;
5. set required vs informational checks and time budgets;
6. define branch protection/release gates;
7. define provider-secret security model.
