# Admin Columns CSV Export Encoder V1

Status: **FOUNDATION ONLY / GATE D REMAINS ACTIVE**  
Parent tracker: GitHub Issue #66  
Implementation issue: GitHub Issue #233  
Source base: `main @ 14c6f0fb5d3ff808a6c7ee34000bb92437bfcefa`

## Purpose

This tranche adds a side-effect-free Admin Columns-owned CSV encoder that can later be consumed by a separately authorized export orchestration layer. It does not read source data, make authorization decisions, execute Query, expose a route, or start a download.

## Input contract

- Columns are a non-empty ordered list of exact `{key,label}` descriptors.
- Column keys are unique bounded UTF-8 strings.
- Rows are a finite ordered list of associative maps whose keys exactly match the declared columns.
- Cells accept only `string`, `int`, finite `float`, `bool`, or `null`.
- Arrays, objects, resources, malformed UTF-8, non-finite floats, key mismatches, and over-budget input fail closed.

Default budgets are 64 columns, 1,000 rows per encoder call, 32 KiB per cell, and 8 MiB encoded output. These are local safety ceilings, not an export-product limit; later orchestration must stream/page authorized data instead of accumulating unbounded exports.

## Encoding contract

- UTF-8 text; no BOM.
- Stable header and cell ordering derived only from the declared column order.
- RFC4180-style comma separation and CRLF record endings.
- Every field is double-quoted deterministically.
- Embedded double quotes are doubled; commas and CR/LF remain valid quoted content.
- `null` encodes as an empty field value.
- Booleans encode as literal `true` / `false`.

## Spreadsheet formula neutralization

After scalar normalization, any cell beginning with optional ASCII space/tab followed by `=`, `+`, `-`, or `@` receives one leading apostrophe before CSV quoting. This forces common spreadsheet consumers to treat the value as text instead of a formula. The original content remains recoverable by removing the single safety prefix for cells known by the export pipeline to have been neutralized.

CSV quoting alone is not treated as formula-injection protection.

## Ownership and security boundary

The encoder intentionally does **not**:

- resolve or read Admin Columns Views;
- invoke Query, Fields, Relations, Taxonomy, Media, Status, provider, or WordPress data APIs;
- infer source-owner export capability;
- authorize users/resources or apply redaction rules;
- expose AJAX/REST/download endpoints;
- select rows or perform pagination;
- buffer an unbounded dataset;
- claim Gate D export completion or product parity.

A later export orchestration tranche must provide only already-authorized scalar values, preserve Query/source-owner/Policy ownership, define redaction/visibility semantics explicitly, and stream/page within a bounded envelope.

## Focused evidence

`tests/Unit/Modules/AdminColumns/AdminColumnsCsvExportEncoderTest.php` covers deterministic ordering, quote/newline behavior, spreadsheet formula vectors, scalar/null handling, exact row-key matching, malformed/non-scalar rejection, finite-float validation, and budget failures.

Gate D remains **ACTIVE / NOT PASS** after this foundation. Export transport/orchestration remains separately gated.
