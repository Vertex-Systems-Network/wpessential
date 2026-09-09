# Custom Tables — Post-Runtime Composition Exact-Main Audit V1

Status: **SUPERVISOR AUDIT / READY FOR PROMOTION**  
Source issue: **#461**  
Exact-main anchor: **`56cbcce2da90a11232fd65b374ad237fc1c67fb0`**

## Purpose

This is the mandatory exact-main audit after Runtime Composition Readiness V1 promoted through Issue #459 / merged PR #460.

It determines whether the repository now has enough production-owned trust provenance to authorize an R1/R2 managed-table execution coordinator. It does not implement or execute managed-table DDL.

## Evidence reviewed

### Production migration/bootstrap registration — PASS

`frameworks/Bootstrap/Plugin.php` registers `CreateMigrationRunStoreMigration` through the canonical Platform `MigrationCoordinator` before `runPending()`. Custom Tables therefore composes the shared Platform migration infrastructure instead of introducing a private migration engine.

The same bootstrap exposes `custom-tables.runtime-composition.factory` only when the canonical `NativeWpdbAdapter` exists, and binds the factory to the current site/network scope plus the canonical `PolicyEngine`.

Audit result: **PASS for bounded production bootstrap/composition.**

### Durable Migration Run repository scope — PASS

`CustomTablesRuntimeCompositionFactory` constructs `WpdbMigrationRunRepository` using the canonical database adapter, the promoted `MigrationRunRecordCodec`, and explicit site scope. `RuntimeCompositionReadinessService` independently verifies site/network execution context and persisted-run fingerprint identity before declaring composition ready.

Audit result: **PASS for bounded durable run identity and scope isolation.**

### Metadata-only precondition composition — PASS WITH TRUST-PROVENANCE GAP

`RuntimeCompositionReadinessService` allowlists only:

- table exists;
- table missing;
- column fingerprint match;
- database feature availability.

Non-allowlisted kinds, including row-count/null/duplicate/range/max-length style requirements, become `Unsupported` and fail closed. The existing `WordPressCt1SchemaIntrospector` is a bounded, trusted, read-only INFORMATION_SCHEMA metadata primitive and remains suitable upstream evidence for a future production facts source.

However, `RuntimeCompositionReadinessService::evaluate()` currently receives `MetadataPreconditionFacts` directly from its caller. The composition boundary validates what those facts mean, but it does not prove that production facts were constructed from the canonical read-only introspector/provider path.

Audit result: **semantic allowlist PASS; production fact provenance NOT YET CLOSED.**

### Recovery verification/freshness — PASS WITH PROVIDER-PROVENANCE GAP

The composition service calls `RecoveryVerificationProviderInterface`, binds the returned evidence to the reviewed plan/provider/version, enforces maximum evidence age, and feeds typed recovery evidence into `RecoveryReadinessDecision`. Missing, stale, mismatched or failed recovery evidence blocks readiness.

The promoted reference implementation `StaticRecoveryVerificationProvider` is deterministic and typed, but `CustomTablesRuntimeCompositionFactory::create()` accepts any implementation of `RecoveryVerificationProviderInterface`. The repository does not yet prove one server-owned provider registry/construction path that establishes which recovery verification implementations are trusted in production.

Audit result: **verification/binding semantics PASS; production provider provenance NOT YET CLOSED.**

### Policy authorization + confirmation binding — PASS WITH CONFIRMATION-PROVENANCE GAP

The factory always uses the canonical `PolicyEngine` through `MigrationExecutionAuthorizationPolicyAdapter`. The composition service binds confirmation to the same run id, plan fingerprint, readiness state revision and actor identity before passing `confirmationProvided=true` into the authorization request. Capability denial, missing/stale confirmation and R3/R4 risk fail closed.

The promoted `BoundMigrationExecutionConfirmation` contract is appropriately revision-bound. But the only concrete confirmation provider in the bounded implementation is `StaticMigrationExecutionConfirmationProvider`, and the factory accepts any `MigrationExecutionConfirmationProviderInterface`. There is not yet a canonical server-owned durable/scoped source that proves where production confirmation came from, when it was created, and that arbitrary callers cannot substitute a different provider implementation.

Audit result: **binding semantics PASS; production confirmation provenance NOT YET CLOSED.**

### Same-plan/run/revision and no-dispatch semantics — PASS

The readiness composition verifies persisted run identity, reviewed/current generation identity, preview plan/provider identity, recovery plan/provider/freshness, Policy actor/capability, confirmation run/plan/revision/actor, and execution-context site/network scope.

`RuntimeCompositionReadinessPackage` hardcodes `execution_allowed=false`. A composition-ready package therefore remains non-executable by construction. Runtime Composition V1 contains no managed-table statement dispatcher, no migration job runner and no public mutation endpoint.

Audit result: **PASS for bounded no-dispatch composition.**

## Exact-head CI and review evidence

PR #460 exact head `8a200980786949475727404b57fabfea95fce95a` passed all applicable pull-request workflows reviewed by this audit:

- PHP Quality Toolchain — PASS;
- Architecture Guards — PASS;
- Platform Compatibility Matrix — PASS;
- Distributable Package — PASS;
- CPT Runtime — PASS;
- Taxonomy Runtime — PASS;
- Status Reference Application — PASS.

Inline review threads were empty at promotion time. PR #460 merged to exact main as `56cbcce2da90a11232fd65b374ad237fc1c67fb0`.

## Execution-coordinator decision

**R1/R2 managed-table execution coordinator: BLOCKED / NOT AUTHORIZED.**

Runtime Composition Readiness V1 closes the bootstrap, scope, binding and no-dispatch composition gap identified by the previous audit. It does **not** yet prove that every readiness fact entering the composition service is produced by a server-owned trusted source.

The blocking trust gaps are:

1. metadata precondition facts are caller-supplied to the service rather than obtained from a canonical trusted metadata-facts source;
2. recovery verification provider selection/construction is caller-injected rather than constrained by a canonical server-owned trusted provider boundary;
3. confirmation provider selection/construction is caller-injected and only a static reference implementation is currently promoted;
4. the exact composition attack matrix should be expanded around missing/mismatched facts, site/network scope, actor/capability denial, recovery absence/staleness and R3/R4 before any dispatcher can consume readiness.

Allowing a dispatcher now would move unresolved trust provenance into the most privileged mutation boundary. That would violate the existing no-bypass architecture.

## Next authorized bounded work package

### Custom Tables — Trusted Runtime Evidence Sources V1

Role: **SUPERVISOR_ONLY / serialized trust-boundary integration**.

Goal: make the production composition boundary obtain its safety facts only from canonical server-owned sources while preserving `execution_allowed=false` and zero managed-table mutation.

Allowed scope:

- introduce a typed metadata-facts provider port and production read-only implementation that derives `MetadataPreconditionFacts` from existing trusted CT1/schema/provider metadata primitives;
- no row-value reads or row aggregate scans while constructing metadata facts;
- establish an explicit server-owned/allowlisted recovery verification provider construction or registry path using existing typed recovery evidence/verification contracts;
- establish a server-owned scoped confirmation source with durable/revision-bound provenance, or an equivalent canonical provider construction boundary that arbitrary runtime callers cannot substitute;
- tighten `CustomTablesRuntimeCompositionFactory` so trusted provider selection is owned by production composition rather than arbitrary callers;
- add focused fail-closed attack tests for missing/mismatched plan/run/revision/site/network/actor/capability/recovery/provider facts and R3/R4;
- keep the returned composition package immutable with `execution_allowed=false`.

### Still forbidden

This next lane does not authorize:

- `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` against managed Custom Tables;
- generated managed-table DDL dispatch through `$wpdb->query()` or any shared mutation API;
- R1/R2 statement execution despite the lane name referring to execution readiness;
- R3/R4 execution;
- live row-count/null/duplicate/range/max-length scans;
- Backup creation or restore side effects;
- migration jobs, leases, retries or Action Scheduler execution;
- row CRUD/Data Source runtime;
- backfill/dedup/shadow-copy/swap;
- CT2/PT-D/CT3 runtime conversion;
- external-table adoption;
- public admin/REST/Ability mutation endpoints;
- deployment/release.

## Exit criteria for another execution-coordinator audit

A later exact-main Supervisor audit may reconsider an R1/R2-only execution coordinator only when repository evidence proves all of the following:

- production metadata facts are sourced through a canonical trusted read-only provider;
- recovery verification provider selection/construction is server-owned and allowlisted;
- confirmation provenance is server-owned, scoped and revision-bound;
- arbitrary caller provider substitution cannot manufacture a ready package;
- the expanded composition attack matrix passes exact-head CI;
- `execution_allowed=false` remains the only behavior of the composition package;
- review threads are clean and current main is reconciled.

## Progress and lifecycle impact

Custom Tables remains **ACTIVE / NOT PASS** at the existing bounded runway indicator of **90%**. This audit does not invent a higher percentage because no new repository-defined percentage milestone has been promoted.

No `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment, release or managed-table mutation claim is promoted.

## Final boundary

Runtime Composition Readiness V1 is accepted as a bounded **no-dispatch composition foundation**. Physical managed-table mutation remains blocked until trusted runtime evidence provenance is promoted and another exact-main audit explicitly authorizes a narrower execution lane.