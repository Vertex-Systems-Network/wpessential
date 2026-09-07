# Admin Columns Portable View Import Commit V1

Status: **IMPLEMENTED FOUNDATION / GATE D STILL ACTIVE**  
Issue: #259  
Parent: #66

## Scope

This tranche connects the certified View portability/remap codec to the authoritative revisioned Admin Columns View definition service through a bounded create-only persistence workflow.

The import path is intentionally internal. It adds no browser, AJAX, REST, CLI, download/upload or automatic discovery surface.

## Commit flow

`AdminColumnsViewImportService::commitCreate()`:

1. decodes the portable document only through `AdminColumnsViewPortabilityCodec`;
2. requires the codec's complete explicit environment remaps;
3. passes the normalized/remapped payload and portable View UUID to `AdminColumnsViewDefinitionService::importCreate()`;
4. returns source revision provenance together with the newly persisted destination Definition.

## Stable identity and revision semantics

The portable View UUID is preserved as the destination Definition id. No replacement View or Column identity is generated during import.

The portable `source_revision` is provenance evidence only. A newly created destination Definition starts at local revision **1**, because destination optimistic locking must describe destination persistence history rather than a foreign environment's revision counter.

## Collision policy

V1 is create-only:

- any existing destination Definition with the portable UUID blocks import;
- any already-owned Admin Columns `view_key` blocks import even under a different UUID;
- no existing View is updated, overwritten or merged;
- no implicit "same environment" behavior is inferred.

Future update/replace import semantics require their own explicit optimistic-lock and authorization contract.

## Ownership boundary

`AdminColumnsViewDefinitionService` remains the owner of canonical normalization, key uniqueness, Definition shape, checksum creation and repository persistence. The import service does not write the repository directly.

The portability codec remains the owner of portable document identity, budgets and remap validation. The import service does not duplicate or weaken those checks.

## Non-scope

- import/update or replace-existing mode;
- import transport or file upload UI;
- Ability/AJAX/REST/CLI wiring;
- provider/remap discovery;
- automatic publishing;
- source-owner mutation;
- row-data CSV export;
- Policy authorization grants;
- Gate D PASS, Gate E start, Status runtime, product parity or deployment.
