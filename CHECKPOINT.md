# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Consent gate
Explicit owner consent is required before any production runtime/source/build/migration work or executable research spike.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

`continue`, `proceed`, planning approval, ADR acceptance or Phase 0 readiness do **not** authorize development.

No runtime PHP/React source, production migration, dependency/bootstrap scaffold, implementation test or executable spike has been created in the target repository.

---

# Product/specification coverage

- **31/31** planned module/platform surfaces have screen/option inventory.
- **31/31** have Phase 0 behavioral specifications.
- Membership System is a full Pro module.
- Detailed specs live under `docs/MODULES/`.
- `docs/IMPLEMENTATION-READINESS-MATRIX.md` keeps every module blocked until relevant architecture evidence + explicit owner development consent exist.
- `docs/OPEN-DECISIONS-REGISTER.md` tracks unresolved decisions/evidence.

## Accepted decisions
- ADR-0001 — WordPress.org Free + separate Pro add-on/trial model.
- ADR-0003 — typed WordPress Abilities as reusable action contract.
- ADR-0004 — no standard arbitrary PHP eval/unrestricted destructive SQL.
- ADR-0007 — Pro expiry preserves data and safe deployed behavior.
- ADR-0013 — Membership, billing subscription/purchase, WordPress roles and Entitlements are separate domains.
- ADR-0014 — explicit owner consent required before development/executable spikes.

## Proposed platform blockers
Still need evidence/acceptance:
- ADR-0002 compatibility floor;
- ADR-0005 UI/design-system runtime;
- ADR-0006 concrete Job Service adapter;
- ADR-0008 Definition Repository physical schema/index benchmark;
- ADR-0009 Secrets Vault crypto/key/recovery prototype;
- ADR-0010 Free↔Pro concrete bootstrap/version protocol;
- ADR-0011 CI execution matrix/tooling;
- ADR-0012 canonical build toolchain.

---

# Current static architecture direction

## Compatibility
Proposed only:
- WordPress minimum candidate **6.9**;
- primary target **7.1**;
- PHP minimum candidate **8.3**.

No executable compatibility matrix has run.

## UI
Proposed only:
- React + TypeScript;
- WPEssential wrapper components as canonical domain UI API;
- stable WordPress Design System/components/DataViews/DataForm behind wrappers;
- Untitled UI primarily visual/interaction reference;
- only explicitly MIT Untitled pieces after runtime/license review;
- no Untitled PRO redistribution by default;
- Lucide vocabulary behind WPE/WordPress icon abstraction.

No UI spike has run.

## Build
Candidate order:
1. `@wordpress/build` stable capabilities;
2. `@wordpress/scripts` comparison/fallback;
3. Vite only if WordPress-native tooling fails a demonstrated requirement;
4. no Laravel Mix carry-forward.

No package install/build ran.

## Background jobs
Job Service abstraction remains canonical. Action Scheduler is preferred concrete candidate behind it; WP-Cron remains traffic-triggered/non-exact.

No dependency/coexistence/load test ran.

---

# Membership planning

Accepted domain split:
- User = identity;
- Role/Capability = WordPress authorization primitive;
- Plan = configured product/access definition;
- Enrollment = membership lifecycle interval;
- external Subscription/Purchase = billing source/reference;
- Entitlement = normalized grant/benefit;
- Access Rule/Policy = resource/action decision.

Detailed planning includes:
- `docs/MODULES/MEMBERSHIP-SYSTEM.md`
- `docs/MODULES/MEMBERSHIP-ACCESS-POLICY.md`
- `docs/MODULES/MEMBERSHIP-ENROLLMENT-STATE-MACHINE.md`
- `docs/MODULES/MEMBERSHIP-MIGRATION-SEMANTICS.md`
- runtime schema/index candidates;
- private-origin protected-file design;
- billing adapter/reconciliation planning.

Key semantics:
- Membership cannot override an outer WordPress/security denial.
- Same-specificity deny wins by default.
- Multiple valid memberships union grants unless an applicable deny/exclusion exists.
- `cancel_at_period_end` is intent, not an inactive access state.
- provider statuses are billing facts translated by adapter/Plan policy.
- stale Entitlement cache after revoke/force-deny is a security defect.
- protected member files require private origin + controlled delivery, not cosmetic hiding of public `/uploads/` URLs.

Initial billing-adapter priority on paper:
1. Manual/Free;
2. WooCommerce one-time purchase mapping;
3. WooCommerce Subscriptions;
4. SureCart;
5. later providers by demand/evidence.

Membership migration source semantics now explicitly cover WooCommerce Memberships/Subscriptions, Paid Memberships Pro and MemberPress. Source membership state and external billing state are never conflated.

No Membership runtime, provider, protected-file or migration executable test has run.

---

# Shared architecture/security contracts

Documented:
- `docs/ARCHITECTURE/DEFINITION-REPOSITORY-CANDIDATE-SCHEMA.md`
- `docs/ARCHITECTURE/FREE-PRO-COMPATIBILITY-STATE-MACHINE.md`
- `docs/ARCHITECTURE/JOB-SERVICE-CONTRACT.md`
- `docs/SECURITY/SECRETS-VAULT-THREAT-MODEL.md`
- `docs/ARCHITECTURE/MODULE-DEPENDENCY-AND-DATA-OWNERSHIP.md`
- `docs/SECURITY/CAPABILITY-POLICY-MATRIX.md`
- `docs/ARCHITECTURE/EVENT-AND-ABILITY-CATALOG.md`
- `docs/ARCHITECTURE/PER-MODULE-CAPABILITY-ABILITY-EVENT-REGISTRY.md`
- `docs/ARCHITECTURE/ERROR-TAXONOMY-AND-FAILURE-UX.md`
- `docs/MODULE-LIFECYCLE-AND-UNINSTALL.md`
- `docs/PERFORMANCE-BUDGETS.md`
- `docs/PRIVACY-DATA-CLASSIFICATION-RETENTION.md`
- `docs/ARCHITECTURE/EXTENSION-SDK-AND-ADAPTER-CONTRACT.md`
- `docs/UI/ADMIN-INFORMATION-ARCHITECTURE.md`
- `docs/ARCHITECTURE/CONTRACT-VERSIONING-AND-DEPRECATION.md`

## Per-module Capability / Ability / Event registry
All 31 surfaces now have explicit intended capability classes, typed Ability names and event families.

Important rules:
- UI/menu visibility is never authorization.
- High-risk restore/reset/force-access/credential/admin-equivalent role changes use dedicated capabilities.
- Ability registration does not auto-expose it to REST/workflow/CLI/AI.
- AI mutations are opt-in; destructive/high-risk operations are AI-disabled by default.
- event delivery assumes at-least-once; consumers require idempotency/duplicate tolerance.

Concrete JSON schemas/tests remain implementation-spec work and are not authorized yet.

## Contract versioning
Product Version, Platform API, Definition Schema, runtime DB schema, Ability/Event schema, Source Adapter and SDK compatibility are separate version families.

Important rules:
- unknown future definition/package schema fails safe/read-only rather than dropping unknown data;
- capabilities are treated as security contracts;
- breaking Ability/Event permission/side-effect changes are breaking even if JSON shape is unchanged;
- third-party adapters declare SDK compatibility ranges;
- runtime DB domains keep independent migration versions.

---

# Migration/import planning

New research/architecture:
- `docs/RESEARCH/MIGRATION-SOURCE-LANDSCAPE.md`
- `docs/ARCHITECTURE/MIGRATION-IMPORT-COMPATIBILITY-PLAN.md`
- `docs/ARCHITECTURE/FIELD-MIGRATION-COMPATIBILITY-MATRIX.md`
- `docs/ARCHITECTURE/SOURCE-MIGRATION-ADAPTER-REGISTRY.md`
- `docs/MODULES/MEMBERSHIP-MIGRATION-SEMANTICS.md`

## Current source research
Static official/current documentation has been reviewed for:
- ACF / ACF PRO;
- Secure Custom Fields (SCF);
- Meta Box;
- JetEngine;
- CPT UI;
- WooCommerce Memberships / WooCommerce Subscriptions;
- Paid Memberships Pro;
- MemberPress.

## Core migration principle
Configuration definitions, runtime data, external billing state and presentation artifacts are separate migration domains.

Examples now explicitly documented:
- ACF Local JSON can move field groups/CPT/taxonomy/options definitions, not all field values;
- Meta Box Builder export moves field settings, not stored field values;
- JetEngine Skins move structures while posts/CCT records/custom-field data have separate data migration needs;
- Woo Memberships CSV moves user-membership state but not recurring subscription/billing state.

## Canonical migration pipeline
**Discover → Snapshot → Parse → Normalize → Map → Validate → Dry Run → User Review → Execute → Verify → Reconcile → Optional Source Deactivation Readiness**

No automatic source cleanup/uninstall.

## Fidelity classes
Every mapping is classified:
- `exact`
- `convertible`
- `lossy`
- `external-reference`
- `unsupported`
- `conflict`

Unknown mappings never default to exact.

## Source-neutral IR
Source adapters produce a neutral intermediate representation. They do **not** directly write WPE tables. Target writes go through WPE module/Data Source APIs after validation/authorization.

## Source Adapter Registry
Each source adapter declares:
- source/version range;
- detection confidence;
- accepted artifact/API/storage readers;
- domains supported;
- fidelity limits;
- certification level;
- fixtures/test version;
- source-deactivation verification capability.

Automatic conversion is allowed only for certified/confirmed source versions in future implementation.

## Initial migration roadmap
Structured content:
1. CPT UI;
2. ACF/SCF;
3. Meta Box;
4. JetEngine after shared module semantics are stable.

Membership adapters only after Membership runtime/access semantics are Accepted:
5. WooCommerce Memberships + Woo Subscriptions reference mapping;
6. PMPro;
7. MemberPress.

Order may change with customer/market evidence.

## Field compatibility matrix
Field-type mapping now covers scalar, choice, date/time, rich/editor, media, map/icon, WordPress reference, repeater/group/flexible and UI-only concepts across ACF/SCF, Meta Box and JetEngine.

Important rules:
- matching type name alone is insufficient for `exact` fidelity;
- return format, storage mode, timestamp/timezone, option source, media identity, reference cardinality and relation semantics matter;
- null/missing/empty/zero/false distinctions are preserved where source semantics distinguish them;
- old-site numeric IDs never become target IDs until referenced objects are mapped;
- imported generated PHP/callback code is never `eval()`ed.

No source adapter, fixture, DB inspection or executable import has run.

---

# Commercial planning

`docs/COMMERCIAL-POSITIONING-AND-PACKAGING.md` records current positioning research.

Position:
> WPEssential is a modular WordPress application platform, not a cheap bundle of unrelated mini-plugins.

Current direction:
- permanently useful Free CPT + Taxonomy foundation;
- released Pro capabilities generally packaged by site-count/support tier rather than per-module fragmentation;
- candidate price bands remain hypotheses, not public commitments;
- no default WPE transaction fee on customer membership/store revenue;
- no default lifetime-license assumption;
- commercial release sequence follows verified capability milestones and reference applications.

---

# Quality/CI planning

`docs/QUALITY/CI-TEST-MATRIX-PLAN.md` defines future PR/main/nightly/release lanes, compatibility, Free↔Pro mismatch, migrations, security regressions, E2E reference workflows, performance fixtures, provider/source-adapter certification and installable ZIP validation.

No CI/test implementation exists yet.

---

# Verified
- Planning branch writes/commits for all documents listed above succeeded.
- 31/31 surfaces remain behaviorally specified.
- Consent governance remains active.
- Static web research was actually performed where recorded.
- Per-module capability/Ability/event registry exists.
- Migration source research, semantic pipeline, field compatibility, membership status mapping and source adapter registry now exist.
- Contract versioning/deprecation architecture exists.
- No implementation/test success is claimed.

# Not verified / intentionally not performed
- Composer/npm install;
- runtime PHP/TS source/build;
- plugin bootstrap/activation;
- DB migrations/tables;
- PHPUnit/Playwright;
- executable compatibility/UI/build spikes;
- Definition Repository benchmark;
- Action Scheduler coexistence/load test;
- Secrets crypto prototype;
- Free↔Pro boot matrix;
- Membership entitlement/cache benchmark;
- protected-file delivery test;
- billing-provider integration;
- source-plugin fixture execution/import adapters;
- backup/restore implementation;
- deployment/release packaging.

These remain unperformed because development/executable-spike consent has not been granted.

---

# Next allowed planning actions
Without development consent:
1. build per-module data/privacy/retention registry using P0–P4 classes;
2. define exact Definition/package portability manifest and conflict UX;
3. deepen backup destination certification + restore matrix;
4. define release/changelog/support/documentation information architecture;
5. convert mature Membership access/state and Platform compatibility semantics into ADRs where ready;
6. define WooCommerce/Elementor/Bricks integration certification policies;
7. define observability/audit event retention and support-bundle contract;
8. keep PR/readiness/open-decisions/checkpoint synchronized.

Before **any** runtime/source/build/migration/executable spike begins, obtain explicit owner consent.

## Resume order
1. `DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
5. `docs/OPEN-DECISIONS-REGISTER.md`
6. relevant module/architecture/security/ADR docs

Repository evidence overrides conversational memory.