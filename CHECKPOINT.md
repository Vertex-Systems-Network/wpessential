# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-09 UTC**  
Canonical audited base anchor: **`main @ 7630ca9f06e609617e70a6db1b2eee8cff9daf19`**  
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

## Surface 7 — Custom Tables current state

Promoted through Issue #463 / merged PR #464, with the post-trust exact-main audit promoted through Issue #465 / merged PR #466:

- typed server-side metadata-facts provider boundary;
- production metadata derived through existing CT1 identity + read-only `WordPressCt1SchemaIntrospector`;
- descriptor/run identity + revision + target schema binding before metadata evaluation;
- internal site-scoped WPE confirmation metadata table through canonical Platform migrations;
- read-only revision/actor-bound confirmation lookup;
- production bootstrap owns the canonical registered runtime-composition factory instance;
- `CustomTablesRuntimeCompositionFactory::create()` accepts zero arguments and constructor is private;
- source failures become deterministic fail-closed reasons;
- expanded mismatch/R3/row-scan attack coverage;
- immutable readiness package remains `execution_allowed=false`;
- PR #464 passed all seven applicable exact-head workflows with clean review state.

The exact-main post-trust audit accepts this as **trust-boundary hardening evidence**, but managed-table execution stays blocked.

### Remaining Custom Tables blockers

1. Public `CustomTablesRuntimeCompositionFactory::production(...)` still accepts trusted-provider interface instances, so the factory API itself is not yet an exclusive intrinsic trust root even though canonical Plugin bootstrap owns the registered instance.
2. Production recovery verification is intentionally `FailClosedRecoveryVerificationProvider`; no real allowlisted recovery-artifact verifier is active.
3. Confirmation storage has a read-only runtime source but no trusted issuance/write workflow in the promoted lane.

Therefore **R1/R2 execution coordinator remains BLOCKED / NOT AUTHORIZED**.

Still forbidden:

- managed-table `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE`, `dbDelta()` or generic DDL dispatch;
- R1/R2 statement execution;
- R3/R4 execution;
- live row scans or row payload reads;
- Backup create/restore side effects;
- jobs/leases/retries/Action Scheduler migration execution;
- CRUD/Data Source/backfill/dedup/shadow-copy/swap;
- CT2/PT-D/CT3 conversion, external adoption or public execution mutation;
- deployment/release/product-parity claims.

Custom Tables is safe-paused at the current bounded milestone until a later explicit trust-activation/hardening lane is promoted.

## Owner-directed current wave — CPT Builder + Taxonomy Builder full-final program

The owner explicitly requested that Surfaces 1 and 2 be taken to genuine full-final completion. Existing code is preserved and gap-audited; it is not replaced speculatively.

### Surface 1 — CPT Builder

Current machine truth after Issue #467 / merged PR #469:

- Atomic lifecycle: **`ATOMIC_INVENTORY_COMPLETE`**;
- Options Bank: **`BANK_REVIEWED`**, **107 records**, zero unresolved native/market/review gates;
- existing runtime baseline includes Definition projection, validation, admin controller, Ability handler and registration provider;
- dedicated CPT Runtime regression workflow is green;
- full-parity Option Contract + UX prerequisite is not yet promoted.

Next authorized lane — queue priority **1000**:

**CPT Builder Atomic Option Contract + UX Closure V1**

Required exit:

- project the reviewed 107-record CPT Bank into `config/product/option-contract.schema.json` compliant machine contracts;
- deterministic 107-record source projection with zero duplicate/missing records;
- `missing=0`, `unclassified=0`;
- reviewed Essential / Advanced / Expert information architecture and interaction contract;
- exhaustive gap matrix against existing CustomPostTypes runtime;
- explicit implementation requirements for security, REST/Ability, import/export/migration, accessibility, compatibility and performance.

No runtime/product-parity certification is claimed by this lane.

### Surface 2 — Taxonomy Builder

Current machine truth after Issue #468 / merged PR #470:

- Options Bank: **`BANK_REVIEWED`**, **71 records**, zero unresolved items;
- Atomic lifecycle: **`UX_CONTRACT_COMPLETE`**;
- schema-valid Taxonomy contract: **20 normalized atomic options**;
- deterministic Bank projection: **71 / 71** records;
- coverage: **`missing=0`, `unclassified=0`**;
- reviewed Essential / Advanced / Expert UX contract;
- accepted runtime gap matrix against the existing Taxonomies baseline;
- dedicated Taxonomy Runtime regression workflow is green, but this does not equal full parity.

Next authorized lane — queue priority **1010**:

**Taxonomy Builder Runtime Gap Closure V1**

Required scope is bounded by the accepted V1 contract/UX/gap matrix and includes focused closure of currently missing/partial behavior such as complete label UX, allowlisted REST/editor/count providers, default-term support, bounded object-term args, effective-args/association diagnostics, compatibility import, guarded key-migration workflow and required runtime/browser/accessibility/security/compatibility/portability evidence.

No `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` claim is allowed until the accepted gaps and required exact-head evidence are actually closed.

## Parallelism

Priority 1000 CPT Contract/UX closure and priority 1010 Taxonomy Runtime Gap Closure are **non-overlapping and dependency-safe** after Issue #471 promotes. They may be claimed in parallel on deterministic branches.

After each lane promotes, exact-main Issues → PRs → queue preflight runs again before the next certification lane opens.

## Definition of “full final” for CPT / Taxonomy

A surface is not full-final merely because its runtime workflow is green. Full-final requires explicit promotion of:

1. reviewed Options Bank;
2. schema-valid Atomic Option Contract with zero missing/unclassified;
3. reviewed UX contract;
4. exhaustive existing-runtime gap matrix;
5. implementation of all accepted missing behavior through canonical owners;
6. Multisite, security, REST/Ability, import/export/migration, accessibility, compatibility and performance evidence as applicable;
7. exact-head required runtime/browser/security/parity tests;
8. machine state `RUNTIME_CERTIFIED`;
9. machine state `PRODUCT_PARITY_CERTIFIED`.

## Product truth

- Accepted structural scope: **56 / 56 Exhaustive**.
- Multisite planning: **56 / 56**.
- AI Prompt planning: **56 / 56**.
- Product parity target for Surfaces 1 and 2: **PARITY_OR_EXCEED**.

Authoritative machine files:

- `config/product/options-bank-progress.json`;
- `config/product/atomic-option-contract-progress.json`;
- `config/product/competitor-parity-surfaces.json`;
- `config/coordination/agent-work-queue.json`.

## Current next action

1. Promote Issue #471 shared-truth transition audit after latest-main, exact-head applicable CI and review checks.
2. Re-run exact-main → Issues → PR/MR preflight.
3. Claim priority 1000 CPT Contract/UX closure and priority 1010 Taxonomy Runtime Gap Closure on their deterministic non-overlapping branches.
4. Continue each surface gate-by-gate until runtime and product-parity machine certification is actually earned.
5. Keep Custom Tables execution blocked unless a later explicit exact-main audit authorizes a separate bounded lane.

Repository evidence overrides conversational memory.