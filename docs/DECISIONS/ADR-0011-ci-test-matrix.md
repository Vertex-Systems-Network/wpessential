# ADR-0011 — CI and Compatibility Test Matrix

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27  
Static/repository audit refreshed: 2026-08-28

## Context

WPEssential spans PHP, React/TypeScript, WordPress APIs, database migrations, REST, jobs, builder adapters, Free/Pro compatibility, external providers and destructive/recovery operations. A single “latest PHP + latest WordPress” job cannot prove production compatibility.

Current repository evidence is important:
- `.github/` on `planning/master-architecture` currently contains the PR template only;
- no `.github/workflows/` implementation has been verified;
- current CI execution capability, branch-protection required checks and repository rulesets are therefore **not established by repository content**;
- earlier provider/protection reads have been incomplete/permission-limited, so protection state must remain `UNKNOWN` unless directly verified later.

The CI contract must be semantically provider-neutral even if GitHub Actions becomes the natural first adapter for this GitHub repository. Moving VCS/CI provider must not change what counts as FAST/FULL, compatibility, security or release evidence.

WordPress Playground is a candidate for fast clean environments, but conventional containers/services remain necessary where real database locking/filesystem/network/provider behavior is required. Exact CI/environment tooling remains executable evidence, not a planning claim.

## Proposed decision

Adopt layered, risk-based CI with explicit evidence provenance rather than one giant Cartesian matrix.

### Lane A — PR / FAST Gate

Required change-relevant checks after implementation exists:
- repository/secret hygiene;
- PHP syntax/coding standards/static analysis;
- JS/TS/style lint/typecheck;
- affected unit tests;
- affected WordPress integration/permission tests;
- affected production build;
- P-008 externalization/asset checks;
- targeted security/negative-requirement tests;
- minimum/current representative environment smoke where cost permits;
- install/activate actual built artifact when the change can affect packaging/runtime.

FAST Gate remains change-targeted. It never substitutes a required FULL Gate.

### Lane B — main/integration / FULL-contract expansion

Broader combinations:
- P-001 selected minimum/current WordPress/PHP/DB profiles;
- single-site and claimed Multisite paths;
- broader unit/integration/permission/security suites;
- migration/upgrade fixtures;
- Free-only and Free+Pro matrices when artifacts exist;
- actual ZIP/package install verification;
- critical E2E paths;
- UI/build regression gates from P-002/P-008.

### Lane C — scheduled/nightly / compatibility early warning

Risk-based extended lanes:
- WordPress trunk/nightly as informational/early-warning until explicitly promoted;
- extended supported PHP/DB combinations;
- persistent cache/cron/large data/background-work profiles;
- page-builder/WooCommerce/integration-version profiles;
- performance and long-running regression evidence;
- historical migration fixtures.

Avoid a full Cartesian explosion when pairwise/risk-based coverage proves the same contract more efficiently.

### Lane D — provider/certification

External provider lanes are isolated from untrusted PR code and ordinary required PR checks.

They may use dedicated sandbox/test credentials only after the provider integration exists and is authorized.

Provider evidence must preserve its own certification model (I0–I5, ET0–ET5, C0–C4, MB0–MB5, etc.); a generic CI green check never upgrades a provider certification.

### Lane E — release candidate/artifact

Before a releasable claim:
- required FAST/FULL gates complete;
- supported compatibility profiles complete;
- security/migration/recovery gates complete as applicable;
- built Free/Pro artifact(s) inspected and installed/tested;
- no dev-only/prohibited/secret material in ZIP;
- metadata/version/dependency/license/translation/RTL consistency verified;
- rollback/recovery notes appropriate to release risk exist;
- signed/update metadata gates are handled by their own accepted protocols where applicable.

## CI trust boundary

CI configuration is privileged production infrastructure.

Rules:
- untrusted fork/PR code never receives production/provider/release secrets;
- secret-bearing/provider/release jobs run only from trusted refs/approval contexts with explicit environment policy;
- pull-request event model must be selected so attacker-controlled code cannot execute with privileged repository token merely to obtain test coverage;
- workflow-generated artifacts/logs are treated as potentially sensitive and scanned/redacted;
- dependency/install scripts are part of the supply-chain threat model;
- third-party CI actions/plugins require version/publisher/permission review and preferably immutable pinning according to future policy;
- CI tokens get minimum permissions by lane;
- release/signing credentials are separated from normal test credentials;
- cache keys/content must not allow untrusted branch poisoning to become trusted release input without validation.

## Baseline, flaky and rerun truth

CI must implement the governance already defined in `docs/QUALITY-GATES.md`:

- verified pre-existing failure → `BASELINE FAILURE` with evidence;
- insufficient evidence → `UNKNOWN/INVESTIGATING`;
- flaky tests are defects;
- rerun-until-green without disclosure is forbidden;
- quarantine requires issue/owner/reason/expiry/blocking classification/replacement evidence;
- infrastructure/provider outage is classified separately from product pass/fail;
- a manually rerun job retains prior failure evidence in the task/release report.

## P-001 / P-002 / P-008 composition

P-007 consumes, but does not replace:
- P-001/CF environment compatibility contract;
- P-002/UI runtime/accessibility/RTL/asset-isolation contract;
- P-008/BT build/externalization/actual-ZIP contract.

CI cannot declare those ADRs accepted simply because some workflow jobs are green. Their fixed fixture evidence remains separately reportable.

## Artifact provenance

Every artifact-bearing CI lane must be able to answer:
- source commit/ref;
- workflow/config revision;
- dependency lock state;
- build tool/runtime versions;
- compatibility environment profile;
- artifact hash/identity;
- checks actually executed against that exact artifact;
- whether artifacts came from trusted or untrusted context.

Source-tree tests do not prove a different ZIP.

## Branch protection / required checks

The desired policy is:
- minimum/current safety and security checks that protect normal merges become required after their reliability/cost are proven;
- nightly/trunk/provider informational lanes are not blindly made merge blockers;
- release gates block release even when they are not PR branch-protection checks.

Current repository branch-protection/ruleset state is **UNKNOWN** and must not be asserted from this ADR.

## Acceptance work

ADR-0011 remains Proposed until authorized future evidence proves:
1. a fixed P-007 protocol passes on the chosen CI adapter(s);
2. P-001/P-002/P-008 executable commands/artifacts exist sufficiently for CI consumption;
3. trusted/untrusted secret isolation is verified;
4. FAST/FULL/baseline/flaky classifications are implemented and observable;
5. minimum/current environment jobs are reproducible;
6. actual built ZIP is verified, not merely source tree;
7. execution duration/cost/reliability is measured;
8. required vs informational checks are explicitly recorded;
9. branch protection/release gating can be configured and verified when the capability is available.

No workflow file, runner, test environment, dependency or CI execution is authorized by this ADR.
