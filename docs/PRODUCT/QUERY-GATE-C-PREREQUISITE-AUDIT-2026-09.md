# Surface 6 Query — Gate C Runtime Prerequisite Audit

Status: **BLOCKED ON SHARED FOUNDATION / Query runtime NOT authorized**  
Audit date: **2026-09-03**  
Audit base: `main @ 93558594b0684388530ea19c3a081a1eacf784cd`  
Parent dependency tracker: GitHub issue #66  
Planning contract: `docs/PRODUCT/QUERY-IMPLEMENTATION-CONTRACT-V1.md`

## Decision

Surface 6 Query has cleared its product Bank and Relations dependency prerequisites, but **must not start runtime execution yet** because two shared foundation seams required by the canonical Query architecture are absent on current main:

1. a canonical **Data Source Registry / adapter contract**;
2. a canonical **shared Cache contract / engine seam**.

The shared Policy seam exists and the project-level development consent covers bounded Query source implementation. The correct next action is therefore to build/certify the missing shared foundations in small serialized contracts, not to let Query invent private replacements.

This audit does not make `OPTION_CONTRACT_COMPLETE` a hard Query runtime prerequisite. The current Query runtime-start contract does not list that lifecycle state as a prerequisite, and repository evidence must not be replaced by an invented gate.

## Runtime-start gate reconciliation

The Query Implementation Contract V1 requires five conditions before runtime starts.

### 1. Query Bank integration coordinator-promoted — PASS

Machine authority: `config/product/options-bank-progress.json`.

Current truth:
- Query: `BANK_REVIEWED / 169`;
- 0 unresolved Bank-review items in the accepted Query review candidate;
- total repository Bank truth: 10 surfaces started / 9 BANK_REVIEWED / 1,890 records.

The planning document's historical `166` Bank basis is stale relative to current machine truth and must not be used to reduce or reinterpret the accepted 169-record Bank.

### 2. Relations Gate B public Query-consumer contract accepted — PASS

Relations Gate B is PASS for the certified native V1 baseline.

PR #128 published the stable `RelationQueryConsumerInterface` and a bounded Relations-owned read adapter. It deliberately hides private edge gateway/table/pivot details and provides storage-opaque, direction-aware, scope-aware primitives suitable for Query/Data Source integration.

Exact certified PR #128 head: `de6ff78339a4611f15a2dd865e4aef0ed2385965`.

Exact-head workflows all succeeded:
- Architecture Guards #879;
- PHP Quality Toolchain #258;
- Platform Compatibility Matrix #541;
- Distributable Package #434;
- Relations Edge Persistence #33.

The dedicated public-consumer integration passed on MySQL 8.4 and MariaDB 10.11 baselines.

### 3a. Shared Policy seam — PASS

Current Platform includes `ExecutionContext`, `AuthorizationRequest`, `PolicyDecision` and `PolicyEngine`.

`PolicyEngine` is a concrete shared authorization boundary over the canonical capability checker, and Bootstrap wires Policy into the Ability infrastructure. Query therefore must consume this shared policy layer rather than create a Query-private authorization engine.

This PASS does not mean the current Policy implementation alone implements every future row/projection/source rule. It means the required shared authorization ownership/seam exists and can be extended through canonical contracts rather than bypassed.

### 3b. Shared Data Source seam — BLOCKED / MISSING

Canonical architecture says structured query/filter/projection is owned by **Surface 6 Query + Data Source Registry** and forbids raw private SQL/query mini-engines.

The Query planning contract requires `source_ref` to resolve through a canonical Data Source/owner registry and describes an adapter descriptor exposing source identity/version, field/projection schema, predicates/operators, sorting, pagination, aggregation, relation integration, authorization mapping, scope, budgets, cache hints, diagnostics and normalized errors.

Current repository inspection finds no concrete shared Data Source Registry/contract/runtime seam:
- no `DataSource*` contract path in `frameworks/Contracts`;
- no Data Source subsystem in `frameworks/Platform`;
- no canonical `platform.data-source` service registration in Bootstrap;
- no Query module exists yet that could legitimately own a private substitute.

**Required resolution before Query execution:** introduce a bounded shared Data Source contract/registry foundation that is provider-neutral, storage-opaque, policy-aware and capable of fail-closed degraded registration. It must not expose arbitrary SQL/table/callback execution.

### 3c. Shared Cache seam — BLOCKED / MISSING

Canonical cross-module ownership declares **cache = shared Cache contract** and forbids module caches that omit principal/site/revision dimensions.

The Query planning contract requires the shared cache engine to preserve identity factors such as Query revision, normalized parameters, provider capability version, site/network scope, principal/access context, source generation, relation/policy generation, locale, projection, sort and pagination.

Current repository inspection finds no concrete shared Cache contract/engine seam:
- no `Cache*` contract path in `frameworks/Contracts`;
- no Cache subsystem in `frameworks/Platform`;
- no canonical `platform.cache` service registration in Bootstrap.

**Required resolution before Query execution:** introduce a bounded shared Cache contract/registry/service seam with explicit namespace/scope, TTL and invalidation/generation primitives, fail-closed key identity rules and no privilege-context leakage. The first foundation does not need to implement every future distributed cache backend, but Query must not invent its own cache engine.

### 4. Explicit development consent — PASS

`GOV-OWNER-CONSENT-001` remains ACTIVE in `DEVELOPMENT-CONSENT.md` and `docs/APPROVAL-LEDGER.md`.

The grant covers subsequent module source development across the accepted 56-surface architecture, including development/test build, CI, automated tests, migrations and dependencies required by approved milestones. It does not waive technical gates or authorize production deployment/release/destructive live operations.

### 5. Runtime implementation scoped into reviewable slices — NOT YET OPEN

A safe first Query runtime slice can be declared only after the two missing shared seams above are certified.

Recommended first Query-owned runtime tranche after those foundations:

**Typed AST + validation foundation V1**, limited to:
- versioned immutable/normalized Query definition/AST value objects;
- finite node grammar and typed scalar/operator validation;
- structural budgets: AST size, nesting depth, predicate count, IN-list size, page-size bounds;
- raw SQL/PHP/callback/unchecked identifier rejection;
- source references validated only through the shared Data Source Registry contract;
- relation references typed against the accepted public Relations consumer contract without executing relation traversal;
- no provider SQL compilation/execution;
- no REST/admin execution path;
- no persistent result cache implementation inside Query;
- exact unit/static/architecture evidence.

This slice keeps provider execution, native WordPress adapters, relation predicate execution, caching behavior, admin preview and public/REST exposure for later bounded work.

## Change-impact contract for the next shared foundations

### Shared Data Source foundation

**Affected:** shared Contracts/Platform service registry, future Query/provider adapters.  
**Unaffected:** Relations private persistence, Fields private storage, Search indexing, provider SDKs.  
**Primary risks:** arbitrary identifier/SQL escape, provider capability ambiguity, authorization bypass, private-storage coupling.  
**Migration:** none expected for V1 contract/registry.  
**Recovery:** remove/disable an unregistered adapter; unknown capabilities fail closed.  
**Verification:** contract validation, duplicate/unknown provider rejection, degraded-provider behavior, policy/scope metadata, architecture no-bypass guards.

### Shared Cache foundation

**Affected:** shared Contracts/Platform cache service seam and future consumers.  
**Unaffected:** Query semantics, domain source truth, authorization authority.  
**Primary risks:** cross-user/cross-site cache leakage, stale privilege reuse, collision, invalidation ambiguity.  
**Migration:** none expected for the first contract/in-memory/request-local foundation.  
**Recovery:** disabling cache must preserve correctness; cache is never source truth.  
**Verification:** namespace/key identity, scope/principal dimensions, TTL/generation invalidation, no privileged-to-lower-context reuse, fail-closed malformed key policy.

## Required execution order

To avoid downstream work on unverified assumptions:

`Gate C prerequisite audit → shared Data Source contract/registry → shared Cache contract/service → Query typed AST + validation V1 → provider execution slices`

Data Source should precede the first Query AST implementation because `source_ref` validation and source capabilities must have a canonical owner. Cache can be built independently after Data Source but must be certified before Query execution/runtime is authorized.

## Explicit non-actions

This audit does not:
- create a Query module;
- execute a Query;
- add raw SQL/provider compilation;
- create a Query-private Data Source registry or cache;
- authorize Query REST/admin execution;
- promote Query to runtime-certified/product-parity status;
- authorize production deployment/release.

## Exit state

**Query Gate C runtime start: BLOCKED.**

Passed prerequisites: Bank, Relations public seam, Policy, development consent.  
Blocking prerequisites: shared Data Source seam, shared Cache seam.  
Next safe work: certify the shared Data Source foundation first, then the shared Cache foundation, then re-run this runtime-start gate before any Query execution code begins.
