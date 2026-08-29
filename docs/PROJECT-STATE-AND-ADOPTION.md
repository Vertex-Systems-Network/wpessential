# WPEssential — Project State & Adoption Baseline

Status: **Phase 0 governance / planning-only**  
Last reviewed: 2026-08-29

## Current canonical state

Primary project state: **`PLANNED_EXISTING_PROJECT`**  
Execution mode: **`PLANNER_ONLY`**  
Lifecycle: **`SPECIFICATION`**

Reason:
- product-option maturity **56/56 Exhaustive**;
- logical Multisite mapping **56/56**;
- AI Prompt mapping **56/56**;
- accepted planning/evidence through **ADR-0209**;
- WP113 exact Market Expansion evidence **1,232/1,232 documented / 0 executed**;
- WP114 First Competitive evidence **880/880 documented / 0 executed**;
- remaining exact planning gap **3,696 definitions / 21 namespaces** in WP115–WP116;
- production implementation has not started;
- owner development consent has not been granted (**0/56**).

## Project-state taxonomy

- `GREENFIELD` — no meaningful existing implementation or plan.
- `PLANNED_EXISTING_PROJECT` — substantial product/architecture/specification exists; implementation absent or limited.
- `ACTIVE_EXISTING_PROJECT` — production code development underway.
- `PRODUCTION_PROJECT` — real users/data/deployments depend on system.
- `LEGACY_OR_MIGRATION` — modernization/migration is primary goal.
- `RECOVERY` — state is broken/corrupt/unsafe/materially uncertain.

State changes require verified evidence, never conversational wording alone.

### PLANNED_EXISTING_PROJECT → ACTIVE_EXISTING_PROJECT
Requires explicit owner consent under ADR-0014, applicable planning/technical entry gates, implementation baseline and a bounded approved implementation milestone in `IMPLEMENTING`.

## Current capability baseline

| Capability | Current planning-session truth |
|---|---|
| GitHub repository read/write for planning docs | AVAILABLE |
| Draft PR metadata | AVAILABLE |
| Linear project planner | AVAILABLE; GitHub remains canonical |
| WordPress/WooCommerce runtime | NOT EXECUTED / consent blocked |
| Database mutation/runtime | NOT EXECUTED / consent blocked |
| Runtime CI/build/tests | NOT EXECUTED as implementation evidence |
| Provider/API/AI/MCP execution | NOT EXECUTED / consent blocked |
| Deployment | NOT EXECUTED |

Unknown runtime/local capabilities must remain `UNKNOWN` unless freshly observed; planning documentation must not infer them.

## Adoption ledger

| Area | Plan → Repository | Repository → Plan | Notes |
|---|---|---|---|
| 56 module/platform surfaces | NOT_STARTED | DOCUMENTED | exhaustive product planning, 0 authorized |
| Shared architecture | NOT_STARTED | DOCUMENTED | accepted paper contracts/ADRs only |
| Universal/adapter exact evidence | NOT_STARTED | DOCUMENTED | SBP…WCA/AIP exact, 0 executed |
| Market Expansion exact evidence | NOT_STARTED | DOCUMENTED | WP113 / ADR-0208 exact, 0 executed |
| First Competitive exact evidence | NOT_STARTED | DOCUMENTED | WP114 / ADR-0209 exact, 0 executed |
| Remaining supplemental exact evidence | NOT_STARTED | PARTIALLY_DOCUMENTED | WP115–WP116 = 3,696 definitions |
| Production PHP/React implementation | NOT_STARTED | N/A | intentionally absent |
| Database/runtime migrations | NOT_STARTED | N/A | no execution |
| CI/build runtime | NOT_STARTED | PARTIALLY_DOCUMENTED | evidence direction exists; execution pending |
| Production deployment | NOT_STARTED | N/A | none |

## Gap/readiness classes

Gap type: `CORRECTION`, `COMPLETION`, `HARDENING`, `OPTIMIZATION`, `NEW_PRODUCT_SCOPE`.

Readiness: `PLANNING GAP`, `RUNTIME EVIDENCE PENDING`, `PROVIDER CERTIFICATION PENDING`, `OWNER CONSENT PENDING`, `NO GAP / READY AS PLAN`.

A `0/N` exact evidence counter is not automatically a planning gap.

## Current planning resume point

- WP112 — DONE / ADR-0207;
- WP113 — DONE / ADR-0208;
- WP114 — DONE / ADR-0209;
- **WP115 — CURRENT — Second Competitive exact evidence (`ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC`, 1,936 definitions)**;
- WP116 — RESERVED — 1,760 definitions.

After WP116, a new closure audit must decide whether P0 may transition to `AWAITING_DEVELOPMENT_APPROVAL`.

Development remains **NOT GRANTED / 0/56**.

## Authority order

For actual state prefer repository/code → database/schema/config → observed execution → executed tests → CI/CD → VCS history → approved docs/ADRs → maintained checkpoint → prior conversation.

Conversation memory never overrides repository evidence.