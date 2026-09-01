# Surface 3 — Field Storage-Key Migration V1

Status: implementation candidate  
Tracker: #68  
Base: `main @ 43a8d7ae03d3b5c921f058452c46138cdacae226`

## Scope

This slice introduces an explicit storage-key migration workflow for the currently certified native WordPress post-meta Field tranche.

It does not make ordinary Field Group save rename-aware. Stable Field UUID identity remains immutable through ordinary save, and key changes continue to require this explicit migration boundary.

## Certified design target

Eligible V1 storage shapes:

- single scalar post meta;
- single-array post meta;
- `single=false` multi-row scalar post meta.

The migration service:

1. requires a Published canonical Surface 3 Field Group and exact expected revision;
2. resolves the Field by stable UUID;
3. limits V1 to direct/top-level Fields;
4. verifies that changing only the key does not change Field/Group runtime storage projection or finite post-type targets;
5. proves ownership of the source registration before any destructive work;
6. preflights the destination registration and rejects foreign/global registration collisions;
7. rejects any pre-existing destination data;
8. snapshots canonical source values before mutation;
9. registers the destination metadata contract when required;
10. copies values through the existing `PostMetaValueStore` and verifies canonical destination state;
11. re-reads each source value immediately before retirement and aborts if it changed after the snapshot;
12. deletes only source rows whose snapshot still matches;
13. retires only provably WPE-owned source registrations;
14. advances the Field Group definition revision only after value/registration migration succeeds;
15. compensates migration-owned mutations on failure and raises `PostMetaRecoveryException` when recovery cannot be fully verified.

## Concurrent-change rule

A source value changed after the migration snapshot is not overwritten during rollback. Recovery restores only source rows that the migration itself already verified as deleted. This prevents a failed migration from clobbering a concurrent edit.

## Explicit Ability

The module exposes the privileged mutation as:

`wpessential/fields/migrate-storage-key`

Required input:

- `group_id`;
- `expected_group_revision`;
- `field_uuid`;
- `destination_key`.

The Ability is `manage_options`-gated, mutation-classified, exposed only through the shared Ability/REST/AJAX infrastructure, and uses an update nonce for AJAX.

## Non-goals

- no automatic rename during normal Field Group save;
- no nested subfield migration V1;
- no options/object-meta/custom-table/provider/Relations migration;
- no raw SQL migration shortcut;
- no broad/global post-meta registration fallback;
- no automatic module boot activation;
- no Fields-private entitlement bypass;
- no production deployment or release.

## Scale boundary

V1 performs a finite per-post-type discovery and an in-memory snapshot before destructive work. That is deliberate for correctness-first certification. Large-data batching/job-backed migration remains a later performance/operations closure gate and must not be inferred as complete from this V1 slice.
