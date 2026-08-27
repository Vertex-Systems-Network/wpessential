# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-27

This register contains unresolved implementation profiles/evidence only. Accepted architecture/product/security decisions are preserved in ADRs through **ADR-0062**.

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
- **N-001/C-001/REST-001/E-001** — Notification/Chat/REST/Email persistence, transport, security and performance evidence.
- **DA/BW/SET/AM/ST/LIST/IMP** — Dashboard/Blueprint/Settings/Menu/Status/Listings/Import runtime evidence.

## C. Membership blockers

### M-001 / P-012 — Enrollment/Entitlement runtime
Open: physical schema/indexes/materialization/scale/multisite, source uniqueness and concurrency.

### M-003 / P-012 — authorization cache
Open: generation/cache/invalidation and revoke-to-deny latency.

### M-005 — protected files
Open: Apache/Nginx/PHP/private object storage/CDN/Range delivery evidence.

### M-006 — provider adapters — ADR-0057 + ADR-0062
Accepted source profiles:
- `billing.manual` — explicit WPE-owned grant source;
- `billing.woocommerce-order` — order + line-item identity; supported Woo paid-state APIs rather than `Completed` alone; refund records required for partial-refund truth;
- `billing.woocommerce-subscriptions` — pending cancellation is paid-through intent, temporary failure/hold is policy input, and Woo role changes are not WPE authority;
- `billing.surecart` — Purchase + Subscription source objects, period/cancellation fields, verified webhook ingress, duplicate/out-of-order reconciliation and test/live separation.

Static research maturity for the four initial profiles is **BE3**. **Current MB-certified count: 0.**

Open future evidence: exact provider/plugin versions, source identity and duplicate handling, current-state reconciliation, cancellation/failure/recovery, refunds/changes, provider→WP-user resolution, restore/clone, migration/privacy and concurrency. No such evidence has been executed.

### M-010 — privacy
Open: exporter/eraser cleanup/runtime/restore verification.

## D. Remote service / commercial distribution

### S-001 — OAuth — ADR-0034 + ADR-0060
Open exact schemas, one-time artifact/token lifecycle, revocation/disconnect, transfer and replay/concurrency evidence.

### S-002 — entitlement — ADR-0017 + ADR-0042 + ADR-0060
Open canonicalizer/interoperability/key custody/exact envelope/freshness/rotation evidence.

### S-003 — updater — ADR-0018 + ADR-0044
Open production verifier/client, TUF metadata/key custody/expiry and conformance evidence.

### S-004/S-005/S-006 — Support/API/Privacy — ADR-0050 + ADR-0054 + ADR-0060
Open OpenAPI/problem/scopes/idempotency/rate-limit contracts, Support runtime, diagnostics preview/redaction, retention/export/delete, logging and no-hidden-identifier evidence.

## E. Connections / Integrations — ADR-0040 + ADR-0055

Accepted Safe HTTP/Webhook/Event Inbox + I0–I5 certification. Open provider adapters, SSRF/signature/replay/idempotency/order evidence, Event Inbox DDL, reconciliation, API-version registry, redaction and multisite.

## F. Backup / Operations

### B-001 — physical bundle — ADR-0033 / P-013
Open exact file/DB artifact formats, chunking, compression/hash encoding.

### B-002 — crypto — ADR-0021 + ADR-0043
Open exact frame/AAD, Argon2id floor, recovery-kit encoding, resume boundaries and fresh-server restore.

### B-003 — providers — ADR-0053 + ADR-0061 / P-013
Accepted C0–C4 model + semantic `bf.*` families + versioned provider profiles + legacy PF ambiguity handling + SE0–SE3 static evidence separation. **34 target profiles, 0 certified.**

Open adapter/profile schema, legacy import mapping, auth, large-transfer behavior, provider limits, integrity, finalization reconciliation, retention/delete semantics and C3/C4 restore evidence.

### B-004 — Remote Copy — ADR-0056
Open physical schema, commit-unknown reconciliation, re-verification, cleanup, lifecycle interference, alternate-copy failover and restore.

### B-005 / Protector / Watermark / XML-RPC
Open documented physical/runtime compatibility/security evidence.

## G. Identity/Admin security

- **UP-001 / ADR-0030** — protected identity/credential/session/profile evidence.
- **RC-001 / ADR-0032** — capability classifier, anti-lockout, multisite/Super Admin, CLI recovery.
- **DW-001 / ADR-0051** — structured remote widget/iframe/XSS/CSP/assets evidence.

## H. Accepted architecture no longer open semantically

ADRs **0035–0062** preserve accepted core semantics. Evidence may refine version-scoped implementation facts but must not silently redesign accepted cores. Billing providers cannot directly own WPE authorization; all source facts remain reconciliation/policy inputs.

## Decision-processing rule

1. Inspect repository source of truth and current official standards/docs.
2. Resolve static semantics in ADR when evidence is sufficient.
3. If runtime evidence is required, document bounded protocol only.
4. **Do not install, compile, migrate, benchmark, test, contact providers, run queues or transmit service data before explicit owner consent.**
5. Synchronize ADR index, Readiness, Checkpoint and Draft PR after planning milestones.

## Next planning-only priorities

1. Email provider-specific capability matrix.
2. Remote Service consent-gated privacy/retention evidence protocol.
3. Refresh remaining low-evidence Backup provider profiles from official docs only.
4. Membership provider version/evidence matrix refinement without execution.
5. Continue narrowing P-003/provider evidence plans.
6. Keep governance synchronized.
