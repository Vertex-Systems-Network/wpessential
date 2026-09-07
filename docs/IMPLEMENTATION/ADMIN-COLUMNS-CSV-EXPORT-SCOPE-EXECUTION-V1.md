# Admin Columns CSV Export Scope Execution V1

Status: **IMPLEMENTED INTERNAL BOUNDARY / GATE D STILL ACTIVE**  
Issue: #263  
Parent: #66

## Scope

This tranche connects the promoted CSV export scope policy to the existing canonical bounded export service. It does not expose a browser, AJAX, REST, HTTP or download endpoint.

`AdminColumnsCsvExportService::exportScoped()` accepts an exact View revision, explicit scope request, existing Admin Columns read controls and scope-specific runtime coordinates. Scope intent is normalized by `AdminColumnsCsvExportScopePolicy`; all row execution still runs through `AdminColumnsReadAdapter`, which preserves Query and Policy ownership.

## Supported scopes

### `current_page`

- requires explicit `page_size` and `offset`;
- `page_size` is bounded to 1–100;
- `offset` is bounded to 0–10000;
- performs exactly one authoritative read;
- does not infer browser pagination state.

### `selected_rows`

- requires the promoted bounded positive selected-row ID list;
- requires exactly one enabled authored native/query Column whose source reference is `post.id`;
- appends an `in` filter through that authored Column so Query remains the row-selection owner;
- rejects missing, duplicate or out-of-request authoritative row IDs;
- preserves Query order when `respect_sort=true`;
- restores caller selected-ID order when `respect_sort=false`.

No hidden/private row-ID projection is introduced.

### `all_matching`

- preserves the existing bounded page loop;
- keeps the finite maximum-row ceiling and one-row overflow probe;
- continues to fail closed rather than emit a partial CSV when the row budget is exceeded.

## Filter and sort behavior

- `respect_filters=true` preserves caller filters and search;
- `respect_filters=false` removes caller filters and search only;
- the canonical View target constraint and selected-row membership predicate are never removed;
- `respect_sort=true` preserves caller ordering;
- `respect_sort=false` removes caller ordering before the authoritative read.

## Column projection

The scope policy requires an explicit non-empty selected-column subset of enabled canonical View Columns. The export service serializes only that subset and preserves the caller-selected Column order. Unselected Column values may still be present in the already-authorized internal View read, but they are never written to the resulting CSV.

CSV formula neutralization, quoting, UTF-8 validation and output budgets remain owned by `AdminColumnsCsvExportEncoder`.

## Revision and integrity behavior

Scoped export requires a positive `expected_view_revision`. The service verifies the authoritative View before execution and verifies read revision evidence during execution. Revision drift fails closed before a CSV is returned.

Selected-row execution additionally proves exact authoritative membership against the caller's normalized requested ID set.

## Ownership boundary

This tranche does not:

- grant authorization;
- bypass Query or Policy;
- read source-owner private storage;
- change Query, Fields or Relations production code;
- add hidden row identities;
- register module services, Abilities or AJAX routes;
- expose HTTP/download/browser transport;
- start bulk mutation;
- authorize Gate E, Status runtime, product parity or deployment.

Gate D remains **ACTIVE / NOT PASS** pending the remaining accepted closure work, including the separately owner-claimed single-row Fields edit UI path.
