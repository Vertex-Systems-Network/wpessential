# Fields Definition Portability V1

## Scope

This slice adds bounded portability for canonical Surface 3 Field Group **definitions**. It does not export or import post-meta values, relation data, provider-owned storage, credentials, arbitrary executable configuration, or production data.

Canonical envelope:

- format: `wpessential.fields.field-groups`
- format version: `1`
- definition type: canonical Surface 3 `field_group`
- definition schema version: `1`
- owner: Surface 3

## Export contract

Export reads only canonical Surface 3 Field Group definitions. Output is deterministically ordered by definition slug and UUID and includes:

- Definition UUID and slug;
- type / owner / schema version;
- status;
- canonical normalized payload;
- source revision as provenance only;
- dependencies;
- canonical payload checksum.

V1 refuses to export Field Groups whose definition schema is not exactly version `1` or whose cross-definition dependencies are non-empty. Either case requires an explicit future compatibility/dependency adapter rather than silent coercion. A stored checksum that disagrees with the canonical normalized payload also fails closed.

Every portable Field must already have a stable UUID. Export does not synthesize missing identities.

## Import contract

Import is **create-only**.

- absent Definition UUID: the canonical definition may be created after complete preflight;
- existing identical Definition UUID: idempotent no-op;
- existing non-identical Definition UUID: reject; never overwrite;
- imported local revision starts at `1`; `source_revision` is provenance and never overwrites local optimistic-concurrency state.

Before the first repository mutation the importer verifies:

1. exact envelope format/version and exact known envelope keys;
2. exact known portable-definition keys;
3. canonical Surface 3 type/owner/schema version;
4. valid Definition status and payload shape;
5. no unresolved dependencies;
6. stable Field UUIDs are present;
7. no duplicate Definition UUID, Definition slug, group key, or Field UUID inside the bundle;
8. canonical normalization succeeds;
9. the portable checksum matches the canonical normalized payload;
10. destination Definition slug ownership does not collide with another persisted Definition;
11. existing repository Field UUID ownership is not violated;
12. canonical Field Group validation passes, including publish-time validation.

Unknown envelope or portable-definition options are rejected. This prevents a future schema generation from introducing semantics that V1 would otherwise ignore while appearing to import successfully.

The importer does not rename Definition slugs, Field Group keys, Field UUIDs, or storage keys to resolve conflicts. A stable Field UUID already owned by another canonical Field Group fails through the existing ownership guard instead of being remapped silently.

Immediately before each create, the importer re-reads the Definition UUID. If an identical definition appeared concurrently it is treated as an idempotent observation; a non-identical definition fails closed. Runtime persistence also has scoped primary-key and type/slug uniqueness, so a later insert race can fail but cannot become an overwrite through this workflow.

## Transaction boundary

The shared `DefinitionRepositoryInterface` does not expose a bundle-wide transaction. V1 therefore performs all deterministic compatibility, checksum, identity, ownership and canonical-validation checks for the complete bundle before the first save.

This guarantees that those preflight failures do not partially import a bundle. It does **not** claim bundle-wide rollback for an unexpected database/backend failure or a race that occurs during sequential persistence after preflight. Such failures are surfaced instead of being converted into update/merge behavior.

## Compatibility policy

V1 supports only portability format `1` and Field Group definition schema `1`.

A future format/schema version requires an explicit compatibility upgrader or adapter. The importer must not silently coerce unknown historical/future shapes through the current normalizer and then pretend they were compatible.

The portable `checksum` is the canonical Field Group **payload** checksum, matching the repository Definition checksum contract. Identity metadata remains subject to strict format, ownership, collision and schema validation rather than being presented as a signed provenance mechanism.

## Runtime exposure

The module registers:

- `wpessential/fields/export-groups`
- `wpessential/fields/import-groups`

Both use the shared Ability/WordPress exposure/AJAX infrastructure. Import is marked mutating and uses the shared update nonce operation. Export is read-only. Both retain the existing module capability boundary.

## Non-goals

- Field value/data export or import;
- storage-key migration (already owned by the explicit migration workflow);
- provider-specific data migration;
- Relations data or cardinality semantics;
- custom-table storage portability;
- dependency remapping;
- destructive merge/overwrite mode;
- bundle-wide database transaction semantics not exposed by the shared repository contract;
- cryptographic signing/authenticity of portable files;
- arbitrary executable PHP/JavaScript configuration;
- production deployment/release.

## Evidence

Primary unit evidence:

- `tests/Unit/Modules/Fields/FieldGroupPortabilityServiceTest.php`
- `tests/Unit/Modules/Fields/FieldGroupPortabilityCollisionTest.php`
- `tests/Unit/Modules/Fields/FieldGroupPortabilityCompatibilityTest.php`

The suites cover deterministic export ordering, published round-trip, local revision reset, idempotent re-import, checksum tamper rejection, create-only same-ID collision behavior, bundle/destination slug collision rejection, format/schema/owner/type/dependency fail-closed handling, strict unknown-key compatibility rejection, unsupported exporter schema rejection, cross-repository Field UUID ownership and bundle-level identity collision rejection.

Exact-head CI is required before this V1 slice can be promoted or counted as closing the #66 import/export blocker.
