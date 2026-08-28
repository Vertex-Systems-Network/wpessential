# ADR-0166 — Status Manager Canonical Evidence Refinement

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP49`  
Development authorization: **NOT GRANTED**

## Decision

Accept the in-place refinement of `docs/QUALITY/STATUS-MANAGER-EXECUTABLE-EVIDENCE-PROTOCOL.md` from SM-01…SM-48 to **SM-01…SM-176** while preserving the original fixtures and the architectural split between WordPress Post Status Adapter and Generic Domain State Machine.

The expanded evidence covers Definition/versioning, native WordPress edit/REST/direct-write boundaries, typed guards and current Policy, concurrency/history/idempotency, Query/CAC projections, Workflow/Job/Notification/provider effects, import/migration/restore/lifecycle, privacy/diagnostics, Multisite and scale.

## Preserved invariants

- Post Status adapter certification never auto-certifies the generic engine, or vice versa.
- UI/condition/cache visibility never authorizes transition.
- Current state, transition intent/result, history and downstream side effects are separate truths.
- Direct-write bypass limitations are reported honestly; WPE does not claim enforcement stronger than tested adapters provide.
- Duplicate requests/Jobs cannot create repeated logical transitions beyond certified idempotency semantics.
- State/key migrations are explicit and recoverable; labels are not portable machine identity.
- Cross-site state/history access is forbidden outside explicitly authorized network scope.

## Evidence status

- SM fixtures documented: **176**
- SM fixtures executed: **0/176**
- WordPress Post Status adapter certifications: **0**
- Generic State Machine certifications: **0**
- Integration/Multisite/performance certifications: **0**

No status registration, post/entity mutation, transition, history row, database migration, Workflow/Job/provider action, cache mutation, Multisite operation or benchmark was executed.

## Consequence

`P0-M00-WP49` becomes planning-complete after source-of-truth synchronization. Runtime evidence and implementation remain blocked by ADR-0014 and the Approval Ledger.
