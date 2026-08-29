# WPEssential — Approval Ledger & Work Lifecycle

Status: **Active governance**  
Last reviewed: **2026-08-29**

## Approval invariant

Production implementation requires explicit scoped owner consent. Planning/documentation/research, ADR acceptance, `continue`, `resume`, Solution Blueprint generation, AI Prompt planning and exact evidence specification do **not** authorize production source/runtime work.

## Current approval ledger

| Approval ID | Scope | Work ID | Status | Included | Excluded / notes |
|---|---|---|---|---|---|
| GOV-OWNER-CONSENT-000 | PROJECT | WPEssential | PENDING | Production implementation only after explicit grant and applicable gates | Owner has **not** granted production development consent. |

No TASK, MODULE, MILESTONE, PHASE or PROJECT implementation approval is ACTIVE.

## Current lifecycle

Project state `PLANNED_EXISTING_PROJECT`; mode `PLANNER_ONLY`; lifecycle `SPECIFICATION`; implementation WIP **0**; current scope **56**; authorization **0/56**; product-option maturity **56/56 Exhaustive**; Multisite **56/56**; AI Prompt **56/56**; runtime-certified surfaces **none**.

Historical scope milestones 31 → 43 → 48 → 50 → 55 → 56 remain preserved.

## Closure/readiness sequence

- WP112 — DONE / ADR-0207 — identified **5,808 / 33 namespaces**.
- WP113 — DONE / ADR-0208 — **1,232/1,232 exact / 0 executed**.
- WP114 — DONE / ADR-0209 — **880/880 exact / 0 executed**.
- **WP115 — CURRENT** — Second Competitive — **1,936 definitions**. Its eleven 16-group envelopes are now explicit; exact individual fixture enumeration remains current work.
- WP116 — RESERVED — **1,760 definitions**.

Known remaining exact planning gap: **3,696 / 21 namespaces**.

P0 remains in `SPECIFICATION`; after WP116, a fresh closure/readiness audit must decide whether it can move to `AWAITING_DEVELOPMENT_APPROVAL`. That audit cannot grant implementation consent itself.

## Readiness classes

`PLANNING GAP`, `RUNTIME EVIDENCE PENDING`, `PROVIDER CERTIFICATION PENDING`, `OWNER CONSENT PENDING`, `NO GAP / READY AS PLAN`.

MPR/RPR/ATM/MDP/STM are now exact-planned under ADR-0209 and remain runtime-pending.

## Consent invariant

Prohibited until explicit scoped consent: production source/runtime changes, dependencies/package setup, WordPress/WooCommerce execution, DB/schema/file mutation, user/role/membership changes, rescue email, admin-theme runtime, browser-code injection, media telemetry/rewrite, reorder, security scan/quarantine, font/provider download, UDS mutation, staging clone/migration, backup run, provider/API/AI/MCP calls, tests, benchmarks, builds, packaging and deployment.

ADR-0014 remains the hard gate. Production development authorization: **NOT GRANTED / 0/56**.