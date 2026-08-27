# ADR-0041 — Import Run, Checkpoint & Rollback Runtime

Status: **Accepted architecture / physical schema-adapter evidence pending**  
Date: 2026-08-27

## Decision

Import/Export runtime uses:

**Source Adapter → Import Plan → Dry Run/fingerprint → durable Import Run → bounded Checkpoints/Items → Source→Target Identity Map → Change Journal → Verification/Reconciliation → optional rollback Plan**.

Source adapters normalize to IR and never directly write target tables. Target mutations go through owning WPE module/Data Source APIs.

Rollback is classified by actual reversibility; WPE never promises universal rollback for irreversible/external side effects.

## Why

- resumable large migrations;
- deterministic re-import/update;
- safe relation/media identity mapping;
- avoids duplicate records after crash;
- preserves administrator edits during re-import/rollback;
- gives truthful recovery boundaries.

## Consequences

- execution pins reviewed source/plan fingerprints;
- stale source requires new dry-run;
- remote media uses Connections SSRF-safe HTTP;
- source-missing record does not imply delete by default;
- source plugin is never auto-uninstalled after migration;
- high-risk imports may require verified Backup Set.

## Evidence still required

After explicit consent: physical run/index schema, crash/resume, identity-map/upsert, rollback conflicts, remote media safety, large imports and certified source adapters.

Supporting doc: `docs/ARCHITECTURE/IMPORT-RUN-CHECKPOINT-ROLLBACK-RUNTIME.md`.