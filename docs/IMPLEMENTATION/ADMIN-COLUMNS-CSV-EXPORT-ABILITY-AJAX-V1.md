# Admin Columns CSV Export Ability/AJAX V1

Status: implementation slice for Admin Columns Gate D. This document does not claim Gate D PASS, product parity, production deployment, browser download UI, or public REST export.

## Purpose

Expose the already-promoted bounded CSV export engine through the canonical WPEssential Ability/AJAX authorization path without creating another export/query engine or emitting HTTP download behavior.

## Canonical path

`admin-columns.export.csv` AJAX route → shared `AbilityAjaxHandler` → `wpessential/admin-columns/export-csv` Ability → `AdminColumnsCsvExportAbilityHandler` → `AdminColumnsCsvExportService::exportScoped()` → `AdminColumnsReadAdapter` → Query/Policy-owned execution → `AdminColumnsCsvExportEncoder`.

The handler does not execute providers directly and does not read Fields/Relations/private owner storage.

## Registration

`AdminColumnsModule` registers `module.admin-columns.csv-export` from the existing View service and read adapter plus the promoted CSV encoder and scope policy. The service is then injected into one read-only Ability handler.

The Ability:

- belongs to Admin Columns owner surface 8;
- requires `manage_options` through the existing shared Policy path;
- is exposed only on Internal and UI execution channels;
- is non-mutating;
- uses the shared AJAX route registry and `NonceOperation::Apply`;
- requires nonce protection and does not allow guests through the canonical route defaults.

## Input envelope

Required top-level fields are exactly:

- `view_id` — lowercase RFC 4122 UUID;
- `expected_view_revision` — positive integer;
- `scope_request` — explicit promoted CSV scope contract;
- `controls` — bounded filter/search/order controls;
- `runtime` — bounded current-page coordinates when applicable.

Unknown or missing top-level fields fail closed in the handler itself in addition to descriptor schema metadata.

The descriptor bounds:

- scopes to `current_page`, `selected_rows`, `all_matching`;
- selected row IDs to at most 100 positive integers;
- selected columns to 1..100 machine keys;
- filters to at most 16;
- search to 200 characters;
- order clauses to at most 4;
- page size to 1..100;
- offset to 0..10000.

Scope-specific semantic validation remains owned by `AdminColumnsCsvExportScopePolicy` and execution validation remains owned by `AdminColumnsCsvExportService` / `AdminColumnsReadAdapter`.

## Output envelope

The handler returns an object only; it does not write headers or files:

- `contract_version: 1`;
- `content_type: text/csv; charset=UTF-8`;
- `filename: admin-columns-export.csv`;
- `bytes` — exact CSV byte length, bounded to 8 MiB;
- `csv` — the encoded payload.

The later browser/download adapter may interpret this metadata, but that adapter is outside this slice.

## Explicit non-goals

This V1 does not add:

- `header()`, `wp_die()`, filesystem writes, temp files, streaming or direct response termination;
- admin browser controls or TypeScript download construction;
- AdminColumnsAdminController projection;
- REST export;
- new authorization/capability semantics;
- direct Query provider execution;
- Query, Fields or Relations production changes;
- bulk mutation;
- Gate E Dynamic Listings or Status runtime work.

## Evidence

Focused tests assert:

- canonical service registration;
- read-only Ability identity, owner, capability and bounded schemas;
- nonce-protected guest-disabled Apply route;
- handler source delegates only to `exportScoped()` and contains no direct HTTP/filesystem transport;
- unknown/missing top-level input and invalid View revision fail closed before export execution.

Promotion requires all applicable exact-head CI to be green and review threads clean.