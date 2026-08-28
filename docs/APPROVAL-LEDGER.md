# WPEssential — Approval Ledger & Work Lifecycle

Status: **Active governance**  
Last reviewed: **2026-08-29**

## 1. Approval scope hierarchy

Approvals are scoped explicitly as `TASK`, `MODULE`, `MILESTONE`, `PHASE`, or `PROJECT`.

An approval applies only to documented scope and inherited reversible engineering decisions. A broader approval does not automatically authorize later destructive/high-risk production work.

## 2. Approval states

Use `PENDING`, `ACTIVE`, `REVOKED`, `EXHAUSTED`, `SUPERSEDED`.

Every durable approval records approval ID, scope, stable work ID, approver/reference, date/time, included/excluded scope, risk exceptions, status and evidence source.

## 3. Current approval ledger

| Approval ID | Scope | Work ID | Status | Included | Excluded / notes |
|---|---|---|---|---|---|
| GOV-OWNER-CONSENT-000 | PROJECT | WPEssential | PENDING | Production implementation only after explicit grant and applicable technical/planning gates | Owner has **not** granted production development consent. `continue`, `resume`, planning/research approval and ADR acceptance are not consent. |

No TASK, MODULE, MILESTONE, PHASE or PROJECT implementation approval is ACTIVE.

## 4. Existing-project approval adoption

When governance is introduced into active work:
- recover clear approval evidence where possible;
- do not retroactively invalidate clearly authorized work;
- backfill durable ledger entries;
- mark uncertain scope `UNKNOWN/PENDING` rather than inventing approval;
- request new approval only for materially new scope/risk.

WPEssential remains planning-only; no retrospective implementation approval exists.

## 5. Development approval readiness rule

Before requesting development approval for a milestone, present scope, exclusions, options/behaviors, roles/permissions, states/workflows, data/migrations, integrations, security/negative requirements, tests/evidence, affected components, compatibility, risks, sequence and rollback/recovery.

Move a work unit to `AWAITING_DEVELOPMENT_APPROVAL` only when its planning/audit package is actually ready. Do not start production implementation until unambiguous approval is recorded.

ADR-0207 explicitly decides that **P0 is not yet ready for this transition**.

## 6. Work lifecycle

Primary lifecycle states:
`SPECIFICATION`, `AUDITING`, `AWAITING_DEVELOPMENT_APPROVAL`, `APPROVED`, `IMPLEMENTING`, `VERIFYING`, `BLOCKED`, `PARTIALLY_COMPLETE`, `DONE`.

Optional: `PAUSED`, `RECOVERY`, `CANCELLED`.

Ambiguity is never approval.

## 7. Stable work IDs

Use `P<phase>-M<milestone>-WP<work-package>-T<task>`. Accepted ADR/spec IDs remain stable and work IDs are not silently repurposed.

## 8. Current WPEssential lifecycle

Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Development approval: `PENDING / NOT GRANTED`  
Implementation WIP: **0**

Scope history:
- original: 31;
- ADR-0177: 43;
- ADR-0188: 48;
- ADR-0194: 50;
- ADR-0195: 55;
- ADR-0197 current: **56 surfaces**.

Current implementation authorization: **0/56**.

Logical product planning status:
- product-option maturity: **56/56 Exhaustive**;
- logical Multisite mapping: **56/56**;
- shared AI Prompt product mapping: **56/56**;
- runtime certifications: **none**.

Accepted exact universal/adapter evidence includes SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA. These plans remain unexecuted and do not grant implementation permission.

## 9. WP112 / ADR-0207 approval-readiness result

WP112 is **DONE / ADR-0207**.

Finding: P0 remains in `SPECIFICATION`; it does **not** move to `AWAITING_DEVELOPMENT_APPROVAL` because 33 accepted market/competitive namespaces still need exact individual fixture expansion.

Remaining exact planning definitions:
- WP113 Market Expansion — **1,232**;
- WP114 First Competitive — **880**;
- WP115 Second Competitive — **1,936**;
- WP116 Third Competitive — **1,760**;
- total: **5,808 fixtures**.

Current planning work: **P0-M00-WP113 — Market Expansion exact executable-evidence specification**.

After WP116, a new closure/readiness audit must decide whether the approval-readiness state can change. That later audit still cannot grant implementation consent by itself.

## 10. Readiness classes

- `PLANNING GAP` — exact design/evidence decisions still missing.
- `RUNTIME EVIDENCE PENDING` — exact protocol exists; execution does not.
- `PROVIDER CERTIFICATION PENDING` — provider contract exists; provider/runtime certification does not.
- `OWNER CONSENT PENDING` — technical plan may exist but implementation permission has not been granted.
- `NO GAP / READY AS PLAN` — planning layer complete only; not runtime-certified.

## 11. Approval autonomy / revocation

After scoped approval, ordinary reversible decisions inside approved scope do not require repeated consent. Escalate material scope/risk changes, destructive actions outside scope, breaking changes, major security/legal/privacy impacts and privileged production/deployment actions.

Owner can revoke approval at any time; after revocation stop new implementation, preserve evidence, reach a safe non-destructive checkpoint and record `REVOKED`.

## 12. Consent invariant

Planning/documentation, ADR acceptance, internet/market research, evidence-protocol design, daily Git-job design, `continue`, `resume`, Solution Blueprint generation and AI Prompt planning do **not** authorize production code, executable workflows/spikes, package installation, WordPress runtime execution, DB/schema mutation, user/role/membership mutation, recovery-email execution, admin theme application, media telemetry/rewrite, browser-code injection, Woo runtime, provider calls, MCP sessions, AI calls, tests, benchmarks or deployment.

ADR-0014 remains the hard consent gate.

Production development authorization remains **NOT GRANTED / 0/56**.