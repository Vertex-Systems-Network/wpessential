# WPEssential — Approval Ledger & Work Lifecycle

Status: **Active governance**  
Last reviewed: 2026-08-29

## 1. Approval scope hierarchy

Approvals are scoped explicitly as `TASK`, `MODULE`, `MILESTONE`, `PHASE`, or `PROJECT`.

An approval applies only to documented scope and inherited reversible engineering decisions. A broader approval does not automatically authorize later destructive/high-risk production work.

## 2. Approval states

Use `PENDING`, `ACTIVE`, `REVOKED`, `EXHAUSTED`, `SUPERSEDED`.

Every durable approval records approval ID, scope, stable work ID, approver/reference, date/time, included/excluded scope, risk exceptions, status and evidence source.

## 3. Current approval ledger

| Approval ID | Scope | Work ID | Status | Included | Excluded / notes |
|---|---|---|---|---|---|
| GOV-OWNER-CONSENT-000 | PROJECT | WPEssential | PENDING | Production implementation once explicitly granted and technical gates permit | Owner has **not** granted production development consent. `continue`, planning approval, research requests and ADR acceptance are not consent. |

No module, milestone, phase or project implementation approval is ACTIVE.

## 4. Existing-project approval adoption

When governance is introduced into active work:
- recover clear approval evidence where possible;
- do not retroactively invalidate clearly authorized work;
- backfill durable ledger entries;
- mark uncertain scope `UNKNOWN/PENDING` rather than inventing approval;
- request new approval only for materially new scope/risk.

WPEssential remains planning-only; no retrospective implementation approval exists.

## 5. Development approval summary

Before requesting development approval for a milestone, present scope, exclusions, options/behaviors, roles/permissions, states/workflows, data/migrations, integrations, security/negative requirements, tests/evidence, affected components, compatibility, risks, sequence and rollback/recovery.

Move a work unit to `AWAITING_DEVELOPMENT_APPROVAL` only when its planning/audit package is actually ready. Do not start production implementation until unambiguous approval is recorded.

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
- original: 31 surfaces;
- ADR-0177: 43 surfaces;
- ADR-0188: 48 surfaces;
- ADR-0194 current: **50 planned module/platform surfaces**.

Current implementation authorization: **0/50**.

Logical product planning status:
- product-option maturity: **50/50 Exhaustive**;
- logical Multisite mapping: **50/50**;
- shared AI Prompt product mapping: **50/50**;
- runtime certifications: none.

Current planning work is **P0-M00-WP65 — F03 Search & Indexing detailed evidence** after completion of owner-requested WP83–WP89 access/admin/media/code planning interrupt.

## 9. Approval autonomy / revocation

After scoped approval, ordinary reversible decisions inside approved scope do not require repeated consent. Escalate material scope/risk changes, destructive actions outside scope, breaking changes, major security/legal/privacy impacts and privileged production/deployment actions.

Owner can revoke approval at any time; after revocation stop new implementation, preserve evidence, reach a safe non-destructive checkpoint and record `REVOKED`.

## 10. Consent invariant

Planning/documentation, ADR acceptance, internet/market research, evidence-protocol design, daily Git-job design, `continue`, `resume`, Solution Blueprint generation and AI Prompt planning do **not** authorize production code, executable workflows/spikes, package installation, WordPress runtime execution, DB/schema mutation, user/role/membership mutation, recovery-email execution, admin theme application, media telemetry/rewrite, browser-code injection, provider calls, MCP sessions, AI calls, tests, benchmarks or deployment.

ADR-0014 remains the hard consent gate.