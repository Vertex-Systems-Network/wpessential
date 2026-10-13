# PHPCS baseline comparison — diagnostic only (Issue #1417)

## Evidence and bounds

- Pristine `main` `7a42187d79c9df93814676594ec1f572f7eec8a5`: 129,898 PHPCS errors and 5,215 warnings (prior exact local audit).
- Repair PR #1416 head `1046c8c54e6f5814e670161df3d153dc1b49e042`: 130,033 PHPCS errors and 5,215 warnings (+135 errors). This is not a clean standards result.
- Exact-head GitHub workflows **PHP Quality**, **Architecture**, **CPT Runtime** and **Taxonomy Runtime** failed at the repository-wide WordPress Coding Standards step. Their downstream PHPStan/PHPUnit/integration steps cannot be inferred green.
- `phpcs.xml.dist` intentionally excludes only WordPress class filename convention for PSR-4 files but includes the full WordPress ruleset, producing widespread formatting conflicts with PSR-ish application source. **Do not disable this existing CI gate merely to merge PR #1416.**

## Diagnostic helper

`tools/quality/compare-phpcs-reports.php` takes two PHPCS `--report=json` results and optionally their distinct absolute workspace roots:

```bash
composer quality:phpcs -- --report=json > baseline-phpcs.json || true
# Repeat at the candidate ref, in its own checkout, using the same locked dependencies/configuration.
php tools/quality/compare-phpcs-reports.php baseline-phpcs.json candidate-phpcs.json /absolute/baseline/checkout /absolute/candidate/checkout
```

Use both locked dependency graphs, same PHP and WPCS/PHPCS versions, same `phpcs.xml.dist` configuration, exact SHAs, and separate clean checkouts. Capture command exits and hashes as evidence. `|| true` is for **capturing JSON in a diagnostic shell only** and must **not** be substituted for a required CI pass. The helper emits per-file, type, sniff and message deltas; exit `1` means an increase, `0` means no increase, `2` means malformed/unanchored input. Do not supply fake/empty baseline reports.

The helper is intentionally **not** wired into branch protections or a replacement for `composer quality:phpcs`; per-message counts cannot detect a new defect offset by a removed identical defect. This diagnostic is not strict changed-line enforcement, an adopted WPCS exception, or a release gate.

## Next decisions/work

1. Obtain comparable PHPCS JSON evidence from exact `main` and #1416 revisions in pinned PHP 8.2 CI-equivalent environments.
2. Fix the 135 *net additional* errors by reviewed changed-file diffs (and identify any masked additions) without sweeping unrelated runtime code.
3. Independently review the project's WordPress and PSR style standard conflict. Any formal ratchet requires rule/file baseline provenance, strict changed-line checks, review and documented governance approval; do not silently change rules or call a diagnostic pass `CI green`.
4. Re-run full required exact-head CI, inspect review threads and main drift, then perform a protected merge only after every required check is green. Production release remains outside scope.

This checkpoint intentionally leaves the ten milestone progress denominators unchanged: no surface runtime parity or release proof was added.
