# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0081**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002 + ADR-0069 + ADR-0075 | WP/PHP/DB compatibility and Multisite activation/scope/site-lifecycle matrix — P-001 + MS protocol |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0006 + ADR-0059 + ADR-0068 + ADR-0069 + ADR-0071 + ADR-0075 | Action Scheduler packaging/coexistence; WPE Job/Attempt physical mapping; claims/fairness/concurrency/backpressure/runners/retention + lifecycle draining/fan-out/isolation — P-003 |
| D-004 | ADR-0008 + ADR-0049 + ADR-0069 + ADR-0071 + ADR-0073 | Definition Repository PT-C D1 benchmark baseline accepted; exact SQL types/lengths/collation/index plans/UUID/hash/JSON/FK/locking/migration evidence — P-004 |
| D-005 | ADR-0009 + ADR-0048 + ADR-0069 + ADR-0075 | Vault envelope/rotation/recovery/interoperability + network-shared/site-private isolation + lifecycle use-grant cleanup — P-005 |
| D-006 | ADR-0010 + ADR-0069 + ADR-0070 + ADR-0072 + ADR-0075 + ADR-0076 | Free↔Pro runtime compatibility including Multisite/site-allocation/version-skew, remote allocation conflicts, HTTP contract and site lifecycle — P-006 |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |

## B. Physical topology / data-runtime blockers

Accepted topology classes remain PT-A native site, PT-B native network/global, PT-C WPE global scoped control-plane, PT-D WPE global scoped high-volume runtime, PT-E WPE per-site custom runtime and PT-F external authority + local references/cache.

Current paper baselines:
- Definition Repository → D1/PT-C first P-004 baseline, ADR-0073;
- Relations → R1/PT-D first P-010 baseline, R2/PT-E mandatory comparison, ADR-0074;
- Forms → FRT1/PT-D first baseline, FRT2/PT-E mandatory comparison, ADR-0077;
- Chat → CRT1/PT-D first baseline, CRT2/PT-E mandatory comparison, ADR-0077;
- Membership → M1/PT-D first P-012 baseline, M2/PT-E mandatory comparison, ADR-0078;
- Notification/Email operational state → NE1/PT-D first baseline, NE2/PT-E mandatory comparison, ADR-0079;
- Event Inbox → EI1/PT-D first baseline, EI2/PT-E mandatory comparison, ADR-0080;
- Audit → AU1/PT-D favored baseline, ADR-0081;
- Job logical history → PT-C/PT-D remains evidence-gated;
- Workflow runtime → PT-D candidate remains P-011;
- user-created Custom Tables → explicit PT-D/PT-E by product contract;
- commercial/support remote authority → PT-F.

Open evidence:
- **Q-001 / P-009** — Query compiler security/cost/cache/scale/network aggregation.
- **R-001 / P-010** — R1 vs R2 exact table/index/storage, endpoint representation, cardinality/concurrency/high-degree/site lifecycle/Backup/large-network/security.
- **WF-001 / P-011** — Workflow PT-D run/step/wait/runtime indexes, JobService integration, cancellation/crash/retry/lifecycle/network coordination.
- **M-001/M-003 / P-012** — M1 vs M2 exact schema/index/cache/generation/locking/revoke-to-deny/seat concurrency/scale.
- **F-001/T-001/FE-001** — Field/Table storage/migration and FRT1 vs FRT2 exact evidence.
- **C-001** — CRT1 vs CRT2 exact indexes/search/retention/revocation/noisy-neighbor evidence.
- **N/E-001** — NE1 vs NE2 exact DDL/index/retention/provider-event correlation and ET certification.
- **EI-001** — EI1 vs EI2 exact claim/dedupe/retention/routing/scale and I4/I5 certification.
- **AU-001** — AU1 exact DDL/index/retention/fail-closed/privacy transforms and optional tamper-evidence attacker-model evidence.

No DDL/index/DB benchmark has been executed.

## C. Multisite & Site Lifecycle blockers — ADR-0069/0071/0075

31/31 surfaces have explicit scope behavior mapped. Future certification remains MS0 Static, MS1 Site Isolation, MS2 Scope Runtime, MS3 Network Ops, MS4 Large-Network/Disaster. **0 runtime fixtures / 0 MS1+ certified**.

Site Lifecycle Coordinator semantics are accepted, but evidence remains for site provisioning idempotency; archive/reactivation; delete/uninitialize ordering; live Job/Workflow/Notification/Webhook drain; PT-C/PT-D/PT-E cleanup/retention; Membership revoke without global-user deletion; Vault/Connection delegation cleanup; Product License release/unknown outcome; scoped Backup/recovery; transfer/clone/DR; crash/restart; 100/1k/10k-site scale; wrong-site destructive/IDOR attacks.

Dedicated lifecycle protocol: **40 fixtures documented / 0 executed**.

## D. Membership blockers — ADR-0078

M1/PT-D is the future first P-012 benchmark and M2/PT-E is mandatory comparison. Principal Access Generation is included in the paper baseline so access-affecting mutations can make stale cache generations unreachable without making cache availability part of authorization correctness.

Open evidence:
- exact Enrollment/Entitlement/Transition/Override/Team/Invitation DDL/indexes;
- access hot-path query count/latency;
- revoke-to-deny and expiry with late jobs;
- Plan Group exclusivity and team-seat races;
- protected-file delivery;
- provider reconciliation/refunds/identity/privacy/clone/restore;
- M1 vs M2 large-network/noisy-neighbor/Backup/lifecycle behavior.

Manual/Woo/WCS/SureCart remain **4 BE3 / 0 MB-certified**.

## E. Notification / Email blockers — ADR-0079

NE1/PT-D is first operational benchmark; NE2/PT-E mandatory comparison. Occurrence, Recipient Notification/read state, Channel Delivery, Email Transport Attempt and verified Delivery Evidence remain separate facts.

Open evidence:
- exact DDL/index/retention;
- fan-out/digest/quiet-hours/backpressure;
- renderer/client compatibility;
- timeout `unknown_outcome` reconciliation and duplicate-send prevention;
- provider event correlation/order/suppression/bounce/complaint truth;
- restore-without-resend;
- NE1 vs NE2 noisy-neighbor/Backup/site lifecycle.

`wp_mail`/SMTP/SES/SendGrid/Mailgun/Postmark remain **6 EE3 / 0 ET-certified**.

## F. Connections / Event Inbox blockers — ADR-0080

EI1/PT-D is first Event Inbox benchmark; EI2/PT-E mandatory large-network comparison.

Accepted boundary: trusted endpoint/Connection/delegation determines scope; attacker-controlled payload fields do not. Event Inbox dedupe never replaces consumer-domain idempotency.

Open evidence:
- provider signature/replay profiles;
- no-provider-ID dedupe contracts;
- conflicting/out-of-order/schema-drift events;
- claim/crash/manual replay;
- network-shared Connection routing;
- raw-payload retention/protection;
- site lifecycle/restore;
- EI1 vs EI2 burst/noisy-neighbor/large-network behavior;
- exact DDL/index/claim/locking;
- I4/I5 provider certification.

Current I4/I5 event certifications: **0**.

## G. Audit blockers — ADR-0081

AU1/PT-D is favored. Audit remains separate from domain history, operational diagnostics and high-volume analytics. Normal application semantics are append-only, but local WordPress DB storage is **not** claimed tamper-proof against a privileged DB/server/root actor.

Open evidence:
- exact DDL/index/retention classes;
- fail-closed vs best-effort policy by Ability/action class;
- privacy redaction/anonymization/export/purge;
- site/network visibility and wrong-site isolation;
- mutation/Audit crash boundary;
- 1M/10M/100M-scale evidence where practical;
- Backup/Restore chronology/provenance;
- optional hash-chain/signed/external checkpoint profiles only if attacker model and key custody justify them.

Executed Audit physical/integrity benchmarks: **0**.

## H. Remote service / commercial distribution / Product License

Accepted through ADR-0076: OAuth/PKCE account-link architecture; signed Product Entitlement separate from API authority; installation/network/site-allocation identity and clone/transfer semantics; resource/state/conflict model; versioned HTTP/OpenAPI principles; ETag/`If-Match`; retry-safe idempotency; RFC 9457 Problem Details; bounded pagination/data minimization; no hidden inventory.

Open: OAuth exact endpoints/scopes/token lifecycle; entitlement canonicalizer/key custody/freshness; production TUF; exact OpenAPI schemas; idempotency retention; ETag races; site-count allocation races; clone/transfer/ownership/offline-grace; Support/diagnostics/privacy runtime.

Remote Service privacy protocol: **30 fixtures / 0 executed**. Product License API/service fixtures: **0 executed**.

## I. Backup / Operations

Exact bundle/chunk/compression/hash format; encryption frame/KDF/recovery-kit; Remote Copy physical schema/commit-unknown/reverification/site-row extraction; and site/network restore remain open. Backup provider registry remains **34 targets / 0 C-certified / 0 C3 Supported**.

## J. Job backend

Action Scheduler 4.1.0 remains preferred reviewed candidate only. WPE JobService owns business idempotency/fairness/history/network coordination/lifecycle drain. Physical Job/Attempt/history PT-C/PT-D mapping and all runtime evidence remain **P-003 unexecuted**.

## K. Identity/Admin security

Profile identity/session evidence, Role anti-lockout/Multisite/Super Admin/CLI recovery and Dashboard Widget remote-content security remain runtime blockers.

## L. Accepted architecture no longer open semantically

ADRs **0035–0081** preserve accepted core semantics. Evidence can refine implementation/version facts but cannot silently redesign accepted cores.

D1/R1/FRT1/CRT1/M1/NE1/EI1 are benchmark baselines, not final DDL. AU1 is a favored Audit topology, not a tamper-proof claim. Site lifecycle facts remain distinct. Product License transport never replaces signed entitlement verification or Membership authorization.

## Decision-processing rule

1. Inspect repo/official evidence.
2. Resolve static semantics in ADR.
3. Document bounded evidence protocol when runtime proof is required.
4. **Do not install, compile, migrate, benchmark, test, contact providers/services, send mail, run queues or transmit data before explicit owner consent.**
5. Synchronize governance after milestones.

## Next planning-only priorities

1. Workflow PT-D run/step/wait physical benchmark profile and P-011 evidence protocol, without execution.
2. WPE Job/Attempt/history PT-C-vs-PT-D physical mapping for P-003, without Action Scheduler bootstrap.
3. Backup Remote Copy physical schema/index/commit-unknown paper profile, without transfers/restores.
4. Vault physical envelope/storage/index alternatives under P-005, without crypto execution.
5. Continue exact P-012/NE/EI/Audit evidence protocols only on paper where useful.
6. Keep P-003/P-005/P-011/P-012/P-013 executable gates intact.