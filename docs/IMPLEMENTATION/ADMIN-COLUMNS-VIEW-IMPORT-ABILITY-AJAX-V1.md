# Admin Columns View Import Ability/AJAX V1

Status: **BOUNDED IMPLEMENTATION — Gate D remains ACTIVE / NOT PASS**

Issue: #274  
Parent: #66

## Purpose

Expose the already-promoted create-only portable Admin Columns View import service through the canonical Ability/AJAX platform without adding browser upload UI, overwrite semantics, automatic environment discovery, or activation side effects.

## Canonical transport

- Ability: `wpessential/admin-columns/import-view`
- AJAX type: `admin-columns.import.view`
- Capability: `manage_options`
- Channels: internal + UI only
- Mutation classification: mutating
- Nonce operation: `NonceOperation::Update`
- Guests: forbidden by the shared AJAX route defaults

## Input boundary

V1 accepts exactly:

- `document`: portable View JSON string, maximum 262144 bytes at the Ability schema boundary and revalidated by `AdminColumnsViewPortabilityCodec`;
- `remaps`: object/map consumed and bounded by the promoted portability codec.

Unknown top-level fields fail closed. No caller-provided destination id, revision, overwrite flag, activation flag, status, user identity, provider-discovery instruction, or storage selector is accepted.

## Create-only and draft-only invariant

`AdminColumnsViewImportAbilityHandler` delegates only to `AdminColumnsViewImportService::commitCreate()` and always supplies `DefinitionStatus::Draft`.

Therefore V1 transport:

- preserves the portable stable View UUID;
- creates destination revision 1;
- preserves source revision as provenance in the response;
- rejects existing destination IDs and owned `view_key` collisions through the authoritative View service;
- cannot overwrite or update an existing View;
- cannot implicitly publish, enable, disable, archive, or otherwise activate the imported View.

A later explicit View lifecycle action is required for activation.

## Ownership preservation

- Portability parsing, byte budgets and explicit remap semantics remain owned by `AdminColumnsViewPortabilityCodec`.
- Create-only persistence and provenance remain owned by `AdminColumnsViewImportService`.
- Revisioned View definition persistence remains owned by `AdminColumnsViewDefinitionService`.
- Policy/capability execution remains owned by the shared Ability platform.
- No Query, Fields, Relations or provider private state is read or mutated by this transport.

## Response boundary

The handler returns only bounded scalar/import metadata plus the serialized destination Definition contract:

- `contract_version`
- `source_view_id`
- `source_revision`
- `destination` with id, slug, type, schema version, owner surface, status, normalized payload, revision, dependencies and checksum.

No repository object, filesystem path, HTTP header, download response, provider secret or diagnostics payload is exposed.

## Explicit non-scope

- browser file picker/upload UI;
- Admin Columns controller or TypeScript changes;
- overwrite/update/upsert import;
- automatic target/source/user/role/capability remapping;
- environment discovery;
- row-data CSV import/export;
- Query/Fields/Relations production changes;
- deployment/release;
- Gate D PASS, Gate E start or Status runtime.

## Acceptance evidence

Focused tests cover:

1. canonical Ability/service/AJAX registration with Update nonce and no guests;
2. strict document/remaps-only schema and 256 KiB document ceiling;
3. handler rejection of unknown/missing input;
4. draft-only delegation to the promoted import service;
5. stable UUID, destination revision 1 and source-revision provenance through a real create-only import path;
6. absence of browser/upload/overwrite transport behavior.

Merge requires all applicable exact-head CI green and clean review threads. Gate D remains ACTIVE / NOT PASS after promotion.
