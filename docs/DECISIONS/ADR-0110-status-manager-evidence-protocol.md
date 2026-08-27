# ADR-0110 — Status Manager Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Status Manager runtime support remains split between the WordPress Post Status Adapter and the Generic Domain State Machine. A future implementation cannot claim production readiness for either engine until the applicable fixtures in `docs/QUALITY/STATUS-MANAGER-EXECUTABLE-EVIDENCE-PROTOCOL.md` pass.

The protocol enforces:
- core/third-party post status preservation;
- status key/storage constraints and post-type availability truthfulness;
- edit/quick/bulk/REST/Form/Dashboard integration evidence;
- migration-first machine-key changes and archive/remove safety;
- explicit enforcement-coverage claims for direct third-party writes;
- generic state-machine publish/initial-state/terminal/reopen semantics;
- actor/resource Policy + transition guards;
- optimistic concurrency and stale-state rejection;
- durable transition history/atomicity or reconciliable failure semantics;
- idempotent duplicate request/Job behavior;
- typed current-state storage adapters;
- Query, Workflow, timed transition and import integration;
- Multisite isolation and large-history/index evidence.

## Current state

SM-01…SM-48 documented. **0/48 executed.**

## Development gate

No status registration, post/state mutation, database migration, Workflow/Job execution or runtime test is authorized before explicit owner consent under ADR-0014.