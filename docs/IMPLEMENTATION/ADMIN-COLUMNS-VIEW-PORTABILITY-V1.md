# Admin Columns View Portability / Remap Foundation V1

Status: **IMPLEMENTED FOUNDATION / GATE D STILL ACTIVE**  
Issue: #244  
Parent: #66

## Scope

This tranche introduces a pure versioned codec for Admin Columns shared View definitions. It is distinct from row-data CSV export.

The codec serializes an already-normalizable View payload together with stable View identity and source revision. Import requires explicit caller-supplied remap tables for environment-sensitive references before the payload is accepted.

## Stable authored identity

The codec preserves:

- stable View UUID;
- source revision metadata;
- ordered Column list;
- Column UUIDs and machine keys;
- normalized authored View state.

No new View/Column IDs are generated during decode.

## Explicit remap boundaries

V1 always requires an explicit target-key remap. Source references owned by Fields, Taxonomy, Relations, Media, Status, Query, provider or renderer owners also require explicit remaps. Native source references remain unchanged.

When assignment state is present, role, user and capability references require explicit complete remap sets.

Unused/foreign mappings are rejected. Multiple source references may not collapse onto one destination inside the same remap set.

## Trusted scope prohibition

Portable documents contain only the versioned definition envelope. Site/network/user execution scope is not portable authored data and cannot be injected into the document. Unknown envelope keys fail closed.

## Safety boundaries

The codec performs no persistence write, environment discovery, Query execution, source-owner mutation, user assignment authorization, row-data export, UI/controller work or transport/download behavior.

All decoded payloads pass the canonical `AdminColumnsViewDefinitionNormalizer` before and after remapping.

## V1 budgets

- portable document: <= 256 KiB;
- each remap set: <= 200 mappings;
- JSON depth: <= 64.

## Non-scope

- automatic same-environment detection;
- persistence/import commit workflow;
- dependency graph migration;
- provider installation/discovery;
- row-data CSV export;
- Gate D PASS, Gate E start, Status runtime, product parity or deployment.
