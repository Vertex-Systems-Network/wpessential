# WPEssential — Current Planning State Supersession Index

Status: **Current-state navigation / historical documents preserved**  
Date: **2026-08-29**  
Accepted by: **ADR-0207**

## Purpose

WPEssential planning evolved through multiple accepted scope milestones. Some long-form master/catalog/roadmap documents intentionally preserve their original historical scope and should not be destructively rewritten merely to replace every old denominator.

This index prevents those historical snapshots from being mistaken for the current project state.

## Current canonical state

- project: `PLANNED_EXISTING_PROJECT`
- execution: `PLANNER_ONLY`
- lifecycle: `SPECIFICATION`
- product scope: **56/56 Exhaustive**
- Multisite mapping: **56/56**
- AI Prompt mapping: **56/56**
- implementation authorization: **0/56**
- latest accepted planning/readiness decision: **ADR-0207**
- current work: **WP113 — Market Expansion exact executable-evidence specification**
- P0 approval-readiness: **NOT READY**
- remaining exact planning gap: **5,808 fixtures across WP113–WP116**

## Current-state authorities

When a historical planning document conflicts with a current denominator/work ID, use this order:

1. `CHECKPOINT.md`
2. current accepted ADRs, currently through ADR-0207
3. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
4. `docs/APPROVAL-LEDGER.md`
5. `docs/WORK-COORDINATION-LEDGER.md`
6. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
7. `docs/OPEN-DECISIONS-REGISTER.md`
8. this supersession index
9. older master/catalog/roadmap snapshots for their historical design detail

## Historical documents preserved

The following long-form documents may contain denominators/current-work language accurate to their original creation milestone. Their architecture/detail remains useful, but their old denominator must not override current state:

- `docs/PRODUCT-MASTER-PLAN.md`
- `docs/MODULE-CATALOG.md`
- `docs/ROADMAP.md`
- older module-suite summaries and expansion-era addenda
- older ADR summaries superseded by later accepted ADRs

Use Git history and the applicable ADR to understand the exact historical scope.

## Scope history

- 31 — original exhaustive product scope
- 43 — ADR-0177
- 48 — ADR-0188
- 50 — ADR-0194
- 55 — ADR-0195
- **56 — ADR-0197 current denominator**

No historical milestone is erased by this index.

## WP112 / ADR-0207 closure result

WP112 found that current product behavior/mapping maturity is strong, but P0 cannot yet move to `AWAITING_DEVELOPMENT_APPROVAL` because exact individual evidence fixtures remain to be enumerated for 33 accepted supplemental/market namespaces.

Required sequence:
- WP113 — RDR/SRT/DMY/LNK/DBM/PDO/MIR — 1,232
- WP114 — MPR/RPR/ATM/MDP/STM — 880
- WP115 — ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC — 1,936
- WP116 — UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX — 1,760

After WP116, run a new closure/readiness audit.

## Consent truth

This index changes documentation authority only. It grants no permission to implement, test, build, migrate, call providers/APIs/AI/MCP, mutate WordPress/data or deploy.

Production development remains **NOT GRANTED / 0/56**.