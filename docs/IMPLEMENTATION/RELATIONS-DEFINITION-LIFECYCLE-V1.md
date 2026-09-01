# Relations Definition Lifecycle V1

Status: implementation candidate for Surface 4 Gate B
Tracker: #101
Parent: #66
Base: `main @ eb0719ea37dc669261bd380233e9d5f5c8b174dc`

## Purpose

This tranche establishes the first Relations runtime slice after the certified Surface 4 Atomic Option Contract. It deliberately stops before edge persistence. The implementation reuses WPEssential's shared Definition Repository, Definition status/checksum model, Ability Registry, WordPress Ability bridge and AJAX route boundary instead of introducing a parallel Relations persistence or authorization engine.

## Canonical Definition

Surface 4 owns Definition type `relation` with `owner_surface_id = 4`.

The V1 payload contains:

- immutable `relation_key`;
- title and description;
- cardinality: `one_to_one`, `one_to_many`, `many_to_one`, `many_to_many`;
- direction policy: reciprocal and bidirectional traversal flags;
- typed `from` and `to` endpoints;
- directional minimum/maximum connection bounds;
- unique-edge policy.

Definition status remains the canonical lifecycle state (`draft`, `published`, `disabled`, `archived`). V1 does not add a second payload-level enabled flag.

## Cardinality and bounds

Directional maximums are enforced server-side:

- one-to-one: `from_max = 1`, `to_max = 1`;
- one-to-many: `from_max = null`, `to_max = 1`;
- many-to-one: `from_max = 1`, `to_max = null`;
- many-to-many: both maximums may be unbounded.

Minimums must be non-negative and cannot exceed a finite maximum. Explicit payload bounds cannot relax a cardinality-imposed maximum.

## Endpoint boundary

The normalizer recognizes the reviewed endpoint vocabulary:

- `post` with subtype;
- `term` with taxonomy subtype;
- `user`;
- `comment`;
- `media`;
- `custom_table` with subtype;
- `registered_entity` with subtype.

Publish validation is intentionally narrower. V1 publishes only endpoints whose runtime ownership is already clear:

- registered WordPress post types;
- registered WordPress taxonomies;
- users;
- comments;
- media/attachments.

`custom_table` and `registered_entity` definitions may be drafted so product configuration is not lost, but publishing fails closed until their adapter registries are implemented and independently certified.

## Lifecycle abilities

The Pro `RelationsModule` registers:

- `wpessential/relations/list-definitions`;
- `wpessential/relations/get-definition`;
- `wpessential/relations/validate-definition`;
- `wpessential/relations/save-definition`;
- `wpessential/relations/status-definition`.

Save and status operations are mutating and use the shared Update nonce boundary. Reads/validation use the Apply boundary. All abilities remain Surface 4-owned and use the shared WordPress Ability/AJAX infrastructure.

The module is not added to the default Free bootstrap. It remains a Pro module that must be contributed/admitted through the existing external module seam.

## Mutation safety

Updates require positive `expected_revision` matching the persisted revision. Stale writes fail closed.

`relation_key` is immutable after creation. New definitions also preflight existing Surface 4 relation definitions and reject duplicate keys before save.

Each persisted candidate uses the shared `Definition` checksum over canonical payload and increments revision monotonically.

Definition reads/listing enforce both Definition type and Surface 4 ownership, preventing a matching type owned by another surface from being treated as a Relations definition.

## Security and no-bypass boundary

V1 uses the existing shared `manage_options` authorization baseline used by current builder modules. This tranche does not create a duplicate role/policy engine or privately mutate role capabilities; dedicated Relations capability policy remains a later shared Policy/Roles integration concern.

The payload normalizer rejects unknown keys, so arbitrary executable PHP/JavaScript/raw SQL configuration is not accepted. There is no direct core-table write path.

## Explicit non-goals

Not implemented in this tranche:

- relation edge tables or edge rows;
- connect/disconnect/bulk edge mutation;
- pivot-value persistence;
- ordering persistence;
- delete cascade execution;
- Query integration;
- editor/admin UI;
- Relations import/export runtime;
- provider/custom-table adapters;
- performance caching/indexing layer;
- Columns/Listings/Status work;
- release or deployment.

## Verification contract

Focused tests cover:

- cardinality defaults and non-relaxation;
- invalid bounds and endpoint subtype shape;
- unknown/executable payload rejection;
- registered native endpoint publish acceptance;
- unknown post/taxonomy and uncertified adapter publish rejection;
- create/update checksum and revision behavior;
- immutable key and duplicate-key protection;
- stale revision conflicts;
- publish status revalidation;
- owner filtering/isolation;
- Pro manifest and shared Ability/AJAX registration.

Exact-head CI is authoritative. This document does not claim certification until every applicable workflow is green on one frozen source SHA.

## Gate state

Merging this slice does not close Relations Gate B. It only establishes the canonical definition/cardinality/endpoint lifecycle needed before a later, separately reviewed edge-persistence tranche.
