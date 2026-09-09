# AI-Native README Module Timeline Closeout Contract

Status: **ACTIVE / OWNER-DIRECTED**  
Source: **Issue #451 / owner instruction 2026-09-10**

## Purpose

Every meaningful WPEssential AI-Native engineering query/work cycle must leave a readable module-level delivery view in `README.md` before the Supervisor reports the query/cycle final.

The README module dashboard is shared engineering truth, not a cosmetic status widget.

## Mandatory module dashboard fields

Every implementation module/surface represented in the README dashboard must include:

1. Module / Surface
2. Lifecycle / Status
3. Progress bar + percentage
4. Start date/time
5. Estimated completion date/time
6. Actual completion date/time
7. Latest evidence
8. Next gate

Canonical timestamp form is ISO-8601 UTC, for example `2026-09-10T14:30:00Z`.

## Evidence rules

### Start date/time

Use the earliest repository-verifiable accepted implementation signal for the bounded module track, such as the accepted implementation issue, deterministic claim, first implementation commit, or promoted work-package entry.

Do not reconstruct a timestamp from conversation memory. If repository evidence has not yet been audited, write:

`UNKNOWN / pending evidence audit`

### Estimated completion date/time

This is a **non-binding engineering forecast**, not a delivery promise, SLA, release date, or certification date.

The forecast must be based on current repository scope, dependency gates, known blockers, active work packages and observed implementation evidence. Recompute it when those inputs materially change.

If the repository does not support a defensible forecast, write a reasoned state such as:

`FORECAST PENDING / post-audit scope required`

Do not invent a calendar timestamp merely to fill the column.

### Actual completion date/time

Populate only from promoted repository evidence: merge, issue closure, bounded certification/audit promotion, or another authoritative lifecycle transition.

For active/incomplete modules use `—`.

For historical completed modules whose exact completion timestamp has not yet been audited, use:

`UNKNOWN / pending evidence audit`

## Progress semantics

Progress percentages continue to measure the explicitly approved/certified **bounded implementation milestone** for the row. A 100% bounded/native baseline does not imply `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment readiness, or release approval.

No percentage may be raised merely because additional work merged if the repository has not defined/promoted a new percentage milestone.

## Final-query gate

Before a Supervisor sends a final response for a meaningful engineering query/cycle that changed repository state, it must:

1. resolve exact current `main`;
2. reconcile open Issues and open PRs/MRs;
3. reconcile accepted merges and shared truth;
4. update the README module dashboard fields above from repository evidence;
5. update the README main/reconciliation anchor where appropriate;
6. record unknown/blocked forecast data explicitly rather than fabricate it;
7. only then report the cycle final.

A Worker that cannot edit Supervisor-owned shared truth must report README timeline reconciliation as an Integration Requirement.

## Scope boundary

This contract changes governance/reporting only. It does not authorize production deployment, release, managed-table physical DDL, destructive mutation, R3/R4 execution, provider side effects, live data scans, or any otherwise gated runtime action.
