# ADR-0116 — Import / Export Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Import / Export cannot claim safe planning, parsing, mapping, resumability, rollback, export privacy, Multisite or scale support until a future implementation passes `docs/QUALITY/IMPORT-EXPORT-EXECUTABLE-EVIDENCE-PROTOCOL.md` for its certified source/target/runtime profile.

The protocol preserves IR1/PT-D as first shared scoped runtime baseline with IR2/PT-E mandatory comparison and fixes evidence for:
- reviewed Plan/Dry Run + source fingerprint pinning;
- private bounded source staging, archive traversal/symlink/bomb defenses;
- declared target mapping and owning-domain authorization/invariants;
- stable source→target Identity Map and concurrent same-source duplicate prevention;
- checkpoint/crash reconciliation across target commit/Map/Checkpoint/Job windows;
- duplicate Job, pause/resume/cancel and site lifecycle behavior;
- truthful R0–R3 rollback/recovery semantics and Backup prerequisite separation;
- Restore revalidation for copied active Runs/Identity Maps;
- Safe HTTP/media/offload handling;
- authorized/redacted site-scoped exports;
- dependency conflicts, retention/cleanup and IR1/IR2 large-data/Multisite evidence.

## Current state

IM-01…IM-56 documented. **0/56 executed.**

## Development gate

No import/export parse, archive extraction, target mutation, runtime DB row, Job, media fetch/upload, rollback, Restore, cleanup or benchmark is authorized before explicit owner consent under ADR-0014.