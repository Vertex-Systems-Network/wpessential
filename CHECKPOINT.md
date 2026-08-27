# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Consent gate
Explicit owner consent is required before runtime/source/build/migration/test implementation or any executable research spike.

Authoritative sources:
- `/DEVELOPMENT-CONSENT.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

`continue`, `proceed`, planning approval, ADR acceptance, technical readiness or Phase 0 completion do **not** authorize development.

No WPEssential production runtime PHP/React source, plugin bootstrap, production migration/table, package/dependency scaffold, implementation test or executable spike has been created in the target repository.

---

# Major planning milestone — 31/31 Exhaustive

`docs/MODULES/OPTION-COVERAGE-MATURITY.md` now records:
- **31/31** planned module/platform surfaces with option inventory;
- **31/31** with behavioral specs;
- **31/31** at **Exhaustive product-option maturity**;
- **0/31 Authorized** for development.

Exhaustive means screen-by-screen controls, small options, defaults, conditional visibility, actions, validation, permissions, destructive behavior, failure states, cross-module dependencies, performance safeguards and future acceptance tests are documented at product level.

Exhaustive does **not** mean physical schemas/dependencies/providers/builds/performance/security are proven.

`docs/MODULES/README.md` is synchronized as the detailed spec index.

---

# Newly completed exhaustive module specs

Automation/communication:
- `FORMS-WORKFLOW-EXHAUSTIVE-SPEC.md`
- `CRON-JOB-BUILDER-EXHAUSTIVE-SPEC.md`
- `NOTIFICATION-SYSTEM-EXHAUSTIVE-SPEC.md`
- `EMAILS-BUILDER-EXHAUSTIVE-SPEC.md`
- `MESSAGE-CHAT-EXHAUSTIVE-SPEC.md`

Integration/data movement:
- `REST-API-BUILDER-EXHAUSTIVE-SPEC.md`
- `WEBHOOKS-CONNECTIONS-EXHAUSTIVE-SPEC.md`
- `IMPORT-EXPORT-EXHAUSTIVE-SPEC.md`

Operations/protection:
- `BACKUP-MANAGER-EXHAUSTIVE-SPEC.md`
- `RESET-MANAGER-EXHAUSTIVE-SPEC.md`
- `PROTECTOR-EXHAUSTIVE-SPEC.md`
- `WATERMARKER-MEDIA-RULES-EXHAUSTIVE-SPEC.md`
- `XML-RPC-MANAGER-EXHAUSTIVE-SPEC.md`

Previously completed exhaustive specs cover all remaining content/data/admin/identity/builder surfaces.

---

# Accepted ADR state

Accepted product/security/governance decisions:
- ADR-0001 — Free WordPress.org + separate Pro add-on/trial distribution.
- ADR-0003 — WordPress Abilities typed action contract.
- ADR-0004 — no standard arbitrary PHP eval/unrestricted destructive raw SQL.
- ADR-0007 — Pro expiry preserves data and safe deployed behavior.
- ADR-0013 — Membership/Billing/Role/Entitlement domain separation.
- ADR-0014 — explicit owner development-consent gate.
- ADR-0015 — Membership access precedence/specificity/deny semantics.
- ADR-0016 — Membership Enrollment lifecycle/cancellation semantics.
- ADR-0017 — signed/site-bound/freshness-aware WPE product entitlement architecture; exact signature profile pending.
- ADR-0018 — Pro signed supply-chain update architecture; exact updater protocol/client pending.
- ADR-0019 — Membership Plan revisions + upgrade/downgrade effective-time semantics.
- ADR-0020 — Membership teams/seats + optional provenance-safe role sync semantics.
- ADR-0021 — Backup encryption/recovery architecture; exact crypto/container profile pending.

ADR index is synchronized in `docs/DECISIONS/README.md`.

---

# Membership semantic status

Core product semantics are accepted:
- User = identity;
- Role/Capability = WordPress authorization primitive;
- Plan = access/product definition;
- Enrollment = lifecycle interval;
- Subscription/Purchase = external billing source/reference;
- Entitlement = normalized grant/benefit;
- Access Rule = resource/action policy.

Accepted rules include:
- outer WordPress/security denial cannot be overridden by Membership;
- most-specific resource/action rule wins over inherited rules;
- same-specificity deny wins;
- valid entitlements union across simultaneous memberships;
- cancellation-at-period-end is intent, not inactive state;
- canonical Enrollment states: pending/trialing/active/grace/paused/expired/revoked;
- provider statuses remain billing facts translated through adapter/Plan policy;
- draft Plan edits never change live access;
- published benefit changes explicitly use follow-current/grandfather/scheduled behavior;
- billing math/proration remains provider responsibility where possible;
- Membership team roles are separate from WordPress roles;
- role sync is off by default and must remove only WPE-owned provenance grants.

Still blocked technically:
- physical Enrollment/Entitlement/index schema benchmark;
- cache/invalidation and revoke-to-deny proof;
- group/seat concurrency implementation;
- protected-file delivery environment certification;
- WooCommerce/Woo Subscriptions/SureCart integration certification;
- provider migration fixtures/performance.

---

# Remote WPE service / product licensing

Planning source:
- `docs/PLATFORM/REMOTE-SERVICE-API-CONTRACT.md`
- `docs/SECURITY/PRODUCT-ENTITLEMENT-SIGNING-OFFLINE-GRACE.md`
- `docs/SECURITY/PRO-UPDATE-SUPPLY-CHAIN-TRUST-MODEL.md`

Accepted boundaries:
- Free remains locally useful/account-free;
- WPE account credentials are not collected by a local WordPress password proxy by default;
- preferred account-link direction is browser authorization + PKCE, exact callback profile pending;
- WordPress.org Free plugin does not auto-download/install/update external Pro ZIPs;
- WPE product-license entitlement is separate from site Membership entitlement;
- service outage != license expiry;
- commercial grace must be cryptographically/service-authorized, not locally invented;
- Pro updates need signed anti-rollback/freeze/compromise-aware trust.

Still open:
- exact OAuth return/callback profile;
- entitlement canonicalization/algorithm/library;
- exact freshness/grace windows;
- updater client/metadata role/key custody implementation;
- executable tamper/update-order evidence.

---

# Backup / Reset / Protection planning

## Backup
Current planning includes:
- 34 target destinations in provider certification matrix;
- protocol-family adapter strategy rather than 34 duplicate engines;
- V0 Generated / V1 Local Verified / V2 Remote Verified / V3 Restore Tested confidence tiers;
- exhaustive Plan/Backup/Destination/Restore UI options;
- multi-destination Required vs Optional semantics;
- retention safeguards;
- restore preflight/verification/recovery states;
- per-backup DEK + independent recovery wrapping architecture.

Important accepted security rule: WordPress salts are **not** the only backup recovery root. Disaster restore must be able to work when original server/database no longer exists, provided recovery material exists.

Still blocked: exact archive/chunk/KDF/AEAD format and actual provider/restore tests.

## Reset
Exhaustive options now include scope/preservation, WPE config/runtime separation, content/taxonomy/comments/settings/users, plugin/theme state, verified restore-point requirement, impact fingerprint, Level 1/2/3 confirmations, concurrency locks and recovery states.

No destructive reset implementation exists.

## Protector
Exhaustive options now include Site Gate, path/resource policies, wp-admin/login controls, rate limits, trusted proxy/CIDR handling, security headers, recovery, REST outer policy, XML-RPC delegation and privacy-safe logging.

## Watermarker
Exhaustive options preserve original media and generate only WPE-owned derivatives/selected generated sizes. Input/output format support is capability-probed; no format is promised solely by MIME name. Batch/offload/EXIF/animation/failure semantics are documented.

## XML-RPC
Exhaustive spec explicitly preserves the WordPress distinction:
- `xmlrpc_enabled` controls authenticated methods;
- pingback/custom methods are separately governed;
- granular method policy uses actual registered-method semantics.

WPE will not label XML-RPC “fully disabled” unless the selected enforcement mode is actually verified.

---

# Migration/import planning

Sources planned/researched:
- ACF/ACF PRO;
- SCF;
- Meta Box;
- JetEngine;
- CPT UI;
- WooCommerce Memberships/Subscriptions;
- Paid Memberships Pro;
- MemberPress.

Canonical migration flow:
**Discover → Snapshot → Parse → Normalize → Map → Validate → Dry Run → Review → Execute → Verify → Reconcile → Optional Source-Deactivation Readiness**

Fidelity classes:
- exact;
- convertible;
- lossy;
- external-reference;
- unsupported;
- conflict.

Source adapters produce a neutral IR; they do not write target DB tables directly.

No executable migration/source fixtures have run.

---

# Shared architecture contracts

Current planning includes:
- Module dependency/data ownership;
- Capability/Policy matrix;
- Event/Ability catalog + per-module registry;
- Error taxonomy;
- Module lifecycle/uninstall;
- Performance budgets;
- Privacy/data classification + per-module retention matrix;
- Extension SDK/adapter contract;
- Admin IA;
- Contract versioning/deprecation;
- Portable WPE configuration package;
- Source Migration Adapter Registry;
- CI/test matrix plan.

---

# Remaining platform blockers requiring executable evidence

- ADR-0002 compatibility floor;
- ADR-0005 UI/design system runtime;
- ADR-0006 Job Service adapter;
- ADR-0008 Definition Repository physical schema;
- ADR-0009 Secrets Vault exact crypto/key profile;
- ADR-0010 Free↔Pro executable boot/update compatibility;
- ADR-0011 CI implementation;
- ADR-0012 build toolchain/externalization.

These cannot be resolved by claiming paper confidence. The required executable spikes are still **not authorized**.

---

# Verification

## Verified
- all planning writes/commits listed in repository succeeded;
- `OPTION-COVERAGE-MATURITY.md` = 31/31 Exhaustive;
- `IMPLEMENTATION-READINESS-MATRIX.md` = 0/31 Authorized and technically blocked as applicable;
- ADR index and Open Decisions Register synchronized;
- current XML-RPC/WordPress image behavior research was checked against official WordPress developer documentation;
- no implementation/build/test success is claimed.

## Not performed / intentionally blocked
- Composer/npm install;
- runtime PHP/React source;
- plugin bootstrap;
- DB tables/migrations;
- PHPUnit/Playwright;
- executable compatibility/UI/build spikes;
- Definition benchmark;
- Action Scheduler coexistence/load test;
- Vault crypto prototype;
- Free↔Pro boot matrix;
- Membership cache/provider/protected-file tests;
- backup/restore/provider tests;
- Reset execution;
- Protector security fixture;
- Watermark media processing;
- XML-RPC enforcement fixture;
- deployment/release packaging.

Reason: explicit development/executable-spike consent has not been granted.

---

# Next allowed planning-only work

The broad option-enumeration phase is complete. Highest-value non-executable work now is:
1. Definition Repository physical-schema alternative review on paper;
2. Query AST v1 formal schema planning;
3. Relations physical-schema/cardinality alternative planning;
4. Workflow graph/run-state schema planning;
5. Membership privacy/retention operational defaults;
6. OAuth/product-entitlement/updater protocol candidate refinement;
7. Backup archive/provider certification protocol refinement;
8. exact consent-gated spike acceptance criteria for every remaining Proposed ADR;
9. keep checkpoint/PR synchronized.

Before **any executable work**, request and obtain explicit owner development consent.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. relevant ADR/module/architecture/security docs

Repository evidence overrides conversation memory.