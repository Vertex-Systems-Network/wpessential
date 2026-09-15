# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-16 UTC**  
Canonical audited base anchor: **`main @ a1675d179e81333361f3b16d37717b0c08774c80`**  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED / RC1_7_DAY_SPRINT`**  
Development approval: **`GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56`**  
RC1 sprint record: **`GOV-OWNER-CONSENT-RC1-001` via Issue #1016**

## Mandatory work-cycle order

Every `start`, `continue` and `resume` cycle must:

1. resolve exact current `main`;
2. classify OPEN Issues first;
3. reconcile eligible OPEN PRs/MRs second;
4. inspect active deterministic claims and `config/coordination/agent-work-queue.json`;
5. execute only a valid bounded slot;
6. require exact-head applicable CI before merge;
7. serialize shared/global writes through the Supervisor;
8. reconcile README/shared truth at a stable integration closeout.

Repository/runtime evidence outranks conversational memory.

## RC1 sprint mode

Target sprint window: **2026-09-16 through 2026-09-22**.

The goal is an installable, testable **RC1 core production candidate**, not 56/56 product parity.

Critical-path RC1 surfaces:

- Surface 1 — CPT Builder;
- Surface 2 — Taxonomy Builder;
- Surface 3 — Fields;
- Surface 4 — Relations;
- Surface 5 — Status;
- Surface 6 — Query;
- Surface 8 — Admin Columns;
- Surface 9 — Listings.

Three implementation lanes are exposed:

- **#1017 / Lane A** — CPT primary stabilization + Taxonomy regression guard;
- **#1018 / Lane B** — Fields + Relations + Status stabilization;
- **#1019 / Lane C** — Query + Admin Columns + Listings stabilization.

The Supervisor owns integration, shared truth, shared bootstrap/build/package changes and merge serialization through **#1016**.

Workers must not edit shared serialized surfaces unless their slot explicitly assigns them. Shared changes are reported as Integration Requirements.

## Explicit RC1 deferrals

The following are not RC1 critical-path implementation work:

- Surface 7 managed Custom Tables DDL/destructive execution;
- new runtime implementation for Surfaces 22–56;
- remote licensing/billing/provider execution;
- updater/TUF rollout;
- destructive Backup/Reset execution;
- full Multisite commercial allocation/clone semantics;
- blanket P-006 144/144 certification;
- production deployment/release.

These remain preserved for later gates and are not deleted or declared unnecessary.

## Current issue classification

- **#858** — `NON_BLOCKING_EXTERNAL_ADMIN`; active ruleset protects main, broader required-CI policy remains an admin residual.
- **#947** — `INDEPENDENT_NONBLOCKING_WORKER_ONLY`; Supervisor must not claim/pre-create its branch or author its evidence.
- **#1014** — `CERTIFICATION_NONBLOCKING_NOT_EXECUTED`; P-006 Wave 1H remains separately gated.
- **#1015** — `CERTIFICATION_NONBLOCKING_NOT_EXECUTED`; P-006 Wave 1I remains separately gated.
- **#1016** — RC1 Supervisor critical path.
- **#1017–#1019** — RC1 implementation critical path.

Open PR count at sprint authorization: **0**.

## Current implementation truth

Accepted product planning remains **56/56 surfaces**.

Existing bounded implementation evidence preserved:

- Surface 2 / Taxonomy — PASS for accepted bounded V1 owner-runtime scope;
- Surface 3 / Fields — PASS for certified native V1 scope;
- Surface 4 / Relations — PASS for certified native V1 baseline;
- Surface 5 / Status — PASS for certified bounded V1 baseline;
- Surface 6 / Query — PASS for certified bounded V1 baseline;
- Surface 8 / Admin Columns — PASS for certified bounded V1 baseline;
- Surface 9 / Listings — PASS for certified bounded V1 baseline;
- Surface 1 / CPT — UX contract + reviewed runtime-gap evidence exists; RC1 runtime stabilization is now the primary Lane A target;
- Surface 7 / Custom Tables — ACTIVE / NOT PASS, bounded runway remains safe-paused;
- Surfaces 11–21 — bounded read-only Pro runtime exposure only, not full-parity runtime certified;
- Surfaces 22–28 — planning/readiness only, Banks unseeded;
- later surfaces remain outside RC1 runtime scope.

Machine full-parity lifecycle is not silently promoted by RC1 stabilization.

## Free / Pro and compatibility truth

Physical Free/Pro package separation, canonical local entitlement state, read-only commercial inventory and local fail-closed compatibility preflight remain accepted architecture.

P-006 accepted truth remains:

- documented fixtures: **144**;
- executed: **21**;
- PASS: **21**;
- FAIL: **0**;
- certified Free↔Pro pairs: **0**;
- P-006 runtime certifications: **0**.

Passed bounded fixtures:
`FP-01/02/03/05/07/08/10/11/13/14/15/16/17/18/19/20/23/25/26/27/28`.

`FP-21/22/24` remain **NOT EXECUTED**.  
`FP-29+` remain **NOT EXECUTED** unless separately exposed by an accepted compatibility slot.  
Temporary grants `-001` through `-005` are consumed and non-reusable.  
Permanent P-001/CF remains uncertified.  
ADR-0010 remains **Proposed**.

RC1 stabilization must preserve compatibility fail-closed behavior and must not manufacture certification.

## Security / repository protection

Active ruleset `23374068` protects the default/main branch with PR-based flow, deletion/non-fast-forward protection, review-thread resolution, strict required `governance` status and zero bypass.

Issue #858 remains open because broader path-applicable required-CI enforcement is not yet configured through repository administration.

RC1 merges must still require every path-applicable exact-head workflow; no source-code workaround may weaken this boundary.

## Verification model

Use the existing two-speed governance:

### Worker FAST gate

As applicable to changed scope:

- coding standards/lint;
- static analysis/typecheck;
- targeted unit tests;
- targeted integration/runtime tests;
- affected admin build;
- targeted security/permission checks.

### Supervisor FULL gate

At integration/RC boundary:

- all path-applicable exact-head workflows;
- broad unit/integration/runtime regression;
- browser/accessibility where applicable;
- deterministic package validation;
- Free-only and Free+Pro composition;
- compatibility/load-order regression;
- security/data-integrity review.

FAST gates never substitute required FULL evidence.

## Shared-surface serialization

One writer only for:

- shared bootstrap/composition roots;
- migrations/schema primitives;
- Policy/auth/entitlement/compatibility core;
- shared Ability/REST schemas;
- Composer/npm manifests and lockfiles;
- CI/build/package configuration;
- coordination queue/checkpoint/approval/shared README truth.

## RC1 definition of done

RC1 may be called a production candidate only when:

- accepted RC1 critical blockers are closed or explicitly documented as non-blocking limitations;
- exact-head applicable FULL evidence is green;
- Free-only and Free+Pro package composition remains valid;
- no unresolved high-severity security/data-integrity regression remains;
- no live/destructive/provider/deploy/release authority was exercised;
- final shared truth is reconciled;
- artifacts remain RC/non-GA unless a later release gate authorizes otherwise.

## Historical authority

Detailed historical evidence remains in:

- `README.md`;
- `docs/APPROVAL-LEDGER.md`;
- `docs/DECISIONS/`;
- `docs/IMPLEMENTATION/`;
- `docs/QUALITY/`;
- Git history and merged PR/Issue evidence.

This checkpoint is intentionally a compact **current-state resume document**, not a replacement for those historical records.
