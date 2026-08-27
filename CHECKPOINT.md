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

Accepted decisions now extend through **ADR-0076**.

Latest milestones:
- ADR-0072 — Product License remote resource/conflict-state model.
- ADR-0073 — Definition Repository PT-C D1 benchmark baseline.
- ADR-0074 — Relations R1/PT-D benchmark baseline with R2/PT-E comparison.
- ADR-0075 — Multisite Site Lifecycle Coordinator.
- **ADR-0076 — Product License HTTP/OpenAPI contract principles.**

## Multisite / Site Lifecycle

- 31/31 surface scopes mapped;
- site scope default, network scope explicit;
- cross-site Relations/Queries Off by default;
- Site Lifecycle Coordinator covers provisioning, state changes, destructive Plan/drain, PT-C/PT-D/PT-E cleanup, PT-F reconciliation, clone/transfer and durable lifecycle journal;
- WordPress site initialization/update/uninitialization/deletion remain distinct lifecycle facts;
- **0 WPE lifecycle hooks/fixtures executed; 0 MS1+ certified**.

## Physical topology / Definition / Relations

PT-A…PT-F topology classes accepted.

Definition Repository:
- PT-C;
- D1 future P-004 baseline: numeric physical IDs, textual portable UUID, explicit scope, bounded identity keys, immutable text payload, minimal indexes;
- D2 binary UUID, D3 native JSON, D4 FK/constraints remain comparisons;
- **0 DDL/benchmarks**.

Relations:
- R1/PT-D future P-010 baseline: shared scoped universal typed edge table;
- R2/PT-E per-site table mandatory comparison;
- R3 per-relation table exceptional only;
- **0 DDL/benchmarks**.

## Product License remote/API state — ADR-0070/0072/0076

Remote resources remain separate:
- Account;
- Product Contract;
- Installation Activation;
- Network Activation;
- Site Allocation;
- optional Review/Transfer resources;
- independently signed Product Entitlement.

Accepted HTTP/OpenAPI paper principles:
- resource-oriented versioned `/v1`-style contract;
- OAuth Account auth separate from local WordPress authorization;
- signed entitlement remains cryptographic authority;
- retryable mutations use stable idempotency semantics;
- mutable resources use ETag/`If-Match`-style optimistic concurrency;
- RFC 9457-compatible Problem Details with stable safe machine codes;
- bounded cursor pagination;
- no hidden site/plugin/content inventory;
- outage uses signed offline entitlement rules and is not expiry.

**0 OpenAPI/server/client/API/service fixtures executed.**

## Provider/runtime state

- Action Scheduler 4.1.0: reviewed candidate only; **P-003 0 executed**.
- Membership Billing: **4 BE3 / 0 MB-certified**.
- Email: **6 EE3 / 0 ET-certified**.
- Backup: **34 targets / 0 C-certified / 0 C3 Supported**.
- Remote Service privacy protocol: **30 fixtures documented / 0 executed**.

## Platform evidence blockers

P-001 compatibility/Multisite; P-002 UI; P-003 Job; P-004 Definition; P-005 Vault; P-006 Free↔Pro/Product License runtime; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup; Site Lifecycle runtime; Product License HTTP/service contract tests.

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- **31/31 Exhaustive, 0/31 Authorized**;
- **31/31 Multisite scopes mapped; 0 MS1+**;
- ADR index/Open Decisions/Readiness/Checkpoint synchronized through **ADR-0076**;
- Product License API candidate contract + ADR-0076 committed;
- Relations benchmark profile + ADR-0074 committed;
- Site Lifecycle Coordinator + ADR-0075 committed;
- Definition D1 baseline + ADR-0073 committed;
- all executable evidence counts remain 0 where not explicitly certified;
- no implementation/test/provider-certification success claimed.

Not performed: dependency installation, Multisite runtime setup, lifecycle hooks, Action Scheduler bootstrap, PHP/React source, DB tables/migrations/indexes, OpenAPI server/client/mock, service calls, queue runs, crypto execution, PHPUnit/Playwright, provider/API/webhook/SMTP calls, commerce transactions, Email sends, Backup/Restore, DB/performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. P-004 Definition benchmark fixture/query-plan protocol without execution.
2. P-010 Relations benchmark fixture/query-plan protocol without execution.
3. Site Lifecycle evidence protocol refinement without hooks/tests.
4. Forms/Chat PT-D-vs-PT-E paper comparison.
5. Product License OpenAPI component schema refinement only where static review adds value.
6. Keep P-003/P-012/P-013 gates intact.
7. Keep governance/Draft PR synchronized.

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
