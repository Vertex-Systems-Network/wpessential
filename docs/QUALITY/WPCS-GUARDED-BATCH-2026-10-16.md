# WPCS guarded batch audit — 2026-10-16

## Scope

This note records the evidence from the guarded, token-preserving remediation run on PR #1416. It is a quality-debt audit, not a standards waiver or a product milestone.

- Source commit: `8029ca74aad1a1a4f2198db48f6d7021aab35008`
- Workflow run: `38949793427`
- Artifact: `wpcs-guarded-diagnostics-8029ca74aad1a1a4f2198db48f6d7021aab35008`
- Artifact ID: `12067811370`
- Artifact digest: `sha256:886994ee093c561576be3f6910c51d91f7c7e1344449321ccdc046a08eae849`

The workflow completed successfully as a guarded diagnostic job. The branch head remained unchanged, so no source formatter output was accepted or pushed.

## Measured report

The exact branch report contained **55,287 errors / 5,104 warnings**, including **38,989 fixable diagnostics**. The guarded selector chose 20 framework files with fixable findings in the explicitly bounded whitespace sniffs. Before and after reports were byte-stable in totals because every candidate was rejected by the token, syntax, PHPUnit, or net-diagnostic safety checks.

The largest relevant diagnostic families were:

| Count | Sniff |
|---:|---|
| 6,679 | `PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket` |
| 6,679 | `PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket` |
| 2,063 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis` |
| 2,015 | `WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeCloseParenthesis` |
| 2,184 | `WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter` |
| 7,093 | `WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase` |
| 2,055 | `WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase` |

## Interpretation

The first three selected files show the same expressions receiving contradictory expectations. The PEAR function-call sniff requests spaces immediately inside parentheses, while the WordPress control-structure sniff rejects spaces for control expressions. The operator sniff also flags the repository's existing compact negation form. Applying PHPCBF to these files can therefore remove one diagnostic family while introducing or retaining another; the guarded workflow correctly rejects such a non-improving result.

The naming findings are a separate compatibility issue. Existing PSR-4 class filenames, public camelCase DTO/property contracts and external action identifiers cannot be renamed as a formatting fix. Any change to the relevant ruleset or baseline requires a separately reviewed governance decision with changed-line and no-new-diagnostic enforcement. This document does not adopt that decision.

## Disposition

- Keep full-tree PHPCS required and failing until a reviewed resolution exists.
- Keep PHPStan and PHPUnit claims separate; the PHP Quality job remains fail-fast after PHPCS.
- Keep guarded remediation available through manual dispatch, with artifact evidence retained for every attempted batch.
- Do not merge PR #1416, close Issue #1417, weaken branch protection, release, deploy, or promote GA from this audit alone.
