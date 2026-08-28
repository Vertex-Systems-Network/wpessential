# WPEssential — Approval Ledger & Work Lifecycle

Status: **Active governance**
Last reviewed: 2026-08-29

## 1. Approval scope hierarchy

Approvals are scoped explicitly as one of:
- `TASK`
- `MODULE`
- `MILESTONE`
- `PHASE`
- `PROJECT`

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

## 6. Approval autonomy

After scoped approval, ordinary reversible decisions inside approved scope do not require repeated consent. Escalate material scope/risk changes, destructive actions outside scope, breaking changes, major security/legal/privacy impacts and privileged production/deployment actions.

## 7. Work lifecycle states

Primary:
`SPECIFICATION`, `AUDITING`, `AWAITING_DEVELOPMENT_APPROVAL`, `APPROVED`, `IMPLEMENTING`, `VERIFYING`, `BLOCKED`, `PARTIALLY_COMPLETE`, `DONE`.

Optional:
`PAUSED`, `RECOVERY`, `CANCELLED`.

Transitions remain evidence/approval-gated; ambiguity is never approval.

## 8. Stable work IDs

Use:
`P<phase>-M<milestone>-WP<work-package>-T<task>`

Accepted ADR/spec IDs remain stable. A work ID must not be silently reassigned to unrelated scope.

## 9. Milestone contract

Every executable milestone defines identity, scope/exclusions, dependencies/blockers, entry criteria, approval state, baseline/VCS state, security/negative requirements, data/migration implications, performance/observability, tests/evidence, integrations, exit criteria and rollback/recovery class.

## 10. Approval precedence / revocation

Prefer the narrower, newer explicit owner instruction for the same scope. Destructive/production restrictions remain unless explicitly lifted. Ambiguous consent is not consent.

Owner can revoke approval at any time; after revocation stop new implementation, preserve evidence, reach a safe non-destructive checkpoint and record `REVOKED`.

## 11. Current WPEssential lifecycle

Project state: `PLANNED_EXISTING_PROJECT`
Execution mode: `PLANNER_ONLY`
Development approval: `PENDING / NOT GRANTED`
Implementation WIP: **0**

Scope history:
- original pre-ADR-0177: 31 surfaces;
- ADR-0177: 43 surfaces;
- ADR-0188 current: **48 planned module/platform surfaces**.

Current implementation authorization: **0/48**.

Logical product planning status:
- product-option maturity: **48/48 Exhaustive**;
- logical Multisite mapping: **48/48**;
- shared AI Prompt product mapping: **48/48**;
- runtime certifications: none.

Current planning work remains active at **P0-M00-WP65 — F03 Search & Indexing detailed evidence**, after completion of the owner-requested WP75–WP82 market-expansion interrupt.

## 12. Consent invariant

Planning/documentation, ADR acceptance, internet/market research, evidence-protocol design, daily Git-job design, `continue`, `resume`, Solution Blueprint generation and AI Prompt planning do **not** authorize production code, executable workflows/spikes, package installation, WordPress runtime execution, DB/schema mutation, provider calls, MCP sessions, AI calls, crawls, data transforms, fixture generation, cleanup, tests, benchmarks or deployment.

ADR-0014 remains the hard consent gate.
