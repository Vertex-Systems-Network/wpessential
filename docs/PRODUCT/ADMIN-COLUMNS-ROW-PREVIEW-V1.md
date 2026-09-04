# Admin Columns — Bounded Row Preview UI V1

## Scope

Surface 8 can now explicitly preview bounded rows for the currently saved canonical Admin Columns View through the accepted Query-owned read path.

The preview is read-only. It does not create inline editing, bulk editing, row/source-data mutation, export/import, REST exposure, raw SQL, direct `WP_Query`, or a second Query execution boundary.

## Route and execution boundary

The existing shared AJAX route is projected into the Admin Columns bootstrap only when the shared dispatcher/gateway is available:

- `admin-columns.read.rows`

The client uses the route only after an administrator presses **Preview rows**. Opening a View or loading the page never starts Query execution automatically.

The server execution path remains:

`shared AJAX -> Ability/policy -> AdminColumnsReadAbilityHandler -> AdminColumnsReadAdapter -> QueryReadConsumerInterface`

The client never supplies a Query AST, raw field reference, provider callback, SQL fragment, `WP_Query` arguments, mutation instruction, or export request.

## Saved View requirement

A preview request is sent only when the current editor session has:

- a canonical saved definition UUID;
- a positive saved revision;
- `published` lifecycle state;
- View `enabled=true`.

This client check is UX only. The authoritative published/enabled enforcement remains the server-side Admin Columns read adapter.

If the active saved View changes through reopen or a successful save, any previously rendered preview is cleared. A new explicit Preview action is required, preventing rows from an older definition/revision from remaining presented as the active preview.

## Bounded request

V1 sends only:

- `view_id`;
- optional search text, trimmed and limited by the 200-character control/schema boundary;
- page size from the fixed set `10`, `20`, `50`, or `100`;
- offset bounded to `0..10000`.

Previous/Next navigation never crosses the accepted offset ceiling. A full page may enable Next; a short page disables it without inventing a total-count contract.

## Response validation

No response replaces the preview until the complete returned object validates.

Required invariants include:

- Query read contract version `1`;
- `ok=true` and `error=null`;
- exact current View UUID;
- exact current View revision;
- 1–100 unique configured columns;
- bounded known column metadata only;
- source owner limited to accepted `native`/`query` read owners;
- at most the requested number of rows and never more than 100;
- returned count exactly equals the validated row count;
- every row contains only the configured column keys and contains every configured key;
- cell values are scalar/null only;
- string cells are limited to 500 characters;
- numeric cells must be finite.

Malformed, stale, policy-denied, Query-failed, or unsupported responses fail closed with bounded generic UI text.

## Rendering and accessibility

Preview output is built with DOM elements only. Column labels and cell values are assigned with `textContent`; server-returned HTML is never injected.

The preview includes:

- a labelled search control;
- a labelled page-size selector;
- explicit Preview, Previous, and Next buttons;
- an `aria-live` status region;
- a semantic table with column headers;
- revision-specific status text explaining that row data was not mutated.

## Non-goals

This tranche does not add:

- inline or bulk editing;
- row/source-data writes;
- selection/actions on previewed rows;
- export/import;
- REST exposure;
- filters or sorting authoring UI;
- total-count semantics;
- Query/Fields/Relations owner-internal changes;
- persistence or migrations;
- Gate D completion or product-parity claims.
