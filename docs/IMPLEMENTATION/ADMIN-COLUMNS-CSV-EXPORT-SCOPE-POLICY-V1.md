# Admin Columns CSV Export Scope Policy V1

Status: **IMPLEMENTED FOUNDATION / GATE D STILL ACTIVE**  
Issue: #253  
Parent: #66

## Scope

This tranche adds a pure, side-effect-free policy for normalizing explicit Admin Columns CSV export scope intent before any row read, authorization or download transport is attempted.

The policy is separate from the existing certified CSV encoder and bounded internal export orchestration service.

## Supported V1 scopes

- `current_page`
- `selected_rows`
- `all_matching`

A scope is usable only when the target integration explicitly advertises it. The policy does not infer support from UI visibility or from a successful read.

## Explicit request contract

Every request must explicitly declare:

- `scope`;
- `selected_row_ids`;
- `selected_columns`;
- `respect_filters`;
- `respect_sort`.

No hidden defaults are invented by the policy.

### Selected rows

`selected_rows` requires at least one unique positive row ID and is bounded to at most 100 IDs in V1.

`current_page` and `all_matching` must not carry row IDs. This prevents a caller from smuggling selection semantics into another scope.

### Selected Columns

The caller supplies the ordered enabled canonical Column-key set separately from the request. Requested Columns must be a non-empty unique subset of that canonical set and caller order is preserved.

This is a presentation/export-selection constraint only. Column visibility or selection never grants authorization to the underlying data.

### Filters and sort

`respect_filters` and `respect_sort` are explicit booleans. The policy does not compile or execute filter/sort semantics; Query remains authoritative for backend execution.

## Ownership boundary

This policy performs no:

- Policy/capability authorization;
- Query execution or row selection;
- source-owner read/mutation;
- CSV serialization/formula handling;
- controller/AJAX/REST/download transport;
- background job or persistence work.

A later integration must combine this normalized intent with canonical Policy, Query/read-owner and export orchestration boundaries.

## V1 bounds

- selected row IDs: <= 100;
- enabled/selected Columns: <= 100;
- supported scopes: <= 3;
- machine keys use the canonical bounded Admin Columns key format.

## Non-scope

- HTTP/download response;
- export progress/background jobs;
- arbitrary/raw hidden fields;
- authorization/redaction engine;
- Query/filter/sort compilation;
- Gate D PASS, Gate E start, Status runtime, product parity or deployment.
