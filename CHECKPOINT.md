# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before any runtime/source/build/migration/test implementation, package/dependency setup, cryptographic key generation, executable benchmark/spike, provider integration or release packaging.

`continue`, `proceed`, planning approval, ADR acceptance, readiness or Phase 0 completion do **not** authorize development.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `AGENTS.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

No production PHP/React source, plugin bootstrap, DB migration/table, package scaffold, executable test/benchmark, provider integration or cryptographic implementation has been created/run.

## Product-spec milestone

- **31/31** surfaces have screen/option inventory.
- **31/31** have behavioral specification.
- **31/31** are at **Exhaustive product-option maturity**.
- **0/31** are Authorized for development.

Primary planning sources:
- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
- `docs/IMPLEMENTATION-READINESS-MATRIX.md`
- `docs/OPEN-DECISIONS-REGISTER.md`
- `docs/DECISIONS/README.md`

Exhaustive product specification is not a runtime/security/performance/provider verification claim.

## Accepted ADR state

Accepted decisions now extend through **ADR-0052**.

Latest accepted additions:
- **ADR-0048** — Secrets Vault uses a random Vault Root Key, per-secret DEKs and versioned key slots; external key preferred, WP-derived convenience slot allowed with warnings, no plaintext fallback.
- **ADR-0049** — Definition Repository relational shape is Definitions + immutable Revisions + revision-aware Dependencies; runtime business data stays outside.
- **ADR-0050** — Support Ticket authority lives in WPE service; WP is a capability-checked client/minimal cache; diagnostics are previewed/redacted before upload.
- **ADR-0051** — Dashboard Widgets treat remote content as untrusted data; remote HTML/JS is not injected into wp-admin; arbitrary iframe is off by default.
- **ADR-0052** — XML-RPC enforcement is layered: endpoint/request gate, registered-method policy, authenticated-method policy and core auth/capability checks are distinct. `xmlrpc_enabled` is not presented as full endpoint disable.

Previously accepted ADRs cover Free/Pro distribution, Abilities, unsafe-code/SQL boundaries, license-expiry continuity, Membership semantics, crypto/update trust, Backup recovery/crypto, Fields/Tables/Forms/Notifications/Chat/REST/Email, Dashboard/Profile/Roles, Component Blueprint, Settings/Admin Menu/Status/Listings, Connections/Webhooks, Import, Protector, Watermark and Reset.

## Platform blockers still requiring executable evidence

- ADR-0002 compatibility floor — P-001.
- ADR-0005 UI/design system — P-002.
- ADR-0006 Job Service adapter — P-003.
- ADR-0010 Free↔Pro executable compatibility — P-006.
- ADR-0011 CI implementation — P-007.
- ADR-0012 build toolchain/externalization — P-008.
- Query/Relations/Workflow/Membership/Backup evidence — P-009…P-013.

ADR-0008 and ADR-0009 have materially narrowed architecture through ADR-0049 and ADR-0048 respectively, but exact DDL/index/crypto interoperability remains evidence-gated.

All future protocols live under `docs/QUALITY/CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md`; **none has been executed**.

## Current architecture snapshot

### Definition/Data platform
- Definition Repository: stable identity rows + immutable revision rows + revision-aware dependencies.
- Query: typed/provider-neutral AST; no raw SQL/PHP node.
- Relations: typed edge model candidate with reverse lookup/cardinality first-class.
- Workflow: runs pin published revisions; execution belongs to Job Service; at-least-once/idempotency semantics.
- Fields: native WP meta/options where natural, Custom Tables for scale/constraints, Relations for relationships, Vault for secrets.
- Custom Tables: desired schema != observed schema; typed Migration Plan controls change.

### Presentation/Admin
- Component Blueprint is shared by Builder Widgets, Dashboard and Listings; third-party builders are adapters.
- Settings separate Definitions from site/network scoped values.
- Admin Menu is runtime-discovered/transformed; hiding is not authorization; safe mode restores native navigation.
- Status Manager separates WordPress Post Status from generic domain state machine.
- Listings use authorization-aware Query results and SSR Component Blueprint.
- Dashboard routes always reauthorize server-side on direct access.
- Dashboard Widgets never trust remote HTML/JS as admin markup.

### Identity/Security
- Generic profile fields cannot mutate passwords, sessions, Application Passwords, roles/caps or Membership authority.
- Role changes preserve a real recovery-principal invariant.
- Vault: random VRK → per-secret DEK → key slots; no plaintext fallback; write-only secret UI.
- Protector: trusted proxy headers only from trusted peers; security rate limiting requires atomic storage semantics.
- XML-RPC: authenticated-method disabling, per-method policy, pingback policy and complete-deny are separate concepts.

### Integrations/Import/Support
- Connections centralizes Safe HTTP, verified webhook ingress and normalized Event Inbox.
- Import uses reviewed Plan/Dry Run fingerprint, durable checkpoint, identity map, change journal and truthful rollback levels.
- Support Tickets are WPE-service authoritative; local client stores only safe minimal cache; attachments/diagnostics require policy + redaction.

## Remote service / distribution

### Product entitlement — ADR-0042
Accepted: Ed25519 + RFC 8785 JCS + WPE domain separation + `kid` + root-authorized signer keysets + freshness/site binding.

Still unverified: canonicalizer/library interoperability, root custody/rotation, exact envelope bytes and operational TTL/skew values.

### Pro updater — ADR-0044
Accepted: TUF 1.0-compatible Root/Targets/Snapshot/Timestamp semantics, rollback/freeze/key-rotation defenses and signed artifact metadata. Current PHP-TUF remains **not production-selected** while upstream warns against production use.

### OAuth — ADR-0034
Accepted: public client + Authorization Code/PKCE S256 + fixed WPE callback + one-time site-bound completion artifact. Exact service/token lifecycle remains unimplemented.

## Backup / Operations

- Backup canonical model: manifest-first independently verifiable multipart bundle.
- Backup crypto: random Backup Set DEK; Sodium secretstream XChaCha20-Poly1305; XChaCha20 DEK wrapping; Argon2id passphrase mode; independent recovery slots; native ext-sodium required for encrypted v1.
- Reset: reviewed Plan → verified restore point → durable journal → staged execution → health verification.
- Watermark: original/current source is never overwritten by WPE; versioned derivatives key off source fingerprint + Rule revision + output/engine profile.
- XML-RPC: layered method/endpoint policy, with compatibility warnings for integrations such as Jetpack.

## Membership

Accepted semantics remain: Role ≠ Membership ≠ Billing ≠ Entitlement; outer security deny cannot be bypassed; deterministic access precedence/lifecycle; Plan revision semantics; teams separate from WP roles; privacy-minimized defaults.

Remaining evidence: physical schema/indexes, revoke-to-deny cache, protected-file delivery, seat concurrency, Woo/Woo Subscriptions/SureCart reconciliation, migration/provider/privacy fixtures.

## Verification state

### Verified
- planning branch isolated from `main`;
- 31/31 Exhaustive; 0/31 Authorized;
- ADR files present through ADR-0052;
- checkpoint synchronized through ADR-0052;
- no implementation/build/test success claimed.

### Not performed / intentionally blocked
- Composer/npm install;
- production PHP/React source;
- plugin activation/bootstrap;
- DB migrations/tables;
- crypto key generation/encryption/signing code;
- PHPUnit/Playwright;
- P-001…P-013 spikes;
- provider/API implementations;
- performance benchmarks;
- Backup/Restore/Reset execution;
- release packaging/deployment.

Reason: explicit owner development/executable-spike consent has not been granted.

## Next allowed planning-only priorities

1. Backup provider certification contract by protocol family and destination capability profile.
2. Remote Service API schemas for account, entitlement, site activation, plans, support and signed metadata.
3. Provider-neutral Connection capability contract and certification states.
4. Backup restore/provider commit/finalization semantics.
5. Extend consent-gated spike protocols where new evidence gates are identified.
6. Keep Readiness/Open Decisions/Draft PR synchronized.

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
