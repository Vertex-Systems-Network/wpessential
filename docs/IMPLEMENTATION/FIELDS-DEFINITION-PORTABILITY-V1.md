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

V1 refuses to export Field Groups with cross-definition dependencies because no dependency remapping contract exists yet. A stored checksum that disagrees with the canonical normalized payload also fails closed.

Every portable Field must already have a stable UUID. Export does not synthesize missing identities.

## Import contract

Import is **create-only**.

- absent Definition UUID: the canonical definition may be created after complete preflight;
- existing identical Definition UUID: idempotent no-op;
- existing non-identical Definition UUID: reject; never overwrite;
- imported local revision starts at `1`; `source_revision` is provenance and never overwrites local optimistic-concurrency state.

Before the first repository mutation the importer verifies:

1. exact envelope format/version;
2. canonical Surface 3 type/owner/schema version;
3. valid Definition status and payload shape;
4. no unresolved dependencies;
5. stable Field UUIDs are present;
6. no duplicate Definition UUID, group key, or Field UUID inside the bundle;
7. canonical normalization succeeds;
8. the portable checksum matches the canonical normalized payload;
9. existing repository Field UUID ownership is not violated;
10. canonical Field Group validation passes, including publish-time validation.

The importer does not rename storage keys. A stable Field UUID already owned by another canonical Field Group fails through the existing ownership guard instead of being remapped silently.

## Compatibility policy

V1 supports only portability format `1` and Field Group definition schema `1`.

A future format/schema version requires an explicit compatibility upgrader or adapter. The importer must not silently coerce unknown historical/future shapes through the current normalizer and then pretend they were compatible.

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
- arbitrary executable PHP/JavaScript configuration;
- production deployment/release.

## Evidence

Primary unit evidence:

- `tests/Unit/Modules/Fields/FieldGroupPortabilityServiceTest.php`

The suite covers deterministic export ordering, published round-trip, local revision reset, idempotent re-import, checksum tamper rejection, create-only collision behavior, format/schema/owner/type/dependency fail-closed handling, cross-repository Field UUID ownership and bundle-level identity collision rejection.

Exact-head CI is required before this V1 slice can be promoted or counted as closing the #66 import/export blocker.
