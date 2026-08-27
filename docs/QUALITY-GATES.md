# WPEssential — Quality Gates & Test Strategy

Status: **Phase 0 mandatory engineering policy**

## 1. Definition

A feature is not complete because it renders correctly once. The applicable gates below must pass, or the task is reported **PARTIALLY COMPLETE** with exact unverified items.

## 2. Required toolchain categories

Final commands are locked after the build ADR, but the repository must provide one canonical command for each applicable category:

- PHP formatting/coding standards
- PHP static analysis
- JavaScript/TypeScript formatting
- ESLint
- TypeScript checking
- frontend production build
- PHPUnit/unit/integration tests
- browser E2E tests
- WordPress compatibility matrix
- dependency/security audit
- package/build artifact validation

No hidden “developer machine only” quality step.

## 3. WordPress standards

Follow WordPress coding/security conventions where they apply. WordPress states that new/updated ecosystem interfaces should target WCAG 2.2 Level AA.

References:
- https://developer.wordpress.org/coding-standards/wordpress-coding-standards/
- https://developer.wordpress.org/coding-standards/wordpress-coding-standards/accessibility/

## 4. CI matrix direction

CI must test the selected supported range, not only the author’s current machine.

Axes after compatibility ADR:
- minimum supported WordPress
- current stable WordPress
- WordPress nightly/beta as allowed-failure compatibility signal where useful
- minimum supported PHP
- current recommended PHP
- newest WordPress-compatible PHP

WordPress Playground is a preferred tool to evaluate because official 2026 documentation supports clean plugin testing, choosing WP/PHP versions, PHPUnit and Playwright E2E.

References:
- https://developer.wordpress.org/playground/handbook/guides/phpunit-testing/
- https://developer.wordpress.org/playground/handbook/guides/e2e-testing-with-playwright/

A second non-Playground environment may still be required for database/filesystem/provider behavior not faithfully represented by Playground.

## 5. Unit tests

Use for deterministic domain rules:
- schema validation
- condition evaluation
- query AST transformations
- relation cardinality
- capability/policy decisions that can be isolated
- definition version/migration logic
- formatter/token behavior
- retry/backoff/idempotency helpers
- provider request signing/mapping

Do not mock so aggressively that tests merely repeat implementation.

## 6. WordPress integration tests

Cover:
- hooks/register/unregister behavior
- CPT/taxonomy registration
- capabilities
- metadata/options
- REST routes/permissions
- cron scheduling
- actual `$wpdb` queries/migrations
- multisite where supported
- activation/deactivation/uninstall behavior
- WordPress filters/actions used by adapters

## 7. E2E tests

Critical user workflows get Playwright/browser tests, for example:

### Platform
- activate → first-run wizard
- Continue Free without account
- enable/disable module
- incompatible Pro/Free state

### CPT/Taxonomy
- create → publish → verify native WP screens
- edit → verify registration update
- dependency warning on destructive change
- import/export round-trip

### High-risk modules later
- role change anti-lockout
- backup creation + verified restore fixture
- reset restore-point flow
- form CRUD workflow
- API endpoint authorization
- frontend dashboard denied/allowed states

E2E tests verify behavior; they do not replace lower-level tests.

## 8. Security test cases

Applicable modules must test:
- unauthenticated requests
- insufficient capabilities
- CSRF/nonces
- IDOR/resource-level authorization
- XSS payloads
- SQL injection-like inputs
- path traversal/archive extraction
- malicious uploads
- SSRF destinations
- role escalation
- duplicate/replay requests
- secret leakage
- multisite boundary

High-risk regressions become permanent automated tests.

## 9. Migration tests

For each schema migration:
- fresh install
- upgrade from previous released schema
- representative existing data
- partial/failure recovery where possible
- idempotent re-run behavior
- indexes/constraints
- large-data strategy
- rollback or documented restore procedure

Never mark a DB migration safe from code inspection alone.

## 10. Backup provider acceptance

A provider is not “supported” because authentication succeeds.

Automated/staged acceptance where provider permits:
1. connect
2. upload known archive
3. list/locate
4. download
5. checksum equality
6. delete/prune
7. expired token refresh
8. interrupted/resumed upload where supported
9. invalid credentials
10. rate-limit/transient failure behavior

Restore is tested independently from upload.

## 11. Query performance tests

Representative datasets must test:
- pagination
- meta-heavy queries
- relation queries
- custom-table indexes
- admin columns with many rows
- listing render
- large import batches

Track query count, execution time and memory in benchmark fixtures. Performance budgets are module-specific and stored with implementation plans.

## 12. Asset isolation tests

Critical WPEssential rule: optional module CSS/JS loads only where used.

Automated assertions should visit:
- Dashboard
- Posts list
- editor
- Plugins
- unrelated plugin screen
- frontend page without WPEssential output
- frontend page with a specific WPEssential output

Verify unexpected WPEssential handles/chunks are absent.

## 13. Accessibility checks

Automated accessibility checks plus keyboard/manual scenarios for core builder patterns:
- focus order
- visible focus
- dialog focus trap/return
- labels/descriptions/errors
- drag/drop keyboard alternative
- color contrast
- status not conveyed by color alone
- tables/data grids
- notifications/toasts
- disabled/loading states

Automated scans cannot be the only accessibility evidence.

## 14. Compatibility tests

When adapter is claimed supported, test with its supported version set:
- Elementor
- Gutenberg/current WordPress editor
- Bricks
- WPBakery
- Visual Composer
- WooCommerce
- object/page caches relevant to module

Do not load/test every integration on every core PR if cost is excessive; maintain smoke/nightly integration suites based on risk.

## 15. Import/export round-trip

For each definition type:
- export
- import on clean site
- compare canonical definition
- conflict mapping
- dependency mapping
- unsupported newer schema
- corrupted checksum/manifest
- secrets absent by default

## 16. Failure injection

External systems must be tested for:
- timeout
- DNS failure
- 4xx auth failure
- 429 rate limit
- 5xx/transient failure
- malformed JSON/XML
- partial upload
- stale token
- revoked token
- database exception where testable

UI must expose actionable state rather than infinite spinners/silent failures.

## 17. Static analysis

Target strong PHP static analysis without allowing a giant permanent baseline to hide new errors. Exact level/tool set by build ADR.

TypeScript runs with strictness appropriate for production and avoids unchecked `any` as the default API/data model.

## 18. Dependency checks

CI checks:
- Composer dependency audit
- npm dependency audit/advisories
- lockfiles committed
- license inventory for distributed dependencies
- prohibited paid/proprietary UI assets in distributable source

Security advisories are assessed, not blindly auto-fixed through major upgrades.

## 19. Build/package gate

Release artifact must verify:
- no tests/dev dependencies accidentally packaged
- no source maps containing sensitive/private paths if policy excludes them
- required build sources documented per WordPress.org rules
- translations/text domain correct
- version consistency plugin header/Composer/package/release metadata
- Free artifact contains no Pro source
- no secrets/local env files
- checksums generated as release process defines

The legacy PHP-version mismatch is explicitly prevented by a consistency test.

## 20. Git/PR gate

Each meaningful PR includes:
- problem/requirement
- research performed (real links if external decisions mattered)
- architecture/change impact
- screenshots/video only when useful, not as test evidence alone
- tests run and exact results
- security considerations
- migration/rollback
- known risks
- documentation/checkpoint update

Commit messages communicate intent. `update`, `fix stuff`, `late work`, `final` are not acceptable commit messages.

## 21. Change impact template

For substantial changes record:

**Affected** — modules/APIs/data/users  
**Unaffected** — explicitly stable areas  
**Risk** — likely failure modes  
**Migration** — existing data/code path  
**Rollback** — recovery mechanism  
**Verification** — tests/evidence

## 22. Release gate

Before release:
- clean checkout build
- all required CI green
- release artifact smoke-tested
- supported upgrade paths tested
- changelog/release notes
- migrations reviewed
- backup/recovery implications reviewed
- Free/Pro compatibility tested
- security dependency review
- no critical/high known defect hidden
- installation/activation on current stable WordPress
- uninstall/data preservation behavior verified

## 23. Production-readiness adversarial review

Ask:

> If this shipped today and failed at 3 AM, what breaks, what data is exposed/corrupted, how is the failure detected, and how does an operator recover?

Important findings block “production ready.”

## 24. Reporting language

Use when applicable:

- **Verified** — evidence/test executed successfully
- **Not Verified** — not executed or environment unavailable
- **Known Risk** — accepted unresolved risk
- **Next Action** — exact follow-up

Never translate “looks correct” into “tests passed.”
