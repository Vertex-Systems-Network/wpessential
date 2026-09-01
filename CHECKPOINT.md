# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-01**  
Canonical implementation source: **`main @ f867eb318c3b529b3bf12b8f37873801c15f2b5b`**  
Planning authority: `planning/master-architecture` through ADR-0213  
Implementation decisions: through **ADR-0222** plus bounded Surface 3 implementation contracts  
Project classification: **`GREENFIELD_IMPLEMENTATION_WITH_EXISTING_ACCEPTED_PLAN`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Lifecycle: **`PHASE_2_CUSTOM_FIELDS_IMPLEMENTATION_ACTIVE`**  
Development approval: **GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56**

## Approval boundary

Authorized sequence:

`Implementation Baseline / Adoption Gate → Machine-enforced architecture guards → Milestone 1 Platform Foundation → module development`.

Source implementation, development/test tooling, CI and milestone-scoped schemas/tests are authorized. Production deployment/release, destructive live-site/customer-data operations, chargeable/irreversible provider side effects and separately privileged merge/release operations remain excluded unless explicitly authorized.

## Product/planning truth

Accepted scope remains **56/56 Exhaustive**, Multisite **56/56**, AI Prompt **56/56**, with no known planning or semantic-owner gap after WP118 / ADR-0213.

Canonical module sequencing remains repository-owned. `docs/ROADMAP.md` starts Phase 2 Structured Data Pro with **Surface 3 Custom Fields**, followed by Relations, Query, Admin Columns, Dynamic Listings and Status. Surface 3 Options Bank remains **BANK_REVIEWED at 618 records**; runtime implementation consumes that reviewed truth and does not create count-only records for UX aliases/presets.

## Implementation gates

- WP119 / ADR-0214 — **DONE / PASS** — greenfield Implementation Baseline / Adoption Gate.
- WP120 / ADR-0215 — **DONE / PASS** — machine-enforced architecture guards.
- WP121 — **DONE / PASS FOR MODULE HANDOFF** — shared Platform foundation readiness closed by WP121.1 through WP121.4.
- Phase 2 / Surface 3 Custom Fields — **ACTIVE / BOUNDED IMPLEMENTATION** — first seven backend slices merged with exact-head CI; value Ability/target binding, multi-row recovery, render/admin completion and broader adapters remain open.

## Surface 3 — Custom Fields implementation checkpoint

### Merged slice 1 — canonical type/repeatability foundation — PASS

PR #35, `feat(fields): add canonical type and repeatability foundation`, established:
- canonical Field Type Registry and UX Preset Registry;
- logical type / editor control / storage / return-format separation;
- Meta Box-style common clone/repeatability semantics for compatible fields;
- sortable clones only when repeatability is enabled and supported;
- Group recursive subfields and container-owned Repeater/Flexible/Gallery ordering semantics;
- enhanced WPE editor-control policy for Date/Time/DateTime/Range/Color instead of browser-native picker UX as the product contract;
- fail-closed Code field policy: stored source text only, never PHP/JavaScript execution.

Exact-head applicable CI passed: Architecture Guards #517, PHP Quality Toolchain #65, Platform Compatibility Matrix #228 and Distributable Package #172.

### Merged slice 2 — Field Group definition lifecycle — PASS

PR #36, `feat(fields): add Field Group definition lifecycle`, established:
- canonical Surface 3 `field_group` Definitions;
- recursive normalized fields and unique keys;
- OR-of-AND location rules;
- Field Group presentation contract;
- draft/publish validation;
- immutable group keys after creation;
- optimistic `expected_revision` mutation safety;
- list/get/save/status/validate handlers through the shared Definition Repository;
- Pro `FieldsModule` module-local Ability/AJAX wiring.

Exact-head applicable CI passed: Architecture Guards #524, PHP Quality Toolchain #69, Platform Compatibility Matrix #235 and Distributable Package #176.

### Merged slice 3 — canonical Field Catalog API — PASS

PR #38, `feat(fields): expose canonical field catalog API`, established a machine-readable backend catalog for future admin/UI consumers so field types and behaviors are not duplicated as hard-coded React truth.

Catalog output includes canonical types, builder-visible presets, logical/editor metadata, modes, clone/sort capability, enhanced-control requirements, no-browser-native-picker policy and code-execution prohibition. Search, Slider, Multi Select, Week, Month, Days/Duration, Section, TinyMCE, color-alpha, local/social Video and WordPress-data selector variants compile to canonical semantics rather than duplicate storage types.

Merged source: `923197edd27ac96a8e7cccc145ab6f139f93b6b9`.  
Exact-head applicable CI passed: Architecture Guards #530, PHP Quality Toolchain #74, Platform Compatibility Matrix #241 and Distributable Package #181.

### Merged slice 4 — typed Field Value normalization — PASS

PR #39, `feat(fields): add typed value normalization foundation`, established an editor-independent, server-side canonical value boundary covering the first safe type tranche:
- required/null semantics, including required empty-list rejection;
- common repeatable/clone value bounds;
- text/email/URL/number/boolean values;
- explicit choice membership for single/multiple controls;
- Date / ISO Week / Month / Time / timezone-explicit DateTime normalized to UTC;
- strict HEX/HEXA/RGB/RGBA/HSL/HSLA grammar;
- WordPress-owned media/post/term/user references;
- recursive Group and Repeater values;
- unknown subfield rejection;
- UI-only fields reject persistence;
- Relationship values fail closed and remain Relations Engine-owned;
- unsupported provider/complex types fail closed rather than accepting arbitrary blobs.

Merged source: **`e4499aad6c092644ff08d758629a513f6f08ef8b`**.  
Exact-head applicable CI passed: Architecture Guards #545, PHP Quality Toolchain #81, Platform Compatibility Matrix #256 and Distributable Package #189.

### Merged slice 5 — stable per-Field identity — PASS

PR #41, `feat(fields): add stable per-field identity`, established durable Field identity before storage binding:
- stable RFC4122 Field UUIDs are assigned at persistence/lifecycle boundaries rather than generated during pure normalization;
- persisted UUIDs survive subsequent normalize/save/status round trips;
- nested Group/Repeater subfields receive stable identity recursively;
- in-place replacement of an existing Field UUID is rejected;
- cross-Field-Group UUID collisions are rejected;
- canonical repeatability/preset state is consumed during re-normalization so persisted Fields round-trip idempotently instead of losing clone/sort semantics.

Merged source: **`197738bc84b675dd435c3f5eafc9c8df443c66c9`**.  
Exact-head applicable CI passed: Architecture Guards #566, PHP Quality Toolchain #95, Platform Compatibility Matrix #274 and Distributable Package #206.

### Merged slice 6 — WordPress registered post-meta storage V1 — PASS

PR #42, `feat(fields): add registered post meta storage v1`, established the first native storage-registration boundary for persisted normalized Fields:
- pure `PostMetaRegistrationCompiler` and thin injectable `WordPressPostMetaRegistrar`;
- persisted stable Field UUID required before storage compilation;
- explicit scalar/list registered-meta type mapping for the first certified tranche;
- deliberate repeatable storage shape: single array value versus non-single scalar rows;
- explicit `show_in_rest.schema.items` for array meta and native non-single REST wrapping verification;
- explicit object-level `edit_post` authorization callback instead of permissive unprotected-meta fallback;
- explicit sanitization through the canonical `FieldValueNormalizer`;
- revision-enabled meta rejected for post subtypes without revision support;
- REST-visible meta rejected for post subtypes without `custom-fields` support;
- structured/provider/Relations/secret/uncertified types fail closed rather than falling back to opportunistic serialization;
- compiler/registrar/value-normalizer exposed only as module-local services; no automatic boot registration and no Pro activation bypass;
- real WordPress integration added to the Platform Compatibility Matrix across WP 6.9/7.1 × PHP 8.2–8.5 plus MariaDB 10.11 baselines;
- native integration proves subtype registration, public REST schema behavior, slash-sensitive scalar/array round trips, explicit auth callback registration, invalid-value sanitization failure with prior-value retention, and revision/custom-fields support guards.

Merged source: **`697eeffea095d1ddd6fc39c4409aa4290af1941e`**.  
Exact-head applicable CI passed: Architecture Guards #584, PHP Quality Toolchain #106, Platform Compatibility Matrix #291 and Distributable Package #219.

### Merged slice 7 — native post-meta value persistence V1 — PASS

PR #44, `feat(fields): add post meta value persistence v1`, established the first canonical server-side value persistence adapter for the registered-meta tranche:
- module-local `PostMetaValueStore` with typed native reads and verified single-value writes;
- write results distinguish `written`, `unchanged`, `deleted` and `absent` while retaining Field UUID/meta-key provenance;
- requested post subtype is verified against the actual post before storage access;
- submitted values normalize through the canonical `FieldValueNormalizer` before mutation;
- `FieldValuePersistenceGuard` rejects non-canonical persistence values and non-finite numeric values (`INF`, `-INF`, `NAN`) through both the store and registered-meta sanitizer path;
- WordPress slashing is applied at the native write boundary and canonical state is re-read after mutation attempts;
- idempotent no-change writes avoid native mutation, while native false/success returns are not accepted without verified post-write state;
- optional null has verified delete/already-absent semantics and boolean false remains distinguishable from absence;
- native string/integer/finite-number/boolean values are deliberately cast before canonical validation; integer overflow and overflow-to-infinity fail closed;
- certified single-array repeatable/list values read/write through the native Metadata API;
- non-single/multi-row metadata remains readable as a typed list but mutation fails before destructive work until snapshot/recovery semantics are certified;
- no migration, raw SQL, public value Ability, target binding, automatic boot registration, provider/Relations storage or Pro activation bypass was introduced;
- real WordPress persistence evidence runs across WP 6.9/7.1 × PHP 8.2–8.5 plus MariaDB 10.11 and verifies slash-sensitive round trips, idempotence, typed reads, finite-value failure retention, multi-row no-partial-mutation behavior and delete/absent semantics.

Merged source: **`f867eb318c3b529b3bf12b8f37873801c15f2b5b`**.  
Exact-head applicable CI passed: Architecture Guards #604, PHP Quality Toolchain #113, Platform Compatibility Matrix #308 and Distributable Package #233.

### Surface 3 integration blockers / non-certifications

Custom Fields is **not** runtime/product complete. In particular:
- registered post-meta compilation/registration V1 and bounded single-value/single-array persistence V1 are certified only for the compatible scalar/list tranche and are not automatically activated at boot;
- no safe `single=false` multi-row replacement/recovery path has been certified;
- no field value storage key rename/migration/rollback implementation has been certified;
- no complete REST/Ability value read/write mutation path has been certified;
- no certified target/location → post subtype registration/value-access binding exists yet;
- no React Field Group builder/renderers have been certified;
- provider-owned/complex types remain fail-closed until their canonical adapters exist;
- import/export, compatibility migrations, performance scale evidence and reference workflow evidence remain open;
- Pro activation is intentionally not wired unconditionally into `frameworks/Bootstrap/Plugin.php`.

The current shared `ModuleRegistry` does not expose a canonical edition/entitlement/module-enable gate. **Do not add a Fields-private licensing bypass.** Shared Free/Pro activation/package ownership must be resolved by its canonical integrator/shared owner before Surface 3 is exposed as an active Pro module.

### Linear coordination status

`CHECKPOINT.md` previously required creating/updating the next module Linear child issue before implementation. A dedicated WPEssential Custom Fields / Surface 3 issue creation was attempted in the connected `VSN — WPEssential` workspace, but Linear rejected the mutation because the workspace has exceeded its free issue limit.

Status: **EXTERNAL COORDINATION UNAVAILABLE / NOT A REPOSITORY IMPLEMENTATION FAILURE**.

No Linear issue ID is invented. Repository planning, source, tests, PRs and this checkpoint remain canonical until Linear write capacity is restored.

## WP121 accepted foundation

Accepted shared production foundation includes:
- Bootstrap / Kernel / Service Registry / Module lifecycle;
- Definition / ExecutionContext / Policy / Ability / Event core;
- Audit foundation, Vault, Assets and Integrations;
- WordPress Capability + Abilities API bridge;
- ADR-0216 engineering/public contract;
- ADR-0217 atomic compiled-registration persistence/recovery;
- ADR-0218 Definition + Audit MySQL persistence + migration ledger;
- ADR-0219 WordPress.org-facing metadata/contribution/release preparedness + `ABSPATH` guards;
- ADR-0220 real WordPress AJAX/nonce/Policy integration;
- ADR-0221 Action Scheduler public-API backend/coexistence profile;
- ADR-0222 durable WPE Job persistence, revision CAS, attempts, leases, heartbeat and checkpoints;
- minimal Platform admin shell + server-rendered Runtime Observatory with progressive TypeScript enhancement;
- locked Composer and Node 24/npm quality toolchains;
- deterministic `@wordpress/scripts` admin artifacts;
- executable 10K/100K compiled-registration scale evidence;
- deterministic runtime-only distributable plugin package/license gate;
- packaged real-browser Runtime Observatory Playwright/Axe baseline;
- real WordPress Multisite two-site AJAX/job/Action Scheduler isolation matrix.

## WP121 bounded closure evidence

### WP121.1 — deterministic distributable package/license — PASS

Canonical package source `019f496e10e04455cd939c75383fc41661dd26f7` passed Distributable Package #3, Architecture Guards #303 and Platform Compatibility Matrix #46.

Package SHA-256: `a61257866088f5bde5a421cef27f9cf8302062eb74eac7a2ee17171415cbe929`; 156 files; 137,667 bytes; single `wpessential/` root; fixed normalized metadata; 0 runtime Composer packages.

### WP121.2 — real-browser E2E/accessibility — PASS

Canonical locked/read-only browser source `9e1039a697db44b6102377eafdf667afdfc79817` passed Browser E2E #11, Architecture Guards #317, Distributable Package #17 and Platform Compatibility Matrix #60.

Evidence: 2/2 Playwright tests; Runtime Observatory progressive enhancement ready; zero page errors; Axe scoped to WPE-owned root with 0 violations / 15 passes; E2E graph 0 vulnerabilities. Artifact ID `9731346638`, digest `sha256:65ec1d2e7ea41e3e4a6f0165a94d6e5a2aa1dcc09b1558c291b6fac2a247b748`.

### WP121.3 — Multisite AJAX & queue-worker isolation — PASS

Exact implementation source `49abadec09676780680e705ae14f9f092609b348` passed:
- Multisite Runtime Isolation `33310952673 / #4`;
- Architecture Guards `33310952677 / #321`;
- Distributable Package `33310952670 / #21`;
- Browser E2E Accessibility `33310952675 / #14`;
- Platform Compatibility Matrix `33310952685 / #64`.

Real WordPress 7.1 / PHP 8.2.33 / MySQL 8.4.11 evidence proves:
- active site/network AJAX context after blog switching;
- same-user cross-site nonce replay rejection;
- durable Job stable-key/read/mutation isolation by explicit network/site scope;
- lease/checkpoint isolation;
- shared WPE network tables without accidental per-site duplicates;
- Action Scheduler 4.1.0 site-store isolation for scheduling/query/cancel.

Artifact ID `9731964919`, digest `sha256:7da43e78c5248cbcf3219b4eef24e0abc6aba312d639ce26e026e433796a4a7b`.

### WP121.4 — aggregate shared-foundation readiness — PASS

Machine-enforced manifest `tools/quality/wp121-readiness.json` anchors the readiness decision to certified implementation source `49abadec09676780680e705ae14f9f092609b348` and exact canonical hosted run IDs/workflow IDs.

`WP121 Shared Foundation Readiness` run `33311289489 / #2` on readiness head `5007de9c84b2b154743b6e50f76cc73e65e6019b` passed:
- exact readiness head/manifest validation;
- certified source ancestry;
- strict no-implementation-drift allowlist after the certified source;
- exact canonical hosted prerequisite run identity/head/workflow/PR/conclusion checks;
- tracked-clean verification;
- machine-readable readiness artifact upload.

Readiness artifact ID `9732050946`, digest `sha256:3cc9cffbb159d90d2fbda4274223ffb0ec708dfd56a2707eb59bf418410cf547`, 14-day retention.

The aggregate gate allows only the readiness workflow/manifest and three canonical readiness/checkpoint documents to differ after the certified source. Any production/test/tooling drift outside that allowlist fails closed and requires a new certified implementation source.

## Readiness decision

**WP121 shared foundation remains PASS for business-module source development under the existing governance boundary.**

This is a module-handoff decision, not a stable-release or production-deployment approval.

## Important staged non-certifications

The following remain real work but do not block bounded module source tranches unless their owning stage requires them:
- WordPress.org submission/stable release;
- live production DB migration/rollback;
- final stable-release Action Scheduler packaging/vendoring mechanism;
- automatic Action Scheduler dispatch → Ability → durable-attempt lifecycle wiring;
- high-concurrency fairness/resource admission/backpressure certification;
- Job checkpoint privacy/retention implementation;
- Audit read/retention/privacy/export/legal-hold product workflows;
- browser/accessibility evidence for future critical interactive WPE admin workflows;
- canonical Free/Pro entitlement/module-enable/package separation;
- periodic reassessment of upstream development-toolchain advisories.

No live provider call, production deployment, destructive live-site/customer-data mutation, live production migration, release or irreversible external operation occurred.

## Current next action

Continue **Surface 3 Custom Fields** as the first Phase 2 Structured Data Pro tranche.

Next bounded implementation target: **typed Field value Ability integration + certified target resolution contract**. Before exposing any read/write route it must:
- resolve a persisted Field Group and stable Field UUID/key to one canonical Field definition rather than accepting arbitrary client-provided Field schema;
- resolve the requested post ID to its actual post subtype and prove that the published Field Group location contract permits that target/context;
- consume the certified `PostMetaValueStore` as the only post-meta mutation path for the bounded tranche;
- enforce resource-level authorization for every read/write through the shared Policy/ExecutionContext/Ability boundary, with no UI-only or AI/MCP bypass;
- define typed Ability input/output schemas and distinguish read from mutating operations;
- reject disabled/draft groups, unknown Field IDs, post-subtype/location mismatch, unsupported storage shapes and Relations/provider-owned values fail-closed;
- preserve idempotent write results and safe error semantics without leaking stack traces or internal storage detail;
- remain module-local/non-booted until the canonical Pro entitlement/module-enable integration gate exists;
- add real WordPress authorization/target-resolution integration evidence before public exposure is certified.

`single=false` multi-row replacement/recovery remains a separate high-integrity storage task unless the Ability slice genuinely requires it. Its future implementation must define pre-write snapshots, duplicate/order semantics, partial-failure detection, compensating restore and post-restore verification before any destructive replacement path is enabled.

After the Ability/target-resolution slice: certified target/location runtime registration binding, admin renderer/editor integration, storage-key rename/migrations, portability/import-export and broader field-type/provider adapters according to Surface 3 dependencies.

Repository evidence overrides conversational memory.
