# WPEssential — Engineering Operating Contract

This file is mandatory reading for every human or AI engineering session.

## Source of truth

Repository/runtime state + tests + documentation + ADRs + Git history are authoritative. `.ai/state/CURRENT-STATE.yaml` and `.ai/state/LAST-CHECKPOINT.md` are mandatory compact resume indexes; `CHECKPOINT.md` remains historical evidence. Chat memory is not.

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
- `.ai/state/CURRENT-STATE.yaml`
- `.ai/state/LAST-CHECKPOINT.md`
- `config/coordination/runner-benchmark.json`
- `docs/AI/RUNNER-BENCHMARK-EXECUTION-POLICY.md`
- `docs/AI/TIMEOUT-RESILIENT-EXECUTION-POLICY.md`
- relevant `CHECKPOINT.md` sections only when historical evidence is needed

These files complement existing architecture/module/security ADRs; they do not replace them.

## Default lifecycle

For every meaningful task:

**Compact State → Refresh Main → Issues First → PRs/MRs Second → Queue → Runner Benchmark → Inspect → Understand → Research → Assess → Plan → Approval/Consent Gate when required → Implement one logical milestone → Fast/Local Verify → Capture Runner Tasks → Immediate Safety/Merge Runner Exceptions → Review → Harden → Document → Commit → Final Consolidated Runner Batch only at closeout → Durable Compact State → Conditional README Progress Reconciliation → Report**

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
3. Read `.ai/state/CURRENT-STATE.yaml`, `.ai/state/LAST-CHECKPOINT.md`, and `docs/PROJECT-STATE-AND-ADOPTION.md`; consult only relevant `CHECKPOINT.md` sections when historical evidence is needed.
4. Detect actual project state, execution mode and available capabilities.
5. Read the relevant product/module/architecture docs.
6. Read applicable ADRs.
7. Resolve exact current `main`/VCS revision and inspect recent relevant history where accessible.
8. **Inspect OPEN Issues first.** Triage dependency-ready/accepted unfinished issues and continue/solve them before inventing new work.
9. **Inspect OPEN PRs/MRs second.** Review mergeability, exact-head CI, review threads, conflicts and dependencies; fix stale/failing accepted PRs and merge merge-ready work before starting new implementation.
10. Re-read active deterministic claim branches and `config/coordination/agent-work-queue.json` after issue/PR reconciliation.
11. Reconcile `config/coordination/runner-benchmark.json`: capture newly discovered material runner work, preserve authorization blocks, and identify mandatory immediate exceptions.
12. Inspect relevant existing implementation and tests.
13. Identify unfinished work, baseline failures and known risks.
14. Verify available build/test commands and applicable FAST/FULL gates.
15. Re-run only the validation that is required now by security, merge protection, migration/auth/secrets/data safety, incident recovery, or current-change integration safety; otherwise capture eligible material runner work for the final batch.
16. Only then plan/implement within the approved scope, including explicit Runner Benchmark impact.

An issue already represented by an open PR/MR must not be duplicated by a new branch unless repository evidence explicitly supersedes the existing path. PR/MR-first cleanup after issue triage does not authorize unsafe merges; all dependency, review and exact-head gates remain mandatory.

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

1. read `.ai/state/CURRENT-STATE.yaml` and `.ai/state/LAST-CHECKPOINT.md`;
2. resolve exact current main and compare it with the compact observed anchor;
3. inspect OPEN Issues first;
4. inspect/fix/merge eligible OPEN PRs/MRs second;
5. inspect commits since the compact observed anchor and queue anchor;
6. verify actual files and tests/evidence;
7. verify current approval/work lifecycle state;
8. identify partial/failed work and baseline failures;
9. re-read active deterministic claims/queue after any accepted merge;
10. reconcile the Runner Benchmark and do not silently rerun historical/authorization-gated evidence;
11. consult only the relevant historical `CHECKPOINT.md` section when needed;
12. continue from the safest verified point.

Never restart completed work without evidence that it is invalid. A missing/delayed chat response is not evidence that repository work failed.

`continue`/`resume` never overrides a pending approval state.

### Timeout-resilient turn boundary

Policy `GOV-AI-NATIVE-TIMEOUT-RESILIENCE-001` is mandatory. By default **one user `continue`/`resume` turn = one logical engineering milestone**. Batch related remote reads, do not tight-poll CI/status endpoints, and use at most one consolidated CI/status refresh per milestone unless a documented security/merge/incident transition requires one additional safe-decision refresh.

If required external CI is still running, write compact state as `WAITING_EXTERNAL` with exact run/source identity and next safe action, then end the milestone. The next `continue` performs one fresh consolidated status check.

Before reporting a repository-changing milestone complete/blocked/waiting, durably reconcile the compact state files and any changed queue/Runner Benchmark truth.

## Runner Benchmark and final-batch rule

Material runner-dependent work is governed by `config/coordination/runner-benchmark.json` and `docs/AI/RUNNER-BENCHMARK-EXECUTION-POLICY.md`.

For every material CI/cloud/container/browser/runtime/matrix/performance/full-regression runner task:

1. assign or reuse a stable `RB-####` entry before execution or deferral;
2. record source, workflow/command, exact environment/fixture/input identity, dependencies, authorization, merge/security classification, expected runner time and dedup key;
3. default to `DEFER_FINAL_BATCH` when the task is non-blocking and safe to postpone;
4. execute immediately when it is security-critical, exact-head merge-required, migration/auth/secrets/data safety-critical, required to validate the current integration, or incident/recovery work;
5. record immediate-exception evidence back into the benchmark;
6. deduplicate equivalent tasks before the final consolidated runner batch;
7. treat deferred work as **NOT EXECUTED**, never PASS.

The final runner batch happens at milestone/integration closeout after intended implementation has settled and before terminal milestone/release claims.

Runner batching never bypasses branch protection or approval boundaries. A benchmark entry does not grant runtime, destructive, provider, production, deploy, release or formal evidence authority. Authorization-gated items remain `BLOCKED_AUTHORIZATION` until a valid current grant explicitly covers them; consumed or historical grants are never reused.

Every implementation plan must state its **Runner Benchmark impact**: expected `RB-####` entries, deferred vs immediate classification, expected runner minutes, dependencies/authorization, and dedup/final-batch grouping. Record `none` when no material runner task is introduced.

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
- checkpoint;
- README current module progress/status dashboard after meaningful completed work cycles.

Do not create documentation for volume; it must help the next engineer make a correct decision.

README progress percentages must be evidence-based and scoped to the currently approved/certified bounded implementation milestone. Do not present a bounded baseline percentage as full product parity. Planning-only modules without a defined implementation baseline must not receive fabricated percentages.

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

After a meaningful state transition update the compact resume layer first:

- `.ai/state/CURRENT-STATE.yaml` — current observed anchor, active issue/PR/branch, milestone status, blockers, Runner Benchmark state and exact next safe action;
- `.ai/state/LAST-CHECKPOINT.md` — short human-readable recovery checkpoint;
- `.ai/state/EXECUTION-JOURNAL.md` — rolling meaningful transitions only.

The size caps and durable-write-before-report rule in `docs/AI/TIMEOUT-RESILIENT-EXECUTION-POLICY.md` are mandatory.

`CHECKPOINT.md` is long-form historical evidence. Update it when a milestone needs durable historical/evidence detail, but do not require a full-file read or append on every small `continue` turn.

README full 56-surface reconciliation is required when module lifecycle/progress/timeline/public delivery truth changed or at a terminal product milestone/integration closeout. Governance/security/coordination-only cycles reconcile compact state and only materially affected public status, avoiding no-op dashboard churn.

Before a long/risky operation create or confirm a recoverable VCS point.

## AI-native rules

AI must compose approved WPEssential actions; it must not become a privileged execution channel.

AI-Native work control also obeys the mandatory operational order:

**Compact State → Exact Main → OPEN Issues → OPEN PRs/MRs → Claims/Queue → Runner Benchmark → One Logical Milestone → Exact-Head Verification/Merge when applicable → Durable Compact State → Conditional README Module Progress Reconciliation → Report**

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

For a meaningful completed work cycle, Definition of Done also includes Supervisor reconciliation of README current status and module-wise progress bars/table, or an explicit durable blocker explaining why that shared-truth update could not be made.

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
- **README module progress updated**
- **Known issues**
- **Not verified**
- **Next safe action**

The goal is a secure, maintainable, testable, observable, documented, recoverable production system with trustworthy engineering history—not merely code that appears to work.
