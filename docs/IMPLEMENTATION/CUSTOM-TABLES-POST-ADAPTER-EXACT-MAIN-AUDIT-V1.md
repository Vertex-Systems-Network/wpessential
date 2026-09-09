# Custom Tables — Post-Adapter Exact-Main Audit V1

Status: **SUPERVISOR AUDIT / NOT YET PROMOTED**  
Source issue: **#453**  
Exact-main anchor: **`91088bf167c2a44cc4e1b3972b4c5445825258d0`**

## Purpose

This audit is the mandatory gate after the concrete post-hardening adapter wave promoted through PRs #447, #448, #449 and #450. It decides whether repository evidence is strong enough to open a bounded R1/R2 managed-table execution coordinator lane.

It does not implement or execute managed-table DDL.

## Promoted evidence reviewed

### Durable Migration Run store

`CreateMigrationRunStoreMigration` implements the canonical `MigrationInterface`, uses an internal WPE metadata table, validates the controlled table prefix and is non-destructive. `WpdbMigrationRunRepository` preserves site-scoped create/get/compare-and-swap semantics and uses the promoted persistence codec.

Audit result: **PASS as a bounded persistence primitive.**

However, this audit does not have repository evidence proving that the Custom Tables store migration is wired into the production bootstrap/composition path that constructs and runs the canonical Platform migration registry. Class existence and unit-level registry compatibility are not equivalent to production registration.

### Metadata-only Precondition probe

`MetadataPreconditionProbe` evaluates only bounded metadata facts for table presence/absence, column fingerprints and already-observable database feature availability. Unsupported requirements fail to `Unsupported`; missing/mismatched facts block.

Audit result: **PASS for metadata-only bounded facts.**

No live row count/null/duplicate/range/max-length scanning is authorized by this primitive.

### Recovery Verification provider boundary

`StaticRecoveryVerificationProvider` verifies typed already-existing recovery evidence by artifact id and reviewed plan fingerprint and fails closed on mismatch.

Audit result: **PASS as a deterministic read/verify reference provider.**

It is not evidence of a production provider integration that verifies an external recovery artifact. Snapshot creation, restore, chargeable provider side effects and secret/provider payload persistence remain absent and blocked.

### Canonical Policy authorization adapter

`MigrationExecutionAuthorizationPolicyAdapter` derives `capabilityAllowed` from canonical `PolicyEngine` authorization and derives actor identity from `ExecutionContext`/`Principal`. It produces a bounded `MigrationExecutionAuthorizationRequest`; it does not dispatch statements or mutate capabilities.

Audit result: **PASS as a Policy-to-authorization-facts adapter.**

The adapter still receives confirmation as an input fact. A future composition boundary must establish the trusted source and freshness of that confirmation rather than treating arbitrary caller input as sufficient authorization evidence.

## Combined readiness decision

**R1/R2 managed-table execution coordinator: BLOCKED / NOT AUTHORIZED.**

The four promoted lanes materially improve the safety foundation, but exact-main evidence does not yet prove one production composition boundary that:

1. registers/boots the internal Migration Run store through canonical Platform migration infrastructure;
2. constructs the durable run repository with explicit site/network scope;
3. assembles only allowlisted metadata precondition probes;
4. obtains bound/fresh recovery verification facts from an explicitly trusted verification port;
5. obtains actor/capability facts from canonical Policy and confirmation from a trusted, revision-bound source;
6. binds all facts to the same reviewed plan/run/readiness revision;
7. fails closed before any statement dispatch when any evidence is missing, stale, mismatched or unsupported.

Opening a DDL executor before this composition evidence exists would make the dispatcher responsible for solving trust/bootstrap concerns that belong upstream.

## Next authorized bounded work package

### Custom Tables — Runtime Composition Readiness V1

Role: **SUPERVISOR_ONLY / serialized integration**.

Goal: prove the production composition/bootstrap path for the already-promoted primitives **without executing managed-table DDL**.

Allowed scope:

- canonical registration/bootstrap of the internal Migration Run store through `frameworks/Platform/Database/Migrations/**`;
- construction/wiring of `WpdbMigrationRunRepository` with explicit site/network scope;
- composition of metadata-only probe registry/adapters;
- composition of read/verify recovery verification port facts;
- composition of canonical Policy-derived authorization facts plus trusted confirmation binding;
- one pure orchestration/readiness service that returns an immutable execution-readiness package/envelope with `execution_allowed=false` or equivalent no-dispatch semantics;
- focused unit/integration evidence proving fail-closed behavior and common revision/plan binding.

Forbidden scope:

- dispatching `CREATE`, `ALTER`, `DROP`, `RENAME`, `TRUNCATE` or `dbDelta()` against managed Custom Tables;
- generic managed-table DDL execution through `$wpdb->query()` or another shared mutation API;
- R3/R4 execution;
- live row scans;
- Backup creation/restore;
- Action Scheduler migration execution, leases or retries;
- backfill/dedup/shadow-copy/swap;
- row CRUD/Data Source runtime;
- CT2/PT-D or CT3 runtime/topology conversion;
- external-table adoption;
- public admin/REST/Ability mutation endpoints;
- deployment/release.

## Exit criteria for a later execution-coordinator audit

A later exact-main Supervisor audit may reconsider an R1/R2-only managed-table execution coordinator only when all of the following are promoted:

- production migration registration/bootstrap evidence for the internal run store;
- durable repository construction evidence with correct scope isolation;
- metadata-probe composition evidence with no row scans;
- trusted recovery verification composition evidence;
- canonical Policy + trusted confirmation composition evidence;
- same-plan/run/revision binding across readiness facts;
- deterministic fail-closed tests for missing/stale/mismatched facts;
- exact-head applicable CI green and clean review threads.

## Change impact

**Affected:** Custom Tables migration readiness/composition planning and next work authorization.  
**Unaffected:** existing managed tables, row data, public APIs, deployment/release state.  
**Risk:** premature statement dispatch if composition trust is skipped.  
**Migration:** none in this audit.  
**Rollback/recovery:** documentation-only; revert the audit promotion if later evidence disproves its conclusions.  
**Verification:** exact-main source inspection plus promoted PR/CI evidence.

## Final boundary

This audit does **not** promote `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`, does not authorize production deployment/release and does not authorize physical managed-table mutation.
