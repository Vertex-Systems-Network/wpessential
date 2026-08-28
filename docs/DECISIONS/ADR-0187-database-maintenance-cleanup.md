# ADR-0187 — Database Maintenance, Cleanup & Storage Health

Status: **Accepted planning architecture / evidence pending / no development authorization**
Date: 2026-08-29

## Context

The owner requested broader market competition beyond the explicitly supplied plugins. Database cleanup/storage-health is a recurring WordPress need and is not covered safely by Reset Manager or generic SQL. Existing WPE shared services can support a provider-owned cleanup architecture without blind deletion.

## Decision

Accept new Pro module surface:

**Database Maintenance, Cleanup & Storage Health Manager**.

Canonical spec:
`docs/MODULES/DATABASE-MAINTENANCE-CLEANUP-EXHAUSTIVE-SPEC.md`

## Architecture

Cleanup is provider/owner-aware. Each cleanup class declares candidate identity, retention/preconditions, batch action, rollback class, post-check, privacy and Multisite scope.

The module reuses DSR, Module Lifecycle, Backup, JobService, Audit, Privacy, Error Taxonomy and Site Lifecycle.

## Accepted semantics

- revisions/autodrafts/trash/comment retention;
- expired transient/cache-like cleanup;
- metadata/relation orphan certainty;
- WPE history/retention providers;
- autoload health;
- table/schema/storage health;
- Dry Run and reviewed Plan;
- Backup/reauth gates;
- resumable batches;
- Run journal and verification;
- third-party/domain cleanup only through certified providers;
- Multisite/global-table boundaries;
- REST/Abilities/MCP/CLI/AI.

## Evidence

Future namespace: **DBM-001…DBM-176**, executed **0/176**.

## Safety

No arbitrary DELETE/TRUNCATE SQL, no heuristic deletion of unknown third-party data, no bypass of legal/privacy retention, no generic DB-engine repair promise and no AI-triggered destructive cleanup without approval.

## Development gate

No DB cleanup, optimization, autoload mutation, table repair or schedule execution is authorized.
