# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0068**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 | WP/PHP/DB compatibility and multisite matrix — P-001 |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006 + ADR-0059 + ADR-0068 | Action Scheduler 4.1.x candidate packaging/coexistence; Free/Pro/Woo/third-party load order; Job/Attempt mapping; claim recovery; uniqueness/history separation; fairness/concurrency/backpressure; runners; retention; multisite — P-003 |
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
- **M-006 / ADR-0057 + ADR-0062 + ADR-0066** — source truth + versioning accepted; four source profiles remain **BE3 static-paper / 0 MB-certified**.

Current 2026-08-28 paper snapshot:
- `billing.manual` — WPE-owned runtime version pending;
- `billing.woocommerce-order` — WooCommerce 11.0.1 current snapshot;
- `billing.woocommerce-subscriptions` — WCS 9.1.0 / Woo 11.0 current snapshot; HPOS first-class;
- `billing.surecart` — SureCart WP 4.7.0 + separately tracked hosted API/event profile.

Open: exact certified ranges, HPOS/legacy matrix, identity/reconciliation, refunds/changes/recovery, provider upgrade/downgrade/security-advisory behavior, user resolution, clone/migration/privacy and concurrency.

- **M-010** — exporter/eraser cleanup/runtime/restore verification.

## D. Email / Notification provider evidence

- **E-001 / ADR-0029 + ADR-0058** — Email IR renderer/inliner/client compatibility, Recipient Delivery/Transport Attempt/Event Ledger physical schema/indexes, attachment privacy, Job fan-out/backpressure.
- **E-002 / ADR-0058 + ADR-0063 + ADR-0067** — six profiles remain **EE3 static-paper / 0 ET-certified**.

Version identities now accepted:
- `wp_mail` → WordPress/P-001 runtime profile;
- generic SMTP → negotiated transport/TLS/AUTH capability profile;
- SES → API v2 + region/account/event-publishing profile;
- SendGrid → Web API v3 + dated Event Webhook/security profile;
- Mailgun → endpoint-specific path-version families + dated webhook/security + region profile;
- Postmark → dated REST/webhook schema + Basic Auth/IP-allowlist security profile, not an invented API-v1 label.

Open: exact adapters/ranges, correlation, webhook authenticity/replay/order, schema drift, unknown outcomes, bounce/complaint/suppression, privacy/redaction and ET0–ET5 certification.

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
- **B-003 / ADR-0053 + ADR-0061 + ADR-0064 + ADR-0065 / P-013** — C0–C4 model + semantic `bf.*` families + versioned provider/static profiles + explicit local/browser/FTP/FTPS/SFTP semantics. **34 targets, 0 certified**.
- **B-004 / ADR-0056** — Remote Copy physical schema, commit-unknown reconciliation, re-verification, cleanup, lifecycle interference, alternate-copy failover and restore.
- **B-005 / Protector / Watermark / XML-RPC** — documented physical/runtime compatibility/security evidence remains open.

## H. Job backend — ADR-0059 + ADR-0068

Accepted static architecture:
- Action Scheduler is preferred backend candidate, currently reviewed at 4.1.0;
- WPE Platform/Free owns one bundled candidate if selected; Pro/modules do not bundle duplicates;
- shared/newest registered runtime may be selected by Action Scheduler; WPE does not force its bundled copy;
- only JobService adapter uses `as_*` APIs;
- large/sensitive payloads and secrets stay out of AS action args;
- WPE business idempotency does not depend on AS unique scheduling;
- WPE Job/Attempt/Audit retention does not depend on AS cleanup defaults.

Open P-003: packaging mechanism, load-order with Woo/third parties, public capability/runtime detection, tables/migrations, mapping/claims/crashes, fairness/concurrency/backpressure, runners, recurrence, cleanup, multisite, upgrade/downgrade/newer-unverified behavior.

## I. Identity/Admin security

- **UP-001 / ADR-0030** — protected identity/credential/session/profile evidence.
- **RC-001 / ADR-0032** — capability classifier, anti-lockout, multisite/Super Admin, CLI recovery.
- **DW-001 / ADR-0051** — structured remote widget/iframe/XSS/CSP/assets evidence.

## J. Accepted architecture no longer open semantically

ADRs **0035–0068** preserve accepted core semantics. Evidence may refine version-scoped implementation facts but must not silently redesign accepted cores. Provider/version/package research is paper evidence only, never runtime certification.

## Decision-processing rule

1. Inspect source of truth and current official standards/docs.
2. Resolve static semantics in ADR when evidence is sufficient.
3. If runtime evidence is required, document bounded protocol only.
4. **Do not install, compile, migrate, benchmark, test, contact providers, send mail, run queues or transmit service data before explicit owner consent.**
5. Synchronize governance after meaningful planning milestones.

## Next planning-only priorities

1. Unified WordPress Multisite scope/ownership architecture.
2. Remaining physical/runtime paper models where static decisions are useful.
3. Continue refining provider/version evidence only when current official facts materially change architecture.
4. Keep P-003/P-012/P-013 executable gates intact.
