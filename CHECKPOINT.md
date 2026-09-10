# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-10 UTC**  
Canonical audited base anchor: **`main @ 4655cf878a5abb220a4e9cfd4b87f4a4633aa36a`**  
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
- immutable readiness package remains `execution_allowed=false`.

The post-trust audit accepts this as trust-boundary hardening evidence, but managed-table execution stays blocked.

### Remaining Custom Tables blockers

1. Public `CustomTablesRuntimeCompositionFactory::production(...)` still accepts trusted-provider interface instances, so the factory API itself is not yet an exclusive intrinsic trust root even though canonical Plugin bootstrap owns the registered instance.
2. Production recovery verification is intentionally `FailClosedRecoveryVerificationProvider`; no real allowlisted recovery-artifact verifier is active.
3. Confirmation storage has a read-only runtime source but no trusted issuance/write workflow in the promoted lane.

Therefore **R1/R2 execution coordinator remains BLOCKED / NOT AUTHORIZED**.

Still forbidden:

- managed-table `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE`, `dbDelta()` or generic DDL dispatch;
- R1/R2, R3 or R4 managed-table statement execution;
- live row scans or row payload reads;
- Backup create/restore side effects;
- jobs/leases/retries/Action Scheduler migration execution;
- CRUD/Data Source/backfill/dedup/shadow-copy/swap;
- CT2/PT-D/CT3 conversion, external adoption or public execution mutation;
- deployment/release/product-parity claims.

Custom Tables is safe-paused at the current bounded milestone until a later explicit trust-activation/hardening lane is promoted.

## Surface 1 — CPT Builder

Current machine truth through Issue #473 / merged PR #477:

- Options Bank **`BANK_REVIEWED`**, 107 records;
- lifecycle **`UX_CONTRACT_COMPLETE`**;
- 23 normalized Atomic Option Contracts;
- deterministic 107/107 Bank source projection with `missing=0`, `unclassified=0`;
- reviewed Essential / Advanced / Expert UX contract;
- accepted runtime gap matrix against the existing CustomPostTypes baseline;
- current Definition projection, validation, admin, Ability and runtime registration baseline remains preserved.

CPT is **NOT `RUNTIME_CERTIFIED`** and **NOT `PRODUCT_PARITY_CERTIFIED`**. No new CPT runtime-gap implementation lane is authorized by the current queue; a later exact-main Supervisor transition audit must explicitly open one.

## Surface 2 — Taxonomy Builder

Parent implementation program: **Issue #474 — Taxonomy Builder Runtime Gap Closure V1**.  
Queue slot: **priority 1010 / OPEN**.  
Planning lifecycle: **`UX_CONTRACT_COMPLETE`**, not runtime/product parity certified.

Current machine truth:

- Options Bank **`BANK_REVIEWED`**, 71 records, zero unresolved reviewed items;
- 20 normalized Atomic Option Contracts;
- deterministic 71/71 Bank source projection with `missing=0`, `unclassified=0`;
- reviewed Essential / Advanced / Expert UX contract;
- accepted live runtime gap matrix against the existing Taxonomies baseline;
- PR #478 promoted Definition completeness: complete reviewed label projection, typed default term and bounded object-term args;
- PR #479 promoted allowlisted runtime provider IDs with JSON-safe persisted identities and last-responsible runtime resolution;
- PR #480 promoted read-only effective args, overrides, association health and REST/rewrite preview diagnostics backend;
- PR #483 promoted diagnostics admin rendering with packaged browser + visible-panel axe evidence;
- PR #486 promoted typed adaptive label generation, hierarchy-aware category-like/tag-like families, explicit override precedence and opt-out validation;
- PR #489 promoted the reviewed label-authoring UX: canonical server-owned `TaxonomyLabelPolicy`, complete 28-label inventory, truthful `Generated` / `WordPress default` / `Explicit override` states, accessible reset controls and reset-one/reset-all semantics;
- PR #489 packaged Browser E2E proves source-state, effective-args reset behavior and axe accessibility without pretending WordPress Playground's SQLite/in-memory Definition fallback provides cross-request durability;
- dedicated real WordPress 7.1 + MySQL 8.4 evidence proves canonical Taxonomy create → `expected_revision=1` CAS update → persisted revision 2 with `automatic_labels=false` and cleared label overrides;
- PR #489 exact head **`dd63e0147ffeea4fe43794dc3e3f7535bbc973f1`** passed all six applicable exact-head workflows: Architecture Guards, Browser E2E Accessibility, PHP Quality Toolchain, Taxonomy Runtime, Distributable Package and Platform Compatibility Matrix;
- PR #489 merged to `main` as **`4655cf878a5abb220a4e9cfd4b87f4a4633aa36a`** with clean review state.

### Remaining Taxonomy Runtime Gap Closure work

The reviewed Labels family is promoted. The next bounded implementation sequence is:

1. **Broader tiered editor + complete option UX** — Essential / Advanced / Expert navigation, inheritance/search/help, visibility policy, controlled provider selectors, default-term, capabilities and bounded term-query controls.
2. **Portability & Compatibility** — declarative Taxonomy Definition import/export and CPT UI compatibility mapping with revision/CAS conflict reporting.
3. **Guarded taxonomy-key migration planning/workflow** — dry-run, dependency impact and recovery design first. Destructive term/key mutation is not authorized by this checkpoint.
4. **Runtime certification audit** — only after accepted gaps and required runtime/browser/accessibility/security/compatibility/portability/performance evidence are actually closed.
5. **Product-parity acceptance** — separate machine promotion after runtime certification.

Issue #474 remains OPEN. This checkpoint does not authorize a certification claim or destructive taxonomy migration.

## Evidence boundary for PR #489

The packaged Browser E2E environment intentionally uses WordPress Playground's SQLite/in-memory Definition fallback. It proves the packaged admin behavior, label-source states, reset effective args and accessibility, but does not claim cross-request Definition durability. Durable save/update/reset behavior is independently enforced by the real WordPress 7.1 + MySQL 8.4 Taxonomy Runtime workflow through canonical Definition persistence and expected-revision CAS semantics.

This separation is intentional evidence accounting, not a reduced product requirement.

## Definition of “full final” for CPT / Taxonomy

A surface is not full-final merely because a runtime workflow is green. Full-final requires explicit promotion of:

1. reviewed Options Bank;
2. schema-valid Atomic Option Contract with zero missing/unclassified;
3. reviewed UX contract;
4. exhaustive existing-runtime gap matrix;
5. implementation of all accepted missing behavior through canonical owners;
6. applicable Multisite, security, REST/Ability, import/export/migration, accessibility, compatibility and performance evidence;
7. exact-head required runtime/browser/security/parity tests;
8. machine state `RUNTIME_CERTIFIED`;
9. machine state `PRODUCT_PARITY_CERTIFIED`.

## Product truth

- Accepted structural scope: **56 / 56 Exhaustive**.
- Multisite planning: **56 / 56**.
- AI Prompt planning: **56 / 56**.
- Product parity target for Surfaces 1 and 2: **PARITY_OR_EXCEED**.

Authoritative machine/shared-truth files:

- `config/product/options-bank-progress.json`;
- `config/product/atomic-option-contract-progress.json`;
- `config/product/competitor-parity-surfaces.json`;
- `config/coordination/agent-work-queue.json`;
- `README.md`.

## Current reconciliation and next action

Issue #490 is the mandatory Supervisor shared-truth reconciliation after merged PR #489. It contains no runtime implementation and must preserve the complete README 56/56 dashboard, bounded PASS semantics and all Custom Tables execution blocks.

After #490 promotes:

1. refresh exact current `main`;
2. reconcile accepted OPEN Issues first — Issue #474 remains the expected active Taxonomy program issue;
3. reconcile OPEN PRs/MRs second;
4. read priority 1010 and the live Taxonomy runtime gap matrix;
5. continue the next bounded **broader tiered editor + complete option UX** slice on the existing deterministic Taxonomy claim path without destructive key migration or certification claims.

Repository evidence overrides conversational memory.