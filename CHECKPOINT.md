# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, package/dependency setup, crypto key generation, executable benchmark/spike, provider/API integration, Backup/Restore execution or release packaging.

`continue`, `proceed`, planning approval, ADR acceptance, readiness or Phase 0 completion do **not** authorize development.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `AGENTS.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

No production PHP/React source, plugin bootstrap, DB migration/table, package scaffold, executable test/benchmark, provider integration or cryptographic implementation has been created/run.

## Product specification milestone

- **31/31** surfaces have screen/option inventory.
- **31/31** have behavioral specification.
- **31/31** are at **Exhaustive product-option maturity**.
- **0/31** are Authorized for development.

Exhaustive product specification is not a runtime/security/performance/provider verification claim.

## Accepted ADR state

Accepted decisions now extend through **ADR-0056**.

Latest additions:
- **ADR-0053** — Backup providers use protocol-family adapters + provider capability profiles; C0–C4 certification; normal Supported Destination label requires C3 Restore Certified.
- **ADR-0054** — Remote-service resources and trust domains are separated; RFC 9457 Problem Details baseline; REST account/catalog data cannot replace signed entitlement/TUF authority.
- **ADR-0055** — Connections are certified by adapter + provider + capability + API version with I0–I5 levels; `Connected` does not imply write/event support.
- **ADR-0056** — Each Backup destination has durable Remote Copy states, provider Commit Point, manifest-last completion, truthful retention/delete semantics and manifest-bound restore identity.

Earlier ADRs through ADR-0052 preserve Free/Pro distribution, development-consent governance, Abilities, unsafe-code/SQL boundaries, Membership semantics, data/runtime architecture, crypto/update trust, OAuth, Component/Settings/Menu/Status/Listings/Connections/Import, Protector/Watermark/Reset, Vault, Definition Repository, Support, Dashboard Widget content trust and XML-RPC.

## Current architecture snapshot

### Platform/Data
- Definition Repository: stable Definitions + immutable Revisions + revision-aware Dependencies.
- Query AST typed/provider-neutral; raw SQL/PHP is not a normal node.
- Relations typed edge model candidate; reverse lookup/cardinality first-class.
- Workflow runs pin published revision and execute via Job Service contract.
- Fields use plural storage families: native meta/options, Custom Tables, Relations, Vault.
- Custom Tables use desired-vs-observed schema + typed Migration Plan.
- Component Blueprint shared across Listings/Dashboard/Builder Widgets; builders are adapters.

### Security/Identity
- Generic Profile fields cannot mutate passwords/sessions/Application Passwords/roles/Membership authority.
- Role mutations preserve a real recovery-principal invariant.
- Vault: random VRK → per-secret DEKs → versioned key slots; no plaintext fallback.
- Protector uses trusted-proxy-aware identity + atomic Rate Limit service.
- XML-RPC complete-deny/authenticated-off/pingback/per-method states are separate.

### Integrations/Remote Service
- Connections: Safe HTTP + verified webhooks + Event Inbox.
- Generic integration certification: I0 Configurable → I1 Auth → I2 Read → I3 Write/Action → I4 Events/Reconciliation → I5 Production Profile.
- Remote service resources: Account, Site Activation, signed Entitlement, Catalog, Support, Docs, Release Notes, Status; TUF update metadata is separate trust authority.
- Support service is authoritative for tickets/messages/attachments; WP client/cache is capability-checked/minimal.

### Backup/Operations
- Backup bundle: manifest-first multipart logical recovery point.
- Backup crypto: Sodium secretstream XChaCha20-Poly1305, XChaCha20 DEK wrapping, Argon2id passphrase mode, independent recovery slots; native ext-sodium required for encrypted v1.
- Provider architecture: protocol families + provider profiles.
- Named target matrix: **34 destinations; 0 certified**.
- Certification: C0 Connectable, C1 Upload, C2 Resumable/Integrity (or explicit non-resumable integrity), C3 Restore, C4 Disaster Restore.
- Normal public Supported Destination label requires C3.
- Remote Copy: planned/staging/uploading/finalizing/committed/verifying/verified plus explicit degraded/delete states.
- Manifest/completion marker published last by default; provider Commit Point defines remote transaction boundary.
- Retention protects the last required verified recovery copy.
- Reset: reviewed Plan → verified restore point → durable journal → staged execution → health verification.
- Watermark: WPE original/current source untouched; deterministic versioned derivatives.

## Backup provider research snapshot

Static official-doc research supports current family planning:
- Amazon S3: multipart independent parts, completion/abort, checksum APIs; multipart ETag not assumed whole-object MD5.
- Google Drive: resumable upload sessions with chunk/status/resume and expiry.
- Google Cloud Storage: resumable uploads intended for large/interrupted transfer; final object only after completion.
- Microsoft Graph Drives: upload sessions with expiry and expected/missing ranges.
- Dropbox: upload sessions start/append/finish + content-hash semantics.
- WebDAV: RFC 4918 does not supply one universal resumable large-upload session profile.
- SFTP: actual resume/rename semantics require client/server certification; no fictional final SFTP RFC assumption.

Provider evidence contract lives in:
- `docs/ARCHITECTURE/BACKUP-PROVIDER-CERTIFICATION-CONTRACT.md`
- `docs/MODULES/BACKUP-PROVIDER-CERTIFICATION-MATRIX.md`
- `docs/ARCHITECTURE/BACKUP-REMOTE-COPY-LIFECYCLE.md`
- `docs/QUALITY/BACKUP-PROVIDER-CERTIFICATION-EVIDENCE-PROTOCOL.md`

## Platform blockers still requiring executable evidence

- P-001 compatibility;
- P-002 UI/design system;
- P-003 Job Service adapter;
- P-004 Definition Repository exact DDL/indexes;
- P-005 Vault exact envelope/interoperability;
- P-006 Free↔Pro runtime compatibility;
- P-007 CI;
- P-008 build toolchain;
- P-009 Query;
- P-010 Relations;
- P-011 Workflow;
- P-012 Membership;
- P-013 Backup/provider certification.

**None has been executed.**

## Membership remaining evidence

- physical Enrollment/Entitlement schema;
- revoke-to-deny cache proof;
- protected-file delivery;
- team/seat concurrency;
- WooCommerce/Woo Subscriptions/SureCart billing/reconciliation certification;
- migration/provider/privacy fixtures.

## Verification state

### Verified
- planning branch isolated from `main`;
- 31/31 Exhaustive;
- 0/31 Authorized;
- ADR index synchronized through ADR-0056;
- named Backup target matrix aligned to ADR-0053 C0–C4 model;
- Remote Service resource schemas and Connection I0–I5 certification docs committed;
- Backup provider evidence protocol documented but not executed;
- no implementation/build/test success claimed.

### Not performed / intentionally blocked
- Composer/npm install;
- production PHP/React source;
- plugin activation/bootstrap;
- DB migrations/tables;
- crypto key generation/encryption/signing code;
- PHPUnit/Playwright;
- P-001…P-013 execution;
- provider credentials/API calls/uploads;
- performance benchmarks;
- Backup/Restore/Reset execution;
- release packaging/deployment.

Reason: explicit owner development/executable-spike consent has not been granted.

## Next allowed planning-only priorities

1. Membership billing-provider adapter certification contract (Manual/WooCommerce/Woo Subscriptions/SureCart).
2. Email transport/provider certification contract and delivery/bounce truth model.
3. Job Service queues/priorities/backpressure/fairness/retention paper model.
4. Remote service field-level privacy/retention matrix.
5. Backup family-specific capability overrides for the 34 target destinations.
6. Keep Open Decisions/Readiness/ADR index/Draft PR synchronized.

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
