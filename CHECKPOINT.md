# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Consent gate
The project owner requires explicit consent before any production development or executable spike.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

`continue`, `proceed`, planning approval, ADR acceptance or Phase 0 readiness do **not** authorize implementation.

No WPEssential runtime PHP/React source, production migration, dependency/bootstrap scaffold, implementation test or executable research spike has been created in the target repository.

---

# Current Phase 0 coverage

## Product scope
- 31/31 planned module/platform surfaces have option/screen inventory.
- 31/31 have Phase 0 behavioral specifications.
- Membership System is a full Pro module.
- Detailed specs live under `docs/MODULES/`.
- `docs/IMPLEMENTATION-READINESS-MATRIX.md` keeps every module individually blocked until its ADR/technical gates and owner consent are satisfied.

## Accepted product/architecture decisions
- ADR-0001 — WordPress.org Free + separate Pro add-on/trial model.
- ADR-0003 — typed WordPress Abilities as reusable action contract.
- ADR-0004 — no standard arbitrary PHP eval/unrestricted destructive SQL.
- ADR-0007 — Pro expiry preserves data and safe deployed behavior.
- ADR-0013 — Membership, billing subscription/purchase, WordPress roles and Entitlements are separate domains.
- ADR-0014 — explicit owner consent required before development/executable spikes.

## Proposed platform blockers
Still require evidence/acceptance:
- ADR-0002 compatibility floor;
- ADR-0005 UI/design-system runtime;
- ADR-0006 concrete Job Service adapter;
- ADR-0008 Definition Repository physical schema/index benchmark;
- ADR-0009 Secrets Vault crypto/key/recovery prototype;
- ADR-0010 Free↔Pro concrete bootstrap/version protocol;
- ADR-0011 CI execution matrix/tooling;
- ADR-0012 canonical build toolchain.

---

# Static research conclusions

## Compatibility
Current proposed direction:
- minimum WordPress candidate: **6.9**;
- primary current target: **7.1**;
- minimum PHP candidate: **8.3**.

Reasoning is documented in ADR-0002 and `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`. No executable compatibility matrix has run.

## UI
Current proposed direction:
- React + TypeScript;
- WPEssential wrapper components are canonical domain UI API;
- stable WordPress Design System/components/DataViews/DataForm behind wrappers;
- Untitled UI primarily as visual/interaction reference;
- only explicitly MIT Untitled UI pieces after runtime/license review;
- no Untitled PRO redistribution by default;
- Lucide vocabulary behind WPE/WordPress icon abstraction.

Reason: WordPress 7.1 remains on React 18.3 while current Untitled UI React targets React 19.2. No UI spike has run.

## Build
Current proposed candidate order:
1. `@wordpress/build` stable plugin-build capabilities;
2. `@wordpress/scripts` comparison/fallback;
3. Vite only if WordPress-native tooling fails a demonstrated WPE requirement;
4. no Laravel Mix carry-forward.

No packages were installed and no build ran.

## Background jobs
Action Scheduler remains preferred concrete candidate behind WPE Job Service because current official behavior supports plugin embedding/coexistence, queued traceability and WP-CLI processing. WP-Cron remains traffic-triggered/non-exact.

No queue dependency/load/coexistence test ran.

---

# Membership planning status

Accepted domain separation:
- User = identity;
- Role/Capability = WordPress authorization primitive;
- Plan = configured product/access definition;
- Enrollment = membership lifecycle interval;
- external Subscription/Purchase = billing source/reference;
- Entitlement = normalized grant/benefit;
- Access Rule/Policy = resource/action decision.

Detailed planning now includes:
- `docs/MODULES/MEMBERSHIP-SYSTEM.md`
- `docs/MODULES/MEMBERSHIP-ACCESS-POLICY.md`
- `docs/MODULES/MEMBERSHIP-ENROLLMENT-STATE-MACHINE.md`
- Membership runtime schema/index candidates;
- protected-file private-origin architecture;
- initial billing-adapter priority/reconciliation planning.

Key semantics:
- Membership cannot override outer WordPress/security authorization denial.
- Same-specificity deny wins by default.
- Multiple valid memberships union grants unless an explicit applicable deny/exclusion applies.
- `cancel_at_period_end` is intent, not an access state.
- provider states such as past_due/refunded/disputed are billing facts translated through adapter/plan policy.
- raw provider events never directly authorize access.
- stale entitlement cache after revoke/force-deny is a security defect.
- protected member files cannot be ordinary public `/uploads/` URLs with cosmetic hiding; they require private origin + controlled delivery.

Current commercial adapter priority on paper:
1. Manual/Free enrollment;
2. WooCommerce one-time purchase mapping;
3. WooCommerce Subscriptions;
4. SureCart;
5. later providers by demand/evidence.

Direct card/payment processing inside WPEssential is not an initial goal.

Membership still needs executable evidence for cache/revocation latency, runtime indexes, protected-file delivery environments, billing adapters/reconciliation, seat concurrency and migration fidelity.

---

# Shared architecture contracts now documented

## Definitions / compatibility / jobs / secrets
- `docs/ARCHITECTURE/DEFINITION-REPOSITORY-CANDIDATE-SCHEMA.md`
- `docs/ARCHITECTURE/FREE-PRO-COMPATIBILITY-STATE-MACHINE.md`
- `docs/ARCHITECTURE/JOB-SERVICE-CONTRACT.md`
- `docs/SECURITY/SECRETS-VAULT-THREAT-MODEL.md`

## Dependency and ownership
`docs/ARCHITECTURE/MODULE-DEPENDENCY-AND-DATA-OWNERSHIP.md`

Defines:
- platform/service/hard/soft/adapter dependency classes;
- authoritative data owner per module;
- no hidden cross-module table access;
- no circular module dependencies;
- disable != delete;
- security/access modules cannot silently expose data when disabled/expired.

## Capability and Policy model
`docs/SECURITY/CAPABILITY-POLICY-MATRIX.md`

Defines:
- granular module capabilities;
- high-risk dedicated capabilities;
- role presets as convenience only;
- resource-level Policy Engine;
- server-side parity across UI/REST/Abilities/CLI/workflow/AI;
- multisite/re-auth/anti-escalation rules.

## Event / Ability vocabulary
`docs/ARCHITECTURE/EVENT-AND-ABILITY-CATALOG.md`

Defines:
- namespaced stable IDs;
- typed schemas;
- read/write/destructive annotations;
- event envelope;
- **at-least-once** event delivery assumption;
- duplicate/out-of-order tolerance;
- AI exposure allowlist separate from Ability registration;
- compatibility/deprecation rules.

## Error model
`docs/ARCHITECTURE/ERROR-TAXONOMY-AND-FAILURE-UX.md`

Defines stable machine codes and categories for validation, authorization, conflict, dependency, integration, rate/capacity, network, data integrity, migration and internal errors, plus consistent UI/retry/logging behavior.

## Module lifecycle/uninstall
`docs/MODULE-LIFECYCLE-AND-UNINSTALL.md`

Defines:
- module canonical states;
- enable/disable/re-enable;
- Pro expiry;
- plugin deactivate vs module disable;
- uninstall default preserves data;
- explicit cleanup levels;
- failed migration safe state;
- dependency-removal degradation;
- recovery surface.

## Performance
`docs/PERFORMANCE-BUDGETS.md`

Defines:
- zero/near-zero unused-module overhead goal;
- asset isolation;
- pagination/no-unbounded-list rules;
- N+1 avoidance;
- representative/stress fixture sizes;
- Membership authorization-cache security/performance constraints;
- future measurable regression gates.

No fake universal millisecond threshold is asserted before baseline benchmarks.

## Privacy / retention
`docs/PRIVACY-DATA-CLASSIFICATION-RETENTION.md`

Defines:
- P0–P4 data classes;
- minimization;
- module retention ownership;
- WordPress personal-data exporter/eraser integration direction;
- membership/chat/forms/log/backup privacy behavior;
- support-bundle redaction;
- no hidden telemetry.

## Extension SDK/adapters
`docs/ARCHITECTURE/EXTENSION-SDK-AND-ADAPTER-CONTRACT.md`

Defines typed extension categories and certification contracts for fields, data sources, builders, workflows, billing, backup providers, notification channels and other integrations. UI-entered arbitrary code is not an SDK mechanism.

## Admin information architecture
`docs/UI/ADMIN-INFORMATION-ARCHITECTURE.md`

Defines:
- one WordPress parent menu: WPEssential;
- suite-grouped navigation rather than 30+ flat items;
- normal admin navigation target max 3 levels;
- common List → Editor → Observe interaction grammar;
- contextual cross-module creation without duplicate mini-builders;
- accessible/degraded/expired/mobile behavior;
- route/module-level asset isolation.

---

# Commercial planning

New document:
- `docs/COMMERCIAL-POSITIONING-AND-PACKAGING.md`

Current position:
> WPEssential is a modular WordPress application platform, not a cheap bundle of unrelated mini-plugins.

Current official competitor pricing anchors were researched for ACF PRO, JetEngine/Crocoblock, Meta Box, Gravity Forms and MemberPress.

Recommendation:
- Free permanently useful CPT + Taxonomy foundation;
- Pro generally includes released Pro modules, with site-count/support tiers rather than recreating per-module fragmentation;
- candidate annual price bands are only research hypotheses, not final public prices;
- no default WPE transaction fee on customers' membership/store revenue;
- lifetime licensing is not a default assumption because platform/provider/security maintenance has ongoing cost;
- contextual upgrade UX, no admin spam;
- commercial release sequencing follows verified capability milestones rather than waiting for every module or selling unimplemented promises.

Reference applications are required as reproducible sales proof: directory/real-estate, client portal, approval workflow, membership portal, data app and recovery drill.

---

# Quality/CI planning

`docs/QUALITY/CI-TEST-MATRIX-PLAN.md` defines future PR/main/nightly/release lanes, compatibility tests, Free↔Pro mismatch tests, migrations, security regressions, E2E reference workflows, performance fixtures, provider certification and installable ZIP validation.

No workflow/test scaffold exists yet.

---

# Verified
- Planning branch writes/commits for the documents above succeeded.
- 31/31 surfaces remain behaviorally specified.
- Consent governance remains active.
- Static research was actually performed where recorded.
- New dependency/data ownership, capability/policy, event/ability, lifecycle, performance, privacy, error, SDK, admin-IA and commercial contracts now exist.
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
- backup/restore implementation;
- deployment/release packaging.

These remain unperformed because development/executable-spike consent has not been granted.

---

# Next allowed planning actions
Without development consent:
1. convert mature paper decisions (Membership access/state, Platform API versioning/deprecation, protected-file model) into Proposed/Accepted ADRs where product semantics are sufficiently clear;
2. create per-module capability/Ability/event ownership registry from the shared contracts;
3. create module-by-module data/privacy/retention matrix;
4. create exact import/migration compatibility plans from ACF/Meta Box/JetEngine/CPT UI/member products;
5. deepen backup provider certification matrix and restore semantics;
6. define upgrade/version/deprecation policy for definitions and SDK;
7. define documentation/support information architecture and release-note policy;
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