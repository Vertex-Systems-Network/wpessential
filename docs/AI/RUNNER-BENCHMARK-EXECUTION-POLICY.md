# AI-Native Runner Benchmark Execution Policy

Status: **ACTIVE / GOVERNANCE**
Source: **Issue #1109**
Policy ID: `GOV-AI-NATIVE-RUNNER-BENCHMARK-001`

## Purpose

WPEssential keeps material runner-dependent work in one durable registry:

`config/coordination/runner-benchmark.json`

The goal is to avoid repeatedly spending CI/runtime capacity while normal development is still changing. Eligible non-blocking runner work is captured as it is discovered, deduplicated, and executed together in a consolidated final runner batch at milestone/integration closeout.

This is an execution-order policy, not an excuse to skip required verification.

## Mandatory development flow

For every meaningful AI-Native development cycle:

**Refresh Main → Issues → PRs/MRs → Queue → Runner Benchmark → Inspect/Plan → Implement → Fast/local verification → Capture new runner work → Immediate safety/merge exceptions → Continue development → Final consolidated runner batch → Closeout**

A material runner task receives a stable `RB-####` ID when it is discovered, even when it will execute immediately.

Each task records at minimum:

- source issue/PR/work package;
- category and workflow/command;
- environment/matrix/fixture identity;
- dependencies;
- authorization state;
- merge-blocking/security classification;
- expected runner time;
- deterministic deduplication key;
- disposition/status;
- terminal evidence when available.

## Default: defer to the final batch

Use `DEFER_FINAL_BATCH` for runner work that:

- is not required to prove the safety of the current change;
- is not required by branch protection or the current PR merge contract;
- is not security-critical;
- is not needed to unblock migration/auth/secrets/data correctness;
- can remain pending without making an implementation claim false.

Deferred means **NOT EXECUTED**, not PASS.

Normal development may continue while these entries accumulate.

## Immediate exceptions

Execute the applicable runner task now when it is classified as any of:

- `SECURITY_CRITICAL`;
- `MERGE_REQUIRED_EXACT_HEAD`;
- `MIGRATION_AUTH_SECRETS_DATA_SAFETY`;
- `CURRENT_CHANGE_INTEGRATION_SAFETY`;
- `INCIDENT_OR_RECOVERY`.

Immediate exceptions are still benchmark entries. Their result can later be reused by the final batch only when the exact source/environment/input identity matches the deduplication key.

Required branch-protection and exact-head merge checks are never deferred merely to save runner capacity.

## Authorization boundary

A Runner Benchmark entry **never grants authority**.

If a task needs separate authorization for formal runtime evidence, destructive migration, provider/license execution, production/live data, deployment, release, or another gated capability, its state remains:

`BLOCKED_AUTHORIZATION`

That item is excluded from the final executable batch until a valid current authorization explicitly covers it.

Consumed, expired, unrelated or historical grants are never reused. “Run everything at the end” does not override an approval gate.

## Deduplication

Before executing the final batch, compute task equivalence from:

**workflow/command + exact source SHA + environment/matrix + fixture/input identity**

Equivalent work executes once. All dependent work items point to the same immutable evidence rather than launching duplicate runners.

A new source SHA, changed fixture/input identity, changed runtime matrix, or changed security-sensitive dependency normally creates a new dedup identity.

## CI/status observation budget

Runner execution and runner observation are separate concerns.

Under `GOV-AI-NATIVE-TIMEOUT-RESILIENCE-001`:

- **tight CI/status polling is forbidden**;
- related runner/status reads should be consolidated;
- one consolidated status refresh per logical milestone is the default;
- when required CI remains in progress, compact state records `WAITING_EXTERNAL`, exact run/source identity and next action, then the milestone stops;
- the next user `continue` performs a fresh consolidated refresh;
- no workflow is rerun merely because a chat/message response timed out.

Security/merge/incident transitions may justify one additional same-milestone refresh when it is necessary for a safe decision; the exception is recorded durably.

## Final consolidated runner batch

The final batch occurs at the milestone/integration closeout after intended implementation has settled and before terminal milestone/release claims.

Entry conditions:

1. normal implementation for the batch is integrated or frozen;
2. every runner task has a stable `RB-####`, dedup key, dependency state and authorization state;
3. mandatory immediate exceptions have already run where required;
4. authorization-blocked entries remain blocked and are not silently included;
5. known baseline failures/flaky tests are classified.

Preferred execution order:

1. dependency/setup validation;
2. broad unit/integration gates;
3. runtime/compatibility/migration matrices;
4. browser/E2E;
5. performance/benchmark/load tasks;
6. package/release simulation only when separately authorized.

Exit conditions:

- every executable entry has terminal evidence;
- failures are investigated/classified rather than hidden by rerun-until-green;
- blocked authorization entries remain explicit;
- exact source SHA and evidence IDs/digests are stored;
- the Runner Benchmark is reconciled before milestone closeout.

## Planning rule

Every new implementation plan must include a **Runner Benchmark impact**:

- new `RB-####` entries expected;
- which are deferred;
- which are immediate exceptions and why;
- expected runner minutes;
- dependencies/authorization;
- final-batch grouping/dedup opportunities.

If no material runner work is expected, record `Runner Benchmark impact: none`.

## Security rule

Security findings are not deferred when delay would leave a known exploitable/high-severity condition in an accepted branch or would make a security PR unverifiable.

Dependency/security audit gates, authorization regressions and security-sensitive exact-head checks are therefore normally immediate exceptions.

## Historical formal evidence

Terminal formal evidence workflows must not be casually re-executed merely because unrelated product files changed. When the historical fixture has a consumed/non-reusable grant or immutable candidate identity, it must be manual/authorization-gated and recorded in the Runner Benchmark.

This prevents old evidence runners from becoming accidental background CI while preserving the ability to execute a separately authorized new tranche.
