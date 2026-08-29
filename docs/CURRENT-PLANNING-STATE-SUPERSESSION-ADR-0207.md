# WPEssential — Current Planning State Supersession Index

Status: **Current-state authority index / planning-only / no development authorization**  
Date: 2026-08-29  
Established by: ADR-0207  
Latest accepted planning/evidence decision: **ADR-0209**

## Purpose

Earlier master plans, catalogs, roadmaps and ADR snapshots intentionally preserve values correct when written. This index identifies current authority without rewriting historical 31/43/48/50/55-surface snapshots.

## Current canonical state

- Project state: `PLANNED_EXISTING_PROJECT`
- Execution mode: `PLANNER_ONLY`
- Lifecycle: `SPECIFICATION`
- Current denominator: **56 surfaces**
- Product-option maturity: **56/56 Exhaustive**
- Multisite mapping: **56/56**
- AI Prompt mapping: **56/56**
- Implementation authorization: **0/56**
- Runtime-certified/implemented surfaces: **none**
- Latest accepted planning/evidence decision: **ADR-0209**
- Current work: **WP115 — Second Competitive exact executable-evidence specification**

Completed exact supplemental tranches:
- WP113 / ADR-0208 — RDR/SRT/DMY/LNK/DBM/PDO/MIR = **1,232/1,232 documented / 0 executed**;
- WP114 / ADR-0209 — MPR/RPR/ATM/MDP/STM = **880/880 documented / 0 executed**.

Known remaining exact planning gap:
- **WP115 CURRENT** = 1,936 definitions across 11 namespaces;
- WP116 = 1,760 across 10;
- total **3,696 definitions across 21 namespaces**.

WP115 preflight also normalized previously under-specified 16-group ownership for BKX/MRL/PBX/JEX/LHX/HFC in `docs/QUALITY/SECOND-COMPETITIVE-EXPANSION-EVIDENCE-MASTER-PLAN.md`. That normalization is planning work only; exact fixture enumeration and formal WP115 acceptance are still pending.

P0 is not yet approval-ready. After WP116, a fresh closure/readiness audit must decide whether lifecycle may move to `AWAITING_DEVELOPMENT_APPROVAL`.

## Current-state authorities

Use, in order appropriate to the question:
1. `CHECKPOINT.md`
2. latest Accepted ADRs, currently through ADR-0209
3. `docs/WORK-COORDINATION-LEDGER.md`
4. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
5. `docs/APPROVAL-LEDGER.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
8. root `README.md`
9. this index

Repository evidence overrides conversation memory.

## Readiness classification

- exact fixture planning missing/ambiguous → `PLANNING GAP`;
- exact protocol exists but 0 executed → `RUNTIME EVIDENCE PENDING`;
- provider contract exists but uncertified → `PROVIDER CERTIFICATION PENDING`;
- implementation permission absent → `OWNER CONSENT PENDING`;
- exact planning/evidence design complete → `NO GAP / READY AS PLAN` at planning layer only.

RDR/SRT/DMY/LNK/DBM/PDO/MIR and MPR/RPR/ATM/MDP/STM are planning-complete at exact evidence-design level and remain unexecuted.

## Consent invariant

No synchronization, ADR acceptance, fixture enumeration, research, planning or `continue/resume` instruction grants development permission.

Production development authorization remains **NOT GRANTED / 0/56** under ADR-0014.