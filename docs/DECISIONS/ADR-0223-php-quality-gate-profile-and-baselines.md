# ADR-0223 — PHP Quality Gate Profile, One-Shot WPCS Normalization and PHPStan Baseline

Status: **ACCEPTED**
Date: **2026-10-18**
Supersedes: the open remediation plan in Issue #1417 (Option A/B) and the manual indentation batches in PR #1416
Approval: `GOV-OWNER-CONSENT-001` ACTIVE (owner instruction: audit the AI plan, fix it, continue development)

## Context

`phpcs.xml.dist` applied the full `WordPress` ruleset to a PSR-4 / PHP 8.2 object-oriented codebase (ADR-0214). Pristine `main` produced **~130k PHPCS errors / ~5.2k warnings** and **473 PHPStan errors**, so every PR touching PHP was red in PHP Quality, Architecture, CPT and Taxonomy workflows.

The AI plan responded by hand-normalizing indentation in 6–35 file batches (PR #1416, ~176 commits, ~31k errors removed in five days) and by recording each count as a checkpoint. Audit findings:

1. **113,371 of 135,113 violations were mechanically fixable by `phpcbf`** in one run; manual batches were the wrong tool.
2. **~10k remaining violations came from sniffs that contradict ADR-0214** (camelCase methods/properties/variables are published ABI, PSR-4 file names, docblock completeness). "Fixing" them means renaming public APIs — not acceptable.
3. `WordPress.WP.CapitalPDangit`'s fixer rewrites string literals (e.g. service id `platform.abilities.wordpress`) — an auto-fixer that changes runtime values.
4. 319 of 473 PHPStan errors were fail-closed runtime guards re-checking PHPDoc-only types on untrusted WordPress input (`treatPhpDocTypesAsCertain`).
5. State files then declared `NO_VALID_WORK_SLOT`, halting development behind a gate that could not turn green by the chosen method.

## Decision

1. **WPCS profile** (`phpcs.xml.dist`): keep the `WordPress` ruleset (formatting, security, escaping, nonce, prepared SQL, i18n, WordPress API rules). Exclude only: PSR-4 file-name sniffs, camelCase naming sniffs, `DisallowShortArraySyntax`, `CapitalPDangit`, `YodaConditions`, docblock-completeness sniffs, `DisallowShortTernary` (spaceship sort chaining), `EmptyStatement.DetectedCatch` (commented catches; `EmptyCatchComment` still enforced) and `ExceptionNotEscaped`. Line length stays a 120-column warning. Errors gate CI; warnings are reported (`ignore_warnings_on_exit`).
2. **One-shot normalization**: `phpcbf` applied to `wpessential.php` + `frameworks/` once. Proof of no behavior change: PHP token stream compared with whitespace/comments removed for every changed file — the only token differences are trailing commas, braces around single-line bodies, standalone `$i++` → `++$i`, one `else { if }` → `elseif`, explicit parentheses for existing `&&`/`||` precedence, and removal of one empty `if` without side effects.
3. **Remaining security sniffs** were resolved individually: JSON bootstrap output (encoded with `JSON_HEX_*`), the `wpdb` adapter boundary, AJAX nonce verified by `AjaxDispatcher`, and fixed i18n label sets carry justified `phpcs:ignore` comments; translator comments were added.
4. **PHPStan**: `treatPhpDocTypesAsCertain: false` plus an immutable `phpstan-baseline.neon` (138 entries) with `reportUnmatchedIgnoredErrors: true`. New errors fail; fixed errors must be removed from the baseline.
5. **Source-text contract tests** whose exact needles encoded the old formatting were updated to the exact new text (no loosening). `validate-engineering-contracts.php` entrypoint markers compare whitespace-insensitively.

## Verification (local PHP 8.3.6; CI pins 8.2)

- PHPCS: **0 errors** (exit 0), 1,541 advisory warnings (mostly 120-column line length).
- PHPStan level 5: **No errors** with baseline; without the profile change the error set is identical before/after normalization (473 = 473).
- PHPUnit per-file, smoke tests, all composer `php` scripts and all workflow `php tools/…` commands: exit codes identical to `main`.

## Consequences

- PHP Quality now reaches PHPUnit. The PHPUnit-recovery scope of Issue #1415 (`7a42187..1046c8c`, 62 paths) was re-applied on top of the normalization by 3-way merge in phpcbf-normalized space, without #1416's indentation commits: **1,421 tests / 7,489 assertions PASS** locally.
- New code must pass PHPCS with zero errors; run `vendor/bin/phpcbf` before committing instead of hand-formatting.
- Re-enabling an excluded sniff requires a new ADR.
