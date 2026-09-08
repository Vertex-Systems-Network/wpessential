# Custom Tables — Post-Hardening-Wave Exact-Main Audit V1

Status: **SUPERVISOR AUDIT / concrete adapter prerequisites may open; managed-table mutation remains blocked**  
Issue: **#441**  
Exact-main audit anchor: **`18165d325dd97d6346ad2982a8464b450eb8273d`**  
Date: **2026-09-08**

## AI-Native preflight

At the audit anchor the mandatory work-cycle order was executed:

1. exact `main` resolved;
2. OPEN Issues checked — **0**;
3. OPEN PRs/MRs checked — **0**;
4. deterministic claims and coordination truth reviewed;
5. only then was this post-hardening Supervisor audit opened.

Issue #415 / merged PR #416 remains canonical: Issues first, PRs/MRs second, claims/queue third, new development last, and README module progress reconciliation at stable cycle closeout.

## Verdict

Surface 7 Custom Tables remains **ACTIVE / NOT PASS**.

The four post-composition hardening prerequisites authorized by Issue #431 / merged PR #432 are promoted:

- Run Persistence Record Codec V1 — merged PR #437;
- Precondition Read-Only Probe Plan V1 — merged PR #438;
- Recovery Evidence Binding/Freshness V1 — Issue #435 / merged PR #439;
- Execution Authorization Envelope V1 — Issue #436 / merged PR #440.

PR #439 promoted at `08f91e5c617b4e2b8de1f253568f3ddfc0802f96`. PR #440 was reconciled to latest main without force, certified by Architecture Guards, PHP Quality Toolchain, Platform Compatibility Matrix and Distributable Package, and promoted at `18165d325dd97d6346ad2982a8464b450eb8273d`.

The README bounded-runway indicator remains **90%**. The repository defines no new milestone percentage for this hardening wave, so this audit intentionally does not fabricate 95% or another higher value. Evidence and next-gate text advance while the progress bar remains evidence-backed at 90%.

## Hardening evidence

### Run persistence record codec

The promoted codec provides a deterministic bounded representation for `MigrationRun` persistence and validates storage-shape/version and canonical run invariants without owning a database adapter, storage table, DDL, jobs or leases.

### Precondition read-only probe plan

The promoted probe-plan contract converts supported precondition requirements into bounded allowlisted read-only descriptors. It does not execute database queries, expose raw user SQL, return row payloads, backfill or deduplicate data.

### Recovery evidence binding/freshness

The promoted binding decision ties recovery evidence to reviewed plan/provider facts and fails closed for missing, stale or mismatched evidence. It performs no Backup creation, verification side effect, restore or persistence.

### Execution authorization envelope

The promoted authorization layer consumes bounded run/revision, actor, capability, confirmation and risk facts after execution readiness. Stale readiness, capability denial, missing confirmation and unsupported R3/R4 risk fail closed. It does not bypass canonical Policy, own provider statements, access the database, schedule work or expose a public mutation endpoint.

## Platform composition finding

`frameworks/Platform/Database/Migrations/**` remains the canonical generic migration coordination/state boundary. In particular `MigrationRegistry`, `MigrationRunner`, `MigrationCoordinator`, `InMemoryMigrationStateStore` and `WpdbMigrationStateStore` already own generic migration registration/execution-state responsibilities.

Custom Tables must compose this Platform boundary rather than create a duplicate migration engine. The existing `WpdbMigrationStateStore` tracks platform migration IDs only; it is not a substitute for the richer Custom Tables `MigrationRun` repository. Any future durable run repository therefore needs an explicitly owned internal metadata schema while its bootstrap/migration lifecycle composes the canonical Platform migration registry/runner.

## Next dependency-safe gates

This audit allows the following **non-managed-table-mutation** work to be planned/implemented after this reconciliation promotes. These lanes are intentionally narrower than a physical DDL executor.

### Lane A — Internal Migration Run Store Schema + WPDB Repository V1 — serialized Supervisor integration

Purpose: provide durable Custom Tables run-state persistence using the promoted record codec and optimistic revision semantics.

Requirements:

- internal WPE metadata table only; never a user managed custom table;
- table bootstrap/versioning must compose `frameworks/Platform/Database/Migrations/**` rather than invent a private migration runner;
- canonical `MigrationRunRepositoryInterface` behavior, including create/get/compare-and-swap;
- record decode/validation through the promoted persistence codec;
- site/network scope must be explicit and tested;
- prepared values and registry-controlled identifiers only;
- focused integration tests against supported MySQL/MariaDB fixtures.

Forbidden: executing a generated Custom Tables provider migration preview, mutating a managed target table, jobs/leases or public mutation endpoints.

Because this touches durable storage ownership and Platform migration registration, it is **serialized / Supervisor-owned integration**, not a free Worker lane.

### Lane B — Metadata-Only Precondition Probe Adapters V1 — parallel Worker

Purpose: implement safe read-only probes that can be answered from trusted schema/provider metadata without scanning table rows.

Allowed initial families: table existence/non-existence, column/schema metadata match, provider feature/capability availability where already observable through canonical read-only boundaries.

Forbidden: row count, NULL/duplicate/range/max-length scans, arbitrary SQL, data payload reads, backfill/deduplication or mutation.

### Lane C — Recovery Verification Provider Port V1 — parallel Worker

Purpose: define a bounded integration port that can obtain verification facts for an already-existing recovery artifact and convert them into the promoted bound/freshness evidence model.

The first tranche is read/verify-only. It must not create snapshots, trigger backups, restore data, incur chargeable provider side effects, or persist secrets/provider payloads.

### Lane D — Execution Authorization Policy Adapter V1 — parallel Worker

Purpose: compose canonical WPE Policy/capability evaluation into the promoted authorization request without duplicating Policy or trusting caller-supplied booleans at the production boundary.

The adapter may produce bounded authorization facts only. It cannot dispatch SQL, change capabilities, expose a public mutation endpoint, schedule jobs or override R3/R4 denial.

## Still explicitly blocked

Even after this audit promotes, the following remain blocked:

- dispatch of generated `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` against managed Custom Tables;
- generic managed-table DDL execution through `$wpdb->query()` or any bypass;
- R3/R4 execution;
- live row-count/null/duplicate/range/max-length scans;
- Backup creation or restore side effects;
- leases, retry workers or Action Scheduler migration execution;
- backfill/deduplication/shadow-copy/swap;
- row CRUD/Data Source runtime;
- CT2/PT-D or CT3 runtime/topology conversion;
- external-table adoption;
- public admin/REST/Ability mutation surfaces;
- `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release approval.

## Next audit requirement

After durable run persistence plus the bounded metadata-probe, recovery-verification and Policy-adapter lanes are promoted, another exact-main Supervisor audit must decide whether an **R1/R2-only managed-table execution coordinator** can be opened. No physical managed-table mutation branch may be pre-created before that audit.