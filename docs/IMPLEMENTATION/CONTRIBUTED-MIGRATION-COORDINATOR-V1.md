# Contributed Migration Coordinator V1

Status: implementation candidate  
Tracker: #104  
Relations dependency: #66 Gate B  
Base: `main @ cd7688f356cda1bcd88f9087598f6cf318ab11d4`

## Purpose

WPEssential core already has a canonical migration registry, runner and persistent migration state store. Before this tranche those objects were local to bootstrap persistence initialization, so an admitted Pro data module could not contribute schema through the same mechanism after core startup.

This tranche exposes a bounded coordinator without changing migration semantics or adding any Relations-specific schema.

## Coordinator contract

`MigrationCoordinator` owns references to the existing:

- `MigrationRegistry`;
- `MigrationRunner`.

It exposes only two operations:

- `register(MigrationInterface $migration)`;
- `runPending()`.

Registration still inherits the registry's unique migration ID and unique sequence guards. Execution still inherits the runner's applied-state lookup, destructive-migration recovery-plan requirement and state-store persistence.

The coordinator does not expose direct SQL, state-store mutation, force-apply, reset, bypass or destructive override APIs.

## Bootstrap integration

On the native MySQL/MariaDB persistence path, bootstrap now creates one registry, runner and coordinator. Existing core migrations are registered through that coordinator and `runPending()` is executed at the same startup point as before.

The resulting coordinator is exposed as:

`platform.database.migrations`

The service is available only when the native `NativeWpdbAdapter` persistence path is active. The existing in-memory/SQLite fallback does not expose a fake durable migration service.

No Pro/Relations migration is hard-wired into the Free bootstrap.

## Module lifecycle

Kernel already uses a two-pass module lifecycle:

1. every admitted module `register()` call;
2. every admitted module `boot()` call.

A durable Pro data module can therefore register its migration with the coordinator during `register()` and invoke `runPending()` during its `boot()` before it starts using the migrated schema. Because all module registration passes complete before the boot pass begins, pending contributed migrations are visible to the same canonical coordinator.

Repeated `runPending()` calls are idempotent through the existing migration state store. Already-applied migration IDs are skipped rather than re-applied.

## Safety boundary

This tranche does not:

- create a Relations edge table;
- add any module-specific schema;
- alter the migration-state schema;
- change core migration IDs or sequences;
- weaken duplicate registration checks;
- weaken destructive migration recovery requirements;
- enable migrations on the fallback in-memory/SQLite path;
- bypass module edition/activation admission;
- perform release or deployment actions.

## Verification

Focused tests prove:

- initial/core-style pass applies registered migrations once;
- a migration registered after that initial pass is applied on the next pending run;
- repeated pending runs are idempotent;
- duplicate migration IDs and sequences still fail through `MigrationRegistry`;
- destructive migrations without a recovery plan still fail through `MigrationRunner` before `apply()`;
- a recoverable destructive fixture remains runner-controlled;
- an explicitly admitted Pro test module contributes during `register()` and applies through the coordinator during `boot()`;
- migration application does not occur during the module registration phase.

Exact-head CI is authoritative. This document does not claim certification until all applicable workflows are green on one frozen source SHA.

## Relations next step

Only after this seam is certified and merged may Surface 4 add its edge persistence migration through `platform.database.migrations`. Relations must still implement and separately certify scoped table design, cardinality/ownership enforcement, transactional connect/disconnect behavior and recovery before Gate B can close.
