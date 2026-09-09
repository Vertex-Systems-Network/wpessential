# AI-Native README Module Timeline Closeout Contract

Status: **ACTIVE / OWNER-DIRECTED**  
Sources: **Issue #451 + Issue #455 / owner instructions 2026-09-10**

## Purpose

Every meaningful WPEssential AI-Native engineering query/work cycle must leave a readable module-level delivery view in `README.md` before the Supervisor reports the query/cycle final.

The README module dashboard is shared engineering truth, not a cosmetic status widget.

## Mandatory complete 56-surface dashboard

The README closeout dashboard must contain **all 56 canonical product surfaces on every meaningful repository-changing cycle**.

The canonical names, numbers and order come from:

`docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`

Closeout is incomplete if a canonical surface is missing, duplicated, casually renamed or omitted merely because implementation has not started. The dashboard must visibly state **56 / 56 modules listed**.

Each canonical surface row must include:

1. Surface # + Module / Surface name
2. Lifecycle / Status
3. Progress bar + percentage when an evidence-backed bounded implementation baseline exists
4. Start date/time
5. Estimated completion date/time
6. Actual completion date/time
7. Latest evidence
8. Next gate

Planning-only/not-yet-baselined surfaces remain visible. They must not receive fabricated implementation percentages or dates. Use explicit states such as `— / no implementation baseline`, `— / no implementation start promoted`, `UNKNOWN / pending evidence audit`, and `FORECAST PENDING / implementation baseline not promoted` as appropriate.

Canonical timestamp form is ISO-8601 UTC, for example `2026-09-10T14:30:00Z`.

## Mandatory Issues -> PR/MR -> development gate

Every Supervisor and Worker `start`, `continue`, or `resume` cycle is hard-gated in this order:

1. resolve exact current `main`;
2. inspect and solve/continue accepted **OPEN Issues first**;
3. inspect, fix, review and merge eligible **OPEN PRs/MRs second**;
4. re-read deterministic claims, coordination queue and shared truth after those reconciliations;
5. **only then** start or claim new development.

New feature development must not begin while an accepted actionable Issue or PR/MR path is being bypassed. The only exception is repository-evidenced blocked/superseded work, and that state must be explicit rather than inferred from chat memory.

An Issue already represented by an open PR/MR is one accepted work path: finish the PR/MR rather than duplicate the Issue on a new branch.

## Evidence rules

### Start date/time

Use the earliest repository-verifiable accepted implementation signal for the bounded module track, such as the accepted implementation issue, deterministic claim, first implementation commit, or promoted work-package entry.

Do not reconstruct a timestamp from conversation memory. If repository evidence has not yet been audited, write:

`UNKNOWN / pending evidence audit`

For a planning-only surface with no promoted implementation start, use:

`— / no implementation start promoted`

### Estimated completion date/time

This is a **non-binding engineering forecast**, not a delivery promise, SLA, release date, or certification date.

The forecast must be based on current repository scope, dependency gates, known blockers, active work packages and observed implementation evidence. Recompute it when those inputs materially change.

If the repository does not support a defensible forecast, write a reasoned state such as:

`FORECAST PENDING / implementation baseline not promoted`

Do not invent a calendar timestamp merely to fill the column.

### Actual completion date/time

Populate only from promoted repository evidence: merge, issue closure, bounded certification/audit promotion, or another authoritative lifecycle transition.

For active/incomplete or planning-only modules use `—`.

For historical completed modules whose exact completion timestamp has not yet been audited, use:

`UNKNOWN / pending evidence audit`

## Progress semantics

Progress percentages continue to measure the explicitly approved/certified **bounded implementation milestone** for the row. A 100% bounded/native baseline does not imply `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment readiness, or release approval.

No percentage may be raised merely because additional work merged if the repository has not defined/promoted a new percentage milestone.

Planning status such as `ATOMIC_INVENTORY_COMPLETE`, `OPTION_CONTRACT_COMPLETE`, or `UX_CONTRACT_COMPLETE` is not an implementation percentage.

## Final-query gate

Before a Supervisor sends a final response for a meaningful engineering query/cycle that changed repository state, it must:

1. resolve exact current `main`;
2. reconcile accepted open Issues first;
3. reconcile eligible open PRs/MRs second;
4. reconcile accepted merges, claims, queue and shared truth;
5. update the README with the complete canonical **56 / 56** module dashboard from repository evidence;
6. verify no canonical surface is missing or duplicated;
7. update the README main/reconciliation anchor where appropriate;
8. record unknown/blocked progress or forecast data explicitly rather than fabricate it;
9. only then report the cycle final.

A Worker that cannot edit Supervisor-owned shared truth must report complete 56-module README reconciliation as an Integration Requirement.

## Scope boundary

This contract changes governance/reporting only. It does not authorize production deployment, release, managed-table physical DDL, destructive mutation, R3/R4 execution, provider side effects, live data scans, or any otherwise gated runtime action.
