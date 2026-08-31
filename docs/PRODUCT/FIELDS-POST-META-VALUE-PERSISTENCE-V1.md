# Surface 3 — Post Meta Value Persistence V1

Status: **IMPLEMENTED CANDIDATE / CI REQUIRED**  
Owner: **Surface 3 — Custom Fields**  
Scope: bounded native WordPress post-meta value read/write persistence for the registered-meta V1 tranche.

## Purpose

This slice builds on the registered-meta contract from PR #42. It adds the first canonical server-side value persistence adapter without exposing a public mutation Ability, without automatically registering Field Groups at boot, and without inventing a second storage engine.

The adapter consumes persisted normalized Field definitions with stable Field UUID identity and reuses `PostMetaRegistrationCompiler` as the storage-shape authority.

## Certified V1 persistence shape

`PostMetaValueStore` supports native reads for the registered-meta V1 scalar/list tranche and writes only when the compiled registration is `single=true`.

Covered write shapes:

- string scalar meta;
- boolean scalar meta;
- integer scalar meta;
- finite number scalar meta;
- integer-reference scalar meta already certified by the registration compiler;
- single-array repeatable/list meta already certified by the registration compiler.

`single=false` multi-row metadata is readable as a typed canonical list, but replacement/mutation is deliberately rejected in V1.

## Canonical write lifecycle

A write follows this sequence:

1. compile the normalized Field definition for the requested post subtype;
2. verify the target post actually belongs to that subtype;
3. fail closed if the storage shape is multi-row;
4. normalize the submitted value through `FieldValueNormalizer`;
5. enforce persisted-value safety through `FieldValuePersistenceGuard`;
6. read the current canonical state when metadata exists;
7. return `unchanged` without a native update when the canonical state already matches;
8. apply the WordPress slashing boundary immediately before `update_post_meta()`;
9. re-read through the same typed canonical path after the native mutation attempt;
10. accept the write only when post-write canonical verification matches the requested canonical value.

The native boolean/int return from `update_post_meta()` is not treated as sufficient business truth. Verified post-write state is authoritative because WordPress can return `false` for no-change/filter/concurrency paths.

## Write result contract

`PostMetaValueWriteResult` reports one of:

- `written` — a canonical value is verified present after a mutation attempt;
- `unchanged` — the requested canonical value was already present and no native update was issued;
- `deleted` — optional `null` removed an existing value and absence was verified;
- `absent` — optional `null` targeted metadata that was already absent.

Every result retains Field UUID and meta-key provenance.

## Typed read contract

Native post-meta storage returns several scalar types as strings. Reads therefore cast according to the compiled registered-meta type before re-applying canonical Field validation.

The V1 read boundary:

- converts registered integer strings to PHP integers only when they fit the platform integer range;
- converts finite registered numbers to PHP int/float as appropriate instead of saturating oversized integer-like number strings;
- converts canonical WordPress boolean representations to `bool`;
- preserves scalar/list string values;
- requires registered array values to be lists;
- re-runs `FieldValueNormalizer` after native casting;
- fails closed when persisted data is corrupt or outside the current canonical Field contract.

Missing metadata is returned as `null`. Boolean `false` remains distinguishable from absence through `metadata_exists()`.

## Finite and persistable value invariant

`FieldValuePersistenceGuard` is shared by the registration sanitizer and the value store. It allows only canonical null/scalar/array data and rejects:

- `INF`;
- `-INF`;
- `NAN`;
- nested non-finite floats;
- objects/resources or other non-canonical persistence values.

This means an alternate direct native `update_post_meta()` call against a registered WPEssential Field still passes through the same persistence guard; the value store is not a privileged bypass.

## Null/delete semantics

For an optional Field, canonical `null` means absence:

- existing metadata is deleted and absence is verified;
- already-missing metadata is an idempotent `absent` result.

Required Field null/empty rejection remains owned by `FieldValueNormalizer` and occurs before mutation.

## Multi-row fail-closed rule

The registration compiler can represent repeatable scalar metadata as `single=false`, but V1 does **not** replace those rows.

A safe replacement path must first define and test:

- pre-mutation snapshot;
- duplicate semantics/order rules;
- partial delete/add failure detection;
- compensating restore or another accepted atomic recovery strategy;
- post-restore verification;
- concurrent writer behavior.

Until that contract exists, multi-row writes throw before any destructive mutation. Existing rows remain readable.

## Change impact

**Affected**

- Surface 3 Fields module-local services;
- registered-meta sanitizer persistence invariant;
- native WordPress post-meta read/write boundary for the certified V1 tranche;
- Platform Compatibility Matrix execution of the new real WordPress persistence fixture.

**Unaffected**

- Field Group definition storage;
- shared Definition Repository;
- Relations-owned values;
- provider/custom-table storage;
- global Module Registry / entitlement ownership;
- public REST/AJAX/Ability mutation APIs;
- automatic runtime Field Group target binding;
- admin React rendering.

**Primary risks**

- WordPress slashing differences;
- stringly typed native metadata reads;
- false/no-change native mutation returns;
- persisted corrupt/legacy values;
- multi-row partial mutation.

**Migration**

No schema/data migration is performed by this slice. Existing compatible native metadata is read through the new typed boundary; incompatible persisted values fail closed rather than being silently rewritten.

**Rollback / recovery**

This source slice is reversible by code rollback because it introduces no migration and no automatic runtime mutation path. Individual verified single-value writes use native WordPress metadata semantics. Multi-row destructive replacement is intentionally not implemented until compensating recovery is certified.

## Verification target

Before merge, exact-head applicable CI must pass:

- Architecture Guards;
- PHP Quality Toolchain;
- Platform Compatibility Matrix;
- Distributable Package.

The Platform Compatibility Matrix executes `tests/Integration/wordpress-fields-meta-persistence.php` across:

- WordPress 6.9 / 7.1;
- PHP 8.2 / 8.3 / 8.4 / 8.5;
- MariaDB 10.11 baselines on both supported WordPress lines.

The real WordPress fixture verifies:

- slash-sensitive scalar round-trip;
- idempotent unchanged writes;
- boolean false versus absent metadata;
- typed integer and finite-number reads;
- non-finite store-write rejection with prior-value retention;
- registered-meta sanitizer rejection outside the store path;
- single-array repeatable round-trip;
- multi-row typed reads plus mutation fail-closed/no-partial-change behavior;
- verified delete and already-absent semantics.

Unit coverage also exercises native false/update verification paths, corrupt persisted values, subtype mismatch, finite-value guarding and numeric overflow handling.

## Explicit non-certifications / MUST NOT

This slice MUST NOT be interpreted as complete Custom Fields runtime storage.

It does not:

- expose value read/write through WordPress Abilities, AJAX or REST handlers;
- bypass capability/resource authorization through a private endpoint;
- auto-register published Field Groups to post types;
- activate the Pro module outside the unresolved shared entitlement/module-enable owner;
- mutate `single=false` multi-row values;
- rename/migrate meta keys when editable Field keys change;
- serialize Group/Repeater/provider/Relations/secret values opportunistically;
- provide import/export or provider migration;
- claim product/admin UI completion.

## Next bounded slice

After exact-head merge certification, the next safe Surface 3 target is **typed value Ability integration + target resolution contract**. That work must resolve Field Group/Field identity to an allowed post subtype, enforce resource authorization on every read/write, and consume this store without creating a second mutation path.

Multi-row replacement/recovery remains a separate high-integrity storage task unless the Ability slice genuinely requires it.
