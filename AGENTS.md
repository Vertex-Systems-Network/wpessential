# WPEssential — Engineering Operating Contract

This file is mandatory reading for every human or AI engineering session.

## Source of truth

Repository state + tests + documentation + ADRs + Git history + latest `CHECKPOINT.md` are authoritative. Chat memory is not.

Never assume prior work is complete because it was discussed. Verify repository state and tests.

## Default lifecycle

For every meaningful task:

**Inspect → Understand → Research → Assess → Plan → Implement → Test → Attack → Review → Harden → Document → Commit → Checkpoint → Report**

Do not jump from requirement to code when architecture, data, security, compatibility, dependency, or migration decisions are involved.

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

## Start-of-session protocol

Before coding:

1. Read this file.
2. Read `CHECKPOINT.md`.
3. Read the relevant product/module/architecture docs.
4. Read applicable ADRs.
5. Inspect current Git status/branch and recent commits.
6. Inspect relevant existing implementation and tests.
7. Identify unfinished work and known risks.
8. Verify available build/test commands.
9. Re-run relevant validation if the checkpoint is stale or uncertain.
10. Only then plan/implement.

## Resume protocol

When resuming work:

1. verify latest checkpoint;
2. inspect commits since it;
3. verify actual files and tests;
4. identify partial/failed work;
5. continue from the safest verified point.

Never restart completed work without evidence that it is invalid.

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

For high-impact/breaking changes document:
- reason;
- affected consumers;
- migration;
- compatibility impact;
- rollback/recovery;
- verification.

## Change-impact protocol

For substantial modifications explicitly record:

**Affected → Unaffected → Risk → Migration → Rollback → Verification**

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

Prefer reversible migrations. When rollback is not practical, document the restore/recovery route before merging.

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

Read `docs/QUALITY-GATES.md`.

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

Do not change a correct test merely to accommodate incorrect implementation.

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
- architecture
- module specification
- research note
- ADR
- security notes
- API docs
- migration notes
- changelog/release notes
- troubleshooting
- checkpoint

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

## Git history

Commits must be small, coherent and reversible where practical.

Good messages explain intent, e.g.:
- `feat(fields): add typed date validation`
- `fix(rest): enforce resource capability on relation delete`
- `docs: record Action Scheduler decision`

Do not use meaningless messages such as `update`, `changes`, `fix stuff`, `late work`, `final`.

Do not rewrite shared history unless explicitly authorized.

## Checkpoints

After a meaningful unit of work update `CHECKPOINT.md` with:
- current branch/phase;
- completed work;
- verification/tests;
- known failures/risks;
- important decisions;
- active files/areas;
- next recommended action.

Before a long/risky operation create or confirm a recoverable Git point.

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

Make reversible, low-risk decisions independently when requirements and architecture are clear.

Do not repeatedly ask questions that repository inspection or legitimate research can answer.

Escalate/ask only when:
- requirements materially conflict;
- the decision is irreversible/high-risk;
- security/legal/data-loss behavior is genuinely ambiguous;
- external credentials/human approval are necessary.

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

A task is **DONE** only when applicable implementation, integration, security, errors, data integrity, performance, tests, documentation, Git history, checkpoint, migration/recovery and observability are complete and verified.

Otherwise report **PARTIALLY COMPLETE**.

## End-of-task engineering report

Concisely report:
- What changed
- Why
- Research performed
- Tests/checks performed
- Security considerations
- Files/components affected
- Commit/checkpoint
- Known issues
- Recommended next action

The goal is a secure, maintainable, testable, observable, documented, recoverable production system with trustworthy engineering history—not merely code that appears to work.
