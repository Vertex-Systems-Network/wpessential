# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-27

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0064**.

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

- **M-001 / P-012** — Enrollment/Entitlement schema/indexes/materialization/scale/multisite and concurrency.
- **M-003 / P-012** — authorization generation/cache/invalidation and revoke-to-deny latency.
- **M-005** — protected-file delivery across Apache/Nginx/PHP/private object storage/CDN/Range.
- **M-006 / ADR-0057 + ADR-0062** — four source profiles (`billing.manual`, Woo order, Woo Subscriptions, SureCart) are **BE3 static-paper / 0 MB-certified**. Exact versions, identity, reconciliation, refunds/changes, recovery, user resolution, clone/migration/privacy and concurrency remain open.
- **M-010** — exporter/eraser cleanup/runtime/restore verification.

## D. Email / Notification provider evidence

- **E-001 / ADR-0029 + ADR-0058** — Email IR renderer/inliner/client compatibility, Recipient Delivery/Transport Attempt/Event Ledger physical schema/indexes, attachment privacy, Job fan-out/backpressure.
- **E-002 / ADR-0058 + ADR-0063** — six profiles (`wp_mail`, generic SMTP, SES, SendGrid, Mailgun, Postmark) are **EE3 static-paper / 0 ET-certified**. Exact adapter versions, correlation, webhook authenticity/replay/order, unknown outcomes, bounce/complaint/suppression/reconciliation, provider scope isolation, privacy/redaction and ET0–ET5 remain open.

Provider terms cannot override WPE truth: `wp_mail` success is local only; SES SEND ≠ DELIVERY; SendGrid processed ≠ delivered; Mailgun accepted ≠ delivered; Postmark Delivery ≠ inbox placement; open/click ≠ human read.

## E. Remote service / commercial distribution

- **S-001 / ADR-0034 + ADR-0060** — OAuth exact schemas, artifact/token lifecycle, revoke/disconnect/transfer/replay/concurrency.
- **S-002 / ADR-0017 + ADR-0042 + ADR-0060** — entitlement canonicalizer/interoperability/key custody/envelope/freshness/rotation.
- **S-003 / ADR-0018 + ADR-0044** — production TUF verifier/client, metadata/key custody/expiry/conformance.
- **S-004/S-005/S-006 / ADR-0050 + ADR-0054 + ADR-0060** — OpenAPI/problem/scopes/idempotency/rate-limit, Support runtime, diagnostics, RR0–RR6 retention/export/delete/logging/no-hidden-identifier evidence.

Future verification is bounded by `docs/QUALITY/REMOTE-SERVICE-PRIVACY-RETENTION-EVIDENCE-PROTOCOL.md`: **30 fixtures documented, 0 executed**.

## F. Connections / Integrations — ADR-0040 + ADR-0055

Accepted Safe HTTP/Webhook/Event Inbox + I0–I5 certification. Open provider adapters, SSRF/signature/replay/idempotency/order evidence, Event Inbox DDL, reconciliation, API-version registry, redaction and multisite.

## G. Backup / Operations

- **B-001 / ADR-0033 / P-013** — exact file/DB artifact formats, chunking, compression/hash encoding.
- **B-002 / ADR-0021 + ADR-0043** — exact frame/AAD, Argon2id floor, recovery-kit encoding, resume boundaries and fresh-server restore.
- **B-003 / ADR-0053 + ADR-0061 + ADR-0064 / P-013** — C0–C4 model + semantic `bf.*` families + versioned provider profiles + versioned static-evidence overlays. **34 target profiles, 0 certified**.

Current static overlay upgrades:
- Box SE3;
- MinIO SE3;
- Rackspace SE2;
- Akamai/Linode SE2;
- Hetzner SE3;
- Bunny Storage SE2 (non-resumable claim until stronger Storage evidence);
- MEGA SE1.

Static overlay does not change public support status. Open auth/transfer/limits/integrity/finalization/retention/delete/C3/C4 restore evidence remains.

- **B-004 / ADR-0056** — Remote Copy physical schema, commit-unknown reconciliation, re-verification, cleanup, lifecycle interference, alternate-copy failover and restore.
- **B-005 / Protector / Watermark / XML-RPC** — documented physical/runtime compatibility/security evidence remains open.

## H. Identity/Admin security

- **UP-001 / ADR-0030** — protected identity/credential/session/profile evidence.
- **RC-001 / ADR-0032** — capability classifier, anti-lockout, multisite/Super Admin, CLI recovery.
- **DW-001 / ADR-0051** — structured remote widget/iframe/XSS/CSP/assets evidence.

## I. Accepted architecture no longer open semantically

ADRs **0035–0064** preserve accepted core semantics. Evidence may refine version-scoped implementation facts but must not silently redesign accepted cores. Static provider overlays are paper evidence only, never certification.

## Decision-processing rule

1. Inspect source of truth and current official standards/docs.
2. Resolve static semantics in ADR when evidence is sufficient.
3. If runtime evidence is required, document bounded protocol only.
4. **Do not install, compile, migrate, benchmark, test, contact providers, send mail, run queues or transmit service data before explicit owner consent.**
5. Synchronize governance after meaningful planning milestones.

## Next planning-only priorities

1. Generic FTP/FTPS/SFTP/local/browser protocol/library evidence without execution.
2. Membership provider version/evidence refinement.
3. Email provider version/evidence refinement.
4. Continue narrowing P-003/provider evidence plans.
5. Consolidate provider overlays only when useful; do not fake SE3 maturity.
