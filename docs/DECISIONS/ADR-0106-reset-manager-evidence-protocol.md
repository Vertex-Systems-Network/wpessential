# ADR-0106 — Reset Manager Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Reset Manager remains blocked until a future implementation passes `docs/QUALITY/RESET-MANAGER-EXECUTABLE-EVIDENCE-PROTOCOL.md`.

The protocol fixes evidence around:
- impact Plan fingerprint/revalidation;
- recovery-principal invariant;
- mandatory verified restore point;
- destructive-operation locking;
- durable Reset Run/stage journal;
- duplicate Job/idempotency/reconciliation;
- DB/filesystem/plugin/theme failure points;
- truthful rollback-vs-recovery classification;
- post-reset health verification;
- Multisite/site-lifecycle isolation;
- restored copied active-Run safety;
- low-resource/high-volume execution.

WordPress Recovery Mode remains fatal-error/plugin-theme recovery assistance and is not used as a transactional data rollback mechanism.

## Current state

RM-01…RM-48 documented. **0/48 executed.**

## Development gate

No Reset Run, lock, journal, deletion, plugin/theme mutation, Backup/Restore or recovery execution is authorized before explicit owner consent under ADR-0014.