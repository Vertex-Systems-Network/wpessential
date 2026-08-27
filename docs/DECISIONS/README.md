# WPEssential Architecture Decision Records

Architecture Decision Records (ADRs) preserve long-lived product, architecture, security, compatibility, data, dependency and distribution decisions.

## Status meanings

- **Proposed** — researched recommendation; not final.
- **Accepted** — source-of-truth decision.
- **Accepted architecture / profile pending** — stable principle accepted; exact library/format/runtime still evidence-gated.
- **Superseded** — replaced by later ADR.
- **Rejected** — considered but intentionally not adopted.

## Rules

1. Accepted ADRs are not silently changed; supersede them with a new ADR.
2. ADRs record why, alternatives, consequences, migration/recovery and review triggers.
3. Proposed Phase-0 blockers must be accepted/superseded before relevant implementation.
4. Even technically accepted work remains blocked until explicit owner development consent under ADR-0014 and `/DEVELOPMENT-CONSENT.md`.
5. Documentation/static research may continue; executable spikes are development and require consent.

## ADR index

| ADR | Status | Decision |
|---|---|---|
| ADR-0001 | **Accepted** | WordPress.org Free + separately distributed Pro; Pro trial belongs to Pro entitlement |
| ADR-0002 | **Proposed blocker** | WordPress 6.9 / PHP 8.3 current minimum candidates; executable matrix pending |
| ADR-0003 | **Accepted** | WordPress Abilities as typed reusable action contract |
| ADR-0004 | **Accepted** | No standard arbitrary PHP eval or unrestricted destructive raw-SQL primitive |
| ADR-0005 | **Proposed blocker** | WPE UI wrappers + stable WordPress components/DataViews; Untitled visual reference/compatible MIT only |
| ADR-0006 | **Proposed blocker** | WPE Job Service abstraction; Action Scheduler preferred adapter candidate |
| ADR-0007 | **Accepted** | Pro expiry preserves data and safe deployed runtime; editing/unsafe operations can lock |
| ADR-0008 | **Proposed blocker** | Definition Repository stable identities + immutable revisions + current/published pointers + dependencies |
| ADR-0009 | **Proposed blocker** | Centralized Secrets Vault; no plaintext fallback; external key separation preferred |
| ADR-0010 | **Proposed blocker** | Explicit Free↔Pro Platform API compatibility + degraded safe boot |
| ADR-0011 | **Proposed blocker** | Layered PR/main/nightly/release CI matrix |
| ADR-0012 | **Proposed blocker** | `@wordpress/build` first candidate; `@wordpress/scripts` fallback; Vite only for proven unmet need |
| ADR-0013 | **Accepted** | Role ≠ Membership ≠ billing Subscription/Purchase ≠ Entitlement |
| ADR-0014 | **Accepted governance** | Production development/executable spikes require explicit owner consent; `continue` never authorizes code |
| ADR-0015 | **Accepted** | Membership outer security denial cannot be bypassed; specificity + same-specificity deny-wins |
| ADR-0016 | **Accepted** | Enrollment states pending/trialing/active/grace/paused/expired/revoked; cancellation is intent |
| ADR-0017 | **Accepted architecture / profile pending** | WPE product entitlement signed, site-bound, freshness-aware; outage ≠ expiry |
| ADR-0018 | **Accepted architecture / protocol pending** | Pro updates use signed anti-rollback/freeze/key-rotation trust; Free is not external Pro updater |
| ADR-0019 | **Accepted** | Draft Plan edits do not alter live access; published changes choose follow-current/grandfather/scheduled |
| ADR-0020 | **Accepted** | Membership team roles separate from WP roles; role sync optional/off by default and provenance-safe |
| ADR-0021 | **Accepted architecture / crypto pending** | Per-backup DEK + independent disaster-recovery wrapping; WP salts not sole recovery root |
| ADR-0022 | **Accepted architecture / adapters pending** | Native WP meta/options where natural; Custom Tables for scale/constraints; Relations for relationships; Vault for secrets |
| ADR-0023 | **Accepted architecture / compiler pending** | Desired schema + typed generated Migration Plan; `dbDelta()` is one compiler tool, not universal migration language |
| ADR-0024 | **Accepted defaults** | Membership category-level retention; minimize raw provider/log data; detailed download/IP logging off by default |
| ADR-0025 | **Accepted architecture / schema pending** | Form Definitions separate from runtime Entries; pinned revision + typed values + explicit projections |
| ADR-0026 | **Accepted architecture / adapters pending** | Notification occurrence, recipient/read state and channel delivery attempts are separate domains |
| ADR-0027 | **Accepted architecture / storage pending** | Chat canonical runtime independent of transport; private assets; search reauthorizes; membership/team revoke affects access |
| ADR-0028 | **Accepted architecture / compiler pending** | REST definitions compile to validated runtime descriptors using WP REST + Policy + Query/Data Source/Abilities |
| ADR-0029 | **Accepted architecture / renderer pending** | Email Definition → compiled descriptor → authorized context → Email IR → HTML/plaintext → delivery attempt |
| ADR-0030 | **Accepted security architecture** | Profile data, identity changes, credentials, authorization and protected user internals are separate action classes |
| ADR-0031 | **Accepted architecture / router pending** | Dashboard Definition → compiled route/component descriptor → server route resolution → Policy → renderer |
| ADR-0032 | **Accepted security architecture** | Role mutations require impact/anti-lockout recovery invariant; break-glass uses WordPress/CLI authority, no anonymous backdoor |
| ADR-0033 | **Accepted logical architecture / physical formats pending** | Backup canonical model is manifest-first independently verifiable multipart logical bundle |
| ADR-0034 | **Accepted security profile / integration pending** | WPE account linking uses fixed WPE OAuth callback + one-time site-bound completion artifact + PKCE S256; Device flow fallback |

---

# Phase 0 implementation rule

Production implementation requires both:

1. relevant technical blockers accepted/superseded with required evidence; and
2. explicit owner development consent.

Current development authorization remains **NOT GRANTED**.

`docs/MODULES/OPTION-COVERAGE-MATURITY.md` records **31/31 surfaces at Exhaustive product-option maturity**, which is not an implementation-ready claim.

---

# Key supporting architecture/security docs

- `docs/ARCHITECTURE/DEFINITION-REPOSITORY-SCHEMA-ALTERNATIVES.md`
- `docs/ARCHITECTURE/QUERY-AST-V1-CANDIDATE-SCHEMA.md`
- `docs/ARCHITECTURE/RELATION-RUNTIME-SCHEMA-ALTERNATIVES.md`
- `docs/ARCHITECTURE/WORKFLOW-RUNTIME-DATA-CANDIDATE.md`
- `docs/ARCHITECTURE/FIELD-STORAGE-ARCHITECTURE-ALTERNATIVES.md`
- `docs/ARCHITECTURE/CUSTOM-TABLES-DDL-MIGRATION-LANGUAGE.md`
- `docs/ARCHITECTURE/FORM-ENTRY-RUNTIME-STORAGE-CANDIDATE.md`
- `docs/ARCHITECTURE/NOTIFICATION-PERSISTENCE-DELIVERY-MODEL.md`
- `docs/ARCHITECTURE/CHAT-RUNTIME-STORAGE-INDEX-ALTERNATIVES.md`
- `docs/ARCHITECTURE/REST-ENDPOINT-COMPILED-RUNTIME-MODEL.md`
- `docs/ARCHITECTURE/EMAIL-RENDERING-DELIVERY-ARCHITECTURE.md`
- `docs/ARCHITECTURE/FRONTEND-DASHBOARD-ROUTE-RUNTIME-MODEL.md`
- `docs/ARCHITECTURE/BACKUP-MANIFEST-CHUNK-PROFILE-CANDIDATE.md`
- `docs/SECURITY/USER-PROFILE-IDENTITY-CHANGE-SECURITY.md`
- `docs/SECURITY/ROLE-CAPABILITY-ANTI-LOCKOUT-RECOVERY.md`
- `docs/SECURITY/OAUTH-ACCOUNT-LINK-THREAT-MODEL-ALTERNATIVES.md`
- `docs/SECURITY/PRODUCT-ENTITLEMENT-SIGNING-OFFLINE-GRACE.md`
- `docs/SECURITY/PRO-UPDATE-SUPPLY-CHAIN-TRUST-MODEL.md`
- `docs/SECURITY/BACKUP-ARCHIVE-ENCRYPTION-KEY-RECOVERY.md`
- `docs/QUALITY/CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md`

---

# Major remaining executable blockers

## Platform
- ADR-0002 compatibility matrix;
- ADR-0005 UI/runtime/accessibility/bundle proof;
- ADR-0006 Job Service adapter/load/coexistence;
- ADR-0008 Definition Repository exact DDL/indexes;
- ADR-0009 Vault exact crypto/key/recovery;
- ADR-0010 Free↔Pro boot/update/downgrade matrix;
- ADR-0011 CI execution;
- ADR-0012 build toolchain/externalization.

## Data/runtime
- Query AST compilers/cost budgets;
- Relation edge indexes/cardinality concurrency;
- Workflow runtime/Job integration;
- field storage scale/migration evidence;
- Custom Tables DDL compiler/large-table recovery;
- Form Entry schema/projections;
- Notification fan-out/dedupe/provider adapters;
- Chat indexes/transport/search projection;
- REST compiler/rate-limit/cache isolation;
- Email renderer/inliner/provider/client certification;
- Dashboard router/component/cache/builder certification;
- Profile protected-meta/session/email-change fixtures;
- Role anti-lockout/recovery fixtures.

## Membership
- Enrollment/Entitlement physical schema/indexes;
- access cache/revoke-to-deny proof;
- protected-file environments;
- billing/reconciliation provider certification;
- seat concurrency and migration fixtures;
- privacy exporter/eraser runtime verification.

## Remote service / distribution
- entitlement signature/canonicalization/library and freshness windows;
- Pro updater exact TUF-compatible client/key custody;
- OAuth ADR-0034 end-to-end integration, token lifetimes/rotation/revocation;
- release tamper/rollback/freeze/update-order fixtures.

## Backup/operations
- manifest-part physical encodings/DB artifact/chunk defaults;
- exact AEAD/KDF/recovery-key profiles;
- provider certification;
- cross-server encrypted restore;
- Reset/Protector/Watermark/XML-RPC runtime certification.

No executable evidence may run before owner consent.