# Admin Columns — Query-backed Row Read Ability V1

## Scope

Surface 8 now exposes the already-certified `AdminColumnsReadAdapter` through the shared Ability and AJAX platform. This tranche adds a server-side bridge only; it does not add an admin row-preview UI, inline/bulk editing, export, REST exposure, or source-owner mutation.

The public Ability is:

- `wpessential/admin-columns/read-rows`

The shared AJAX route is:

- `admin-columns.read.rows`

Both remain owned by Surface 8 and require the existing `manage_options` capability boundary. The Ability is read-only (`mutates=false`) and is exposed only to Internal/UI execution channels.

## Input boundary

The Ability requires a canonical lowercase RFC 4122 `view_id` and accepts only the bounded public read controls already understood by `AdminColumnsReadAdapter`:

- `filters` — at most 16 entries;
- `search` — at most 200 characters;
- `order_by` — at most 4 entries;
- `page_size` — 1 through 100;
- `offset` — 0 through 10,000.

Unknown top-level properties are rejected by the Ability schema. The handler independently validates `view_id` before delegation, removes it from the adapter input, and reuses the authoritative `ExecutionContext` without inventing another principal/site/policy model.

Nested filter/order semantics, column-key mapping, source ownership, published/enabled View enforcement, Query source selection, projection bounds, normalized Query failures, and returned row mapping remain owned by the already-certified `AdminColumnsReadAdapter` and Query read consumer.

## Execution path

The path is deliberately singular:

`shared AJAX route -> AbilityAjaxHandler -> AbilityRegistry/policy -> AdminColumnsReadAbilityHandler -> AdminColumnsReadAdapter -> QueryReadConsumerInterface`

There is no private AJAX fallback, direct `WP_Query`, raw SQL, Query AST input, REST route, provider callback, or source-owner storage access in this tranche.

## Output

The handler returns the existing adapter result unchanged, including:

- contract version;
- `ok`;
- View id/revision;
- configured columns;
- bounded rows;
- returned count;
- normalized Query error when present.

A malformed View id is rejected before Query execution. Draft, disabled, unsupported, or otherwise unreadable Views continue to fail closed at the existing adapter boundary.

## Non-goals

This tranche does not add:

- row-preview UI;
- inline editing;
- bulk editing;
- row/source-data mutations;
- import/export;
- REST exposure;
- new persistence or migrations;
- Query/Fields/Relations owner-internal changes;
- Gate D completion or product-parity claims.
