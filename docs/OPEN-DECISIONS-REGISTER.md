# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-27

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0063**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 | WP/PHP/DB compatibility and multisite matrix — P-001 |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006 + ADR-0059 | Action Scheduler coexistence, Job/Attempt mapping, claim recovery, fairness, concurrency, backpressure, runners, retention and multisite — P-003 |
| D-004 | ADR-0008 + ADR-0049 | Definition Repository exact DDL/index/locking evidence — P-004 |
| D-005 | ADR-0009 + ADR-0048 | Vault exact envelope/rotation/recovery/interoperability — P-005 |
| D-006 | ADR-0010 | Free↔Pro runtime compatibility — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Data/runtime blockers

- **Q-001 / P-009** — Query compiler security/cost/cache/scale.
- **R-001 / P-010** — Relations indexes/cardinality/concurrency/delete behavior.
- **WF-001 / P-011** — Workflow waits/parallel/retry/idempotency/cancel/crash + JobService.
- **F-001/T-001/FE-001** — Field/Table/Form physical storage, migration and runtime evidence.
- **N-001/C-001/REST-001** — Notification/Chat/REST persistence, security and performance evidence.
- **DA/BW/SET/AM/ST/LIST/IMP** — Dashboard/Blueprint/Settings/Menu/Status/Listings/Import runtime evidence.

## C. Membership blockers

### M-001 / P-012 — Enrollment/Entitlement runtime
Open: physical schema/indexes/materialization/scale/multisite, source uniqueness and concurrency.

### M-003 / P-012 — authorization cache
Open: generation/cache/invalidation and revoke-to-deny latency.

### M-005 — protected files
Open: Apache/Nginx/PHP/private object storage/CDN/Range delivery evidence.

### M-006 — provider adapters — ADR-0057 + ADR-0062
Accepted source profiles: `billing.manual`, `billing.woocommerce-order`, `billing.woocommerce-subscriptions`, `billing.surecart`.

Static research maturity: **4 BE3 profiles; 0 MB-certified**.

Open future evidence: exact provider/plugin versions, source identity and duplicate handling, current-state reconciliation, cancellation/failure/recovery, refunds/changes, provider→WP-user resolution, restore/clone, migration/privacy and concurrency.

### M-010 — privacy
Open: exporter/eraser cleanup/runtime/restore verification.

## D. Email / Notification provider evidence

### E-001 — renderer/runtime — ADR-0029 + ADR-0058
Open Email IR renderer/inliner/client compatibility, Recipient Delivery/Transport Attempt/Event Ledger physical schema/indexes, attachment/runtime privacy, JobService fan-out/backpressure and deterministic revision rendering.

### E-002 — initial provider profiles — ADR-0058 + ADR-0063
Accepted source-truth profiles: `email.wordpress-wp-mail`, `email.smtp-generic`, `email.amazon-ses`, `email.twilio-sendgrid`, `email.mailgun`, `email.postmark`.

Static research maturity: **6 EE3 profiles; 0 ET-certified**.

Accepted static rules: `wp_mail()` success is local processing only; generic SMTP relay acceptance is not final inbox/receiving-server proof; SES SEND ≠ DELIVERY; SendGrid processed ≠ delivered; Mailgun accepted ≠ delivered; Postmark Delivery means destination server accepted, not inbox placement; late bounce/complaint facts can coexist with earlier delivery evidence; open/click never becomes Read/Human Seen; webhook-security features are provider-specific and never fabricated.

Open future evidence: exact adapters/versions, credential/rate-limit/outage behavior, provider correlation, webhook security/replay/duplicate/order, unknown outcomes, bounce/complaint/suppression/reconciliation, region/subaccount/stream/config-set isolation, privacy/tag/log redaction, JobService backlog and ET0–ET5 certification.

## E. Remote service / commercial distribution

### S-001 — OAuth — ADR-0034 + ADR-0060
Open exact schemas, one-time artifact/token lifecycle, revocation/disconnect, transfer and replay/concurrency evidence.

### S-002 — entitlement — ADR-0017 + ADR-0042 + ADR-0060
Open canonicalizer/interoperability/key custody/exact envelope/freshness/rotation evidence.

### S-003 — updater — ADR-0018 + ADR-0044
Open production verifier/client, TUF metadata/key custody/expiry and conformance evidence.

### S-004/S-005/S-006 — Support/API/Privacy — ADR-0050 + ADR-0054 + ADR-0060
Open OpenAPI/problem/scopes/idempotency/rate-limit contracts, Support runtime, diagnostics preview/redaction, retention/export/delete, logging and no-hidden-identifier evidence.

Future verification is bounded by `docs/QUALITY/REMOTE-SERVICE-PRIVACY-RETENTION-EVIDENCE-PROTOCOL.md`, which defines **30 consent-gated fixtures; 0 executed**.

## F. Connections / Integrations — ADR-0040 + ADR-0055

Accepted Safe HTTP/Webhook/Event Inbox + I0–I5 certification. Open provider adapters, SSRF/signature/replay/idempotency/order evidence, Event Inbox DDL, reconciliation, API-version registry, redaction and multisite.

## G. Backup / Operations

### B-001 — physical bundle — ADR-0033 / P-013
Open exact file/DB artifact formats, chunking, compression/hash encoding.

### B-002 — crypto — ADR-0021 + ADR-0043
Open exact frame/AAD, Argon2id floor, recovery-kit encoding, resume boundaries and fresh-server restore.

### B-003 — providers — ADR-0053 + ADR-0061 / P-013
Accepted C0–C4 model + semantic `bf.*` families + versioned provider profiles + SE0–SE3 static evidence separation. **34 target profiles, 0 certified**.

Open auth, transfer, limits, integrity, finalization, retention/delete and C3/C4 restore evidence.

### B-004 — Remote Copy — ADR-0056
Open physical schema, commit-unknown reconciliation, re-verification, cleanup, lifecycle interference, alternate-copy failover and restore.

### B-005 / Protector / Watermark / XML-RPC
Open documented physical/runtime compatibility/security evidence.

## H. Identity/Admin security

- **UP-001 / ADR-0030** — protected identity/credential/session/profile evidence.
- **RC-001 / ADR-0032** — capability classifier, anti-lockout, multisite/Super Admin, CLI recovery.
- **DW-001 / ADR-0051** — structured remote widget/iframe/XSS/CSP/assets evidence.

## I. Accepted architecture no longer open semantically

ADRs **0035–0063** preserve accepted core semantics. Evidence may refine version-scoped implementation facts but must not silently redesign accepted cores.

## Decision-processing rule

1. Inspect source of truth and current official standards/docs.
2. Resolve static semantics in ADR when evidence is sufficient.
3. If runtime evidence is required, document bounded protocol only.
4. **Do not install, compile, migrate, benchmark, test, contact providers, send mail, run queues or transmit service data before explicit owner consent.**
5. Synchronize governance after meaningful planning milestones.

## Next planning-only priorities

1. Refresh low-evidence Backup provider profiles from official docs only.
2. Membership provider version/evidence refinement without execution.
3. Email provider version/evidence refinement without execution.
4. Continue narrowing P-003/provider evidence plans.
5. Keep governance synchronized.
