# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-09 UTC**  
Canonical audited base anchor: **`main @ 62240fc03b8e6b91e720f6845bbdaee584151f9f`**  
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

Promoted through Issue #463 / merged PR #464:

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

Issue #465 / current audit accepts this as **trust-boundary hardening evidence**, but managed-table execution stays blocked.

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

## Owner-directed next wave — CPT Builder + Taxonomy Builder full-final program

The owner explicitly requested that Surfaces 1 and 2 be taken to genuine full-final completion. Existing code is preserved and gap-audited; it is not replaced speculatively.

### Surface 1 — CPT Builder

Current machine truth:

- Atomic inventory: **`ATOMIC_INVENTORY_COMPLETE`**;
- Options Bank: **`BANK_SURFACE_SEEDED`**, **107 records**;
- existing runtime baseline includes Definition projection, validation, admin controller, Ability handler and registration provider;
- dedicated CPT Runtime regression workflow is green;
- full-parity planning prerequisites are **not yet closed**.

Next authorized lane — queue priority **980**:

**CPT Builder Options Bank Audit Closure V1**

Required exit:

- exhaustive current WordPress native cross-check;
- approved market/specialist benchmark audit;
- all 107 records classified;
- duplicates/ownership/deferred/rejected-unsafe/WPE-exceed consistency resolved;
- zero unresolved review items;
- promote Surface 1 Bank to `BANK_REVIEWED` only with evidence.

No runtime/product-parity certification is claimed by this lane.

### Surface 2 — Taxonomy Builder

Current machine truth:

- Atomic inventory: **`ATOMIC_INVENTORY_COMPLETE`**;
- Options Bank: **`BANK_REVIEWED`**, **71 records**, zero unresolved items;
- existing runtime baseline includes Definition projection, validation, admin controller, Ability handler, object-type catalog and registration provider;
- dedicated Taxonomy Runtime regression workflow is green;
- no schema-valid Taxonomy option-contract file exists yet.

Next authorized lane — queue priority **990**:

**Taxonomy Builder Atomic Option Contract + UX Closure V1**

Required exit:

- project all reviewed Bank records + Wave-1 taxonomy inventory into `config/product/option-contract.schema.json` compliant contracts;
- `missing=0`, `unclassified=0`;
- reviewed Essential / Advanced / Expert information architecture and interaction contract;
- deterministic source projection/ownership consistency;
- exhaustive gap matrix against the existing Taxonomy runtime baseline;
- implementation requirements for Multisite, security, REST/Ability, import/export/migration, accessibility and compatibility.

No `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` claim is allowed yet.

## Parallelism

Priority 980 CPT Bank closure and priority 990 Taxonomy Contract/UX closure are **non-overlapping and dependency-safe** after Issue #465 promotes. They may be claimed in parallel on deterministic branches.

After each planning lane promotes, exact-main Issues → PRs → queue preflight runs again before that surface's implementation/parity lane opens.

## Definition of “full final” for CPT / Taxonomy

A surface is not full-final merely because its runtime workflow is green. Full-final requires explicit promotion of:

1. reviewed Options Bank;
2. schema-valid Atomic Option Contract with zero missing/unclassified;
3. reviewed UX contract;
4. exhaustive existing-runtime gap matrix;
5. implementation of all accepted missing behavior through canonical owners;
6. Multisite, security, REST/Ability, import/export/migration, accessibility and compatibility evidence as applicable;
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

1. Promote Issue #465 audit/shared-truth PR after latest-main, exact-head applicable CI and review checks.
2. Re-run exact-main → Issues → PR/MR preflight.
3. Claim priority 980 CPT Bank closure and priority 990 Taxonomy Option Contract/UX closure on their deterministic non-overlapping branches.
4. Continue each surface gate-by-gate until runtime and product-parity machine certification is actually earned.
5. Keep Custom Tables execution blocked unless a later explicit exact-main audit authorizes a separate bounded lane.

Repository evidence overrides conversational memory.