# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0071**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 + ADR-0069 | WP/PHP/DB compatibility and Multisite activation/scope/isolation matrix — P-001 + MS protocol |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006 + ADR-0059 + ADR-0068 + ADR-0069 + ADR-0071 | Action Scheduler packaging/coexistence; WPE Job/Attempt physical PT-C/PT-D mapping; claims/fairness/concurrency/backpressure/runners/retention + Multisite fan-out/isolation — P-003 |
| D-004 | ADR-0008 + ADR-0049 + ADR-0069 + ADR-0071 | Definition Repository PT-C preferred topology; exact DDL/index/locking/tombstone/site-network scale — P-004 |
| D-005 | ADR-0009 + ADR-0048 + ADR-0069 | Vault envelope/rotation/recovery/interoperability + network-shared/site-private isolation — P-005 |
| D-006 | ADR-0010 + ADR-0069 + ADR-0070 | Free↔Pro runtime compatibility including Multisite/site-allocation/version-skew lifecycle — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Physical topology / data-runtime blockers — ADR-0071

Accepted topology classes:
- PT-A native WordPress site/blog storage;
- PT-B native WordPress network/global primitives;
- PT-C WPE global scoped control-plane tables;
- PT-D WPE global scoped high-volume runtime tables;
- PT-E WPE per-site custom runtime tables;
- PT-F external authoritative state + local scoped references/cache.

Current paper preferences:
- Definition Repository → PT-C;
- Job logical history → PT-C/PT-D;
- Audit → PT-D;
- Relations → PT-D candidate;
- Membership Enrollment/Entitlement → PT-D candidate;
- Workflow runtime → PT-D candidate;
- Notification/Email operational state → PT-D candidate;
- Event Inbox → PT-D candidate;
- Form Entries → PT-D vs PT-E evidence-gated;
- Chat Messages → PT-D vs PT-E evidence-gated;
- Custom Tables → explicit PT-D/PT-E by product scope;
- Support/commercial authority → PT-F.

Open evidence:
- **Q-001 / P-009 + ADR-0069/0071** — Query compiler security/cost/cache/scale plus authorized bounded network aggregation against chosen storage adapters.
- **R-001 / P-010 + ADR-0069/0071** — global scoped Relation edge-table indexes/cardinality/concurrency/delete/site-transfer scale vs per-site alternative.
- **WF-001 / P-011 + ADR-0069/0071** — Workflow PT-D runtime indexes/waits/parallel/retry/idempotency/cancel/crash + network coordinator.
- **F-001/T-001/FE-001** — Field/Table/Form storage/migration; Forms PT-D vs PT-E benchmark; site deletion/Backup extraction.
- **N-001/C-001/REST-001** — Notification PT-D candidate, Chat PT-D vs PT-E, REST compiled runtime persistence/security/performance.
- **Audit/Event Inbox** — PT-D retention/index/partition evidence.
- **DA/BW/SET/AM/ST/LIST/IMP** — Dashboard/Blueprint/Settings/Menu/Status/Listings/Import runtime + Multisite scope evidence.

No DDL/index/DB benchmark has been executed.

## C. Multisite blockers — ADR-0069 + ADR-0071

Logical architecture is Accepted and **31/31 surfaces have scope behavior mapped**.

Future certification protocol:
- MS0 Static Compatible;
- MS1 Activation & Site Isolation;
- MS2 Scope Runtime Certified;
- MS3 intentional Cross-Site/Network Operations Certified;
- MS4 Large-Network & Disaster Certified.

Current state: **0 Multisite runtime fixtures executed; 0 surfaces MS1+ certified**.

Open evidence:
- subdirectory/subdomain networks;
- Network Admin/Site Admin/Super Admin target authorization;
- nested `switch_to_blog()`/restore failure safety;
- cache isolation;
- network default/site override/lock behavior;
- Definition PT-C network template/propagation/conflict;
- site lifecycle create/archive/delete across PT-C/PT-D/PT-E;
- JobService network fan-out/fairness/backpressure;
- Vault shared-use without secret reveal;
- Membership PT-D candidate site isolation;
- REST/Ability IDOR;
- shared-table site-row Backup extraction + selected-site/network Restore;
- Reset/Import/Uninstall scope;
- large-network row-growth vs table-count performance;
- Free↔Pro version skew.

Protocol: `docs/QUALITY/MULTISITE-SCOPE-ISOLATION-EVIDENCE-PROTOCOL.md`.

## D. Membership blockers

- **M-001 / P-012 + ADR-0071** — Enrollment/Entitlement PT-D candidate schema/indexes/materialization/scale/Multisite and concurrency.
- **M-003 / P-012** — authorization generation/cache/invalidation and revoke-to-deny latency.
- **M-005** — protected-file delivery across Apache/Nginx/PHP/private object storage/CDN/Range.
- **M-006 / ADR-0057 + ADR-0062 + ADR-0066 + ADR-0069** — source truth/versioning/site-default scope accepted; four source profiles remain **BE3 static-paper / 0 MB-certified**.

Current paper snapshot:
- `billing.manual` — WPE-owned runtime version pending;
- `billing.woocommerce-order` — WooCommerce 11.0.1 snapshot;
- `billing.woocommerce-subscriptions` — WCS 9.1.0 / Woo 11.0 snapshot; HPOS first-class;
- `billing.surecart` — SureCart WP 4.7.0 + separately tracked hosted API/event profile.

Open: exact certified ranges, HPOS/legacy matrix, identity/reconciliation, refunds/changes/recovery, provider upgrade/downgrade/security-advisory behavior, user resolution, clone/migration/privacy, concurrency and Multisite isolation.

- **M-010** — exporter/eraser cleanup/runtime/restore verification.

## E. Email / Notification provider evidence

- **E-001 / ADR-0029 + ADR-0058 + ADR-0071** — Email IR renderer/inliner/client compatibility, PT-D candidate Recipient Delivery/Transport Attempt/Event Ledger schema/indexes, attachment privacy, Job fan-out/backpressure.
- **E-002 / ADR-0058 + ADR-0063 + ADR-0067 + ADR-0069** — six profiles remain **EE3 static-paper / 0 ET-certified**.

Version identities accepted:
- `wp_mail` → WordPress/P-001 runtime profile;
- generic SMTP → negotiated transport/TLS/AUTH capability profile;
- SES → API v2 + region/account/event-publishing profile;
- SendGrid → Web API v3 + dated Event Webhook/security profile;
- Mailgun → endpoint-specific path-version families + dated webhook/security + region profile;
- Postmark → dated REST/webhook schema + current security profile.

Open: adapters/ranges, correlation, webhook authenticity/replay/order, schema drift, unknown outcomes, bounce/complaint/suppression, privacy/redaction, Multisite site correlation and ET0–ET5 certification.

## F. Remote service / commercial distribution / product license

- **S-001 / ADR-0034 + ADR-0060** — OAuth exact schemas, artifact/token lifecycle, revoke/disconnect/transfer/replay/concurrency.
- **S-002 / ADR-0017 + ADR-0042 + ADR-0060 + ADR-0070** — entitlement canonicalizer/interoperability/key custody/envelope/freshness/rotation plus installation/network/site allocation binding.
- **S-003 / ADR-0018 + ADR-0044** — production TUF verifier/client, metadata/key custody/expiry/conformance.
- **S-004/S-005/S-006 / ADR-0050 + ADR-0054 + ADR-0060** — OpenAPI/problem/scopes/idempotency/rate-limit, Support runtime, diagnostics, RR0–RR6 retention/export/delete/logging/no-hidden-identifier evidence.
- **S-007 / ADR-0070** — exact Installation/Network/Site Allocation service schema, plan site-count policy, staging/dev/DR allowances, clone reconciliation, transfer/migration, allocation races, offline grace, ownership transfer and disaster restore.

Future Remote Service verification is bounded by `docs/QUALITY/REMOTE-SERVICE-PRIVACY-RETENTION-EVIDENCE-PROTOCOL.md`: **30 fixtures documented, 0 executed**.

Product-license future evidence additionally requires single-site/network activation, selected allocations, site-count races, staging/production clone conflict, domain/host migration, network transfer, restored stale entitlement, outage/expiry/revocation, ownership transfer and no-hidden-inventory proof.

## G. Connections / Integrations — ADR-0040 + ADR-0055 + ADR-0069 + ADR-0071

Accepted Safe HTTP/Webhook/Event Inbox + I0–I5 certification. Event Inbox currently prefers PT-D. Open provider adapters, SSRF/signature/replay/idempotency/order evidence, Event Inbox DDL/index/retention, reconciliation, API-version registry, redaction, network-shared connection delegation and Multisite isolation.

## H. Backup / Operations

- **B-001 / ADR-0033 / P-013** — exact file/DB artifact formats, chunking, compression/hash encoding.
- **B-002 / ADR-0021 + ADR-0043** — exact frame/AAD, Argon2id floor, recovery-kit encoding, resume boundaries and fresh-server restore.
- **B-003 / ADR-0053 + ADR-0061 + ADR-0064 + ADR-0065 / P-013** — C0–C4 model + semantic `bf.*` families + versioned provider/static profiles + explicit local/browser/FTP/FTPS/SFTP semantics. **34 targets, 0 certified**.
- **B-004 / ADR-0056 + ADR-0069 + ADR-0071** — Remote Copy physical schema, commit-unknown reconciliation, re-verification, shared-table site-row extraction, alternate-copy failover, site/network restore.
- **B-005 / Protector / Watermark / XML-RPC** — documented physical/runtime compatibility/security evidence remains open, including network scope where applicable.

## I. Job backend — ADR-0059 + ADR-0068 + ADR-0069 + ADR-0071

Accepted static architecture:
- Action Scheduler preferred candidate, reviewed at 4.1.0;
- WPE Platform/Free owns one bundled candidate if selected; Pro/modules do not bundle duplicates;
- shared/newest registered runtime may be selected by Action Scheduler;
- only JobService adapter uses `as_*` APIs;
- large/sensitive payloads and secrets stay out of AS action args;
- WPE business idempotency does not depend on AS unique scheduling;
- WPE Job/Attempt/Audit retention does not depend on AS cleanup defaults;
- WPE logical Job history prefers PT-C/PT-D and remains independent from AS physical tables;
- network coordination/fairness remains WPE JobService responsibility.

Open P-003: packaging/load order/tables/migrations/logical mapping/claims/crashes/fairness/concurrency/backpressure/runners/recurrence/cleanup/Multisite/upgrade/downgrade/newer-unverified behavior.

## J. Identity/Admin security

- **UP-001 / ADR-0030 + ADR-0069** — protected identity/credential/session/profile evidence across site/global identity scope.
- **RC-001 / ADR-0032 + ADR-0069** — target-site capability classifier, anti-lockout, Super Admin/network, CLI recovery.
- **DW-001 / ADR-0051 + ADR-0069** — structured remote widget/iframe/XSS/CSP/assets + Site vs Network Dashboard evidence.

## K. Accepted architecture no longer open semantically

ADRs **0035–0071** preserve accepted core semantics. Evidence may refine version-scoped implementation facts but must not silently redesign accepted cores. Provider/version/package/static research is paper evidence only, never runtime certification.

Product license cannot become Membership authorization; Multisite network activation cannot become implicit network-global data authority; clone/staging classification cannot silently grant extra production allocations; physical topology class cannot override logical scope/security semantics.

## Decision-processing rule

1. Inspect source of truth and current official standards/docs.
2. Resolve static semantics in ADR when evidence is sufficient.
3. If runtime evidence is required, document bounded protocol only.
4. **Do not install, compile, migrate, benchmark, test, contact providers, send mail, run queues or transmit service data before explicit owner consent.**
5. Synchronize governance after meaningful planning milestones.

## Next planning-only priorities

1. Product-license allocation API/resource paper schemas and conflict state machine, without service calls.
2. Definition Repository PT-C exact DDL alternatives/index hypotheses without executing DDL.
3. Relations PT-D vs per-site alternative paper comparison.
4. Site lifecycle coordinator across PT-C/PT-D/PT-E.
5. Keep P-003/P-012/P-013 executable gates intact.
