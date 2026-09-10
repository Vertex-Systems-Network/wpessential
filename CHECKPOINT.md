# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-10 UTC**  
Canonical audited base anchor: **`main @ 7fb7cdd0fde2ea97f11290bdc8af55887d03685e`**  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Development approval: **`GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56`**

## Mandatory work-cycle order

Every `start`, `continue` and `resume` cycle must:

1. resolve exact current `main`;
2. reconcile accepted **OPEN Issues first**;
3. reconcile eligible **OPEN PRs/MRs second**;
4. confirm no accepted actionable path is being bypassed;
5. read deterministic claims + `config/coordination/agent-work-queue.json`;
6. only then claim/start dependency-ready work;
7. reconcile README + shared truth before final reporting.

No force/reuse of deterministic claim branches. Latest-main reconciliation, exact-head CI and clean review threads are required before promotion.

## Certified bounded implementation gates

- Surface 3 / Fields — **PASS FOR CERTIFIED NATIVE V1 SCOPE**.
- Surface 4 / Relations — **PASS FOR CERTIFIED NATIVE V1 BASELINE**.
- Surface 5 / Status — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 6 / Query — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 8 / Admin Columns — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 9 / Listings — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 7 / Custom Tables — **ACTIVE / NOT PASS**, bounded runway **90%**.

These bounded states do not imply `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release readiness.

## Surface 7 — Custom Tables safety boundary

Promoted through Issue #463 / merged PR #464 and Issue #465 / merged PR #466. Trust-boundary hardening is accepted, but managed-table execution remains blocked.

Still forbidden:

- managed-table `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE`, `dbDelta()` or generic DDL dispatch;
- R1/R2, R3 or R4 managed-table statement execution;
- live row scans or row payload reads;
- Backup create/restore side effects;
- jobs/leases/retries/Action Scheduler migration execution;
- CRUD/Data Source/backfill/dedup/shadow-copy/swap;
- CT2/PT-D/CT3 conversion, external adoption or public execution mutation;
- deployment/release/product-parity claims.

Custom Tables remains safe-paused at the evidence-backed 90% bounded runway indicator.

## Surface 1 — CPT Builder

Current machine truth through Issue #473 / merged PR #477:

- Options Bank `BANK_REVIEWED`, 107 records;
- lifecycle `UX_CONTRACT_COMPLETE`;
- 23 normalized Atomic Option Contracts;
- deterministic 107/107 projection with `missing=0`, `unclassified=0`;
- reviewed Essential / Advanced / Expert UX contract;
- accepted runtime gap matrix.

CPT is not `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`. No CPT runtime lane is opened by the current ten-worker wave.

## Surface 2 — Taxonomy Builder

Parent implementation program: **Issue #474 — Taxonomy Builder Runtime Gap Closure V1**.  
Queue slot: **priority 1010 / OPEN**.  
Planning lifecycle: **`UX_CONTRACT_COMPLETE`**, not runtime/product parity certified.

Promoted bounded evidence through merged PRs #478, #479, #480, #483, #486 and #489 includes Definition completeness, allowlisted runtime provider identities, read-only diagnostics, diagnostics admin rendering, adaptive label generation and the reviewed 28-label authoring UX with MySQL-backed revision/CAS persistence evidence.

### Current unmerged Taxonomy slice

Deterministic branch: `agent/taxonomy-runtime-gap-closure-v1`  
Current branch head: **`591987e470d4cbe12e9ae1071c2b15a8c4cb83e4`**  
Base lineage: exact audited `main @ 7fb7cdd0fde2ea97f11290bdc8af55887d03685e`.

Bounded scope in progress:

- keyboard-reachable Essential / Advanced / Expert tier navigation;
- native visibility/admin authoring for existing canonical optional boolean fields;
- truthful Default/inherit versus explicit state;
- removing inherited optional keys from authored payload rather than writing guessed defaults;
- preserving hidden existing payload values across tier switches;
- Expert tier remains visible but provider/migration execution controls stay gated.

Current commits on the claim branch include:

- `0da4b2042dcbccf27b7978d6d711acd0b33af97b` — taxonomy visibility/tier helper;
- `9c36e93bb073f017219d9b6735eb9491361a94a7` — server-rendered tier and inheritance controls;
- `591987e470d4cbe12e9ae1071c2b15a8c4cb83e4` — TypeScript collection/hydration/reset wiring.

This slice is **not yet promoted**. Browser/axe evidence, focused tests, gap-matrix reconciliation, exact-head CI and clean review state remain required before merge.

## Owner-directed ten-worker parallel wave

Supervisor Issue **#492** records the owner direction to run a minimum of ten workers on different modules. The wave is intentionally split into one runtime implementation lane plus nine planning/contract-only lanes so repository lifecycle gates are not fabricated.

### Active worker topology

1. **Worker-01 / Taxonomy Builder (Surface 2)** — Issue #474 — `agent/taxonomy-runtime-gap-closure-v1` — runtime gap closure allowed inside existing accepted contract only.
2. **Worker-02 / Dashboard Widgets (Surface 10)** — Issue #493 — `agent/dashboard-widgets-option-contract-ux-v1` — planning/contract only.
3. **Worker-03 / Admin Menu (Surface 11)** — Issue #494 — `agent/admin-menu-option-contract-ux-v1` — planning/contract only.
4. **Worker-04 / Settings Pages (Surface 12)** — Issue #495 — `agent/settings-pages-option-contract-ux-v1` — planning/contract only.
5. **Worker-05 / Frontend Dashboard (Surface 13)** — Issue #496 — `agent/frontend-dashboard-option-contract-ux-v1` — planning/contract only.
6. **Worker-06 / User Profile (Surface 14)** — Issue #497 — `agent/user-profile-option-contract-ux-v1` — planning/contract only.
7. **Worker-07 / Membership (Surface 15)** — Issue #498 — `agent/membership-option-contract-ux-v1` — planning/contract only.
8. **Worker-08 / Builder Widgets (Surface 16)** — Issue #499 — `agent/builder-widgets-option-contract-ux-v1` — planning/contract only.
9. **Worker-09 / Forms & Workflows (Surface 17)** — Issue #500 — `agent/forms-workflows-option-contract-ux-v1` — planning/contract only.
10. **Worker-10 / Cron (Surface 18)** — Issue #501 — `agent/cron-option-contract-ux-v1` — planning/contract only.

### Planning-worker contract

Workers 02–10 must begin from current repository evidence for their own surface and may produce only:

- Options Bank / Atomic Inventory audit evidence;
- schema-valid Atomic Option Contract where prerequisites support it;
- reviewed Essential / Advanced / Expert UX contract;
- current-runtime gap matrix;
- security, capability/Ability, Multisite, portability, compatibility, accessibility and performance requirements.

They may not:

- edit README, CHECKPOINT or the coordination queue directly;
- write runtime implementation before a later explicit gate;
- claim `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`;
- perform destructive provider/database/content/user mutation;
- deploy or release.

Each worker is isolated to one module and one deterministic branch. Shared-truth requirements return to Supervisor.

## Queue state after Supervisor wave

- Priority 1010 remains OPEN: Taxonomy Runtime Gap Closure V1 / Issue #474 / runtime allowed.
- Priorities 1020–1100 are OPEN planning/contract lanes for Surfaces 10–18 / Issues #493–#501 / runtime disallowed.
- All nine planning lanes are independent and may progress in parallel with Taxonomy because they do not own Taxonomy runtime code.
- No CPT runtime lane or Custom Tables execution lane is opened.

## Definition of “full final”

A surface is not full-final because a planning document, branch or regression workflow exists. Full-final requires explicit evidence-backed promotion of the applicable Bank, Atomic contract, UX contract, runtime gap closure, Multisite/security/Ability/portability/accessibility/compatibility/performance evidence, exact-head tests, `RUNTIME_CERTIFIED`, and finally `PRODUCT_PARITY_CERTIFIED`.

## Product truth

- Accepted structural scope: **56 / 56 Exhaustive**.
- Multisite planning: **56 / 56**.
- AI Prompt planning: **56 / 56**.
- README module dashboard must remain **56 / 56 listed**.

Authoritative machine/shared-truth files:

- `config/product/options-bank-progress.json`;
- `config/product/atomic-option-contract-progress.json`;
- `config/product/competitor-parity-surfaces.json`;
- `config/coordination/agent-work-queue.json`;
- `README.md`.

## Current next action

1. Promote Supervisor Issue #492 shared-truth PR after exact-main, review and applicable CI gates.
2. Fast-forward untouched planning-worker branches to the promoted Supervisor merge without force.
3. Keep Worker-01 on the existing Taxonomy branch; finish focused tier/visibility tests and exact-head promotion gates.
4. Workers 02–10 audit their module evidence and produce contract/UX/gap artifacts only.
5. Reconcile every merged worker result through Issues → PRs → queue → README 56/56 before opening any runtime implementation for Surfaces 10–18.

Repository evidence overrides conversational memory.