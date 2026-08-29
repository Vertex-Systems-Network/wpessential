# WPEssential — Approval Ledger & Work Lifecycle

Status: **Active governance**  
Last reviewed: **2026-08-29**

Production implementation requires explicit scoped owner consent. Planning/documentation/research, ADR acceptance, `continue`, `resume`, Solution Blueprint generation, AI Prompt planning and exact evidence specification do **not** authorize production source/runtime work.

## Current approval

| Approval ID | Scope | Work ID | Status | Notes |
|---|---|---|---|---|
| GOV-OWNER-CONSENT-000 | PROJECT | WPEssential | PENDING | Owner has **not** granted production development consent. |

No implementation approval is ACTIVE.

## Current lifecycle

`PLANNED_EXISTING_PROJECT` / `PLANNER_ONLY` / `SPECIFICATION`; scope **56**, Exhaustive **56/56**, Multisite **56/56**, AI Prompt **56/56**, authorization **0/56**, runtime-certified surfaces **none**.

WP112 DONE / ADR-0207 identified 5,808 exact definitions. WP113 DONE / ADR-0208 closed 1,232. WP114 DONE / ADR-0209 closed 880. **WP115 CURRENT** has 1,936 definitions; WP116 reserved 1,760. Known remaining gap: **3,696 / 21 namespaces**.

WP115’s eleven group envelopes are now explicit; exact fixture enumeration remains current planning work.

After WP116, a fresh closure audit must decide whether P0 can move to `AWAITING_DEVELOPMENT_APPROVAL`; it still cannot grant implementation consent by itself.

## Readiness classes

`PLANNING GAP`, `RUNTIME EVIDENCE PENDING`, `PROVIDER CERTIFICATION PENDING`, `OWNER CONSENT PENDING`, `NO GAP / READY AS PLAN`.

## Consent invariant

Until explicit scoped consent is ACTIVE, prohibited work includes production source/runtime changes, package/dependency setup, WordPress/WooCommerce execution, DB/schema/file mutation, user/role/membership changes, browser-code injection, media rewrite, reorder, security scan/quarantine, font/provider download, UDS mutation, staging clone/migration, backup run, provider/API/AI/MCP calls, tests, benchmarks, builds, packaging and deployment.

ADR-0014 remains the hard gate. Production development authorization: **NOT GRANTED / 0/56**.