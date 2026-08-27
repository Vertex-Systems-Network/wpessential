# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0075**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 + ADR-0069 + ADR-0075 | WP/PHP/DB compatibility and Multisite activation/scope/site-lifecycle matrix — P-001 + MS protocol |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006 + ADR-0059 + ADR-0068 + ADR-0069 + ADR-0071 + ADR-0075 | Action Scheduler packaging/coexistence; WPE Job/Attempt physical mapping; claims/fairness/concurrency/backpressure/runners/retention + lifecycle draining/fan-out/isolation — P-003 |
| D-004 | ADR-0008 + ADR-0049 + ADR-0069 + ADR-0071 + ADR-0073 | Definition Repository PT-C D1 benchmark baseline accepted; exact SQL types/lengths/collation/index plans/UUID/hash/JSON/FK/locking/migration evidence — P-004 |
| D-005 | ADR-0009 + ADR-0048 + ADR-0069 + ADR-0075 | Vault envelope/rotation/recovery/interoperability + network-shared/site-private isolation + site lifecycle use-grant cleanup — P-005 |
| D-006 | ADR-0010 + ADR-0069 + ADR-0070 + ADR-0072 + ADR-0075 | Free↔Pro runtime compatibility including Multisite/site-allocation/version-skew, remote allocation conflicts and site lifecycle — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Physical topology / data-runtime blockers — ADR-0071/0073/0074

Accepted topology classes:
- PT-A native WordPress site/blog storage;
- PT-B native WordPress network/global primitives;
- PT-C WPE global scoped control-plane tables;
- PT-D WPE global scoped high-volume runtime tables;
- PT-E WPE per-site custom runtime tables;
- PT-F external authoritative state + local scoped references/cache.

Current paper preferences/baselines:
- Definition Repository → PT-C; D1 future benchmark baseline accepted by ADR-0073;
- Job logical history → PT-C/PT-D;
- Audit → PT-D;
- Relations → **R1/PT-D benchmark baseline accepted by ADR-0074; R2/PT-E mandatory comparison**;
- Membership Enrollment/Entitlement → PT-D candidate;
- Workflow runtime → PT-D candidate;
- Notification/Email operational state → PT-D candidate;
- Event Inbox → PT-D candidate;
- Form Entries → PT-D vs PT-E evidence-gated;
- Chat Messages → PT-D vs PT-E evidence-gated;
- Custom Tables → explicit PT-D/PT-E by product scope;
- Support/commercial authority → PT-F.

Definition D1 baseline:
- numeric physical IDs;
- transparent textual UUID;
- explicit network/site scope coordinates;
- bounded normalized identity keys;
- immutable text-document revision payload;
- minimal workload-driven indexes;
- application same-definition pointer integrity + diagnostics;
- binary UUID/native JSON/DB-FK profiles remain P-004 comparisons.

Relations R1 baseline:
- shared scoped universal typed edge-table family;
- site/network scope is explicit in hot read/write identity;
- reverse lookup first-class;
- concurrency-safe cardinality/duplicate semantics required;
- R2 per-site table remains required comparison;
- R3 per-relation table is exceptional/evidence-backed only;
- cross-site Relations remain Off by default.

Open evidence:
- **Q-001 / P-009 + ADR-0069/0071** — Query compiler security/cost/cache/scale plus authorized bounded network aggregation against chosen storage adapters.
- **R-001 / P-010 + ADR-0069/0071/0074** — R1 PT-D vs R2 PT-E table/index/storage, endpoint representation, cardinality/concurrency, high-degree, site deletion/transfer/Backup, large-network and scope-attack evidence.
- **WF-001 / P-011 + ADR-0069/0071/0075** — Workflow PT-D indexes/waits/parallel/retry/idempotency/cancel/crash + network/site lifecycle behavior.
- **F-001/T-001/FE-001** — Field/Table/Form storage/migration; Forms PT-D vs PT-E benchmark; site deletion/Backup extraction.
- **N-001/C-001/REST-001** — Notification PT-D candidate, Chat PT-D vs PT-E, REST compiled runtime persistence/security/performance.
- **Audit/Event Inbox** — PT-D retention/index/partition + site lifecycle evidence.
- **DA/BW/SET/AM/ST/LIST/IMP** — Dashboard/Blueprint/Settings/Menu/Status/Listings/Import runtime + Multisite scope/lifecycle evidence.

No DDL/index/DB benchmark has been executed.

## C. Multisite & Site Lifecycle blockers — ADR-0069/0071/0075

Logical architecture is Accepted and **31/31 surfaces have scope behavior mapped**.

Future certification protocol:
- MS0 Static Compatible;
- MS1 Activation & Site Isolation;
- MS2 Scope Runtime Certified;
- MS3 intentional Cross-Site/Network Operations Certified;
- MS4 Large-Network & Disaster Certified.

Current state: **0 Multisite runtime fixtures executed; 0 surfaces MS1+ certified**.

ADR-0075 additionally accepts one Site Lifecycle Coordinator across creation/provisioning, status changes, uninitialize/delete, clone/migration/transfer, PT-C/PT-D/PT-E cleanup and PT-F reconciliation.

Open lifecycle evidence:
- site create/init hook ordering and idempotent provisioning;
- archive/spam/deleted status transitions/reactivation;
- `switch_to_blog()`/restore safety;
- third-party/core deletion bypassing WPE preflight;
- lifecycle drain of Jobs/Workflow/Email/Webhooks;
- PT-C tombstone/retention;
- PT-D per-domain cleanup/anonymization/retention;
- PT-E partial table cleanup/version registry;
- Membership site revoke without global user deletion;
- shared Vault/Connection use-grant cleanup;
- Product License allocation release/unknown outcome;
- scoped Backup/recovery point;
- site/network transfer and clone/DR restore;
- crash/restart at lifecycle phases;
- 100/1k/10k-site fan-out;
- destructive wrong-site/IDOR regression.

Protocol baseline remains `docs/QUALITY/MULTISITE-SCOPE-ISOLATION-EVIDENCE-PROTOCOL.md`; site-lifecycle-specific fixture expansion remains planning-only.

## D. Membership blockers

- **M-001 / P-012 + ADR-0071/0075** — Enrollment/Entitlement PT-D candidate schema/indexes/materialization/scale/Multisite/lifecycle and concurrency.
- **M-003 / P-012** — authorization generation/cache/invalidation and revoke-to-deny latency.
- **M-005** — protected-file delivery across Apache/Nginx/PHP/private object storage/CDN/Range.
- **M-006 / ADR-0057 + ADR-0062 + ADR-0066 + ADR-0069** — source truth/versioning/site-default scope accepted; four source profiles remain **BE3 static-paper / 0 MB-certified**.

Current paper snapshot:
- `billing.manual` — WPE-owned runtime version pending;
- `billing.woocommerce-order` — WooCommerce 11.0.1 snapshot;
- `billing.woocommerce-subscriptions` — WCS 9.1.0 / Woo 11.0 snapshot; HPOS first-class;
- `billing.surecart` — SureCart WP 4.7.0 + separately tracked hosted API/event profile.

Open: certified ranges, HPOS/legacy matrix, identity/reconciliation, refunds/changes/recovery, provider upgrade/downgrade/security-advisory behavior, user resolution, clone/migration/privacy, concurrency, Multisite isolation and site-teardown billing-vs-access behavior.

- **M-010** — exporter/eraser cleanup/runtime/restore verification.

## E. Email / Notification provider evidence

- **E-001 / ADR-0029 + ADR-0058 + ADR-0071 + ADR-0075** — Email IR renderer/inliner/client compatibility, PT-D candidate Recipient Delivery/Transport Attempt/Event Ledger schema/indexes, attachment privacy, Job fan-out/backpressure and site-lifecycle drain.
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
- **S-002 / ADR-0017 + ADR-0042 + ADR-0060 + ADR-0070 + ADR-0072** — entitlement canonicalizer/interoperability/key custody/envelope/freshness/rotation plus installation/network/site-allocation binding and resource-state reconciliation.
- **S-003 / ADR-0018 + ADR-0044** — production TUF verifier/client, metadata/key custody/expiry/conformance.
- **S-004/S-005/S-006 / ADR-0050 + ADR-0054 + ADR-0060** — OpenAPI/problem/scopes/idempotency/rate-limit, Support runtime, diagnostics, RR0–RR6 retention/export/delete/logging/no-hidden-identifier evidence.
- **S-007 / ADR-0070 + ADR-0072 + ADR-0075** — exact Account/Contract/Installation/Network/Site Allocation resources, idempotent mutation protocol, expected-version concurrency, allocation capacity races, clone conflicts, transfer/migration, offline grace, local/remote commit-unknown recovery, site teardown release/reconciliation, ownership transfer and disaster restore.

Future Remote Service verification is bounded by `docs/QUALITY/REMOTE-SERVICE-PRIVACY-RETENTION-EVIDENCE-PROTOCOL.md`: **30 fixtures documented, 0 executed**.

## G. Connections / Integrations — ADR-0040 + ADR-0055 + ADR-0069 + ADR-0071 + ADR-0075

Accepted Safe HTTP/Webhook/Event Inbox + I0–I5 certification. Event Inbox currently prefers PT-D. Open provider adapters, SSRF/signature/replay/idempotency/order evidence, Event Inbox DDL/index/retention, reconciliation, API-version registry, redaction, network-shared connection delegation, Multisite isolation and lifecycle cleanup/revocation.

## H. Backup / Operations

- **B-001 / ADR-0033 / P-013** — exact file/DB artifact formats, chunking, compression/hash encoding.
- **B-002 / ADR-0021 + ADR-0043** — exact frame/AAD, Argon2id floor, recovery-kit encoding, resume boundaries and fresh-server restore.
- **B-003 / ADR-0053 + ADR-0061 + ADR-0064 + ADR-0065 / P-013** — C0–C4 model + semantic `bf.*` families + versioned provider/static profiles + explicit local/browser/FTP/FTPS/SFTP semantics. **34 targets, 0 certified**.
- **B-004 / ADR-0056 + ADR-0069 + ADR-0071 + ADR-0075** — Remote Copy physical schema, commit-unknown reconciliation, re-verification, shared-table site-row extraction, lifecycle recovery requirement, alternate-copy failover, site/network restore.
- **B-005 / Protector / Watermark / XML-RPC** — documented physical/runtime compatibility/security evidence remains open, including network scope/lifecycle where applicable.

## I. Job backend — ADR-0059 + ADR-0068 + ADR-0069 + ADR-0071 + ADR-0075

Accepted static architecture:
- Action Scheduler preferred candidate, reviewed at 4.1.0;
- WPE Platform/Free owns one bundled candidate if selected; Pro/modules do not bundle duplicates;
- shared/newest registered runtime may be selected by Action Scheduler;
- only JobService adapter uses `as_*` APIs;
- large/sensitive payloads and secrets stay out of AS action args;
- WPE business idempotency does not depend on AS unique scheduling;
- WPE Job/Attempt/Audit retention does not depend on AS cleanup defaults;
- WPE logical Job history prefers PT-C/PT-D and remains independent from AS physical tables;
- network coordination/fairness and site lifecycle drain remain WPE JobService/Coordinator responsibilities.

Open P-003: packaging/load order/tables/migrations/logical mapping/claims/crashes/fairness/concurrency/backpressure/runners/recurrence/cleanup/Multisite/site-lifecycle/upgrade/downgrade/newer-unverified behavior.

## J. Identity/Admin security

- **UP-001 / ADR-0030 + ADR-0069** — protected identity/credential/session/profile evidence across site/global identity scope.
- **RC-001 / ADR-0032 + ADR-0069** — target-site capability classifier, anti-lockout, Super Admin/network, CLI recovery.
- **DW-001 / ADR-0051 + ADR-0069** — structured remote widget/iframe/XSS/CSP/assets + Site vs Network Dashboard evidence.

## K. Accepted architecture no longer open semantically

ADRs **0035–0075** preserve accepted core semantics. Evidence may refine version-scoped implementation facts but must not silently redesign accepted cores. Provider/version/package/static research is paper evidence only, never runtime certification.

Product license cannot become Membership authorization; Multisite network activation cannot become implicit network-global data authority; clone/staging classification cannot silently grant extra production allocations; physical topology class cannot override logical scope/security semantics; D1/R1 are benchmark baselines, not pre-approved final DDL; WordPress site deletion/uninitialization/status changes cannot be collapsed into one generic cleanup event.

## Decision-processing rule

1. Inspect source of truth and current official standards/docs.
2. Resolve static semantics in ADR when evidence is sufficient.
3. If runtime evidence is required, document bounded protocol only.
4. **Do not install, compile, migrate, benchmark, test, contact providers, send mail, run queues or transmit service data before explicit owner consent.**
5. Synchronize governance after meaningful planning milestones.

## Next planning-only priorities

1. Product License exact OpenAPI candidate resource shapes without service implementation.
2. P-004 Definition benchmark fixture/query-plan design without execution.
3. P-010 Relations benchmark fixture/query-plan design without execution.
4. Site Lifecycle Coordinator evidence protocol refinement without hooks/tests.
5. Forms/Chat PT-D-vs-PT-E paper comparison where not already sufficient.
6. Keep P-003/P-012/P-013 executable gates intact.
