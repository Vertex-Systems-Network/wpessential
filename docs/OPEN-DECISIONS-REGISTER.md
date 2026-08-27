# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0070**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 + ADR-0069 | WP/PHP/DB compatibility and Multisite activation/scope/isolation matrix — P-001 + MS protocol |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006 + ADR-0059 + ADR-0068 + ADR-0069 | Action Scheduler packaging/coexistence; Job/Attempt mapping; claims/fairness/concurrency/backpressure/runners/retention + Multisite fan-out/isolation — P-003 |
| D-004 | ADR-0008 + ADR-0049 + ADR-0069 | Definition Repository exact DDL/index/locking/global-vs-per-site topology — P-004 |
| D-005 | ADR-0009 + ADR-0048 + ADR-0069 | Vault envelope/rotation/recovery/interoperability + network-shared/site-private isolation — P-005 |
| D-006 | ADR-0010 + ADR-0069 + ADR-0070 | Free↔Pro runtime compatibility including Multisite/site-allocation/version-skew lifecycle — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Data/runtime blockers

- **Q-001 / P-009 + ADR-0069** — Query compiler security/cost/cache/scale plus authorized bounded network aggregation.
- **R-001 / P-010 + ADR-0069** — Relations indexes/cardinality/concurrency/delete behavior and cross-site deny/default.
- **WF-001 / P-011 + ADR-0069** — Workflow waits/parallel/retry/idempotency/cancel/crash + JobService + network coordinator behavior.
- **F-001/T-001/FE-001** — Field/Table/Form physical storage, migration, scope topology and runtime evidence.
- **N-001/C-001/REST-001** — Notification/Chat/REST persistence, security, site/network isolation and performance evidence.
- **DA/BW/SET/AM/ST/LIST/IMP** — Dashboard/Blueprint/Settings/Menu/Status/Listings/Import runtime + Multisite scope evidence.

## C. Multisite blockers — ADR-0069

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
- Definition network template/propagation/conflict;
- site lifecycle create/archive/delete;
- JobService network fan-out/fairness/backpressure;
- Vault shared-use without secret reveal;
- Membership site isolation;
- REST/Ability IDOR;
- selected-site/network Backup/Restore;
- Reset/Import/Uninstall scope;
- large-network performance;
- Free↔Pro version skew.

Protocol: `docs/QUALITY/MULTISITE-SCOPE-ISOLATION-EVIDENCE-PROTOCOL.md`.

## D. Membership blockers

- **M-001 / P-012** — Enrollment/Entitlement schema/indexes/materialization/scale/Multisite and concurrency.
- **M-003 / P-012** — authorization generation/cache/invalidation and revoke-to-deny latency.
- **M-005** — protected-file delivery across Apache/Nginx/PHP/private object storage/CDN/Range.
- **M-006 / ADR-0057 + ADR-0062 + ADR-0066 + ADR-0069** — source truth/versioning/site-default scope accepted; four source profiles remain **BE3 static-paper / 0 MB-certified**.

Current paper snapshot:
- `billing.manual` — WPE-owned runtime version pending;
- `billing.woocommerce-order` — WooCommerce 11.0.1 snapshot;
- `billing.woocommerce-subscriptions` — WCS 9.1.0 / Woo 11.0 snapshot; HPOS first-class;
- `billing.surecart` — SureCart WP 4.7.0 + separately tracked hosted API/event profile.

Open: certified ranges, HPOS/legacy matrix, identity/reconciliation, refunds/changes/recovery, provider upgrade/downgrade/security-advisory behavior, user resolution, clone/migration/privacy, concurrency and Multisite isolation.

- **M-010** — exporter/eraser cleanup/runtime/restore verification.

## E. Email / Notification provider evidence

- **E-001 / ADR-0029 + ADR-0058** — Email IR renderer/inliner/client compatibility, Recipient Delivery/Transport Attempt/Event Ledger physical schema/indexes, attachment privacy, Job fan-out/backpressure.
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

Product-license future evidence additionally requires:
- single-site/network activation;
- selected site allocations;
- site-count exhaustion/release race;
- staging clone and duplicate production clone;
- domain/host migration;
- network-to-network site transfer;
- deleted/recreated site identity;
- stale entitlement after Backup restore;
- service outage vs expiry vs revocation;
- ownership transfer;
- no hidden site inventory/telemetry.

## G. Connections / Integrations — ADR-0040 + ADR-0055 + ADR-0069

Accepted Safe HTTP/Webhook/Event Inbox + I0–I5 certification. Open provider adapters, SSRF/signature/replay/idempotency/order evidence, Event Inbox DDL, reconciliation, API-version registry, redaction, network-shared connection delegation and Multisite isolation.

## H. Backup / Operations

- **B-001 / ADR-0033 / P-013** — exact file/DB artifact formats, chunking, compression/hash encoding.
- **B-002 / ADR-0021 + ADR-0043** — exact frame/AAD, Argon2id floor, recovery-kit encoding, resume boundaries and fresh-server restore.
- **B-003 / ADR-0053 + ADR-0061 + ADR-0064 + ADR-0065 / P-013** — C0–C4 model + semantic `bf.*` families + versioned provider/static profiles + explicit local/browser/FTP/FTPS/SFTP semantics. **34 targets, 0 certified**.
- **B-004 / ADR-0056 + ADR-0069** — Remote Copy physical schema, commit-unknown reconciliation, re-verification, cleanup, lifecycle interference, alternate-copy failover, site/network restore.
- **B-005 / Protector / Watermark / XML-RPC** — documented physical/runtime compatibility/security evidence remains open, including network scope where applicable.

## I. Job backend — ADR-0059 + ADR-0068 + ADR-0069

Accepted static architecture:
- Action Scheduler preferred candidate, reviewed at 4.1.0;
- WPE Platform/Free owns one bundled candidate if selected; Pro/modules do not bundle duplicates;
- shared/newest registered runtime may be selected by Action Scheduler;
- only JobService adapter uses `as_*` APIs;
- large/sensitive payloads and secrets stay out of AS action args;
- WPE business idempotency does not depend on AS unique scheduling;
- WPE Job/Attempt/Audit retention does not depend on AS cleanup defaults;
- network coordination/fairness remains WPE JobService responsibility, not Action Scheduler magic.

Open P-003: packaging/load order/tables/migrations/mapping/claims/crashes/fairness/concurrency/backpressure/runners/recurrence/cleanup/Multisite/upgrade/downgrade/newer-unverified behavior.

## J. Identity/Admin security

- **UP-001 / ADR-0030 + ADR-0069** — protected identity/credential/session/profile evidence across site/global identity scope.
- **RC-001 / ADR-0032 + ADR-0069** — target-site capability classifier, anti-lockout, Super Admin/network, CLI recovery.
- **DW-001 / ADR-0051 + ADR-0069** — structured remote widget/iframe/XSS/CSP/assets + Site vs Network Dashboard evidence.

## K. Accepted architecture no longer open semantically

ADRs **0035–0070** preserve accepted core semantics. Evidence may refine version-scoped implementation facts but must not silently redesign accepted cores. Provider/version/package/static research is paper evidence only, never runtime certification.

Product license cannot become Membership authorization; Multisite network activation cannot become implicit network-global data authority; clone/staging classification cannot silently grant extra production allocations.

## Decision-processing rule

1. Inspect source of truth and current official standards/docs.
2. Resolve static semantics in ADR when evidence is sufficient.
3. If runtime evidence is required, document bounded protocol only.
4. **Do not install, compile, migrate, benchmark, test, contact providers, send mail, run queues or transmit service data before explicit owner consent.**
5. Synchronize governance after meaningful planning milestones.

## Next planning-only priorities

1. Physical Multisite data topology alternatives for Definition Repository/Relations/Jobs without DDL execution.
2. Product-license allocation API/resource paper schemas and conflict state machine, without service calls.
3. Remaining physical/runtime paper models where static decisions are useful.
4. Keep P-003/P-012/P-013 executable gates intact.
