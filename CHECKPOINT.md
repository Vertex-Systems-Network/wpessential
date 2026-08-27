# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, package/dependency setup, executable benchmark or research spike.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

`continue`, `proceed`, planning approval, ADR acceptance, technical readiness or Phase 0 completion do **not** authorize development.

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

Exhaustive specification does not imply physical schema, dependency, provider, performance or security verification.

---

# Accepted ADR state

Accepted decisions now extend through **ADR-0034**.

Latest accepted architecture additions:
- ADR-0029 — Email rendering/delivery separation.
- ADR-0030 — User/Profile identity-security boundaries.
- ADR-0031 — Frontend Dashboard compiled route/component runtime.
- ADR-0032 — Role/capability anti-lockout and WordPress/CLI recovery model.
- ADR-0033 — Backup manifest-first multipart logical bundle.
- ADR-0034 — Account-link OAuth fixed WPE callback + one-time site-bound completion artifact + PKCE S256.

Earlier accepted ADRs cover Free/Pro distribution, Abilities, unsafe code/SQL boundaries, license-expiry runtime, Membership domain/access/lifecycle/change/team semantics, product-entitlement/update trust, Backup encryption/recovery, Field storage, Custom Tables migrations, Membership privacy, Form Entry, Notifications, Chat and REST compiled runtime.

Exact implementation profiles remain pending where ADR status says so.

---

# Platform blockers still requiring executable evidence

- ADR-0002 compatibility floor — P-001.
- ADR-0005 UI/design-system runtime — P-002.
- ADR-0006 Job Service concrete adapter — P-003.
- ADR-0008 Definition Repository exact DDL/indexes — P-004.
- ADR-0009 Secrets Vault exact crypto/key/recovery — P-005.
- ADR-0010 Free↔Pro boot/update/downgrade compatibility — P-006.
- ADR-0011 CI implementation — P-007.
- ADR-0012 build toolchain/externalization — P-008.

Additional Query/Relations/Workflow/Membership/Backup protocols are P-009…P-013.

All protocols live in `docs/QUALITY/CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md` and **none has been executed**.

---

# Current technical paper architecture

## Definition Repository
Stable identity/lifecycle + immutable revisions + current/published pointers + revision-aware dependencies. Runtime data remains separate.

## Query AST
Typed/provider-neutral AST; no raw SQL/PHP node; typed parameters, joins/relations, aggregates, pagination and security/cost budgets.

## Relations
Universal typed edge-table family is current paper preference; reverse lookup and concurrency-safe cardinality first-class.

## Workflow
`Definition Repository → Workflow Runtime → Job Service`; runs pin published definition revision; waits/approvals/steps durable; at-least-once/idempotency/unknown-outcome semantics.

## Custom Fields
ADR-0022: native WP meta/options where natural; Custom Tables for scale/constraints; Relations for relationships; Vault for secrets; no universal EAV/JSON store.

## Custom Tables
ADR-0023: desired schema and observed schema separate; typed generated Migration Plans with fingerprints/risk/preconditions/recovery/verification. `dbDelta()` is one tool, not universal source of truth.

## Form Entries
ADR-0025: runtime Entries separate from Form Definitions; pinned revision, typed values, explicit projections, protected file references; passwords/tokens never Entry values.

## Notifications
ADR-0026: occurrence, per-recipient/read state and per-channel delivery attempts separate; provider handoff ≠ delivered.

## Chat
ADR-0027: canonical runtime independent of transport; private assets; search reauthorizes; Membership/team revocation affects access.

## REST
ADR-0028: published definitions compile to validated descriptors over WordPress REST + Policy + Query/Data Source/Abilities.

## Email
ADR-0029: `Template → Compiled Descriptor → Authorized Context → Email IR → HTML/Plaintext → Delivery Attempt`. No browser-builder runtime as canonical email renderer. Exact renderer/inliner/client certification pending.

## User/Profile security
ADR-0030: ordinary profile data, identity changes, credentials, authorization state and protected internals are separate action classes. Generic fields cannot mutate passwords/sessions/Application Passwords/roles/Membership authority.

## Frontend Dashboard
ADR-0031: `Dashboard Definition → Compiled Route/Component Descriptor → Server Route Resolution → Policy → Renderer`. Client navigation is enhancement only; direct URLs reauthorize server-side.

## Roles/Capabilities
ADR-0032: high-risk mutations require impact/anti-lockout analysis; WPE does not knowingly leave zero recovery principals. Primary break-glass uses WordPress-authenticated/WP-CLI authority; no anonymous recovery backdoor.

## Backup bundle
ADR-0033: manifest-first independently verifiable multipart logical bundle. Single ZIP remains optional small/manual convenience. Exact file-record/DB payload/chunk/compression/hash profiles require evidence.

## OAuth account linking
ADR-0034: public client + Authorization Code/PKCE S256, fixed WPE-owned OAuth callback, one-time site-bound completion artifact, no reusable tokens/passwords in browser return URL. Device Authorization fallback.

---

# Membership status

Accepted semantics/defaults:
- Role ≠ Membership ≠ Billing Subscription/Purchase ≠ Entitlement.
- outer WordPress/security deny cannot be bypassed.
- specificity + same-specificity deny-wins.
- Enrollment lifecycle pending/trialing/active/grace/paused/expired/revoked.
- cancellation-at-period-end is intent.
- Plan draft edits do not alter live access.
- published benefit changes use follow-current/grandfather/scheduled semantics.
- team roles separate from WordPress roles; role sync off by default/provenance-safe.
- category-level privacy/retention defaults; raw provider/detailed download/IP logging minimized/off by default.

Remaining evidence:
- physical Enrollment/Entitlement schema/indexes;
- cache/revoke-to-deny proof;
- protected-file environments;
- team/seat concurrency;
- WooCommerce/Woo Subscriptions/SureCart adapters/reconciliation;
- migration/provider fixtures;
- privacy exporter/eraser runtime verification.

---

# Remote service / distribution

Accepted:
- Free remains account-free/useful locally;
- local WP is not WPE password proxy;
- Free does not auto-install/update external Pro ZIPs;
- product license separate from site Membership access;
- service outage != expiry;
- signed product entitlement and signed Pro update trust required;
- OAuth profile accepted in ADR-0034.

Still open:
- exact OAuth service/token lifetimes/rotation/revocation implementation;
- entitlement canonicalization/algorithm/library/freshness windows;
- updater exact TUF-compatible client/key custody/thresholds.

---

# Backup / recovery

Planning includes:
- 34 destination targets through protocol/provider adapters;
- V0 Generated / V1 Local Verified / V2 Remote Verified / V3 Restore Tested;
- exhaustive Backup/Plan/Destination/Restore UI;
- restore preflight/recovery;
- ADR-0021 encryption/recovery architecture;
- ADR-0033 logical multipart bundle.

Still open:
- exact manifest/part physical encodings;
- DB artifact format;
- chunk-size/compression/hash defaults;
- exact AEAD/KDF/recovery key profile;
- provider certification;
- encrypted cross-server restore proof.

---

# Verification

## Verified
- planning branch remains isolated from `main`;
- 31/31 Exhaustive; 0/31 Authorized;
- ADR index synchronized through ADR-0034;
- Open Decisions synchronized;
- latest Email/Profile/Dashboard/Role/Backup/OAuth architecture docs committed;
- static primary-source research recorded where used;
- no implementation/build/test success claimed.

## Not performed / intentionally blocked
- Composer/npm install;
- production PHP/React source;
- plugin bootstrap/activation;
- DB migrations/tables;
- PHPUnit/Playwright;
- P-001…P-013 spikes;
- provider/API implementations;
- performance benchmarks;
- Backup/Restore/Reset execution;
- release packaging/deployment.

Reason: explicit owner development/executable-spike consent has not been granted.

---

# Next allowed planning-only priorities

1. shared Component Blueprint/runtime schema for Dashboard/Builder Widgets/Listings;
2. Settings Page storage/site-vs-network scope runtime;
3. Admin Menu mutation/conflict/safe-mode model;
4. Status Manager generic state-machine runtime;
5. Dynamic Listings renderer/cache architecture;
6. Connections/Webhooks normalized event inbox + SSRF/replay architecture;
7. Import run/checkpoint/rollback runtime;
8. entitlement/update exact cryptographic profiles on paper;
9. keep Readiness/Checkpoint/PR synchronized.

Before **any executable work**, obtain explicit owner consent.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. `docs/DECISIONS/README.md`
8. relevant module/architecture/security docs

Repository evidence overrides conversational memory.