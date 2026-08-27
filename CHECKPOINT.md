# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Current objective

Continue evidence-backed planning, architecture, security and option-level specification without starting production development.

The project owner explicitly requires consent before development. This is persisted in:
- `/DEVELOPMENT-CONSENT.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

`continue`, `proceed`, planning approval, ADR acceptance or Phase 0 readiness do **not** authorize implementation. Executable research spikes also require explicit consent.

No WPEssential runtime PHP/React source, production migration, build scaffold or implementation test has been created in the target repository.

---

# Verified completed planning

## Repository/reference audit

- Target repo planning isolated on `planning/master-architecture`.
- Legacy `wpessential/wpessential-dashboard-builder` inspected for useful experiments and architecture drift.
- Legacy issues to avoid include PHP-version mismatch, `inc/daabase` PSR-4 typo, mixed Laravel Mix/Vite tooling, unconditional admin timing logging and low-information historical commits.

## Product/module planning

### 31/31 module/platform surfaces inventoried and behaviorally specified

Detailed source:
- `docs/MODULES/README.md`
- `docs/MODULES/SPECIFICATION-STANDARD.md`
- `docs/MODULES/COMMON-OPTION-CONTRACTS.md`
- `docs/MODULES/OPTION-INVENTORY.md`
- suite specifications under `docs/MODULES/`

`Specified` means intended screens/options/defaults/validation/permissions/lifecycle/failure/dependencies/assets/tests are documented. It does **not** mean implementation exists or is authorized.

### Implementation readiness audit

`docs/IMPLEMENTATION-READINESS-MATRIX.md` now marks every module individually with:
- current specification maturity;
- shared blockers;
- module-specific blockers;
- first safe implementation milestone after future consent.

All modules are currently **BLOCKED from implementation**, including Free CPT/Taxonomy, because owner development consent has not been granted and shared ADR blockers remain.

### Open decisions register

`docs/OPEN-DECISIONS-REGISTER.md` records unresolved platform and Membership decisions, the evidence required, and whether executable proof is eventually needed.

---

# Membership System planning

Membership remains a full Pro module.

Accepted product architecture in ADR-0013:
- User = identity;
- Role/Capability = WordPress authorization primitive;
- Membership Plan = configured access/product definition;
- Enrollment = membership lifecycle instance;
- external Subscription/Purchase = billing source/reference;
- Entitlement = normalized grant/benefit;
- Access Rule/Policy = resource/action decision.

Billing providers are adapters, not the access source of truth. Raw provider events never directly authorize a request.

Additional detailed planning now exists:

## `docs/MODULES/MEMBERSHIP-ACCESS-POLICY.md`

Candidate access semantics include:
- non-membership WordPress/security authorization remains an outer boundary;
- Membership cannot override a WordPress-forbidden action;
- exact-resource rules are more specific than inherited/type/global rules;
- same-specificity deny wins by default;
- multiple valid memberships union entitlements unless an explicit applicable deny/exclusion applies;
- manual `force_allow/force_deny` overrides are exceptional, capability-protected and audited;
- no applicable Membership rule means Membership does not restrict an otherwise public resource;
- stale cache after revoke/force-deny is defined as a security bug;
- admin access bypass uses a dedicated capability, not hard-coded role-name logic;
- diagnostics must explain why a user can/cannot access a resource.

This is a candidate semantic contract, not implemented or Accepted yet.

## `docs/MODULES/MEMBERSHIP-ENROLLMENT-STATE-MACHINE.md`

Candidate canonical states:
- `pending`
- `trialing`
- `active`
- `grace`
- `paused`
- `expired`
- `revoked`

Important modeling corrections:
- `cancel_at_period_end` is a cancellation intent/scheduled end, not automatically an access state;
- provider states such as `past_due`, `payment_failed`, `refunded`, `disputed` are billing facts translated by adapter/plan policy;
- terminal expired/revoked intervals create a new Enrollment for later reactivation by default rather than rewriting history;
- authorization state commits before non-critical notification/webhook side effects;
- external lifecycle events require idempotency/out-of-order handling.

---

# Static compatibility/UI/toolchain research

New research note:
- `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`

## Compatibility recommendation updated

ADR-0002 remains Proposed, but static recommendation is now:
- minimum WordPress candidate: **6.9**;
- primary current target: **7.1**;
- minimum PHP candidate: **8.3**, replacing the earlier PHP 8.2 preference.

Reason:
- Abilities API is native from WordPress 6.9;
- WordPress currently recommends PHP 8.3+;
- PHP 8.2 security support ends 2026-12-31;
- a new long-lived platform should avoid requiring an early PHP-floor raise.

Executable compatibility proof has **not** been performed.

## UI direction corrected

Current official evidence shows:
- WordPress 7.1 remains on React 18.3;
- React 19 was punted after mixed runtime/JSX incompatibilities caused plugin failures;
- current Untitled UI React targets React 19.2;
- WordPress 7.1 exposes public Design System tokens/ThemeProvider and mature DataViews/DataForm capabilities;
- Untitled UI PRO source has distribution restrictions; explicitly MIT components are separate.

Therefore ADR-0005 now proposes:
- WPEssential wrapper components as canonical domain API;
- WordPress Design System / stable `@wordpress/components`, `@wordpress/dataviews`, `@wordpress/theme` behind wrappers;
- Untitled UI as visual/interaction reference;
- only explicitly MIT Untitled pieces after runtime/license compatibility review;
- no Untitled PRO source distribution by default;
- Lucide visual vocabulary behind a WPEssential/WordPress icon abstraction.

ADR-0005 remains Proposed pending an authorized executable UI spike.

## Build direction corrected

ADR-0012 no longer assumes Vite should be the first candidate.

Current Proposed order:
1. `@wordpress/build` stable build capabilities;
2. `@wordpress/scripts` comparison/fallback;
3. Vite only if WordPress-native tooling fails a proven WPEssential requirement;
4. no Laravel Mix carry-forward.

Official `@wordpress/build` currently provides WordPress plugin-focused esbuild/TS/JSX/CSS/RTL/PHP-registration/dependency-externalization behavior. Its experimental pages/routes/widgets are explicitly excluded from foundational assumptions.

No package was installed and no build was run.

---

# Shared architecture contracts added

## Definition Repository

`docs/ARCHITECTURE/DEFINITION-REPOSITORY-CANDIDATE-SCHEMA.md`

Strongest paper model currently:
- numeric local primary key;
- stable UUID portable identity;
- identity/lifecycle table;
- immutable revision table;
- revision-aware dependency-edge table;
- separate `current_revision_id` and `published_revision_id`;
- typed versioned JSON/LONGTEXT module payload;
- only proven list/index fields normalized;
- optimistic concurrency;
- site-local storage by default;
- modules register schemas/migrators/dependency extraction/compilers rather than private revision systems.

No DB table/migration exists. Benchmark remains required and consent-gated.

## Free ↔ Pro compatibility

`docs/ARCHITECTURE/FREE-PRO-COMPATIBILITY-STATE-MACHINE.md`

Planning defines:
- separate Product Version / Platform API Version / migration versions;
- Free owns platform/kernel contracts;
- Pro declares Platform API compatibility range;
- Pro missing Free → inert/degraded, no fatal/migration;
- too-old/too-new mismatch → fail-safe degraded state;
- migration-required and migration-failed states;
- compatibility separate from licensing;
- shared service/runtime ownership rules;
- mixed update-order test matrix.

No bootstrap implementation exists.

## Job Service

`docs/ARCHITECTURE/JOB-SERVICE-CONTRACT.md`

Planning defines:
- canonical job states/payload semantics;
- typed registered job types;
- delayed/recurring/manual modes;
- timing/lag distinction;
- idempotency;
- retry/backoff;
- business concurrency locks;
- user/system principal behavior;
- no arbitrary serialized objects/secrets in payload;
- cancellation/chunking;
- runner health;
- normalized observability.

Current preferred concrete candidate remains Action Scheduler behind WPEssential Job Service because official/current evidence shows it is designed for plugin distribution/coexistence, traceable queues and WP-CLI processing. WP-Cron remains non-exact/traffic-triggered.

No dependency installed and no queue run performed.

## Secrets Vault

`docs/SECURITY/SECRETS-VAULT-THREAT-MODEL.md`

Candidate model:
- protect DB-only disclosure, not claim safety from full server compromise;
- preferred external 256-bit key outside DB;
- WordPress-key-derived convenience fallback with explicit salt-rotation/recovery warning;
- no plaintext fallback;
- PHP Sodium XChaCha20-Poly1305 AEAD candidate;
- versioned envelope/per-secret DEK model candidate;
- write-only saved secret fields;
- generic REST/Abilities/AI never return secret values;
- secrets excluded from ordinary config exports/support bundles;
- fail closed on lost/unavailable key;
- explicit key rotation/recovery documentation.

No crypto prototype exists and no real credential was used.

---

# Quality/CI planning

New plan:
- `docs/QUALITY/CI-TEST-MATRIX-PLAN.md`

It defines future PR/main/nightly/release lanes, minimum/current compatibility, Free↔Pro mismatch tests, migrations, security regressions, E2E reference workflows, performance fixtures, provider acceptance and installable ZIP validation.

No `.github/workflows` implementation or test scaffold has been created.

---

# Accepted decisions

- ADR-0001 — Free WordPress.org + separate Pro add-on/trial model.
- ADR-0003 — typed WordPress Abilities as reusable action contract.
- ADR-0004 — no standard arbitrary PHP eval/unrestricted destructive SQL.
- ADR-0007 — Pro expiry preserves data and safe deployed behavior.
- ADR-0013 — Membership/Billing/Role/Entitlement separation.
- **ADR-0014 — explicit owner consent required before production development/executable spikes.**

---

# Proposed Phase 0 blockers

Still require acceptance/evidence:
- ADR-0002 compatibility floor;
- ADR-0005 UI/design system runtime;
- ADR-0006 concrete Job Service adapter;
- ADR-0008 Definition Repository physical schema/index benchmark;
- ADR-0009 Secrets Vault cryptographic/key model prototype;
- ADR-0010 Free↔Pro concrete bootstrap/version protocol;
- ADR-0011 CI execution matrix/tooling;
- ADR-0012 canonical build toolchain.

Membership also needs final decisions for entitlement runtime schema/cache, access precedence acceptance, protected file architecture, billing adapters/reconciliation, seats/teams concurrency, role-sync conflict and privacy/retention.

---

# Verification

## Verified

- GitHub planning writes/commits listed above succeeded;
- 31/31 surfaces remain specified;
- development-consent governance is persisted;
- static current research was actually performed for WordPress/PHP/React/UI/build/Action Scheduler/crypto primitives;
- new architecture/security documents are present;
- no successful implementation/test claim is made.

## Not verified / intentionally not performed

- Composer/npm install;
- PHP/TS source build;
- runtime plugin bootstrap;
- database migrations/tables;
- PHPUnit/Playwright;
- WordPress activation;
- executable compatibility matrix;
- UI/build comparison spike;
- Definition Repository benchmark;
- Action Scheduler coexistence/load test;
- Vault crypto prototype;
- Free↔Pro boot matrix;
- Membership entitlement benchmark;
- protected-file delivery;
- billing-provider integration;
- backup/restore implementation;
- deployment/release packaging.

These remain unperformed because production/executable development consent has not been granted.

One attempted documentation write for a consolidated future executable-spike proposal file was blocked by the connector's safety classification and was **not** counted as completed work. The actual consent boundary remains fully documented in `/DEVELOPMENT-CONSENT.md`.

---

# Next allowed planning actions

Without development consent, continue only non-executable planning such as:

1. finalize Membership access precedence/state semantics into an ADR after product review;
2. design Membership runtime table/index candidates on paper;
3. design protected-file deployment matrix on paper;
4. rank initial billing adapters commercially/technically without integrating them;
5. refine Definition Repository index/schema alternatives before benchmark;
6. refine Free↔Pro Platform API versioning/deprecation policy;
7. refine Secrets Vault recovery/operator UX;
8. update ADR recommendations using static evidence;
9. keep PR/checkpoint/readiness matrix synchronized.

Before **any** runtime/source/build/migration/executable spike begins, ask the owner for explicit consent.

## Resume instruction

Future AI/engineer must read, in order:
1. `DEVELOPMENT-CONSENT.md`;
2. `AGENTS.md`;
3. this checkpoint;
4. `docs/IMPLEMENTATION-READINESS-MATRIX.md`;
5. `docs/OPEN-DECISIONS-REGISTER.md`;
6. relevant module/architecture/security specs and ADRs.

Repository evidence overrides conversational memory. Proposed ADRs remain Proposed until evidence and acceptance are recorded.
