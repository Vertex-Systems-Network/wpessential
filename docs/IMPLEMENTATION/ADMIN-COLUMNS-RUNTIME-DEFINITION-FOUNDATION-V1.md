# Admin Columns Runtime Definition Foundation V1

Status: **IMPLEMENTATION CANDIDATE / GATE D NOT PASS**  
Parent: #66  
Issue: #191

## Purpose

Establish the first Surface 8-owned runtime boundary after Query Gate C passed and the Admin Columns product contract reached `UX_CONTRACT_COMPLETE`.

This tranche implements only the revisioned **authored shared View/Column definition** foundation. It intentionally does not mix Personal preferences, effective runtime capability state or diagnostics into the same persistence model.

## Canonical ownership

- Surface 8 owns View/Column identity, presentation ordering/layout, typed source references and presentation visibility state.
- Query continues to own backend sort/filter/search semantics and execution.
- Fields, Relations, Taxonomy, Status, Media and provider adapters continue to own source truth and mutations.
- Policy continues to own authorization. View assignment and visibility are presentation data only.
- Content Order owns persistent content ordering; Column reorder is presentation-only.
- No arbitrary PHP/JavaScript, raw SQL, callback or private owner storage is accepted.

## Definition contract

`AdminColumnsViewDefinitionNormalizer` owns Definition type `admin_columns_view` and Surface ID 8.

The bounded V1 authored payload contains:

- stable `view_key`, name and enabled state;
- typed target `{type,key}`;
- optional presentation assignment references;
- optional shared layout defaults;
- one to 100 ordered Column definitions;
- optional presentation visibility state.

Each Column requires:

- stable RFC 4122 UUID and machine key;
- label and enabled state;
- typed `{owner,reference}` source reference;
- allowlisted display format;
- at most one primary Column per View;
- optional bounded presentation layout.

Duplicate UUIDs/keys, malformed references, unsupported owner/source modes, arbitrary unknown options and multiple primary Columns fail closed.

Personal preferences, effective runtime state and diagnostics are not recognized top-level options, so they cannot silently enter the shared revisioned definition.

## Revisioned service

`AdminColumnsViewDefinitionService` consumes only the shared `DefinitionRepositoryInterface` and provides internal foundation operations:

- list/get Surface 8-owned View Definitions;
- create with generated stable UUID;
- optimistic revision update;
- immutable `view_key` after creation;
- cross-definition key collision rejection;
- explicit Definition status change;
- canonical payload checksum on every persisted revision.

No REST/AJAX/admin route or execution endpoint is exposed in this tranche.

## Module boundary

`AdminColumnsModule` registers only:

- `module.admin-columns.view-normalizer`;
- `module.admin-columns.views`.

It requires the canonical shared Definition repository and refuses service-ID collisions. `boot()` intentionally installs no admin route, Query adapter or mutation surface.

## Explicit non-goals

- Query read/sort/filter/search adapter;
- inline/bulk/quick-edit mutation;
- export;
- Personal preference storage;
- effective capability or diagnostic persistence/exposure;
- target/list-table hooks;
- canonical admin page/build/enqueue;
- Renderer/provider execution;
- runtime/product parity lifecycle promotion;
- Gate D PASS, Gate E start or Status runtime.

Those concerns must use later dependency-safe tranches rather than widening this foundation.
