# Local development baseline — 2026-10-13

Status: LOCAL WIP, NOT CI-PASS, NOT MERGE-READY, NO DEPLOYMENT.

## Isolated development environment

- Persistent Vercel sandbox `wpessential-localdev`, workspace `/vercel/wpessential`, source `main` commit `7a42187d79c9df93814676594ec1f572f7eec8a5` before local edits.
- Separate disposable WordPress 7.1 at `/vercel/wpessential-wordpress`, MariaDB 11.8 database `wpessential_local`, PHP 8.5.4, Composer 2.9.5, Node 24.21, npm 11.19.
- WordPress frontend responded HTTP 200; WPEssential Free plugin activated from symlink. No production data/secrets/providers or live deployment.
- Local database and WP administrator credentials remain only in protected `/vercel/.wpessential-local-credentials`; do not commit or copy them.

## Actual verified baseline

- `composer install`, `npm ci`, PHP architecture validator, npm TypeScript typecheck, and npm admin build succeeded.
- Discovered `frameworks/functions.php` early `exit` inside Composer's `autoload.files`, so plain `vendor/bin/phpunit --version` and test commands falsely exited status 0 without executing tests. Changed guard to `return` without registering WordPress functions outside WordPress.
- After fixing name collisions with PHPUnit final `TestCase::status()` and `TestCase::run()` in test helper methods, real PHPUnit 11.5.56 suite executed **1,418 tests, 6,042 assertions, 187 errors, 41 failures**, 7 PHPUnit deprecations on PHP 8.5.4. These are NOT PASS and require separate root-cause triage.
- Error groups: 123 field-settings named-map violations, 27 relation bounds, 9 AbilityDescriptor name validation, 7 relation direction, 6 portable-view remap, plus smaller categories. A focused Dashboard Draft review suite ran six tests with one pre-existing assertion failure.
- PHPCS targeted legacy files did not pass due existing coding-style violations; no false pass claim.

## Additional local correction batch — empty object/map handling

- PHP `array_is_list([])` returns true; optional empty map inputs were incorrectly rejected by multiple existing normalizers and a portability remap validator. Six scoped source files now accept the *empty* PHP array as an empty map while continuing to reject any **non-empty numeric list** (no widened executable/list authorization).
- Source scope: `FieldDefinitionNormalizer`, `FieldGroupDefinitionNormalizer` (presentation/storage), `FieldValueNormalizer`, `PostMetaRegistrationCompiler`, `RelationDefinitionNormalizer`, `AdminColumnsViewPortabilityCodec`.
- Three additional regression tests verify empty Settings, empty Field Group presentation/storage, and empty Relation bounds/direction defaults; non-empty numeric lists still rejected in the Field and Relation cases.
- Full local PHPUnit on PHP 8.5: **1,421 tests / 7,206 assertions, 24 errors, 24 failures, 1 warning, 7 deprecations**. Previous baseline before the changes: 1,418 tests / 6,042 assertions, 187 errors / 41 failures. This is progress, **NOT a green gate**.
- Focused FieldDefinitionNormalizer (8 tests), FieldGroupDefinitionNormalizer (5 tests), RelationDefinitionNormalizer (9 tests) suites passed; TypeScript typecheck and architecture validation passed.
- Remaining failures include AbilityDescriptor naming mismatch (`wpessential/admin-columns/personal-preference/<verb>` vs the strict two-part action contract), and other runtime/test fixture drift. Investigate separately rather than loosening authorization/validation contracts blindly.

## Next development steps

1. Reconcile PHPUnit CI invocation so test commands cannot silently claim green on zero executed tests.
2. Triage deterministic contract errors by shared root cause; validate against accepted architecture/ADRs before changing product behavior or test expectations.
3. Repair one bounded dependency-ready group locally, rerun focused PHPUnit and broader suite, then static checks. Do not weaken quality gates or promote full 56-surface parity.
4. After exact-head tests can pass, review and push scoped PR with evidence; do not merge this WIP as-is.

This record is local working-state evidence, not production or runtime certification.

## Continuation: canonical names, fail-closed boot, and test fixtures

- Aligned the invalid four-segment Admin Columns personal-preference Ability names and Status admin Ability names to the strict existing `wpessential/<domain>/<action>` contract without changing AJAX route types, nonce operations, privilege requirements, mutation boundaries or caller identity source. These registrations previously failed canonical descriptor validation; no alias or relaxed validator was introduced.
- Added missing-service checks to Admin Columns required service registrations, so missing dependencies fail closed as domain-specific `LogicException`; optional dispatcher/gateway are looked up only when provided.
- Relations repeated module boot rechecks migrations and reuses only the validated canonical admin controller instead of attempting duplicate service registration; foreign controller types are rejected.
- Repaired focused test fixtures: WordPress CT1 fake reports MySQL identity; Admin Columns personal preference test uses by-reference reader; Dashboard import/status/constructor fixture drift corrected; Admin Columns route expectation reconciled with registered canonical route list.
- Latest full PHP 8.5.4 PHPUnit 11.5.56: **1,421 tests, 7,422 assertions, 4 errors, 24 failures, 1 warning, 7 PHPUnit deprecations**. This is still a **failed suite** and **not merge-ready**. Remaining errors involve Dashboard/Relations checksum and revision fixtures; do not weaken the in-memory repository's fail-closed integrity guarantees. Remaining assertion failures require separate root-cause review.
- Focused Admin Columns module tests, Relations migration boot, CT1 introspection, Dashboard adapter, Status Ability and Admin Columns preference tests passed; TypeScript typecheck and Architecture Guard passed after changes. No production deployment.
