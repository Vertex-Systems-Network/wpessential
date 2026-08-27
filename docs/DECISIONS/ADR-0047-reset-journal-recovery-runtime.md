# ADR-0047 — Reset Execution Journal & Recovery Architecture

Status: **Accepted architecture; destructive runtime evidence pending**  
Date: 2026-08-27

## Decision

WPEssential Reset Manager uses a staged destructive workflow with:

- immutable reviewed Reset Plan + fingerprint;
- verified Backup/restore point before high-risk destructive execution;
- durable recovery journal that survives the reset domains;
- explicit per-domain owner adapters;
- maintenance/mutation locking;
- ADR-0032 recovery-principal invariant;
- truthful reversible/backup-restorable/compensatable/irreversible classifications;
- checkpointed failure recovery rather than pretending whole-site reset is one ACID transaction;
- post-reset WordPress/admin/data health verification before completion.

A whole-DB reset that would erase its only journal/recovery state before recovery is established is blocked.

## Why

WordPress reset crosses DB, filesystem, plugin/theme and external-provider boundaries that cannot be rolled back by a single database transaction. A durable journal + verified backup is required for diagnosable, resumable, recoverable destructive operations.

## Security/data boundaries

- never intentionally remove the last legitimate recovery principal without verified replacement;
- passwords/recovery secrets never live in journal/log;
- third-party plugin data is not deleted without explicit certified ownership/scope;
- external irreversible deletes require heightened confirmation;
- screenshots/video are optional UX evidence, not recovery truth;
- WordPress fatal-error Recovery Mode is not treated as Reset transaction state.

## Remaining evidence

Exact recovery-store schema/location, DB/DDL behavior, filesystem permissions, multisite, plugin/theme/uploads adapters, crash recovery, Backup restore integration and post-reset health fixtures require executable testing after owner consent.

See `docs/ARCHITECTURE/RESET-EXECUTION-JOURNAL-RECOVERY-RUNTIME.md`.

Development/destructive execution remains prohibited until explicit owner consent under ADR-0014.