# ADR-0023 — Typed Custom Table Migration Language

Status: **Accepted architecture / compiler and DB strategies pending evidence**  
Date: 2026-08-27

## Context

Custom Tables Builder must support schema evolution without exposing arbitrary destructive SQL as the normal product model. WordPress `dbDelta()` is useful for selected create/update cases but is syntax-sensitive and cannot be treated as a universal safe rename/drop/high-risk migration engine.

## Decision

WPEssential Custom Tables uses a **typed desired-schema + generated Migration Plan** architecture.

The published table Definition expresses desired schema. The physical DB schema is independently introspected. WPE generates a typed migration plan from observed → desired.

Normal migration operations are registered structured operations such as:
- create/add/alter/rename/drop table or column;
- add/drop/replace indexes/keys;
- typed backfill/transform;
- shadow copy/verify/swap strategies where later proven.

Raw user-entered DDL is not the standard product migration format.

## Required semantics

Every plan includes:
- source schema fingerprint;
- target Definition revision/schema version;
- deterministic operation order;
- risk classification;
- preconditions/data compatibility checks;
- dependency impact;
- backup/recovery requirement;
- expected availability/lock class;
- verification and recovery strategy.

A stale plan cannot execute against materially changed observed schema without revalidation/regeneration.

## Risk classes

R0 metadata-only → R1 additive → R2 controlled data-affecting → R3 high-impact → R4 destructive/data-loss capable.

R3/R4 require heightened authorization/re-auth, impact preview and verified recovery point according to policy.

## WordPress primitive use

`dbDelta()` may be used by a future provider compiler only where its behavior matches the required operation. It is an implementation tool, not WPE's source-of-truth migration language.

## Rollback wording

WPE does not promise universal transactional DDL rollback. Each operation declares whether it is:
- reversible;
- reversible while recovery copy exists;
- recoverable only from Backup;
- irreversible after recovery window.

UI uses “recovery” rather than falsely claiming rollback when DB/filesystem semantics cannot guarantee it.

## Drift

Manual/third-party schema drift is detected and classified. WPE does not blindly overwrite unknown drift merely to force physical schema to match a Definition.

## Import

Portable packages contain logical schema, not generated environment-specific SQL. Target site generates its own local Migration Plan after compatibility/introspection.

## Rejected alternatives

- arbitrary phpMyAdmin-style destructive SQL as primary migration UX;
- publishing a Definition and assuming DB is already migrated;
- using `dbDelta()` as the only migration engine for every operation;
- auto-dropping physical tables when a Definition is deleted;
- silent truncation/type conversion.

## Evidence still required

Provider-specific compiler behavior, exact MySQL/MariaDB support, online/copy strategies, DDL locking, large-table migration and recovery remain future consent-gated evidence under P-001/P-004 and Custom Tables benchmarks.

## Supporting document

`docs/ARCHITECTURE/CUSTOM-TABLES-DDL-MIGRATION-LANGUAGE.md`

## Development gate

No DDL compiler, migration, table or executable benchmark is authorized by this ADR. ADR-0014 remains controlling.