# AI-Native README Module Timeline Closeout Contract

Status: **ACTIVE / OWNER-DIRECTED**  
Sources: **Issue #451 + Issue #455 + Issue #1151 / owner instructions through 2026-09-22**

## Purpose

Every meaningful repository-changing WPEssential Supervisor milestone must leave a current, readable **Current AI-Native Development Progress** view in `README.md` before the Supervisor reports the milestone complete, blocked or waiting.

When module lifecycle/progress/timeline/public delivery truth changes, or at a terminal product milestone/integration closeout, the complete 56-surface dashboard must also be reconciled.

README progress is shared engineering truth, not a cosmetic status widget.

## Mandatory every-milestone live progress block

For every meaningful repository-changing Supervisor milestone, `README.md` must include a current **Current AI-Native Development Progress** block containing:

1. reconciled `main` anchor where appropriate;
2. active or just-completed Issue/PR;
3. active product surface or governance milestone;
4. milestone state;
5. evidence-backed bounded progress bar/percentage only when a promoted scale exists;
6. latest durable evidence;
7. exact next gate/blocker.

Governance/security/coordination-only milestones update this concise block even when the 56-row dashboard itself does not need a rewrite. A stale or omitted live progress block makes the milestone closeout incomplete.

No percentage, timestamp, delivery promise, runtime certification or product-parity state may be invented merely to populate this block.

## Mandatory complete 56-surface dashboard

When this contract's README trigger applies, the closeout dashboard must contain **all 56 canonical product surfaces**. Governance/security/coordination-only cycles that do not change module delivery truth reconcile compact AI state instead and do not rewrite the entire dashboard solely for churn.

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

Before a Supervisor sends a final response for any meaningful repository-changing cycle, it must:

1. resolve exact current `main`;
2. reconcile accepted open Issues first;
3. reconcile eligible open PRs/MRs second;
4. reconcile accepted merges, claims, queue, Runner Benchmark and compact shared truth;
5. update the README **Current AI-Native Development Progress** block from repository evidence;
6. when module delivery/lifecycle truth changed or a terminal product milestone/integration closeout is being reported, also reconcile the complete canonical **56 / 56** module dashboard;
7. when the full dashboard is required, verify no canonical surface is missing or duplicated;
8. record unknown/blocked progress or forecast data explicitly rather than fabricate it;
9. only then report the cycle final.

A Worker that cannot edit Supervisor-owned shared truth must report live README progress reconciliation as an Integration Requirement, plus complete 56-module dashboard reconciliation when that trigger applies.

## Scope boundary

This contract changes governance/reporting only. It does not authorize production deployment, release, managed-table physical DDL, destructive mutation, R3/R4 execution, provider side effects, live data scans, or any otherwise gated runtime action.
