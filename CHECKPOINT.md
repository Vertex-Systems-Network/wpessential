# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, package/dependency setup, crypto key generation, executable benchmark/spike, provider/API integration, email/SMTP sends, Backup/Restore execution or release packaging.

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

Accepted decisions now extend through **ADR-0058**.

Latest additions:
- **ADR-0053** — Backup providers use protocol-family adapters + provider capability profiles; C0–C4 certification; normal Supported Destination label requires C3 Restore Certified.
- **ADR-0054** — Remote-service resources and trust domains are separated; RFC 9457 Problem Details baseline; REST account/catalog data cannot replace signed entitlement/TUF authority.
- **ADR-0055** — Connections are certified by adapter + provider + capability + API version with I0–I5 levels; `Connected` does not imply write/event support.
- **ADR-0056** — Each Backup destination has durable Remote Copy states, provider Commit Point, manifest-last completion, truthful retention/delete semantics and manifest-bound restore identity.
- **ADR-0057** — Membership billing integrations produce verified commercial source facts; reconciliation + WPE policy own Enrollment/Entitlement transitions; billing profiles use MB0–MB5 certification.
- **ADR-0058** — Email transport/API acceptance, receiving-server delivery, failure/complaint/suppression and engagement are separate evidence; provider support uses ET0–ET5 certification and never infers inbox/read truth.

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

### Membership billing
- Billing providers are commercial source-of-fact systems, not direct Membership authorization authority.
- Canonical path: `Billing Source → verified source fact/event → Billing Adapter → reconciliation → Membership policy → Enrollment → Entitlement`.
- Cancellation intent such as `pending-cancel` / `set_to_cancel` is not automatic immediate access revocation.
- Billing certification: MB0 Detected/Mapping → MB1 Source Read → MB2 Grant → MB3 Renewal/Failure/Cancellation → MB4 Refund/Change/Reconciliation → MB5 Production Profile.

### Email delivery
- Canonical path: `Notification Instance → Recipient Delivery → Rendered Message → Transport Attempt → Provider Message Reference → verified Provider Event Ledger → Derived Delivery Outcome`.
- `wp_mail()` success = local transport processing evidence only.
- SMTP/API/provider acceptance is not automatically destination-server delivery.
- WPE `Delivered` is scoped as **Delivered to Receiving Server** only when a certified provider event proves recipient/destination mail-server acceptance.
- Receiving-server acceptance is not inbox placement, visibility or read proof.
- Complaint/suppression/asynchronous failures remain append-oriented facts and may coexist with earlier delivery evidence.
- Open/click are optional telemetry observations, not `Read`.
- Email provider certification: ET0 Configured → ET1 Submission → ET2 Resilient Submission → ET3 Delivery Truth → ET4 Feedback/Suppression/Reconciliation → ET5 Production Profile.
- Initial future evidence order: wp_mail, generic SMTP, SES, SendGrid, Mailgun, Postmark.

### Backup/Operations
- Backup bundle: manifest-first multipart logical recovery point.
- Backup crypto: Sodium secretstream XChaCha20-Poly1305, XChaCha20 DEK wrapping, Argon2id passphrase mode, independent recovery slots; native ext-sodium required for encrypted v1.
- Named target matrix: **34 destinations; 0 certified**.
- Certification: C0 Connectable → C1 Upload → C2 Resumable/Integrity → C3 Restore → C4 Disaster Restore.
- Normal public Supported Destination label requires C3.
- Remote Copy uses explicit commit/verify/degraded/delete states; manifest/completion marker is published last by default.

## Email standards/provider research snapshot

Static official documentation currently supports the accepted truth boundaries:
- WordPress documents that `wp_mail()` true does not mean the user received the email.
- RFC 5321 defines 4yz as transient and 5yz as permanent negative completion, and says `250 OK` after DATA transfers delivery/relay responsibility to that SMTP receiver.
- Amazon SES distinguishes SEND, DELIVERY, BOUNCE, COMPLAINT and DELIVERY_DELAY; DELIVERY is recipient-mail-server delivery.
- Twilio SendGrid distinguishes processed, delivered, deferred, bounce and dropped; delivered is receiving-server delivery.
- Mailgun distinguishes accepted/queued from delivered-to-recipient-server and temporary/permanent failures; current webhook security uses signed timestamp/token semantics.
- Postmark explicitly defines Delivery as receiving-server acceptance and states this does not prove inbox placement.

Evidence contracts:
- `docs/ARCHITECTURE/EMAIL-TRANSPORT-PROVIDER-CERTIFICATION.md`
- `docs/QUALITY/EMAIL-TRANSPORT-CERTIFICATION-EVIDENCE-PROTOCOL.md`

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

## Remaining evidence highlights

Membership:
- physical Enrollment/Entitlement schema, revoke-to-deny cache, protected files, seats/concurrency, MB0–MB5 provider certification, reconciliation/identity/refund/change/restore/privacy fixtures.

Email/Notification:
- physical Recipient Delivery/Attempt/Event indexes;
- renderer/inliner/client compatibility;
- wp_mail/SMTP behavior;
- ET0–ET5 SES/SendGrid/Mailgun/Postmark certification;
- webhook signature/replay/rotation;
- bounce/complaint/suppression/reconciliation;
- unknown-outcome duplicate prevention;
- Job Service rate/backpressure behavior;
- privacy/retention/multisite/restore-clone fixtures.

Backup:
- physical bundle/Remote Copy schema, provider C0–C4 certification, crypto framing/KDF/recovery kit and fresh-server restore.

## Verification state

### Verified
- planning branch isolated from `main`;
- 31/31 Exhaustive;
- 0/31 Authorized;
- ADR index/Open Decisions/Readiness/Checkpoint synchronized through ADR-0058;
- Membership billing contract + MB0–MB5 ADR committed;
- Email delivery-truth contract + ET0–ET5 ADR/evidence protocol committed;
- Backup provider matrix remains 34 targets / 0 certified;
- no implementation/build/test success claimed.

### Not performed / intentionally blocked
- Composer/npm install;
- production PHP/React source;
- plugin activation/bootstrap;
- DB migrations/tables;
- crypto key generation/encryption/signing code;
- PHPUnit/Playwright;
- P-001…P-013 execution;
- SMTP/provider credentials/API/webhook calls or email sends;
- performance benchmarks;
- Backup/Restore/Reset execution;
- release packaging/deployment.

Reason: explicit owner development/executable-spike consent has not been granted.

## Next allowed planning-only priorities

1. Job Service operation classes/priorities/backpressure/fairness/retention paper model.
2. Remote service field-level privacy/retention matrix.
3. Backup family-specific capability overrides for the 34 target destinations.
4. Membership billing provider-specific capability/evidence profiles.
5. Email provider-specific capability matrix.
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
