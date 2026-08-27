# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, package/dependency setup, crypto key generation, executable benchmark/spike, queue/runner execution, provider/API integration, remote-service transmission/diagnostics upload, email/SMTP sends, Backup/Restore execution or release packaging.

`continue`, `proceed`, planning approval, ADR acceptance, readiness or Phase 0 completion do **not** authorize development.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `AGENTS.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

No production PHP/React source, plugin bootstrap, DB migration/table, package scaffold, executable test/benchmark, queue execution, provider integration, service transmission or cryptographic implementation has been created/run.

## Product specification milestone

- **31/31** surfaces have screen/option inventory.
- **31/31** have behavioral specification.
- **31/31** are at **Exhaustive product-option maturity**.
- **0/31** are Authorized for development.

Exhaustive product specification is not a runtime/security/performance/provider verification claim.

## Accepted ADR state

Accepted decisions now extend through **ADR-0061**.

Latest additions:
- **ADR-0053** — Backup provider C0–C4 restore-first certification.
- **ADR-0054** — Remote-service resource/trust separation + RFC 9457 baseline.
- **ADR-0055** — Connection/provider I0–I5 capability certification.
- **ADR-0056** — Backup Remote Copy commit/verify/retention/delete/restore lifecycle.
- **ADR-0057** — Membership billing source facts + reconciliation + MB0–MB5 certification.
- **ADR-0058** — Email delivery truth + ET0–ET5 provider certification.
- **ADR-0059** — backend-neutral JobService semantics: at-least-once, explicit idempotency, reviewed urgency/fairness, resource/concurrency keys, backpressure/chunking and cooperative cancellation.
- **ADR-0060** — purpose-scoped/minimized Remote Service privacy/retention; Free activation sends nothing; account link is not telemetry consent.
- **ADR-0061** — semantic `bf.*` Backup family keys are canonical; numeric PF aliases are legacy/ambiguous; provider profiles are separately versioned; SE0–SE3 static research never implies C0–C4 certification.

ADR-0006 remains Proposed/P-003 only for the concrete Action Scheduler backend evidence; ADR-0059 fixes the semantics any backend must satisfy.

## Current architecture snapshot

### Platform/Data
- Definition Repository: stable Definitions + immutable Revisions + revision-aware Dependencies.
- Query AST typed/provider-neutral; raw SQL/PHP is not a normal node.
- Relations typed edge model candidate; reverse lookup/cardinality first-class.
- Workflow runs pin published revision and execute through JobService.
- Fields use plural storage families: native meta/options, Custom Tables, Relations, Vault.
- Custom Tables use desired-vs-observed schema + typed Migration Plan.
- Component Blueprint shared across Listings/Dashboard/Builder Widgets; builders are adapters.

### Job Service
- Logical model: `Job Type Registration + Schedule Definition + Job Record + Attempt Record + Runner + Execution Policy`.
- Mutating work assumes at-least-once execution opportunity, never magical exactly-once external effects.
- Backend `unique` scheduling is not business idempotency.
- Urgency classes are restricted and fairness/age protection prevents sustained starvation.
- Resource classes + concurrency keys bound DB/CPU/filesystem/network/provider/destructive work.
- Large workloads use checkpointed chunks/continuations and backpressure/admission.
- Cancellation/pause are cooperative and truthful about checkpoint boundaries.
- Queue ordering is not a business dependency guarantee.
- Request-driven WP-Cron is not exact wall-clock scheduling.
- Action Scheduler remains preferred backend candidate; raw backend priority/batch/claim values are not stable WPE semantics.

### Integrations/Remote Service
- Connections: Safe HTTP + verified webhooks + Event Inbox; I0–I5 certification.
- Remote resource domains have separate trust semantics; TUF update metadata is separate authority.
- Free activation alone performs no WPE remote transmission.
- Account connection is purpose-scoped and does not opt into telemetry.
- Public Catalog/Docs/Release/Status avoid hidden site/account/install IDs where possible.
- Diagnostics requires separate preview + approval.
- RR0–RR6 are purpose-based retention classes.
- Disconnect is distinct from WPE account/support/commercial-history deletion.

### Membership billing
- Billing provider → verified source fact → adapter → reconciliation → Membership policy → Enrollment → Entitlement.
- Cancellation intent is not automatic immediate access revocation.
- Billing provider certification uses MB0–MB5.

### Email delivery
- Notification → Recipient Delivery → Rendered Message → Transport Attempt → Provider Message Reference → verified Provider Event Ledger → derived outcome.
- `wp_mail()`/provider acceptance is not inbox delivery/read proof.
- ET0–ET5 provider certification governs claims.

### Backup/Operations
- manifest-first multipart backup bundle;
- Sodium/Argon2id accepted encryption profile;
- C0–C4 restore-first provider certification;
- durable Remote Copy commit/finalization lifecycle;
- semantic family keys + separately versioned provider profiles;
- Reset and Watermark accepted safety architectures.

## Backup provider identity/capability snapshot

Source:
- `docs/ARCHITECTURE/BACKUP-PROVIDER-FAMILY-CAPABILITY-REGISTRY.md`
- `docs/MODULES/BACKUP-PROVIDER-CERTIFICATION-MATRIX.md`
- `docs/DECISIONS/ADR-0061-backup-provider-family-identity-registry.md`

Current state:
- **34 target destinations**;
- **34/34 have a stable `provider_key` + canonical semantic family assignment**;
- static official-document maturity is tracked separately with SE0–SE3;
- **0 providers are C0–C4 certified**.

Canonical families:
- `bf.local-filesystem`, `bf.browser-export`, `bf.ftp`, `bf.ftps`, `bf.sftp`, `bf.webdav`, `bf.s3`, `bf.gcs`, `bf.azure-blob`, `bf.google-drive`, `bf.msgraph-drive`, `bf.dropbox`, `bf.swift`, `bf.native`.

Important corrections/overrides:
- earlier numeric PF namespaces collided, so bare PF values are not machine identifiers;
- `S3 compatible` never means all Amazon features/limits;
- Scaleway's researched profile documents max 1000 multipart parts and must not inherit Amazon's 10,000-part assumption;
- generic WebDAV is non-resumable by default; Nextcloud/ownCloud chunking is provider/version-specific;
- OneDrive Personal and Business/SharePoint remain separate profiles despite shared Graph family;
- pCloud progress/partial upload is not treated as crash-resumable provider session evidence;
- `bf.native` inherits no capabilities automatically;
- Browser/manual export is not a durable remote Backup destination.

## Platform blockers still requiring executable evidence

- P-001 compatibility;
- P-002 UI/design system;
- **P-003 Action Scheduler/JobService concrete backend**;
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

## Backup future evidence highlights

- family/provider registry schema and adapter selection;
- legacy PF source-namespace import handling;
- auth/least privilege/token/credential rotation;
- provider-specific multipart/chunk/session limits;
- process crash/resume/restart;
- explicit commit + commit-unknown reconciliation;
- checksum/read-back/corruption evidence;
- retention/version/Object Lock/trash/delete semantics;
- external lifecycle/deletion interference;
- C3 full remote restore;
- C4 fresh/disaster restore;
- provider/API-version regression and certification downgrade/expiry behavior.

## Verification state

### Verified planning/documentation
- planning branch isolated from `main`;
- 31/31 Exhaustive;
- 0/31 Authorized;
- ADR index/Open Decisions/Readiness/Checkpoint synchronized through ADR-0061;
- Remote Service privacy/retention matrix + ADR-0060 committed;
- JobService semantic architecture + ADR-0059 committed;
- Email ET0–ET5 architecture/evidence protocol committed;
- Membership MB0–MB5 contract committed;
- Backup semantic family/provider registry + ADR-0061 committed;
- Backup target matrix normalized to stable `bf.*` family keys;
- 34/34 target profiles recorded; 0 certified;
- no implementation/build/test success claimed.

### Not performed / intentionally blocked
- Composer/npm install;
- Action Scheduler package setup/migration;
- queue action creation/runner execution;
- production PHP/React source;
- DB migrations/tables;
- crypto key generation/encryption/signing code;
- PHPUnit/Playwright;
- P-001…P-013 execution;
- provider/API/webhook/SMTP execution;
- WPE service/account-link/diagnostics transmission;
- Backup provider credential/auth/upload/delete/restore probes;
- performance benchmarks;
- Backup/Restore/Reset execution;
- release packaging/deployment.

Reason: explicit owner development/executable-spike consent has not been granted.

## Next allowed planning-only priorities

1. Membership billing provider-specific capability/evidence profiles.
2. Email provider-specific capability matrix.
3. Remote Service consent-gated privacy/retention evidence protocol without executing it.
4. Refresh remaining SE0/SE1 Backup provider official-doc profiles without API execution.
5. Continue narrowing provider/P-003 evidence plans without execution.
6. Keep governance and Draft PR synchronized.

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
