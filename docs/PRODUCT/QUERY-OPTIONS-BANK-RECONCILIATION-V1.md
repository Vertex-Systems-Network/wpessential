# WPEssential Surface 6 — Query Options Bank Reconciliation V1

Status: **current-main-synchronized surface-local candidate / Query runtime remains blocked**  
Date: **2026-09-02**  
Claim base: `main @ 1687d7f0412051d5a5d8fbbcc1f9e7af64114a15`  
Claim branch: `agent/query-bank-reconciliation-v1`  
Recovered source: `planning/options-bank-query-v1`

## Purpose

Recover the stale Surface 6 Query planning/Options Bank candidate onto current `main` without direct-merging its diverged history, without starting Query runtime, and without taking shared/global write ownership from the Supervisor/Integrator.

At recovery time the stale source branch was **13 commits ahead / 37 commits behind** current `main`, with merge base `b06c3999d91979f76909352a8fd8a52729524637`. The AUTO worker therefore recreated only the Query-local candidate paths on the deterministic claim branch instead of moving, rebasing, force-updating, or merging the stale source branch.

## Current recovered candidate

The reconciled Surface 6 Bank contains **169 unique records across five Query-local shards**:

- `query.json` — 50
- `query--predicates.json` — 60
- `query--runtime-guards.json` — 49
- `query--wpe-exceed-v1.json` — 7
- `query--roles-v1.json` — 3

Lifecycle evidence remains surface-local:

- Bank records: **169**
- duplicate IDs: **0 expected**
- duplicate option paths: **0 expected**
- `UNREVIEWED`: **0**
- native audit: **NATIVE_AUDITED**, 30 dispositions, 0 unresolved
- market audit: **MARKET_AUDITED**, 3 primary providers + 1 specialist, 82 Bank references, 0 unresolved
- review certificate: **BANK_REVIEWED candidate**, 169 records, 0 unresolved

Canonical shared `config/product/options-bank-progress.json` intentionally remains `query = UNSEEDED / 0` on this worker branch. Shared lifecycle promotion and aggregate counter changes are Integration Requirements, not worker-owned writes.

## Reconciliation correction: WP_User_Query role filters

The stale native audit incorrectly reused `query.status.include` / `query.status.exclude` for `WP_User_Query` role filtering. That conflates two different canonical owners.

The reconciled candidate adds Query-local role predicate semantics:

- `query.role.owner_contract`
- `query.role.include`
- `query.role.exclude`

Query owns the filtering semantics. **Surface 30 Roles** retains ownership of role identity, role definitions, capability grants, and membership. Authorization remains Policy-owned. No role mutation or capability grant behavior is introduced here.

The native audit now maps `role / role__in / role__not_in` to those role-predicate records and records `owner_surface = roles` for the referenced identity contract.

## Ownership and no-bypass boundaries

Surface 6 Query owns typed query semantics: AST structure, predicates, ordering, source-native search, bounded pagination, provider capability validation, reusable definitions, diagnostics intent, and Query-specific execution safeguards.

Query does **not** own:

- Fields schema/value storage;
- Relations definition/edge storage;
- Roles definitions or capability grants;
- Status definitions/transitions;
- Search indexing/ranking infrastructure;
- Listings rendering;
- Admin Columns presentation;
- shared Data Source, Policy, cache, REST, Ability, Composer, CI, or global lifecycle registries.

Arbitrary authored SQL, unchecked identifiers, arbitrary PHP/callback execution, Policy bypass, hidden peer storage assumptions, unbounded public queries, and AI privileged execution remain rejected.

## Runtime boundary

This slot is planning-only. Query runtime is still dependency-blocked by Relations Gate B and the accepted public Query-consumer/Data Source seam.

The recovered implementation contract remains planning evidence only. Any relation-bearing Query node must continue to fail closed until Surface 4 exposes a stable public consumer contract; no Query code may inspect private relation tables/classes or infer storage layout.

## Worker write scope

This branch is restricted to:

- Query-local Bank shards;
- Query-local native/market/review artifacts;
- Query-local smoke tests;
- Query-local planning/research/implementation-contract documentation.

It intentionally does **not** edit:

- `config/product/options-bank-progress.json`;
- semantic registries unless an actual Surface 6 semantic mapping becomes necessary;
- Composer/lockfiles;
- shared schemas;
- README/CHECKPOINT/project-state truth;
- CI workflows;
- shared Data Source/Policy/cache contracts;
- runtime source.

## Verification plan

Surface-local smoke evidence is carried by:

- `tests/Smoke/options-bank-query-native-audit-contract.php`
- `tests/Smoke/options-bank-query-market-audit-contract.php`
- `tests/Smoke/options-bank-query-review-contract.php`

The review contract now requires exactly **169** records and the three role-ownership correction IDs.

Repository-wide Options Bank progress guards are expected to remain fail-closed until the Supervisor/Integrator performs the serialized shared promotion of Query from `UNSEEDED / 0`. That expected shared-state blocker must not be bypassed or “fixed” by a worker-owned shared progress edit.

## Integration Requirements

1. **IR-QUERY-001 — shared lifecycle:** Supervisor/Integrator promotes Surface 6 shared progress through the accepted Bank lifecycle using the reconciled **169-record** candidate.
2. **IR-QUERY-002 — aggregate truth:** recompute shared seeded/native/market/review counters and total Bank records only in the serialized integration lane.
3. **IR-QUERY-003 — semantic registry:** confirm again that no Surface 6 alias/effective-derivation entry is required; add one only if evidence proves it.
4. **IR-QUERY-004 — test registration:** register Query smoke tests in shared Composer/CI aggregation only through the integrator-owned shared write.
5. **IR-QUERY-005 — Relations dependency:** do not start Query runtime until Relations Gate B publishes/accepts the public Query consumer contract and Gate C is explicitly opened.
6. **IR-QUERY-006 — shared architecture:** reuse canonical Data Source, Policy, cache, Ability and REST contracts; never create Query-private substitutes.

## Source-document interpretation

`QUERY-OPTIONS-BANK-RESEARCH-V1.md` and `QUERY-IMPLEMENTATION-CONTRACT-V1.md` were recovered from the stale planning branch as substantive source evidence. Their historical header values (`166` records, old base/branch) describe the predecessor candidate. Where those header values conflict with the reconciled machine artifacts, **this reconciliation record plus the current machine artifacts are authoritative for the worker handoff**. Their runtime safety and ownership rules remain applicable.

This reconciliation does not claim runtime implementation, Gate C start, product parity, release, deployment, or production readiness.
