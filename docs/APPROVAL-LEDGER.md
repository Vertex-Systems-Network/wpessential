# WPEssential — Approval Ledger & Work Lifecycle

Status: **Active governance**  
Last reviewed: 2026-08-28

## 1. Approval scope hierarchy

Approvals are scoped explicitly as one of:

- `TASK`
- `MODULE`
- `MILESTONE`
- `PHASE`
- `PROJECT`

An approval applies only to the documented scope and inherited reversible engineering decisions within that scope.

A broader approval does not automatically authorize a later destructive production action if a separate high-risk approval is required.

## 2. Approval states

Use:
- `PENDING`
- `ACTIVE`
- `REVOKED`
- `EXHAUSTED`
- `SUPERSEDED`

Every durable approval record should include:
- approval ID;
- scope type;
- stable scope/work ID;
- approved-by identity/reference when known;
- approved-at date/time/reference;
- included scope;
- excluded scope;
- risk/destructive exceptions;
- status;
- evidence/source reference.

## 3. Current approval ledger

| Approval ID | Scope | Work ID | Status | Included | Excluded / notes |
|---|---|---|---|---|---|
| GOV-OWNER-CONSENT-000 | PROJECT | WPEssential | PENDING | Production implementation once explicitly granted and technical gates permit | Current owner has **not** granted production development consent. `continue`, planning approval and ADR acceptance are not consent. |

No module, milestone, phase or project implementation approval is currently ACTIVE.

## 4. Existing-project approval adoption

When this governance is introduced into an existing active project:
- do not retroactively invalidate clearly authorized work;
- recover approval evidence from repository/issue/PR/checkpoint/project records where possible;
- backfill a durable ledger entry;
- mark uncertain scope `UNKNOWN/PENDING` rather than inventing approval;
- request new approval only for materially new scope/risk.

WPEssential is currently planning-only, so no retrospective implementation approval exists to adopt.

## 5. Development approval summary

Before requesting development approval for a milestone, present a concise summary covering:
- scope;
- exclusions/non-goals;
- options/behaviors;
- roles/permissions;
- workflows/states;
- data/migrations;
- integrations;
- security/negative requirements;
- tests/evidence;
- affected existing components;
- compatibility;
- risks;
- implementation sequence;
- rollback/recovery.

Set the work state to:

`AWAITING_DEVELOPMENT_APPROVAL`

Do not start production implementation until an unambiguous approval is received and recorded.

## 6. Approval autonomy rule

Once a milestone is approved:
- do not repeatedly ask approval for ordinary reversible technical decisions already inside documented scope;
- proceed autonomously in small verified batches;
- escalate only when material scope/risk changes.

Request additional approval if:
- materially new product behavior appears;
- approved scope changes;
- destructive data action is required beyond accepted scope;
- major breaking change becomes necessary;
- serious security/legal/privacy implications emerge;
- privileged production action/deployment requires explicit authorization.

## 7. Work lifecycle states

Every non-trivial module/milestone/work package uses one primary lifecycle state:

- `SPECIFICATION`
- `AUDITING`
- `AWAITING_DEVELOPMENT_APPROVAL`
- `APPROVED`
- `IMPLEMENTING`
- `VERIFYING`
- `BLOCKED`
- `PARTIALLY_COMPLETE`
- `DONE`

Optional operational states:
- `PAUSED`
- `RECOVERY`
- `CANCELLED`

### State rules

`SPECIFICATION → AUDITING` when documentation is ready for adversarial/self-audit.

`AUDITING → AWAITING_DEVELOPMENT_APPROVAL` only when material documentation gaps are resolved or explicitly blocked.

`AWAITING_DEVELOPMENT_APPROVAL → APPROVED` only from explicit scoped owner approval.

`APPROVED → IMPLEMENTING` only after entry gates/baseline/VCS safety are confirmed.

`IMPLEMENTING → VERIFYING` when bounded implementation is complete enough for applicable checks.

`VERIFYING → DONE` only when Definition of Done is satisfied.

Any unresolved applicable item means `PARTIALLY_COMPLETE`, `BLOCKED`, or another truthful non-DONE state.

## 8. Stable work IDs

New executable planning should use stable hierarchical IDs:

`P<phase>-M<milestone>-WP<work-package>-T<task>`

Examples:
- `P1-M01-WP01-T01`
- `P3-M02-WP04-T03`

Rules:
- do not retroactively rename hundreds of existing ADR/spec IDs;
- existing ADR/evidence IDs remain valid stable references;
- assign work IDs when a unit enters milestone/execution planning;
- use the same ID in issue/PR/checkpoint/test evidence when practical;
- one work ID must not be silently reused for unrelated scope.

## 9. Milestone contract template

Every executable milestone defines:

### Identity
- milestone ID;
- goal;
- owner/agents where known;
- lifecycle state.

### Scope
- included;
- excluded;
- dependencies;
- blockers;
- affected modules/shared surfaces.

### Entry criteria
- required ADR/spec status;
- approval status;
- baseline/VCS state;
- required dependency/runtime availability.

### Engineering gates
- security requirements;
- negative requirements;
- data/migration implications;
- performance/observability requirements;
- tests/evidence;
- integration requirements.

### Exit criteria
- Definition of Done;
- compatibility evidence;
- documentation/checkpoint;
- release/deployment requirement if applicable;
- rollback/recovery classification.

## 10. Approval precedence and conflict

When two approval records appear inconsistent:
1. prefer the narrower, newer explicit owner instruction for the same scope;
2. destructive/production-specific restrictions remain in force unless explicitly lifted;
3. repository governance constraints remain binding;
4. ambiguous consent is not consent.

Record supersession rather than deleting historical approval evidence.

## 11. Revocation

Owner may revoke approval at any time.

After revocation:
- stop new implementation in revoked scope;
- preserve current work/evidence;
- reach a safe checkpoint if doing so is non-destructive;
- update approval state to `REVOKED`;
- return affected work to planning/blocked state.

## 12. Current WPEssential lifecycle

Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Development approval: `PENDING / NOT GRANTED`  
Implementation authorization: `0/31` surfaces.

Current planning resume point remains the checkpoint after ADR-0116.