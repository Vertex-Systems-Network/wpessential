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

## Current WPEssential lifecycle

Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Lifecycle: `SPECIFICATION`  
Development approval: `PENDING / NOT GRANTED`  
Implementation WIP: **0**  
Current scope: **56 surfaces**  
Authorization: **0/56**  
Product-option maturity: **56/56 Exhaustive**  
Multisite mapping: **56/56**  
AI Prompt mapping: **56/56**  
Runtime-certified surfaces: **none**

Historical scope milestones 31 → 43 → 48 → 50 → 55 → 56 remain preserved.

## Closure/readiness sequence

- WP112 — DONE / ADR-0207 — identified **5,808 exact definitions / 33 namespaces**.
- WP113 — DONE / ADR-0208 — RDR/SRT/DMY/LNK/DBM/PDO/MIR, **1,232/1,232 documented / 0 executed**.
- WP114 — DONE / ADR-0209 — MPR/RPR/ATM/MDP/STM, **880/880 documented / 0 executed**.
- **WP115 — CURRENT** — Second Competitive, **1,936 definitions**.
- WP116 — RESERVED — Third Competitive, **1,760 definitions**.

Known remaining exact planning gap: **3,696 definitions / 21 namespaces**.

P0 remains in `SPECIFICATION`; it does **not** move to `AWAITING_DEVELOPMENT_APPROVAL` yet. After WP116, a fresh closure/readiness audit must decide that state separately. That audit still cannot grant implementation consent itself.

## Readiness classes

- `PLANNING GAP` — exact planning/evidence detail still missing.
- `RUNTIME EVIDENCE PENDING` — exact protocol exists; execution does not.
- `PROVIDER CERTIFICATION PENDING` — provider contract exists; provider/runtime certification does not.
- `OWNER CONSENT PENDING` — implementation permission not granted.
- `NO GAP / READY AS PLAN` — planning layer complete only.

MPR/RPR/ATM/MDP/STM now join the exact-planned namespaces as `NO GAP / READY AS PLAN` at evidence-design level and remain `RUNTIME EVIDENCE PENDING` operationally.

## Development approval readiness rule

Before requesting scoped development approval, the selected work must present scope/exclusions, behaviors/options, roles/permissions, states/workflows, data/migrations, integrations, security/privacy/negative requirements, evidence plan, compatibility, risks and rollback/recovery.

Implementation begins only after unambiguous approval is recorded as ACTIVE for the exact scope.

## Consent invariant

Until then, prohibited work includes production source/runtime changes, dependency/package setup, WordPress/WooCommerce execution, DB/schema/file mutation, user/role/membership changes, rescue email execution, admin-theme runtime, browser-code injection, media telemetry/rewrite, provider/API/AI/MCP calls, tests, benchmarks, migrations, builds, packaging and deployment.

ADR-0014 remains the hard gate. Production development authorization: **NOT GRANTED / 0/56**.