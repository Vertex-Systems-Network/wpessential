# WPEssential — Quality Gates & Test Strategy

Status: **Phase 0 mandatory engineering policy**

## 1. Definition

A feature is not complete because it renders correctly once. The applicable gates below must pass, or the task is reported **PARTIALLY_COMPLETE** with exact unverified items.

Planning documents/evidence protocols are not executed test evidence. In `PLANNER_ONLY` mode executable outcomes are `NOT EXECUTED`.

## 2. Two-speed verification

WPEssential uses two verification speeds.

### FAST GATE

Run during a bounded implementation change as applicable:
- relevant formatter/coding standards;
- targeted lint;
- targeted typecheck/static analysis;
- affected unit tests;
- affected integration/permission tests;
- affected production build;
- targeted static/security checks.

FAST GATE optimizes feedback speed while the change is small.

### FULL GATE

Run at milestone/release boundaries as applicable:
- broad unit tests;
- WordPress integration tests;
- E2E;
- migration/upgrade/recovery tests;
- authorization/security regression;
- compatibility matrix;
- dependency/security audits;
- production build/package validation;
- broader regression/performance evidence.

FAST GATE never substitutes a required FULL GATE.

## 3. Baseline failure policy

A failure verified to predate the current change is labeled:

`BASELINE FAILURE`

Record:
- check/test name;
- baseline revision;
- first observed date;
- failure signature/summary;
- blocking/non-blocking classification;
- linked issue/work item;
- owner when known.

Do not:
- blame new work for a verified baseline failure;
- silently change unrelated code just to make all checks green;
- hide a baseline failure from milestone/release reporting.

If evidence is insufficient to prove the failure predates the change, classify it `UNKNOWN/INVESTIGATING`, not baseline.

## 4. Flaky-test policy

A test that intermittently fails without an intended state change is a defect.

Forbidden:
- repeatedly rerunning until green and reporting only the passing run;
- weakening a correct test solely to reduce flakiness;
- calling an unstable suite fully green without disclosing instability.

Temporary quarantine, if genuinely necessary, must record:
- test ID/name;
- failure signature;
- evidence of flakiness;
- linked defect/work item;
- owner;
- release-blocking classification;
- quarantine reason;
- expiry/review date;
- replacement verification where required.

## 5. Review evidence classification

Every meaningful implementation review is labeled:
- `INDEPENDENT REVIEW`
- `SELF REVIEW`
- `AUTOMATED REVIEW`

The same AI/person authoring and reviewing the change is `SELF REVIEW`, not independent review.

Automated/static review is useful evidence but is not an independent architectural/security reviewer.

## 6. Required toolchain categories

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

## 7. WordPress standards

Follow WordPress coding/security conventions where they apply. WordPress states that new/updated ecosystem interfaces should target WCAG 2.2 Level AA.

References:
- https://developer.wordpress.org/coding-standards/wordpress-coding-standards/
- https://developer.wordpress.org/coding-standards/wordpress-coding-standards/accessibility/

## 8. CI matrix direction

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

## 9. Unit tests

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

## 10. WordPress integration tests

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

## 11. E2E tests

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

## 12. Security and negative-requirement tests

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
- important `MUST NOT` behavior from the module/milestone specification.

High-risk regressions and critical negative requirements become permanent automated tests where practical.

## 13. Migration tests

For each schema migration:
- fresh install
- upgrade from previous released schema
- representative existing data
- partial/failure recovery where possible
- idempotent re-run behavior
- indexes/constraints
- large-data strategy
- deployment ordering/compatibility
- rollback or documented restore procedure

For risky schema evolution, test any accepted `Expand → Migrate/Backfill → Verify → Contract` sequence where used.

Never mark a DB migration safe from code inspection alone.

## 14. Backup provider acceptance

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

## 15. Query performance tests

Representative datasets must test:
- pagination
- meta-heavy queries
- relation queries
- custom-table indexes
- admin columns with many rows
- listing render
- large import batches

Track query count, execution time and memory in benchmark fixtures. Performance budgets are module-specific and stored with implementation plans.

## 16. Asset isolation tests

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

## 17. Accessibility checks

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

## 18. Compatibility tests

When adapter is claimed supported, test with its supported version set:
- Elementor
- Gutenberg/current WordPress editor
- Bricks
- WPBakery
- Visual Composer
- WooCommerce
- object/page caches relevant to module

Do not load/test every integration on every core PR if cost is excessive; maintain smoke/nightly integration suites based on risk.

## 19. Import/export round-trip

For each definition type:
- export
- import on clean site
- compare canonical definition
- conflict mapping
- dependency mapping
- unsupported newer schema
- corrupted checksum/manifest
- secrets absent by default

## 20. Failure injection

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

## 21. Concurrency/idempotency evidence

Where relevant, test:
- duplicate requests/jobs;
- simultaneous updates;
- stale writes;
- lease/lock expiry;
- crash-before/after authoritative mutation;
- unknown external outcome;
- retry/reconciliation.

A duplicate delivery/request must not be assumed safe because the queue/client normally sends once.

## 22. Static analysis

Target strong PHP static analysis without allowing a giant permanent baseline to hide new errors. Exact level/tool set by build ADR.

TypeScript runs with strictness appropriate for production and avoids unchecked `any` as the default API/data model.

## 23. Dependency checks

CI checks:
- Composer dependency audit
- npm dependency audit/advisories
- lockfiles committed
- license inventory for distributed dependencies
- prohibited paid/proprietary UI assets in distributable source

Security advisories are assessed, not blindly auto-fixed through major upgrades.

## 24. Build/package gate

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

## 25. Git/PR gate

Each meaningful PR includes:
- problem/requirement/work ID where assigned
- research performed (real links if external decisions mattered)
- architecture/change impact
- screenshots/video only when useful, not as test evidence alone
- exact FAST/FULL tests run and results
- baseline/flaky failures disclosed
- review classification (`INDEPENDENT`, `SELF`, `AUTOMATED`)
- security considerations
- migration/rollback/recovery classification
- known risks
- documentation/checkpoint update

Commit messages communicate intent. `update`, `fix stuff`, `late work`, `final` are not acceptable commit messages.

## 26. Change impact template

For substantial changes record:

**Affected** — modules/APIs/data/users  
**Unaffected** — explicitly stable areas  
**Risk** — likely failure modes  
**Migration** — existing data/code path  
**Rollback/Recovery** — recovery mechanism/class  
**Verification** — FAST/FULL tests/evidence

## 27. Release gate

Before release:
- clean checkout build
- all required CI green or explicitly blocked by documented unacceptable failure (which blocks release)
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
- release state/recovery class recorded per `docs/RELEASE-INCIDENT-RECOVERY-GOVERNANCE.md`.

## 28. Production-readiness adversarial review

Ask:

> If this shipped today and failed at 3 AM, what breaks, what data is exposed/corrupted, how is the failure detected, and how does an operator recover?

Important findings block “production ready.”

## 29. Stop-the-line quality failures

Immediately stop affected work for evidence of:
- unexpected data loss/corruption;
- cross-site/user protected data leakage;
- credentials/secrets exposure;
- critical authorization bypass;
- migration corruption;
- unexplained massive/out-of-scope diff;
- repository/environment state that cannot be safely identified.

Follow `docs/RELEASE-INCIDENT-RECOVERY-GOVERNANCE.md`.

## 30. Reporting language

Use when applicable:

- **Verified** — evidence/test executed successfully
- **Not Verified** — not executed or environment unavailable
- **Baseline Failure** — verified to predate current change
- **Flaky / Investigating** — unstable/uncertain failure; not green evidence
- **Known Risk** — accepted unresolved risk
- **Next Action** — exact follow-up

Never translate “looks correct” into “tests passed.”