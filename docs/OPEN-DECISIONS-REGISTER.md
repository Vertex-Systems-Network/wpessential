# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0076**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 + ADR-0069 + ADR-0075 | WP/PHP/DB compatibility and Multisite activation/scope/site-lifecycle matrix — P-001 + MS protocol |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006 + ADR-0059 + ADR-0068 + ADR-0069 + ADR-0071 + ADR-0075 | Action Scheduler packaging/coexistence; WPE Job/Attempt physical mapping; claims/fairness/concurrency/backpressure/runners/retention + lifecycle draining/fan-out/isolation — P-003 |
| D-004 | ADR-0008 + ADR-0049 + ADR-0069 + ADR-0071 + ADR-0073 | Definition Repository PT-C D1 benchmark baseline accepted; exact SQL types/lengths/collation/index plans/UUID/hash/JSON/FK/locking/migration evidence — P-004 |
| D-005 | ADR-0009 + ADR-0048 + ADR-0069 + ADR-0075 | Vault envelope/rotation/recovery/interoperability + network-shared/site-private isolation + site lifecycle use-grant cleanup — P-005 |
| D-006 | ADR-0010 + ADR-0069 + ADR-0070 + ADR-0072 + ADR-0075 + ADR-0076 | Free↔Pro runtime compatibility including Multisite/site-allocation/version-skew, remote allocation conflicts, HTTP contract and site lifecycle — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Physical topology / data-runtime blockers — ADR-0071/0073/0074

Accepted topology classes: PT-A native site, PT-B native network/global, PT-C WPE global scoped control-plane, PT-D WPE global scoped high-volume runtime, PT-E WPE per-site custom runtime, PT-F external authority + local references/cache.

Current paper baselines/preferences:
- Definition Repository → PT-C; D1 benchmark baseline ADR-0073;
- Relations → R1/PT-D benchmark baseline ADR-0074; R2/PT-E mandatory comparison;
- Job logical history → PT-C/PT-D;
- Audit → PT-D;
- Membership/Workflow/Notification/Event Inbox → PT-D candidates;
- Form Entries/Chat → PT-D vs PT-E evidence-gated;
- Custom Tables → explicit PT-D/PT-E;
- commercial/support authority → PT-F.

Open evidence:
- **Q-001 / P-009** — Query compiler security/cost/cache/scale/network aggregation.
- **R-001 / P-010** — R1 vs R2 table/index/storage, endpoint representation, cardinality/concurrency/high-degree/site lifecycle/Backup/large-network/security.
- **WF-001 / P-011** — Workflow PT-D runtime + JobService + lifecycle.
- **F-001/T-001/FE-001** — Field/Table/Form storage/migration; Forms PT-D-vs-PT-E.
- **N-001/C-001/REST-001** — Notification/Chat/REST persistence/security/performance.
- Audit/Event Inbox PT-D retention/index evidence.

No DDL/index/DB benchmark executed.

## C. Multisite & Site Lifecycle blockers — ADR-0069/0071/0075

31/31 surfaces have explicit scope behavior mapped. Future certification: MS0 Static, MS1 Site Isolation, MS2 Scope Runtime, MS3 Network Ops, MS4 Large-Network/Disaster. **0 runtime fixtures / 0 MS1+ certified**.

Site Lifecycle Coordinator semantics are accepted, but open evidence includes:
- site creation/init/provisioning idempotency;
- archive/spam/deleted transitions/reactivation;
- switch/restore failure safety;
- deletion/uninitialization ordering and third-party bypass;
- live Job/Workflow/Email/Webhook drain;
- PT-C/PT-D/PT-E cleanup/retention;
- Membership revoke without global-user deletion;
- Vault/Connection delegation cleanup;
- Product License release/unknown outcome;
- scoped Backup/recovery;
- transfer/clone/DR;
- crash/restart and 100/1k/10k-site scale;
- wrong-site destructive/IDOR attacks.

## D. Membership blockers

- Enrollment/Entitlement PT-D schema/indexes/materialization/scale/concurrency — P-012.
- revoke-to-deny cache/invalidation.
- protected files across web-server/object-storage profiles.
- Manual/Woo/WCS/SureCart remain **4 BE3 / 0 MB-certified**.
- refunds/reconciliation/identity/clone/privacy/lifecycle remain open.

## E. Email / Notification provider evidence

- Email renderer + Recipient Delivery/Attempt/Event PT-D schema + lifecycle drain remain open.
- wp_mail/SMTP/SES/SendGrid/Mailgun/Postmark remain **6 EE3 / 0 ET-certified**.
- provider event authenticity/replay/order, correlation, bounce/complaint/suppression and load evidence open.

## F. Remote service / commercial distribution / Product License

Accepted through ADR-0076:
- OAuth/PKCE account-link architecture;
- signed Product Entitlement separate from API authority;
- installation/network/site-allocation identity and clone/transfer semantics;
- remote resource/state/conflict model;
- resource-oriented versioned HTTP/OpenAPI contract principles;
- ETag/`If-Match` optimistic concurrency;
- retry-safe mutation idempotency keys;
- RFC 9457-compatible Problem Details;
- bounded pagination/data minimization;
- no hidden site inventory.

Open evidence:
- **S-001** OAuth exact endpoints/scopes/token lifecycle/revoke/replay.
- **S-002** entitlement canonicalizer/key custody/freshness/keyset rotation/binding.
- **S-003** production TUF client/key custody/conformance.
- **S-004/S-005/S-006** Support/diagnostics/OpenAPI/privacy retention runtime.
- **S-007** exact OpenAPI component schemas, header/idempotency retention, ETag behavior, last-seat races, release/reallocate races, timeout reconciliation, clone/transfer, ownership transfer, offline grace, local-success/remote-unknown and remote-success/local-failure cases.

Remote Service privacy protocol: **30 fixtures documented / 0 executed**. Product License API/service fixtures: **0 executed**.

## G. Connections / Integrations

Safe HTTP/Webhook/Event Inbox + I0–I5 accepted. Open provider adapters, SSRF/signature/replay/idempotency/order, PT-D Event Inbox DDL/retention, network-shared delegation and lifecycle cleanup evidence.

## H. Backup / Operations

- exact bundle artifact/chunk/compression/hash format open;
- exact encryption frame/KDF/recovery-kit evidence open;
- 34 provider targets remain **0 C-certified**;
- Remote Copy physical schema/commit-unknown/reverification/site-row extraction/lifecycle/site-network restore open;
- Protector/Watermark/XML-RPC runtime evidence open.

## I. Job backend

Action Scheduler 4.1.0 remains preferred reviewed candidate only. WPE JobService owns idempotency/fairness/history/network coordination/lifecycle drain. **P-003 unexecuted**.

## J. Identity/Admin security

Profile identity/session evidence, Role anti-lockout/Multisite/Super Admin/CLI recovery and Dashboard Widget remote-content security remain runtime blockers.

## K. Accepted architecture no longer open semantically

ADRs **0035–0076** preserve accepted core semantics. Evidence can refine implementation/version facts but cannot silently redesign them.

D1/R1 are benchmark baselines, not final DDL. Site deletion/uninitialization/status changes remain distinct. Product License API transport never replaces signed entitlement verification or Membership authorization.

## Decision-processing rule

1. Inspect repo/official evidence.
2. Resolve static semantics in ADR.
3. Document bounded evidence protocol when runtime proof is required.
4. **Do not install, compile, migrate, benchmark, test, contact providers/services, send mail, run queues or transmit data before explicit owner consent.**
5. Synchronize governance after milestones.

## Next planning-only priorities

1. P-004 Definition benchmark fixture/query-plan protocol without execution.
2. P-010 Relations benchmark fixture/query-plan protocol without execution.
3. Site Lifecycle evidence protocol expansion without hooks/tests.
4. Forms/Chat PT-D-vs-PT-E paper comparison.
5. Product License OpenAPI component schema detail only where static review adds value.
6. Keep P-003/P-012/P-013 executable gates intact.
