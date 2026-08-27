# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependencies, executable spikes/benchmarks, queue execution, provider/API interactions, service transmission, SMTP/email sends, Backup/Restore operations or release packaging.

`continue` and planning acceptance do **not** authorize development.

Source of truth: `/DEVELOPMENT-CONSENT.md`, `AGENTS.md`, ADR-0014.

## Product milestone

- **31/31 Exhaustive product-option maturity**
- **31/31 Multisite scope behavior mapped**
- **0/31 Authorized**
- **0 surfaces Multisite runtime-certified at MS1+**
- Implemented: none
- Runtime verified: none

## Accepted architecture

Accepted decisions now extend through **ADR-0081**.

Latest milestones:
- ADR-0077 — Forms FRT1/PT-D and Chat CRT1/PT-D first benchmark baselines; PT-E comparisons mandatory.
- ADR-0078 — Membership M1/PT-D first P-012 baseline; M2/PT-E mandatory comparison.
- ADR-0079 — Notification/Email NE1/PT-D operational baseline; NE2/PT-E mandatory comparison.
- ADR-0080 — Event Inbox EI1/PT-D first baseline; EI2/PT-E mandatory comparison.
- **ADR-0081 — Audit AU1/PT-D retention/index/integrity profile.**

Earlier still-active foundations include ADR-0073 Definition D1, ADR-0074 Relations R1, ADR-0075 Site Lifecycle and ADR-0076 Product License HTTP/OpenAPI principles.

## Multisite / Site Lifecycle

- 31/31 surface scopes mapped;
- site scope default, network scope explicit;
- cross-site Relations/Queries Off by default;
- Site Lifecycle Coordinator covers provisioning, status changes, destructive Plan/drain, PT-C/PT-D/PT-E cleanup, PT-F reconciliation, clone/transfer and durable lifecycle journal;
- WordPress site initialization/update/uninitialization/deletion remain distinct lifecycle facts;
- lifecycle evidence protocol documents **40 fixtures / 0 executed**;
- **0 WPE lifecycle hooks/fixtures executed; 0 MS1+ certified**.

## Physical topology baselines

PT-A…PT-F topology classes remain accepted. Current future benchmark profiles:

- Definition Repository: **D1/PT-C** first P-004 baseline; D2 binary UUID, D3 native JSON, D4 constraint/FK comparisons; **0 executed**.
- Relations: **R1/PT-D** first P-010 baseline; R2/PT-E mandatory comparison; R3 per-relation exceptional; **0 executed**.
- Forms: **FRT1/PT-D** first baseline; FRT2/PT-E mandatory comparison; **0 executed**.
- Chat: **CRT1/PT-D** first baseline; CRT2/PT-E mandatory comparison; **0 executed**.
- Membership: **M1/PT-D** first P-012 baseline; M2/PT-E mandatory comparison; **0 executed**.
- Notification/Email: **NE1/PT-D** first operational baseline; NE2/PT-E mandatory comparison; **0 executed**.
- Event Inbox: **EI1/PT-D** first baseline; EI2/PT-E mandatory comparison; **0 executed**.
- Audit: **AU1/PT-D** favored baseline; exact DDL/retention/integrity evidence pending; **0 executed**.
- Workflow: PT-D candidate only; P-011 physical profile still open.
- WPE Job/Attempt/history: PT-C/PT-D mapping still open under P-003.

No DDL, migration, table, index or DB benchmark has been executed.

## Membership — ADR-0078

M1 future store family keeps Enrollments authoritative and materialized Entitlements derived/rebuildable. A small scoped Principal Access Generation store is part of the first benchmark profile so committed access-affecting mutations can advance authorization/cache generation without making cache availability part of correctness.

Critical gates:
- request-time timestamp expiry even if jobs are late;
- no provider API call on ordinary access checks;
- wrong-site Membership access must be zero;
- committed revoke cannot leave a stale allow path;
- concurrent Plan Group change cannot violate exclusivity;
- concurrent last-seat acceptance cannot overbook;
- restore cannot silently resurrect stale access without reconciliation.

**P-012 executable evidence: 0. Billing profiles: 4 BE3 / 0 MB-certified.**

## Notification / Email — ADR-0079

Truth remains split between Notification Occurrence, Recipient Notification/read state, Channel Delivery, Email Transport Attempt and verified Delivery Evidence.

Accepted paper rules:
- transport/provider acceptance != Delivered;
- timeout can be `unknown_outcome`;
- no blind resend where the provider may have accepted;
- verified Event Inbox facts feed Email-domain evidence;
- out-of-order provider events cannot regress aggregate state by arrival order alone;
- Restore does not resend terminal historical messages merely because records were restored.

**NE executable evidence: 0. Email profiles: 6 EE3 / 0 ET-certified.**

## Event Inbox — ADR-0080

EI1/PT-D is the first future benchmark profile; EI2/PT-E remains mandatory comparison.

Accepted paper rules:
- required authenticity/replay checks precede normal processable Event Inbox acceptance/business mutation;
- trusted endpoint/Connection/delegation determines scope, not attacker payload `site_id`/resource IDs;
- duplicate provider delivery cannot create a second business transition;
- Event Inbox dedupe does not replace consumer-domain idempotency;
- out-of-order/schema-drift events are explicit;
- raw payload retention is protected/minimized;
- Restore does not blindly replay historical processed events.

**Event Inbox benchmarks: 0. I4/I5 provider event certifications: 0.**

## Audit — ADR-0081

AU1/PT-D is the favored future physical baseline.

Boundaries:
- Audit Event != structured domain history != short-lived operational diagnostics != high-volume access analytics;
- normal application semantics are append-only;
- corrections are new linked events;
- retention/privacy transforms are explicit governed operations;
- no secrets, auth/cookie headers, reset tokens or arbitrary full request/provider payloads by default;
- retention is classed rather than one universal forever period;
- Restore cannot erase the fact that Restore occurred and imported historical events preserve provenance.

Critical integrity statement: a local WordPress database Audit table is **not claimed tamper-proof** against a sufficiently privileged DB/server/root actor. Hash-chain/signed/external checkpoint mechanisms remain optional future evidence profiles only if their attacker model and key custody are actually proven.

**Audit physical/integrity benchmarks: 0.**

## Product License remote/API state — ADR-0070/0072/0076

Remote resources remain separate: Account, Product Contract, Installation Activation, Network Activation, Site Allocation, optional Review/Transfer resources and independently signed Product Entitlement.

Accepted paper principles remain resource-oriented versioned API, OAuth separation, signed entitlement authority, idempotent retries, ETag/`If-Match`, RFC 9457-compatible Problem Details, bounded pagination/data minimization and no hidden site/plugin/content inventory.

**0 OpenAPI/server/client/API/service fixtures executed.**

## Provider/runtime state

- Action Scheduler 4.1.0: reviewed candidate only; **P-003 0 executed**.
- Membership Billing: **4 BE3 / 0 MB-certified**.
- Email: **6 EE3 / 0 ET-certified**.
- Event adapters: **0 I4/I5 certified**.
- Backup: **34 targets / 0 C-certified / 0 C3 Supported**.
- Remote Service privacy protocol: **30 fixtures / 0 executed**.

## Platform evidence blockers

P-001 compatibility/Multisite; P-002 UI; P-003 Job backend/history; P-004 Definition; P-005 Vault; P-006 Free↔Pro/Product License runtime; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup; Forms/Chat/NE/EI/Audit physical runtime; Site Lifecycle; Product License API/service.

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- **31/31 Exhaustive, 0/31 Authorized**;
- **31/31 Multisite scopes mapped; 0 MS1+**;
- governance synchronized through **ADR-0081**;
- ADR-0077 Forms/Chat baselines committed;
- ADR-0078 Membership physical baseline committed;
- ADR-0079 Notification/Email operational baseline committed;
- ADR-0080 Event Inbox physical baseline committed;
- ADR-0081 Audit retention/integrity profile committed;
- all executable evidence counts remain 0 unless an older provider static maturity label explicitly says otherwise;
- no implementation/test/provider-certification success claimed.

Not performed: dependency installation, Multisite runtime setup, lifecycle hooks, Action Scheduler bootstrap, PHP/React source, DB tables/migrations/indexes, OpenAPI server/client/mock, provider/service calls, queue runs, crypto execution, PHPUnit/Playwright, webhook/SMTP processing, commerce transactions, Email sends, Backup/Restore, DB/performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. Workflow PT-D run/step/wait physical benchmark profile + P-011 protocol, without execution.
2. WPE Job/Attempt/history PT-C-vs-PT-D physical mapping for P-003, without backend bootstrap.
3. Backup Remote Copy physical schema/index/commit-unknown profile under P-013, without transfers/restores.
4. Vault physical envelope/storage/index alternatives under P-005, without crypto execution.
5. Refine bounded evidence protocols only where they reduce later ambiguity.
6. Keep P-003/P-005/P-011/P-012/P-013 gates intact.
7. Keep Draft PR/governance synchronized.

Before any executable work, explicit owner consent is required.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`
6. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
7. `docs/OPEN-DECISIONS-REGISTER.md`
8. `docs/DECISIONS/README.md`
9. relevant architecture/security/module/provider docs

Repository evidence overrides conversational memory.