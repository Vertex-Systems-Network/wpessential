# Fields Semantic Canonicalization V1

Snapshot: 2026-09-01

Surface: 3 — Fields / Field Groups / Control Registry

Status: semantic consolidation gate **CLOSED** for the six blocker families identified by Formal Bank Review V1.

Lifecycle status remains `BANK_SURFACE_SEEDED`. This pass closes one review blocker only; it does not certify the native or market inventories.

## Boundary decision

Discovery records remain immutable evidence. Canonicalization does not delete a record merely because later review proves that it is an alias or an effective/resolved form of another record.

Cross-record meaning is recorded separately in `config/product/options-bank-semantic-relations.json` and enforced by the Options Bank smoke contract.

This avoids two bad outcomes:

1. deleting research evidence and losing traceability to competitor/native terminology;
2. allowing downstream Option Contracts or UI generators to interpret semantically duplicate records as independent controls.

## Canonical relationships

| Source record | Relation | Canonical/authored target | Decision |
| --- | --- | --- | --- |
| `fields.behavior.validation_required` | `ALIAS` | `fields.field.required` | `field.required` is the authored Required contract. The shared validation record remains discovery evidence. |
| `fields.field.cloneable` | `ALIAS` | `fields.behavior.repeat_clone` | The shared repeatability primitive is canonical because its child records already define clone bounds, defaults, storage behavior and UX. |
| `fields.field.sortable` | `ALIAS` | `fields.behavior.repeat_sort` | Sorting is part of the canonical repeatability family. |
| `fields.behavior.rest_schema` | `EFFECTIVE_DERIVATION` | `fields.field.rest_schema` | `field.rest_schema` is authored configuration; `rest.schema` is the resolved native/effective schema after registration/type/provider rules. |
| `fields.behavior.value_escape` | `EFFECTIVE_DERIVATION` | `fields.field.escape_html` | `field.escape_html` is authored policy; `value.escape` is the resolved pipeline/output state. |
| `fields.behavior.value_formatted` | `EFFECTIVE_DERIVATION` | `fields.field.format_value` | `field.format_value` is authored policy; `value.formatted` is the resolved formatted-value state. |

## Machine contract

`tests/Smoke/options-bank-contract.php` now validates the semantic registry in addition to the existing Bank rules.

Required invariants:

- semantic registry version is supported;
- relation type is allowlisted (`ALIAS` or `EFFECTIVE_DERIVATION`);
- referenced surface exists;
- source and target records both exist on that same surface;
- source and target cannot be identical;
- one source cannot have multiple semantic mappings;
- aliases must point directly to canonical records and cannot form alias chains.

The discovery count remains unchanged at **596 Fields records** and **774 total Bank records**.

## Downstream consumption rule

Before a Bank record becomes an Atomic Option Contract, consumers must resolve semantic relationships:

- `ALIAS`: do not create a second authored control. Preserve the alias as source/evidence/compatibility terminology pointing to the canonical target.
- `EFFECTIVE_DERIVATION`: treat the source as resolved state, diagnostics, preview, API projection or runtime/effective-value evidence unless a later reviewed contract explicitly makes it independently authorable.

This registry is product-planning metadata, not runtime configuration.

## Remaining Surface 3 blockers

Formal promotion still requires:

1. an explicit one-row-per-native-argument `register_meta()` disposition matrix;
2. Block Bindings source identity/label atomicity and adjacent-source/hook ownership decisions;
3. a provider-by-provider market capability-to-Bank-record coverage matrix;
4. any genuinely missing records discovered by those evidence matrices;
5. exact-head CI after those changes and a final formal gate rerun.

Therefore:

- `NATIVE_AUDITED`: **NO**
- `MARKET_AUDITED`: **NO**
- `BANK_REVIEWED`: **NO**
- semantic consolidation blocker from Review V1: **CLOSED**
