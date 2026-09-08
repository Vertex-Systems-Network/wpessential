# Custom Tables Definition Compiler V1

Exact implementation anchor: `main @ 649a2c4307f2d2f6ea148a4b2a0b3c40911d49ca`

This tranche implements only the canonical Surface 7 desired-state `table` Definition compiler and immutable schema descriptor authorized by the post-Status entry audit.

## Bounded contract

The `table` Definition remains authored desired state only. Compilation validates and normalizes:

- stable logical table key and human label;
- fixed V1 storage mode `managed`;
- fixed V1 topology `site` / CT1-PT-E semantics;
- positive desired schema version;
- ordered typed columns with stable keys;
- explicit primary-key references;
- bounded unique/non-unique secondary indexes;
- optional bounded charset/collation inheritance policy;
- bounded privacy/data classification metadata.

V1 supports a deliberately small physical type vocabulary: `bigint`, `integer`, `decimal`, `varchar`, `text`, `datetime`, `boolean`, `json`.

The compiler rejects unknown keys, raw SQL/DDL fragments, callback/executable channels, unknown type families, duplicate column/index keys, invalid primary/index references, invalid nullable/default/auto-increment combinations, and out-of-range length/precision/scale values.

## Deterministic output

Compilation returns an immutable descriptor carrying Definition identity/revision plus normalized semantic desired state and a SHA-256 compatibility fingerprint. The fingerprint excludes environment-specific table prefixes and generated SQL.

## Critical invariant

Saving or publishing a `table` Definition does not create, alter, rename or drop any database object. This tranche contains no database adapter, migration runner, `dbDelta()`, SQL compiler, row CRUD/Data Source provider or physical-schema introspection path.

All physical DDL/migration work remains separately serialized and blocked pending a later exact-main audit with explicit recovery/security semantics.
