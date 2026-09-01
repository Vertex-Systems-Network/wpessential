# Fields Stable Identity V1

Status: **bounded implementation foundation**  
Surface: **3 — Custom Fields**

## Purpose

Every persisted Field and nested subfield needs a stable UUID that is independent from its editable machine key, label, editor preset and eventual storage key. This is required before durable value storage, migration, import/export and dependency references can safely bind to a Field definition.

## Contract

- `uuid` is a lowercase RFC 4122 UUID.
- Pure `FieldDefinitionNormalizer` may accept a missing UUID for pre-save previews/drafts, but never generates identity itself.
- UUID generation occurs only at the canonical Field Group save/status mutation boundary.
- Existing UUID is preserved when a client omits it and the same field machine key still exists.
- A submitted existing UUID can identify the same field even when other presentation data changes.
- The same existing machine key cannot silently replace its UUID in-place.
- UUIDs must be unique across the complete nested Field Group tree.
- Save-time repository checks reject UUIDs already owned by another canonical Surface 3 Field Group.
- Nested Group/Repeater subfields receive the same stable identity guarantees.
- Legacy Field Group payloads without per-field UUIDs are backfilled on their next canonical save/status mutation rather than receiving random IDs during read/validation.

## Canonical round-trip requirement

Persisted normalized fields are valid input to `FieldDefinitionNormalizer` again without semantic loss. In particular:

- preset identity such as `multi_select` remains attached to its canonical `select` type;
- clone/repeatability enabled state is preserved;
- sortable clone state is preserved;
- clone storage mode/min/max/empty-start/add-label settings are preserved;
- field UUID is preserved.

This prevents a status transition or revision-safe re-save from silently resetting clone/sort behavior.

## Storage dependency

The next WordPress registered-meta storage adapter may rely on UUID as canonical field identity, but must still define storage keys and migrations explicitly. UUID does not authorize using arbitrary metadata keys or bypassing storage ownership.

## Non-goals

- no value storage is added here;
- no UUID is exposed as an editable product slug;
- no field-key migration workflow is claimed complete;
- no import conflict resolver is claimed complete;
- no Pro activation or entitlement bypass is introduced.
