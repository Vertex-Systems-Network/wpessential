# WPEssential — Current Planning State Supersession Index

Status: **Current-state authority index / planning-only / no development authorization**  
Date: 2026-08-29  
Established by: ADR-0207  
Latest accepted planning/evidence decision: **ADR-0208**

## Purpose

WPEssential has a long Phase 0 planning history. Earlier master plans, catalogs, roadmaps and ADR snapshots intentionally preserve the scope/counters that were correct when they were written. Those historical values must not be silently rewritten.

This file identifies the current-state authority so historical 31/43/48/50/55-surface or earlier work-package statements are not misread as present truth.

## Current canonical state

- Project state: `PLANNED_EXISTING_PROJECT`
- Execution mode: `PLANNER_ONLY`
- Lifecycle: `SPECIFICATION`
- Current surface denominator: **56**
- Product-option maturity: **56/56 Exhaustive**
- Logical Multisite mapping: **56/56**
- Module-wide AI Prompt mapping: **56/56**
- Implementation authorization: **0/56**
- Runtime-certified/implemented surfaces: **none**
- Latest accepted planning/evidence decision: **ADR-0208**
- Current work: **WP114 — First Competitive exact executable-evidence specification**

WP113 / ADR-0208 completed RDR/SRT/DMY/LNK/DBM/PDO/MIR exact evidence:
- **1,232/1,232 exact fixtures documented**;
- **0 executed**.

Known remaining exact planning gap:
- **WP114 CURRENT**: MPR/RPR/ATM/MDP/STM = 880;
- WP115: 1,936;
- WP116: 1,760;
- total **4,576 exact definitions across 26 namespaces**.

The WP114 group ownership and hard boundaries are current in `docs/QUALITY/ACCESS-ADMIN-MEDIA-CODE-MARKET-EVIDENCE-MASTER-PLAN.md`.

P0 is not yet approval-ready. After WP116, a new closure/readiness audit must decide whether the lifecycle may move to `AWAITING_DEVELOPMENT_APPROVAL`.

## Current-state authorities

Use these when determining current status/resume point:

1. `CHECKPOINT.md`
2. latest Accepted ADRs, currently through ADR-0208
3. `docs/WORK-COORDINATION-LEDGER.md`
4. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
5. `docs/APPROVAL-LEDGER.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
8. root `README.md`
9. this supersession index

Repository evidence overrides conversation memory.

## Historical files

Long-form files such as `docs/PRODUCT-MASTER-PLAN.md`, `docs/MODULE-CATALOG.md`, `docs/ROADMAP.md`, early module indexes and older ADRs may contain earlier denominators/work IDs. Treat those statements as historical snapshots unless explicitly updated by a later addendum/ADR.

Do not rewrite accepted historical details merely to change a denominator.

## Readiness classification

- Exact protocol exists but is 0 executed → `RUNTIME EVIDENCE PENDING`, not automatically planning gap.
- Provider contract exists but no certified execution → `PROVIDER CERTIFICATION PENDING`.
- Exact fixture specification is still missing/ambiguous → `PLANNING GAP`.
- Implementation permission absent → `OWNER CONSENT PENDING`.
- Exact planning/evidence design complete → `NO GAP / READY AS PLAN` at planning layer only.

RDR/SRT/DMY/LNK/DBM/PDO/MIR are now `NO GAP / READY AS PLAN` for evidence design and `RUNTIME EVIDENCE PENDING` for execution.

## Consent invariant

No current-state synchronization, ADR acceptance, fixture enumeration, research, planning or `continue/resume` instruction grants development permission.

Production development authorization remains **NOT GRANTED / 0/56** under ADR-0014.