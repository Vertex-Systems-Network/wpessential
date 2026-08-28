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

An approval applies only to the documented scope and inherited reversible engineering decisions within that scope. A broader approval does not automatically authorize a later destructive production action if a separate high-risk approval is required.

## 2. Approval states

Use:
- `PENDING`
- `ACTIVE`
- `REVOKED`
- `EXHAUSTED`
- `SUPERSEDED`

Every durable approval record should include approval ID, scope type, stable work ID, approver/reference, date/time, included/excluded scope, risk exceptions, status and evidence source.

## 3. Current approval ledger

| Approval ID | Scope | Work ID | Status | Included | Excluded / notes |
|---|---|---|---|---|---|
| GOV-OWNER-CONSENT-000 | PROJECT | WPEssential | PENDING | Production implementation once explicitly granted and technical gates permit | Current owner has **not** granted production development consent. `continue`, planning approval and ADR acceptance are not consent. |

No module, milestone, phase or project implementation approval is currently ACTIVE.

## 4. Existing-project approval adoption

When governance is introduced into existing active work:
- do not retroactively invalidate clearly authorized work;
- recover approval evidence from repository/issue/PR/checkpoint/project records where possible;
- backfill a durable ledger entry;
- mark uncertain scope `UNKNOWN/PENDING` rather than inventing approval;
- request new approval only for materially new scope/risk.

WPEssential remains planning-only; no retrospective implementation approval exists to adopt.

## 5. Development approval summary

Before requesting development approval for a milestone, present a concise summary covering scope, exclusions, options/behaviors, roles/permissions, workflows/states, data/migrations, integrations, security/negative requirements, tests/evidence, affected existing components, compatibility, risks, sequence and rollback/recovery.

Set the work state to `AWAITING_DEVELOPMENT_APPROVAL` only when the applicable planning/audit package is actually ready. Do not start production implementation until unambiguous approval is received and recorded.

## 6. Approval autonomy rule

Once a milestone is approved, do not repeatedly ask approval for ordinary reversible technical decisions already inside documented scope. Escalate when material scope/risk changes, destructive data work exceeds scope, a breaking change becomes necessary, serious security/legal/privacy implications emerge, or privileged production/deployment action requires approval.

## 7. Work lifecycle states

Primary lifecycle states:
- `SPECIFICATION`
- `AUDITING`
- `AWAITING_DEVELOPMENT_APPROVAL`
- `APPROVED`
- `IMPLEMENTING`
- `VERIFYING`
- `BLOCKED`
- `PARTIALLY_COMPLETE`
- `DONE`

Optional: `PAUSED`, `RECOVERY`, `CANCELLED`.

`SPECIFICATION → AUDITING` when documentation is ready for adversarial/self-audit.  
`AUDITING → AWAITING_DEVELOPMENT_APPROVAL` only when material documentation gaps are resolved or explicitly blocked.  
`AWAITING_DEVELOPMENT_APPROVAL → APPROVED` only from explicit scoped owner approval.  
`APPROVED → IMPLEMENTING` only after entry gates/baseline/VCS safety are confirmed.  
`IMPLEMENTING → VERIFYING` when bounded implementation is ready for applicable checks.  
`VERIFYING → DONE` only when Definition of Done is satisfied.

## 8. Stable work IDs

New execution/planning work uses:

`P<phase>-M<milestone>-WP<work-package>-T<task>`

Do not retroactively rename accepted ADR/spec IDs. The same work ID should be reused across checkpoint/PR/evidence when practical and never silently reassigned to unrelated scope.

## 9. Milestone contract

Every executable milestone defines identity, scope/exclusions, dependencies/blockers, entry criteria, approval state, baseline/VCS state, security/negative requirements, data/migration implications, performance/observability, tests/evidence, integration requirements, exit criteria and rollback/recovery class.

## 10. Approval precedence / revocation

Prefer the narrower, newer explicit owner instruction for the same scope; destructive/production-specific restrictions remain unless explicitly lifted; ambiguous consent is not consent. Record supersession rather than deleting history.

Owner may revoke approval at any time. After revocation stop new implementation, preserve evidence, reach a safe checkpoint where non-destructive, record `REVOKED`, and return affected work to planning/blocked state.

## 11. Current WPEssential lifecycle

Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Development approval: `PENDING / NOT GRANTED`  
Current canonical product scope after ADR-0177: **43 planned module/platform surfaces**  
Implementation authorization: **0/43** surfaces  
Implementation WIP: **0**

Historical `0/31` statements refer to the original pre-ADR-0177 scope only.

Current planning work is active on the expanded universal-foundation evidence sequence after ADR-0181; the project is **not** at a global implementation-approval gate while that newly requested planning work remains open.

## 12. Consent invariant

Planning/documentation, ADR acceptance, internet research, evidence-protocol design, `continue`, `resume` and Solution/AI prompt approval do **not** authorize production code, executable spikes, package installation, WordPress runtime execution, DB/schema mutation, provider calls, MCP sessions, AI calls, migrations, tests, benchmarks or deployment.

ADR-0014 remains the hard consent gate.