# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependency/package setup, executable benchmark or provider/API integration.

`continue`, `proceed`, planning approval, ADR acceptance, technical readiness or Phase 0 completion do **not** authorize development.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

No production PHP/React source, plugin bootstrap, DB migration/table, package scaffold, implementation test, benchmark or provider integration has been created/run.

---

# Product specification milestone

- **31/31** surfaces have screen/option inventory.
- **31/31** have behavioral specification.
- **31/31** are at **Exhaustive product-option maturity**.
- **0/31** are Authorized for development.

Primary planning sources:
- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
- `docs/MODULES/README.md`
- `docs/IMPLEMENTATION-READINESS-MATRIX.md`
- `docs/OPEN-DECISIONS-REGISTER.md`
- `docs/DECISIONS/README.md`

Exhaustive specification does not imply physical schema, provider, performance or security verification.

---

# Accepted ADR state

Accepted architecture/product/security decisions now extend through **ADR-0041**.

Latest accepted decisions:
- ADR-0035 — shared Component Blueprint runtime across Builder Widgets/Dashboard/Listings.
- ADR-0036 — Settings Definition separated from scoped site/network runtime value documents.
- ADR-0037 — Admin Menu runtime discovery + stable transformation rules + safe mode; hide ≠ authorization.
- ADR-0038 — Status Manager separates WordPress Post Status adapter from generic domain state machine.
- ADR-0039 — Dynamic Listing compiled runtime with authorization-aware pagination/cache and Component Blueprint SSR.
- ADR-0040 — centralized Safe HTTP + verified Webhook Gateway + durable normalized Event Inbox.
- ADR-0041 — durable Import Plan/Dry Run/checkpoint/identity-map/change-journal/rollback architecture.

Earlier accepted ADRs cover Free/Pro distribution, Abilities, unsafe code/SQL boundaries, license-expiry runtime, Membership semantics, product entitlement/update trust, Backup encryption/logical bundle, Field storage, Custom Tables migration, privacy, Form Entries, Notifications, Chat, REST, Email, Profile security, Dashboard runtime, Role anti-lockout and OAuth.

---

# Platform blockers still requiring executable evidence

- ADR-0002 compatibility floor — P-001.
- ADR-0005 UI/design system — P-002.
- ADR-0006 Job Service adapter — P-003.
- ADR-0008 Definition Repository exact DDL/indexes — P-004.
- ADR-0009 Secrets Vault exact crypto/key/recovery — P-005.
- ADR-0010 Free↔Pro executable compatibility — P-006.
- ADR-0011 CI implementation — P-007.
- ADR-0012 build toolchain/externalization — P-008.
- Query/Relations/Workflow/Membership/Backup evidence — P-009…P-013.

All protocols are documented under `docs/QUALITY/CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md`; **none has been executed**.

---

# Current shared runtime architecture

## Definition / Query / Relations / Workflow
- Definition Repository: stable identity + immutable revisions + current/published pointers + dependencies.
- Query AST: typed/provider-neutral; no raw SQL/PHP node.
- Relations: universal typed edge-table family is paper preference; reverse lookup/cardinality first-class.
- Workflow: `Definition Repository → Workflow Runtime → Job Service`; active runs pin published revision.

## Data and builders
- Fields: native WP meta/options where natural; Custom Tables for scale/constraints; Relations for relationships; Vault for secrets.
- Custom Tables: desired vs observed schema + typed Migration Plan.
- Component Blueprint ADR-0035: one portable component model; builders are adapters; shared server renderer default.
- Settings ADR-0036: explicit site/network/network-default+site-override values; Vault secrets; REST off by default.
- Admin Menu ADR-0037: dynamic menu discovery + stable transformation; corrupt rules fall back native WP navigation.
- Status ADR-0038: Post Status adapter and generic state machine are separate.
- Listings ADR-0039: `Compiled Listing → Authorized Query → Visible Result Set → Component Blueprint SSR → optional enhancement`.

## Application runtime
- Form Entry ADR-0025.
- Notifications ADR-0026.
- Chat ADR-0027.
- REST ADR-0028.
- Email ADR-0029.
- User/Profile security ADR-0030.
- Frontend Dashboard ADR-0031.
- Role anti-lockout ADR-0032.

## Integrations/import
- Connections ADR-0040 centralizes outbound safe HTTP, webhook verification/replay/idempotency and normalized events.
- Import ADR-0041 uses reviewed Plan/Dry Run fingerprint, durable checkpoints, identity mapping and truthful rollback classes.

## Backup/account
- Backup ADR-0021 + ADR-0033: per-backup DEK, independent recovery wrapping, manifest-first multipart logical bundle.
- OAuth ADR-0034: public client + Authorization Code/PKCE S256 + fixed WPE callback + one-time site-bound completion artifact.

---

# Membership state

Accepted semantics/defaults remain:
- Role ≠ Membership ≠ Subscription/Purchase ≠ Entitlement.
- outer security deny cannot be bypassed.
- specificity + same-specificity deny-wins.
- canonical Enrollment lifecycle.
- Draft Plan edits do not alter live access.
- published benefit changes use follow-current/grandfather/scheduled modes.
- team roles separate from WP roles; role sync off by default/provenance-safe.
- raw provider/detailed download/IP logging minimized/off by default.

Remaining evidence: physical schema/indexes, cache/revoke-to-deny, protected-file environments, team concurrency, billing/reconciliation adapters, migration/provider and privacy-runtime fixtures.

---

# Remote service / distribution

Accepted:
- Free stays useful/account-free;
- local WP is not WPE password proxy;
- Free does not auto-install/update external Pro ZIP;
- product license separate from site Membership access;
- service outage ≠ expiry;
- product entitlement is signed/freshness-aware;
- Pro update channel requires signed anti-rollback/freeze trust;
- OAuth profile accepted in ADR-0034.

Open exact profiles:
- entitlement envelope/algorithm/key rotation/freshness windows;
- updater TUF-compatible client/metadata/key custody;
- OAuth endpoint/token lifetimes/rotation/revocation implementation.

---

# Backup / recovery

Planning includes:
- 34 destination targets through protocol/provider adapters;
- V0 Generated / V1 Local Verified / V2 Remote Verified / V3 Restore Tested;
- exhaustive Backup/Restore options;
- per-backup DEK + independent recovery wrapping;
- manifest-first multipart logical bundle.

Open: physical file-record/DB encoding, chunk/compression/hash defaults, AEAD/KDF/recovery-key profile, provider certification and cross-server encrypted restore proof.

---

# Verification

## Verified
- planning branch remains isolated from `main`;
- 31/31 Exhaustive; 0/31 Authorized;
- ADR index synchronized through ADR-0041;
- Open Decisions synchronized with latest accepted architecture;
- latest Component/Settings/Menu/Status/Listing/Connections/Import docs committed;
- no implementation/build/test success claimed.

## Not performed / intentionally blocked
- Composer/npm install;
- production PHP/React source;
- plugin activation/bootstrap;
- DB migrations/tables;
- PHPUnit/Playwright;
- P-001…P-013 spikes;
- provider/API implementation;
- performance benchmarks;
- Backup/Restore/Reset execution;
- release packaging/deployment.

Reason: explicit owner development/executable-spike consent has not been granted.

---

# Next allowed planning-only priorities

1. product-entitlement exact signed-envelope/profile candidates;
2. Backup AEAD/KDF/recovery-key profile;
3. Pro updater TUF role/key/expiry profile;
4. Protector rate-limit/recovery runtime;
5. Watermark derivative identity/storage runtime;
6. Reset execution journal/recovery runtime;
7. synchronize Readiness/Checkpoint/PR after each milestone.

Before **any executable work**, obtain explicit owner consent.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. `docs/DECISIONS/README.md`
8. relevant architecture/security/module docs

Repository evidence overrides conversational memory.