# WPEssential — Project State & Adoption Baseline

Status: **Phase 0 governance / planning-only**  
Last reviewed: 2026-08-29

## Current canonical state

Primary project state: **`PLANNED_EXISTING_PROJECT`**  
Execution mode: **`PLANNER_ONLY`**  
Lifecycle: **`SPECIFICATION`**

Current evidence:
- product-option maturity **56/56 Exhaustive**;
- logical Multisite mapping **56/56**;
- AI Prompt mapping **56/56**;
- accepted planning/evidence through **ADR-0209**;
- WP113 exact Market Expansion **1,232/1,232 / 0 executed**;
- WP114 First Competitive **880/880 / 0 executed**;
- remaining exact planning gap **3,696 definitions / 21 namespaces**;
- current work **WP115**;
- production implementation has not started;
- owner development consent **NOT GRANTED / 0/56**.

WP115 preflight has normalized explicit group ownership for BKX/MRL/PBX/JEX/LHX/HFC from the accepted Second Competitive addendum. Exact individual fixture enumeration remains pending; no runtime evidence was produced.

## State-transition rule

`PLANNED_EXISTING_PROJECT → ACTIVE_EXISTING_PROJECT` requires explicit owner development consent under ADR-0014, relevant planning/technical gates, a recorded implementation baseline and a bounded approved milestone in `IMPLEMENTING`.

Conversation wording alone never changes project state.

## Current capability baseline

| Capability | Current truth |
|---|---|
| GitHub planning repository read/write | AVAILABLE |
| Draft PR metadata | AVAILABLE |
| Linear project planner | AVAILABLE; GitHub canonical |
| WordPress/WooCommerce runtime | NOT EXECUTED / consent blocked |
| Database mutation/runtime | NOT EXECUTED / consent blocked |
| Runtime CI/build/tests | NOT EXECUTED as implementation evidence |
| Provider/API/AI/MCP execution | NOT EXECUTED / consent blocked |
| Deployment | NOT EXECUTED |

Unobserved runtime/local capabilities remain `UNKNOWN` rather than inferred.

## Adoption ledger

| Area | Plan → Repository | Repository → Plan | Notes |
|---|---|---|---|
| 56 surfaces | NOT_STARTED | DOCUMENTED | exhaustive, 0 authorized |
| Universal/adapter exact evidence | NOT_STARTED | DOCUMENTED | exact, 0 executed |
| WP113 Market exact evidence | NOT_STARTED | DOCUMENTED | ADR-0208, 0 executed |
| WP114 First Competitive exact evidence | NOT_STARTED | DOCUMENTED | ADR-0209, 0 executed |
| WP115–WP116 remaining exact evidence | NOT_STARTED | PARTIALLY_DOCUMENTED | 3,696 definitions remain |
| Production PHP/React implementation | NOT_STARTED | N/A | intentionally absent |
| DB/runtime migrations | NOT_STARTED | N/A | none |
| CI/build runtime | NOT_STARTED | PARTIALLY_DOCUMENTED | evidence direction only |
| Production deployment | NOT_STARTED | N/A | none |

## Readiness classes

`PLANNING GAP`, `RUNTIME EVIDENCE PENDING`, `PROVIDER CERTIFICATION PENDING`, `OWNER CONSENT PENDING`, `NO GAP / READY AS PLAN`.

A `0/N` exact protocol is not automatically a planning gap.

## Current resume point

- WP112 — DONE / ADR-0207;
- WP113 — DONE / ADR-0208;
- WP114 — DONE / ADR-0209;
- **WP115 — CURRENT — ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC — 1,936 definitions**;
- WP116 — RESERVED — 1,760.

After WP116 a new closure audit must decide whether P0 may transition to `AWAITING_DEVELOPMENT_APPROVAL`.

Development remains **NOT GRANTED / 0/56**.

## Authority order

For actual state prefer repository/code → DB/schema/config → observed execution → executed tests → CI/CD → VCS history → approved docs/ADRs → maintained checkpoint → prior conversation.

Conversation memory never overrides repository evidence.