# ADR-0127 — P-007 CI / Quality Matrix Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Context

ADR-0011 proposes layered CI across PR/FAST, main/FULL, nightly/upstream, provider/certification and release-artifact lanes. Current repository evidence shows no workflow implementation under `.github/workflows`; branch-protection/ruleset state is not established and remains UNKNOWN unless later directly verified.

The existing CI plan and generic P-007 spike define useful categories but did not fix trust/adversarial evidence for untrusted PR secrets, CI-provider independence, actual artifact provenance, baseline/flaky truth, cache/action supply-chain boundaries, cancellation semantics or explicit required-vs-informational mapping.

## Decision

Accept `docs/QUALITY/P007-CI-QUALITY-MATRIX-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical bounded future P-007 evidence contract.

It defines **CI-01…CI-120** covering:
- repository/provider/runner/branch-protection capability detection;
- provider-neutral semantic gate mapping;
- untrusted PR token/secret/environment/action/cache isolation;
- PR FAST Gate and minimum/current compatibility subset;
- main/FULL integration/security/migration/E2E evidence;
- nightly/trunk/PHP/DB/cache/scheduler/builder/performance early-warning lanes;
- exact source/config/lock/artifact hash provenance;
- P-008 externalization/assets/RTL/localization and P-002 accessibility integration;
- BASELINE FAILURE and UNKNOWN/INVESTIGATING attribution;
- flaky-test/rerun/quarantine discipline;
- durable security/negative-requirement regression lanes;
- historical migration/actual-artifact evidence;
- provider-specific certification isolation;
- performance harness/threshold provenance;
- machine/human reports, required-vs-informational checks, cancellation and release gating.

## Preserved architecture

This ADR does not accept ADR-0011 and does not create/select a CI provider or workflow implementation.

Preserved rules:
- FAST and FULL are distinct;
- CI consumes but cannot replace P-001/P-002/P-008 evidence;
- minimum/current supported environments are first-class;
- actual built ZIP is tested and bound to provenance;
- untrusted code receives no trusted/provider/release secrets;
- baseline and flaky failures remain visible and truthfully classified;
- provider outage/infrastructure failure is separate from product result;
- provider-specific certification cannot be promoted by generic CI green;
- trunk/nightly can remain informational until explicitly promoted;
- critical security/data-loss/isolation failures cannot be silently allowed-failure;
- branch-protection/ruleset state remains UNKNOWN until verified;
- CI semantic contract remains portable even if GitHub Actions is later the first adapter.

## Evidence state

At acceptance:
- CI fixtures documented: **120**;
- CI fixtures executed: **0/120**;
- P-007 CI runtime certification: **0**;
- workflow implementation: **none verified**;
- branch-protection/ruleset verification: **UNKNOWN**;
- ADR-0011: **Proposed**.

## Selection rule

A CI system cannot be accepted because it produces green badges. It must prove secret isolation, deterministic/reproducible required checks, baseline/flaky honesty, actual artifact identity, compatibility coverage and release gating.

A workflow that exposes trusted secrets to attacker-controlled PR code, hides failures through reruns, certifies an untested ZIP, or reports unavailable protection state as enabled is a stop-the-line failure.

## Development gate

This ADR authorizes no workflow/config file, runner, environment/secret setting, container/Playground setup, dependency installation, test/build/provider execution, branch-protection change or release gate. Explicit owner development/executable-spike consent under ADR-0014 remains required.
