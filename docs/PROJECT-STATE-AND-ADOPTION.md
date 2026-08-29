# WPEssential — Project State & Adoption Baseline

Status: **Phase 0 governance / planning-only**  
Last reviewed: 2026-08-29

## Current canonical state

Project state **`PLANNED_EXISTING_PROJECT`**; execution **`PLANNER_ONLY`**; lifecycle **`SPECIFICATION`**.

- 56/56 Exhaustive product surfaces;
- 56/56 Multisite mapping;
- 56/56 AI Prompt mapping;
- accepted planning/evidence through ADR-0209;
- WP113 exact evidence 1,232/1,232 / 0 executed;
- WP114 exact evidence 880/880 / 0 executed;
- remaining exact planning 3,696 / 21 namespaces;
- current WP115;
- implementation not started and owner consent NOT GRANTED / 0/56.

WP115 master-plan preflight has fixed all eleven group envelopes; BKX/MRL/PBX/JEX/LHX/HFC explicit ranges were normalized from the accepted addendum before exact fixture enumeration. This produced no runtime evidence.

## State transition

`PLANNED_EXISTING_PROJECT → ACTIVE_EXISTING_PROJECT` requires explicit owner consent under ADR-0014, applicable gates, an implementation baseline and a bounded approved milestone in `IMPLEMENTING`.

Conversation wording alone never changes project state.

## Current capability truth

GitHub planning read/write and Draft PR metadata are available; Linear mirrors planning with GitHub canonical. WordPress/WooCommerce runtime, DB mutation, runtime CI/build/tests, provider/API/AI/MCP execution and deployment remain NOT EXECUTED/consent blocked unless later evidence says otherwise.

## Adoption ledger

| Area | State |
|---|---|
| 56 surfaces | DOCUMENTED / NOT IMPLEMENTED |
| Universal/adapter exact evidence | DOCUMENTED / 0 executed |
| WP113 exact evidence | DOCUMENTED / 0 executed |
| WP114 exact evidence | DOCUMENTED / 0 executed |
| WP115–WP116 remaining exact evidence | PARTIALLY DOCUMENTED / 3,696 remain |
| Production implementation | NOT STARTED |
| DB/runtime migrations | NOT STARTED |
| CI/build runtime | NOT EXECUTED |
| Deployment | NOT STARTED |

## Readiness classes

`PLANNING GAP`, `RUNTIME EVIDENCE PENDING`, `PROVIDER CERTIFICATION PENDING`, `OWNER CONSENT PENDING`, `NO GAP / READY AS PLAN`.

A `0/N` exact protocol is not automatically a planning gap.

## Resume point

WP112 DONE / ADR-0207; WP113 DONE / ADR-0208; WP114 DONE / ADR-0209.

**WP115 CURRENT — ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC — 1,936 exact definitions.** WP116 remains reserved at 1,760.

After WP116, a new closure audit must decide whether P0 may transition to `AWAITING_DEVELOPMENT_APPROVAL`.

Development remains **NOT GRANTED / 0/56**.

## Authority order

Repository/code → DB/schema/config → observed execution → executed tests → CI/CD → VCS history → approved docs/ADRs → maintained checkpoint → prior conversation. Conversation memory never overrides repository evidence.