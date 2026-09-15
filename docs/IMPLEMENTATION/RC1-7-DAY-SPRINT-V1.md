# WPEssential RC1 — 7-Day Fast-Development Sprint V1

Status: **ACTIVE / BOUNDED RC1 STABILIZATION**
Owner milestone record: **GOV-OWNER-CONSENT-RC1-001 / Issue #1016**
Sprint window: **2026-09-16 through 2026-09-22**
Initial main anchor: **a1675d179e81333361f3b16d37717b0c08774c80**

## 1. Objective

Ship an installable, testable WPEssential **RC1 core production candidate** using the existing architecture and accepted bounded implementations.

RC1 is intentionally narrower than the canonical 56-surface product vision. It is a delivery milestone, not a product-parity or certification shortcut.

## 2. Product scope freeze

RC1 critical-path surfaces:

1. CPT Builder
2. Taxonomy Builder
3. Fields
4. Relations
5. Status
6. Query
8. Admin Columns
9. Listings

Shared Platform/Kernel/Admin/package seams may change only when required to integrate or fix a demonstrated RC1 blocker.

No new runtime feature surface is added merely because there is spare agent capacity.

## 3. Parallel execution model

Maximum active implementation WIP: **3**.

### Lane A — #1017

CPT primary runtime stabilization with Taxonomy regression guard.

Deterministic branch:
`agent/rc1-lane-a-cpt-taxonomy-v1`

### Lane B — #1018

Fields + Relations + Status stabilization.

Deterministic branch:
`agent/rc1-lane-b-fields-relations-status-v1`

### Lane C — #1019

Query + Admin Columns + Listings stabilization.

Deterministic branch:
`agent/rc1-lane-c-query-columns-listings-v1`

### Supervisor — #1016

Owns:

- exact-main reconciliation;
- shared/global writes;
- merge order;
- integration requirements from Workers;
- final FULL gate;
- queue/checkpoint/README reconciliation;
- RC artifact truth.

Read-only QA/security/release analysis may run concurrently without becoming additional shared-surface writers.

## 4. Issue classification during RC1

The hard Issues-first rule remains active, but explicit states prevent unrelated blocked work from deadlocking the sprint:

- #858 — `NON_BLOCKING_EXTERNAL_ADMIN`
- #947 — `INDEPENDENT_NONBLOCKING_WORKER_ONLY`
- #1014 — `CERTIFICATION_NONBLOCKING_NOT_EXECUTED`
- #1015 — `CERTIFICATION_NONBLOCKING_NOT_EXECUTED`
- #1016–#1019 — RC1 critical path

An issue becomes blocking again immediately if fresh evidence shows it owns a dependency required by an RC1 lane.

## 5. Shared-surface ownership

Workers must not independently edit these serialized surfaces unless explicitly reassigned:

- shared bootstrap/composition roots;
- Free/Pro bootstrap/package boundary;
- migrations/schema primitives;
- Policy/auth/entitlement/compatibility core;
- shared REST/Ability schemas;
- Composer/npm manifests and lockfiles;
- global CI/build/package configuration;
- coordination queue;
- checkpoint/approval/shared README truth.

If a Worker needs one, it records an **Integration Requirement** with exact desired behavior and tests.

## 6. Verification strategy

### Worker FAST gate

Run the smallest complete gate that proves the changed surface:

- PHPCS/lint as applicable;
- PHPStan/typecheck as applicable;
- targeted unit tests;
- targeted WordPress/integration/runtime evidence;
- affected admin build;
- targeted security/permission tests.

A Worker does not need to wait for every repository-wide matrix before submitting a bounded PR.

### Supervisor integration FULL gate

Before merge/final RC promotion, run every path-applicable exact-head workflow and broad regression required by the touched surfaces.

The FULL gate includes, where applicable:

- Architecture Guards;
- PHP Quality Toolchain;
- Platform Compatibility Matrix;
- Distributable Package;
- Browser E2E/Accessibility;
- CPT/Taxonomy/runtime reference workflows;
- Free-only and Free+Pro package composition;
- compatibility/load-order regression.

A FAST pass never overrides a required FULL failure.

## 7. Feature-freeze policy

Core implementation window comes first.

After feature freeze:

- no new product scope;
- no opportunistic refactors;
- no broad dependency upgrades;
- only RC blocker fixes, integration, regression, security/data-integrity fixes and release-candidate evidence.

If a newly discovered issue cannot be fixed safely inside the bounded window, document it as an explicit RC limitation or defer RC promotion.

## 8. Explicit deferrals

RC1 does not authorize:

- Custom Tables managed DDL/destructive execution;
- new runtime Surfaces 22–56;
- remote licensing/billing/provider execution;
- updater/TUF rollout;
- destructive Backup/Reset execution;
- full Multisite commercial allocation/clone semantics;
- full P-006 144/144 execution/certification;
- production deployment/release.

These remain legitimate future roadmap items.

## 9. Compatibility boundary

Current formal P-006 truth remains:

`144 documented / 21 executed / 21 PASS / 0 FAIL / 0 certified pairs / 0 runtime certifications`

RC1 must preserve the existing local fail-closed Free/Pro compatibility architecture.

#1014 and #1015 remain separate compatibility evidence gates and do not block core RC1 work.

## 10. Daily delivery sequence

### Day 1 — state compaction and lane activation

- record #1016 authorization;
- classify nonblocking residuals;
- expose three RC1 lanes;
- compact queue/checkpoint;
- repair non-behavioral metadata defects;
- establish clean baseline.

### Days 2–4 — parallel bounded stabilization

- Lane A/B/C implement only accepted RC blockers;
- short-lived PRs;
- targeted FAST evidence;
- frequent Supervisor integration.

### Day 5 — feature freeze

- stop new product scope;
- finish accepted critical-path implementation;
- integrate shared requirements.

### Day 6 — FULL regression

- full path-applicable CI;
- package/install/load-order;
- security/data integrity;
- browser/accessibility/runtime regression.

### Day 7 — buffer and RC closeout

- fix only RC blockers;
- re-run exact-head FULL evidence;
- reconcile shared truth;
- identify immutable RC artifacts;
- keep release/GA separately gated.

## 11. Failure policy

Speed never authorizes:

- weakening a valid assertion;
- force-pushing over another lane;
- bypassing Policy/capability/nonce checks;
- hidden Pro dependency from Free;
- destructive data shortcuts;
- production side effects;
- fabricated PASS/certification.

When a dependency or architecture conflict appears:

`STOP → REASSESS → INTEGRATE OR RESCOPE`

rather than creating a giant speculative diff.

## 12. Reporting

Worker completion reports:

- issue/slot;
- base SHA;
- deterministic branch;
- exact head SHA;
- changed files;
- tests;
- defects fixed;
- Integration Requirements;
- known limitations.

Supervisor integration reports:

- merged exact heads;
- final main;
- applicable FULL gates;
- security/data-integrity status;
- Free/Pro/package status;
- queue/checkpoint/README reconciliation;
- RC/non-GA artifact boundary.
