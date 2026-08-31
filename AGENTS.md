# WPEssential — Engineering Operating Contract

This file is mandatory reading for every human or AI engineering session.

## Source of truth

Repository state + tests + documentation + ADRs + Git history + latest `CHECKPOINT.md` are authoritative. Chat memory is not.

Never assume prior work is complete because it was discussed. Verify repository state and tests.

For actual-state conflicts use the authority order defined in `docs/PROJECT-STATE-AND-ADOPTION.md`. Repository/runtime evidence outranks conversational memory.

## Mandatory governance references

Before meaningful engineering work read/apply as relevant:

- `DEVELOPMENT-CONSENT.md`
- `docs/PROJECT-STATE-AND-ADOPTION.md`
- `docs/APPROVAL-LEDGER.md`
- `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`
- `docs/RELEASE-INCIDENT-RECOVERY-GOVERNANCE.md`
- `docs/QUALITY-GATES.md`
- latest `CHECKPOINT.md`

These files complement existing architecture/module/security ADRs; they do not replace them.

## Default lifecycle

For every meaningful task:

**Inspect → Understand → Research → Assess → Plan → Approval/Consent Gate when required → Implement → Test → Attack → Review → Harden → Document → Commit → Checkpoint → Report**

Do not jump from requirement to code when architecture, data, security, compatibility, dependency, migration or approval decisions are involved.

Prefer:
- correctness over speed
- maintainability over cleverness
- simplicity over unnecessary abstraction
- security by default
- explicit decisions over assumptions
- reusable platform contracts over duplicate module logic
- tested behavior over claims
- stable dependencies over novelty
- reversible changes over destructive shortcuts
- small batches over giant AI diffs
- repository evidence over conversational memory

## Project-state and capability detection

At session start identify the canonical project state from:

- `GREENFIELD`
- `PLANNED_EXISTING_PROJECT`
- `ACTIVE_EXISTING_PROJECT`
- `PRODUCTION_PROJECT`
- `LEGACY_OR_MIGRATION`
- `RECOVERY`

Also identify actual execution mode/capabilities. Never claim terminal, database, runtime, CI, deployment, VCS protection or provider abilities that were not verified.

If a provider/tool cannot expose a fact, record `UNKNOWN`/`UNAVAILABLE`; do not infer it.

Current WPEssential baseline is maintained in `docs/PROJECT-STATE-AND-ADOPTION.md`.

## Start-of-session protocol

Before coding:

1. Read this file.
2. Read `DEVELOPMENT-CONSENT.md` and `docs/APPROVAL-LEDGER.md`.
3. Read `CHECKPOINT.md` and `docs/PROJECT-STATE-AND-ADOPTION.md`.
4. Detect actual project state, execution mode and available capabilities.
5. Read the relevant product/module/architecture docs.
6. Read applicable ADRs.
7. Inspect current VCS status/branch/revision and recent relevant history where accessible.
8. Inspect relevant existing implementation and tests.
9. Identify unfinished work, baseline failures and known risks.
10. Verify available build/test commands and applicable FAST/FULL gates.
11. Re-run relevant validation if the checkpoint is stale or uncertain and execution is authorized.
12. Only then plan/implement within the approved scope.

## Existing-project adoption protocol

For an existing planned/developed project do not restart or rebuild it from zero.

Use:

**Inspect → Baseline → Audit Existing Plan → Compare Plan With Reality → Identify Gaps → Amend Plan → Preserve Existing Work → Continue Safely**

Maintain Plan→Repository and Repository→Plan status as defined in `docs/PROJECT-STATE-AND-ADOPTION.md`.

Classify newly discovered gaps as:
- `CORRECTION`
- `COMPLETION`
- `HARDENING`
- `OPTIMIZATION`
- `NEW_PRODUCT_SCOPE`

`NEW_PRODUCT_SCOPE` is never silently approved merely because it appears useful.

## Resume protocol

When resuming work:

1. verify latest checkpoint;
2. inspect commits since it;
3. verify actual files and tests/evidence;
4. verify current approval/work lifecycle state;
5. identify partial/failed work and baseline failures;
6. continue from the safest verified point.

Never restart completed work without evidence that it is invalid.

`continue`/`resume` never overrides a pending approval state.

## External research rule

Research current official/primary sources when a decision depends on changing external facts, APIs, versions, standards, security guidance, licensing, third-party builders/providers, or WordPress behavior.

Prioritize:
1. WordPress/core official docs and source
2. official framework/library/provider docs
3. OWASP/standards bodies where applicable
4. official competitor docs for product benchmarking
5. reputable secondary sources only when primary sources are insufficient

Before implementing each module, refresh its competitor/API research recorded under `docs/RESEARCH/`.

Do not claim research that was not performed.

## Architecture-before-code rule

Before a substantial feature:

- inspect existing architecture;
- identify shared services it should reuse;
- identify public/internal APIs and dependents;
- identify data ownership and migrations;
- identify authorization boundary;
- identify failure/recovery behavior;
- identify performance risks;
- identify compatibility/integration impact;
- choose the smallest maintainable implementation.

Before adding a new service/library/pattern ask:
- does WPEssential already solve this?
- can the current abstraction be extended safely?
- what complexity/runtime/bundle cost is introduced?
- how is it tested?
- what if the dependency disappears?
- who maintains it in six months?

WPEssential is one platform, not isolated mini-plugins.

## Preserve existing work

Do not unnecessarily:
- rewrite working code;
- delete behavior;
- rename broad namespaces/APIs;
- replace dependencies;
- remove tests to make CI pass;
- discard historical decisions;
- overwrite configuration blindly.

During feature work also follow the no-unrelated-cleanup and small-batch rules in `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`.

For high-impact/breaking changes document:
- reason;
- affected consumers;
- migration;
- compatibility impact;
- rollback/recovery;
- verification.

## Change-impact protocol

For substantial modifications explicitly record:

**Affected → Unaffected → Risk → Migration → Rollback/Recovery → Verification**

If the actual change expands materially beyond the estimated file/module/API/migration/dependency/config budget, stop and reassess rather than letting scope creep become an oversized diff.

## Milestones, work packages and approvals

Use milestone-level approval for substantial systems where practical.

New execution planning should use stable work IDs per `docs/APPROVAL-LEDGER.md`:

`P<phase>-M<milestone>-WP<work-package>-T<task>`

Do not retroactively rename existing ADR/evidence IDs.

Every executable milestone defines goal, included/excluded scope, dependencies, blockers, entry/exit criteria, security/data/test/integration requirements, deployment and rollback/recovery.

Approval scopes are `TASK`, `MODULE`, `MILESTONE`, `PHASE`, or `PROJECT` and must be recorded durably. Once a milestone is approved, ordinary reversible implementation decisions inside documented scope do not require repeated owner approval.

## Safe parallel development

Classify concurrent work:
- `PARALLEL_SAFE`
- `COORDINATED_PARALLEL`
- `SERIALIZE`
- `BLOCKED`

Follow shared-surface ownership, WIP limits, critical-path classification and merge-order rules in `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`.

Never allow multiple autonomous agents to silently overwrite migrations, lockfiles, authorization core, global configuration, shared API schemas, central routing, CI/build or other serialized shared surfaces.

## Security contract

Read `docs/SECURITY.md` before security-sensitive work.

Always consider, where applicable:
- authentication
- capabilities/resource authorization
- CSRF
- validation/sanitization/output escaping
- XSS
- SQL injection
- SSRF
- IDOR
- upload/archive safety
- secrets
- session/token security
- rate limits/abuse
- privilege escalation
- CORS
- dependency/supply-chain risk
- multisite boundaries
- sensitive logging
- recovery from destructive actions

Never hard-code credentials/tokens/secrets.

Never weaken security only to simplify development.

### Explicit prohibited default patterns

Unless a future accepted ADR says otherwise:
- no `eval()` / arbitrary user-entered PHP execution;
- no arbitrary destructive SQL console as a standard product feature;
- no permission enforcement only in React/UI;
- no AI/MCP privileged bypass;
- no secrets in frontend bootstrap/localized script data;
- no global optional-module asset enqueue;
- no URL hiding presented as authentication/security.

### Negative requirements

Substantial module/milestone specs must state important `MUST NOT` behavior. Critical negative rules become automated/adversarial tests where applicable.

## Data integrity

Before data/schema changes review:
- existing data;
- types/nullability/defaults;
- uniqueness/constraints;
- indexes/query patterns;
- relationships;
- transactions/concurrency;
- migration size/downtime;
- rollback/restore;
- multisite scope;
- backup implications.

Prefer reversible migrations. For risky schema evolution, consider `Expand → Migrate/Backfill → Verify → Contract` when it materially reduces deployment risk.

When rollback is not practical, document the restore/recovery route before merging.

## Performance

Do not prematurely optimize, but reject obvious avoidable cost.

Review:
- query count/N+1;
- indexes;
- payload size;
- pagination;
- memory;
- rendering/bundle cost;
- background work;
- network calls;
- cache invalidation;
- large datasets/imports/backups.

Optional-module CSS/JS must only load where needed. Asset isolation is a tested product requirement.

## External-service resilience

Assume every external service can fail with:
- timeout;
- DNS/network failure;
- bad credentials;
- expired/revoked token;
- rate limiting;
- malformed/partial response;
- provider outage;
- duplicate/replayed request.

Design retries only where safe and idempotent. Never silently swallow errors.

User-facing errors must be actionable and production-safe; never expose stack traces, SQL details, credentials or internal secrets.

## Observability

Where meaningful provide:
- structured logs/events;
- correlation/run IDs;
- health/diagnostic state;
- job/workflow history;
- integration failure metadata;
- Site Health integration;
- privacy-safe support bundle data.

Never log secrets or unnecessary sensitive content.

## Tests and quality gates

Read `docs/QUALITY-GATES.md` and `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`.

Use two speeds:
- `FAST GATE` during bounded implementation;
- `FULL GATE` at milestone/release boundaries.

A meaningful feature is complete only after the applicable checks execute:
- formatting
- linting
- static analysis/typecheck
- build
- unit tests
- WordPress integration tests
- REST/API tests
- E2E tests
- migration tests
- security regression tests
- compatibility tests
- dependency audits
- release/package checks

Tests are risk-driven, not coverage-percentage theater.

Always include relevant:
- happy path
- invalid input
- boundary/empty state
- unauthorized/forbidden state
- failure/timeout state
- concurrency/idempotency where relevant
- recovery/rollback
- regression scenario
- important negative/MUST-NOT behavior.

Do not change a correct test merely to accommodate incorrect implementation.

A pre-existing failure is `BASELINE FAILURE`, not automatically a regression. A flaky test is a defect; rerun-until-green is not acceptable evidence.

If a check cannot run, record exactly what, why, and how it will be verified.

## UI/UX/accessibility

User-facing work must include:
- responsive behavior;
- semantic HTML;
- keyboard support;
- visible focus;
- screen-reader semantics;
- loading/empty/error/success states;
- disabled states;
- confirmation and recovery for destructive work;
- consistent builder interaction grammar;
- no information communicated by color alone.

Use the approved design-system ADR. Do not import paid/proprietary Untitled UI assets unless licensing has been explicitly reviewed and approved.

## Dependency policy

Before adding any package:
- check existing equivalent;
- verify active maintenance;
- verify supported WP/PHP/Node/browser versions;
- inspect license;
- review known security issues;
- assess transitive/runtime/bundle impact;
- document why it is justified.

Do not add a dependency for trivial code that is safer to maintain locally.

Lockfiles are required for distributable builds where applicable.

## Documentation policy

Important knowledge must live in the repo, not only in conversation.

Update the relevant:
- project state/adoption baseline;
- approval/work lifecycle ledger;
- architecture;
- module specification;
- research note;
- ADR;
- security notes;
- API docs;
- migration notes;
- changelog/release notes;
- troubleshooting;
- checkpoint.

Do not create documentation for volume; it must help the next engineer make a correct decision.

## ADR policy

Create/update an ADR when a decision materially affects:
- public architecture/contracts;
- compatibility floor;
- data/schema ownership;
- dependency/platform choice;
- security model;
- Free/Pro distribution;
- licensing;
- migration/rollback;
- job/runtime execution;
- secrets;
- AI exposure.

Accepted ADRs are not silently reversed. Supersede them with a new ADR that explains why.

## Git/VCS history and protection

Commits must be small, coherent and reversible where practical.

Good messages explain intent, e.g.:
- `feat(fields): add typed date validation`
- `fix(rest): enforce resource capability on relation delete`
- `docs: record Action Scheduler decision`

Do not use meaningless messages such as `update`, `changes`, `fix stuff`, `late work`, `final`.

Do not rewrite shared history unless explicitly authorized.

Inspect provider protections where accessible: required reviews/checks, CODEOWNERS, rulesets/protected branches, merge queue/train, tags/releases, deployment approvals and security scans. If inaccessible, record `UNKNOWN`; never weaken protection simply to merge faster.

## Checkpoints

After a meaningful unit of work update `CHECKPOINT.md` with:
- current project/execution/work lifecycle state;
- current branch/phase/work ID where assigned;
- completed work;
- verification/tests;
- baseline/flaky/known failures;
- important decisions;
- active files/areas;
- approvals/blockers/risks;
- exact next safe action.

Before a long/risky operation create or confirm a recoverable VCS point.

## AI-native rules

AI must compose approved WPEssential actions; it must not become a privileged execution channel.

Where practical expose operations as typed WordPress Abilities with:
- stable name;
- input/output JSON schema;
- permission callback;
- read/write/destructive classification;
- dry-run where meaningful;
- audit metadata.

AI/MCP integrations are opt-in and ability-allowlisted. Model output is untrusted input and must pass the same validation/policy layer as UI/REST calls.

Generated destructive changes require preview/diff/confirmation according to policy.

## Autonomy and ambiguity

Make reversible, low-risk decisions independently when requirements, approval and architecture are clear.

Do not repeatedly ask questions that repository inspection or legitimate research can answer.

Escalate/ask only when:
- requirements materially conflict;
- approved scope materially changes;
- the decision is irreversible/high-risk;
- security/legal/data-loss behavior is genuinely ambiguous;
- external credentials/human approval are necessary;
- privileged production action requires explicit authorization.

When minor behavior is unspecified:
1. inspect conventions/docs;
2. research current best practice if needed;
3. choose the simplest production-appropriate behavior;
4. record important assumptions.

Do not invent major product requirements.

## Technical debt

Classify discovered debt:
- Critical
- High
- Medium
- Low

Fix Critical/High debt when it directly threatens current work. Record lower debt in the maintained backlog/checkpoint rather than forgetting it.

## Review classification

Every meaningful review must be labeled truthfully:
- `INDEPENDENT REVIEW`
- `SELF REVIEW`
- `AUTOMATED REVIEW`

The same AI/person reviewing its own work is `SELF REVIEW`, not independent review.

## Release and incident safety

Read `docs/RELEASE-INCIDENT-RECOVERY-GOVERNANCE.md` before release/high-risk recovery work.

Distinguish:
- `BUILT`
- `DEPLOYED`
- `RELEASED`
- `PRODUCTION_VERIFIED`

Classify recovery:
- `SIMPLE_ROLLBACK`
- `ROLLBACK_WITH_COMPATIBILITY`
- `FORWARD_FIX_PREFERRED`
- `IRREVERSIBLE`

On a production incident switch to:

`STABILIZE → CONTAIN → PRESERVE EVIDENCE → DIAGNOSE → RECOVER → VERIFY → ROOT CAUSE → PREVENT RECURRENCE`

Stop affected work immediately for the stop-the-line triggers documented there.

## Planner-only mode

When execution is unavailable or prohibited, set `EXECUTION_MODE = PLANNER_ONLY` and mark code/test/build/deployment outcomes `NOT EXECUTED`.

Planning artifacts never count as runtime evidence.

## No fake completion

Never claim:
- a test passed if it was not run;
- research occurred if it did not;
- a feature is secure without meaningful review;
- deployment succeeded without verification;
- migration is safe without existing-data analysis;
- bug fixed without reproducing/verifying expected behavior.

Use:
- **Verified**
- **Not Verified**
- **Known Risk**
- **Next Action**

when useful.

## Definition of Done

A task is **DONE** only when applicable approved implementation, integration, security, errors, data integrity, performance, tests, documentation, VCS history, checkpoint, migration/recovery and observability are complete and verified.

Otherwise report `PARTIALLY_COMPLETE`, `VERIFYING`, `BLOCKED` or another truthful lifecycle state.

## End-of-task engineering report

Concisely report:
- **Status**
- **Changed**
- **Why**
- **Research performed**
- **Tests/checks**
- **Security**
- **Data/migration**
- **Affected areas**
- **VCS/commit**
- **Documentation/Memory updated**
- **Known issues**
- **Not verified**
- **Next safe action**

The goal is a secure, maintainable, testable, observable, documented, recoverable production system with trustworthy engineering history—not merely code that appears to work.