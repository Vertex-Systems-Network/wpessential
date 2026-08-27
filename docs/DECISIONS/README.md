# WPEssential Architecture Decision Records

Status: **Phase 0 planning / no development authorization**  
Last synchronized: 2026-08-27

ADRs preserve long-lived product, architecture, security, data, compatibility and distribution decisions. Accepted ADRs are never silently changed; a reversal requires a superseding ADR. Exact implementation profiles can remain evidence-gated even when architecture is Accepted.

**Hard rule:** technical acceptance never grants development permission. `/DEVELOPMENT-CONSENT.md` and ADR-0014 require explicit owner consent before source/build/migration/test/benchmark/provider implementation.

## ADR index

| ADR | Status | Decision |
|---|---|---|
| ADR-0001 | Accepted | WordPress.org Free + separately distributed Pro; Pro trial belongs to Pro entitlement |
| ADR-0002 | Proposed blocker | WP 6.9 / PHP 8.3 current minimum candidates; executable compatibility matrix pending |
| ADR-0003 | Accepted | WordPress Abilities as typed reusable action contract |
| ADR-0004 | Accepted | No standard arbitrary PHP eval or unrestricted destructive raw-SQL primitive |
| ADR-0005 | Proposed blocker | WPE UI wrappers + stable WordPress components/DataViews; Untitled visual reference/compatible MIT only |
| ADR-0006 | Proposed blocker | WPE Job Service abstraction; Action Scheduler preferred adapter candidate |
| ADR-0007 | Accepted | Pro expiry preserves data and safe deployed runtime; editing/unsafe operations can lock |
| ADR-0008 | Proposed blocker | Definition Repository identities + immutable revisions + current/published pointers + dependencies |
| ADR-0009 | Proposed blocker | Centralized Secrets Vault; no plaintext fallback; external key separation preferred |
| ADR-0010 | Proposed blocker | Explicit Free↔Pro Platform API compatibility + degraded safe boot |
| ADR-0011 | Proposed blocker | Layered PR/main/nightly/release CI matrix |
| ADR-0012 | Proposed blocker | `@wordpress/build` first candidate; `@wordpress/scripts` fallback; Vite only for proven unmet need |
| ADR-0013 | Accepted | Role ≠ Membership ≠ billing Subscription/Purchase ≠ Entitlement |
| ADR-0014 | Accepted governance | Production development/executable spikes require explicit owner consent; `continue` never authorizes code |
| ADR-0015 | Accepted | Membership outer security denial cannot be bypassed; specificity + same-specificity deny-wins |
| ADR-0016 | Accepted | Enrollment states pending/trialing/active/grace/paused/expired/revoked; cancellation is intent |
| ADR-0017 | Accepted architecture | WPE product entitlement signed, site-bound and freshness-aware; outage ≠ expiry |
| ADR-0018 | Accepted architecture | Pro updates use signed anti-rollback/freeze/key-rotation trust; Free is not external Pro updater |
| ADR-0019 | Accepted | Draft Plan edits do not alter live access; published changes choose follow-current/grandfather/scheduled |
| ADR-0020 | Accepted | Membership team roles separate from WP roles; role sync optional/off by default and provenance-safe |
| ADR-0021 | Accepted architecture | Per-backup DEK + independent disaster-recovery wrapping; WP salts not sole recovery root |
| ADR-0022 | Accepted architecture | Native WP meta/options where natural; Custom Tables for scale/constraints; Relations for relationships; Vault for secrets |
| ADR-0023 | Accepted architecture | Desired schema + typed Migration Plan; `dbDelta()` is one compiler tool, not universal migration language |
| ADR-0024 | Accepted defaults | Membership category-level retention; minimize raw provider/log data; detailed download/IP logging off by default |
| ADR-0025 | Accepted architecture | Form Definitions separate from runtime Entries; pinned revision + typed values + explicit projections |
| ADR-0026 | Accepted architecture | Notification occurrence, recipient/read state and channel delivery attempts are separate domains |
| ADR-0027 | Accepted architecture | Chat canonical runtime independent of transport; private assets; search reauthorizes; membership/team revoke affects access |
| ADR-0028 | Accepted architecture | REST definitions compile to validated runtime descriptors using WP REST + Policy + Query/Data Source/Abilities |
| ADR-0029 | Accepted architecture | Email Definition → compiled descriptor → authorized context → Email IR → HTML/plaintext → delivery attempt |
| ADR-0030 | Accepted security architecture | Profile data, identity changes, credentials, authorization and protected user internals are separate action classes |
| ADR-0031 | Accepted architecture | Dashboard Definition → compiled route/component descriptor → server route resolution → Policy → renderer |
| ADR-0032 | Accepted security architecture | Role mutations require impact/anti-lockout recovery invariant; break-glass uses WordPress/CLI authority, no anonymous backdoor |
| ADR-0033 | Accepted logical architecture | Backup canonical model is manifest-first independently verifiable multipart logical bundle |
| ADR-0034 | Accepted security profile | Account link uses fixed WPE OAuth callback + one-time site-bound completion artifact + PKCE S256; Device flow fallback |
| ADR-0035 | Accepted architecture | One shared Component Blueprint for Builder Widgets, Dashboards and Listings; builders are adapters |
| ADR-0036 | Accepted architecture | Settings Definition separate from site/network scoped runtime value documents; Vault-backed secrets; explicit inheritance |
| ADR-0037 | Accepted architecture | Admin Menu uses runtime discovery + stable transformation rules; menu hiding ≠ authorization; safe mode restores native navigation |
| ADR-0038 | Accepted architecture | Status Manager separates WordPress Post Status adapter from generic domain state machine |
| ADR-0039 | Accepted architecture | Listings compile to authorized Query → visible result set → Component Blueprint SSR; cache safety derived from dependencies |
| ADR-0040 | Accepted security architecture | Centralized Safe HTTP + verified Webhook Gateway + durable normalized Event Inbox for external I/O |
| ADR-0041 | Accepted architecture | Imports use reviewed Plan/Dry Run fingerprint + durable checkpoints + identity map + change journal + truthful rollback classes |
| ADR-0042 | Accepted crypto profile / evidence pending | Product entitlement uses Ed25519 + RFC 8785 JCS + domain separation + root-authorized signer keysets |
| ADR-0043 | Accepted crypto profile / evidence pending | Backup encryption uses Sodium secretstream XChaCha20-Poly1305, XChaCha20 DEK wrapping and Argon2id; native ext-sodium required |
| ADR-0044 | Accepted protocol profile / client pending | Pro automated updates target TUF 1.0-compatible Root/Targets/Snapshot/Timestamp semantics; current PHP-TUF not production-selected |
| ADR-0045 | Accepted security architecture / evidence pending | Protector uses trusted-proxy-aware request gating + shared atomic Rate Limit service + non-authenticating recovery mode |
| ADR-0046 | Accepted media architecture / evidence pending | Watermarker is non-destructive derivative pipeline keyed by source fingerprint + Rule revision + output/engine profile |
| ADR-0047 | Accepted destructive-workflow architecture / evidence pending | Reset uses reviewed Plan + verified restore point + durable journal + recovery-principal invariant + post-health verification |

## Product specification milestone

`docs/MODULES/OPTION-COVERAGE-MATURITY.md` records:
- **31/31** module/platform surfaces at Exhaustive product-option maturity;
- **0/31** Authorized for development.

## Major supporting architecture/security docs

- Definition Repository: `docs/ARCHITECTURE/DEFINITION-REPOSITORY-SCHEMA-ALTERNATIVES.md`
- Query AST: `docs/ARCHITECTURE/QUERY-AST-V1-CANDIDATE-SCHEMA.md`
- Relations: `docs/ARCHITECTURE/RELATION-RUNTIME-SCHEMA-ALTERNATIVES.md`
- Workflow: `docs/ARCHITECTURE/WORKFLOW-RUNTIME-DATA-CANDIDATE.md`
- Field storage: `docs/ARCHITECTURE/FIELD-STORAGE-ARCHITECTURE-ALTERNATIVES.md`
- Custom Tables: `docs/ARCHITECTURE/CUSTOM-TABLES-DDL-MIGRATION-LANGUAGE.md`
- Component Blueprint: `docs/ARCHITECTURE/COMPONENT-BLUEPRINT-RUNTIME-MODEL.md`
- Settings/Admin Menu/Status/Listings/Import: respective files under `docs/ARCHITECTURE/`
- Backup manifest: `docs/ARCHITECTURE/BACKUP-MANIFEST-CHUNK-PROFILE-CANDIDATE.md`
- Reset runtime: `docs/ARCHITECTURE/RESET-EXECUTION-JOURNAL-RECOVERY-RUNTIME.md`
- Entitlement crypto: `docs/SECURITY/PRODUCT-ENTITLEMENT-CRYPTO-PROFILE-CANDIDATE.md`
- Backup crypto: `docs/SECURITY/BACKUP-ENCRYPTION-CRYPTO-PROFILE-CANDIDATE.md`
- Pro updater: `docs/SECURITY/PRO-UPDATE-TUF-PROFILE-CANDIDATE.md`
- Protector: `docs/SECURITY/PROTECTOR-REQUEST-GATE-RATE-LIMIT-RUNTIME.md`
- Watermark: `docs/ARCHITECTURE/WATERMARK-DERIVATIVE-IDENTITY-STORAGE-RUNTIME.md`
- OAuth/Profile/Role/Connections security: corresponding files under `docs/SECURITY/`
- Spike protocols: `docs/QUALITY/CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md`

## Remaining evidence blockers

### Platform
ADR-0002, 0005, 0006, 0008, 0009, 0010, 0011 and 0012 remain executable-evidence blockers.

### Runtime/data
Query compiler/cost budgets, Relation concurrency/indexes, Workflow/Job integration, Field storage scale, Custom Tables compiler/large-table recovery, Forms/Notifications/Chat/REST/Email/Dashboard/Components/Settings/Menu/Status/Listings/Import physical runtime evidence.

### Membership
Enrollment/Entitlement physical schema, revoke-to-deny cache proof, protected-file environments, provider reconciliation, seat concurrency, migration/privacy runtime fixtures.

### Remote service/distribution
ADR-0042 canonicalizer/library/keyset rotation/TTL interoperability; ADR-0044 production TUF client/key custody/conformance; ADR-0034 OAuth endpoint/token lifecycle integration.

### Backup/operations
ADR-0043 exact framing/AAD/KDF parameters/recovery-kit encoding and restore performance; provider certification; ADR-0045 Protector hook/rate-store/proxy fixtures; ADR-0046 image-editor/offload certification; ADR-0047 Reset recovery-store/destructive crash/multisite proof; XML-RPC runtime certification.

No executable evidence may run before explicit owner consent.