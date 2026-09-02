# WPEssential Surface 9 — AUTO Listings Bank Reconciliation

Status: **AUTO Worker reconciliation / planning-only / runtime NOT authorized**  
Date: **2026-09-02**  
Slot: `listings-bank-reconciliation-v1`  
Work mode: `INTEGRATION_WORK`  
Claim branch: `agent/listings-bank-reconciliation-v1`  
Exact claim base: `main @ b0ce9c64ce2ef4959870711899fc9c796047afd0`

## 1. Why this slot was selected

The Worker followed the current AUTO queue rather than choosing a module from chat memory.

At claim time:

- `relations-gate-b-closure-v1` was not free because deterministic branch `agent/relations-gate-b-closure-v1` already existed;
- `taxonomy-bank-reconciliation-v1` had been superseded/completed by accepted Taxonomy worker PR #120 and the subsequent shared lifecycle promotion already present on current `main`;
- `query-bank-reconciliation-v1` was not free because deterministic branch `agent/query-bank-reconciliation-v1` already existed;
- `listings-bank-reconciliation-v1` remained `OPEN`, its deterministic claim branch did not exist, and current shared Bank truth still reported Listings as `UNSEEDED / 0`.

The exact deterministic Listings branch was therefore created from `main @ b0ce9c64ce2ef4959870711899fc9c796047afd0` without force.

## 2. Stale source audit and recovery method

Queue source: `planning/options-bank-listings-v1`.

At the claim audit that branch was:

- **11 commits ahead** of current main;
- **67 commits behind** current main;
- merge base `0415b2067c3882841c1359753dd34adcd0602543`;
- limited to 11 Listings-local Bank/audit/review/research/UX/planning files.

The stale branch was **not merged, rebased into main, force-updated, or treated as current repository truth**.

Its exact useful Listings-local blobs were recovered onto the current-main claim branch as a new bounded commit:

`2290e3964826734a6a0e9689ebee639a3a2939c3` — `docs(listings): recover reviewed planning candidate`

This preserves current main ancestry while retaining the accepted surface-local planning evidence for revalidation.

## 3. Current-main ownership revalidation

Current canonical contracts still assign:

- reusable Listing/template presentation definitions and collection composition to **Surface 9 Listings**;
- structured query/filter/sort/projection/pagination semantics to **Surface 6 Query**;
- relation edges/cardinality/pivot/order to **Surface 4 Relations**;
- Field schema/value storage to **Surface 3 Fields**;
- indexed relevance/facets/search semantics to **Surface 34 Search**;
- custom-table schema/row truth to **Surface 7 / Data Source**;
- rendering, Policy, Data Source and Asset behavior to their shared canonical contracts.

The recovered candidate preserves those boundaries. It does not introduce a private SQL engine, raw `WP_Query` configuration language, arbitrary PHP callback execution, private relation store, Search index, or builder-private canonical data model.

`Visibility is presentation, not authorization` remains an explicit UX/runtime boundary.

## 4. Revalidated research result

The recovered research targets WordPress 7.1 and current official/native collection-rendering capabilities together with mature market patterns from JetEngine, Elementor Loop Grid, Bricks Query Loop, WP Grid Builder, FacetWP and Meta Box Views.

The current revalidation found no repository or provider evidence requiring count inflation, semantic-owner reassignment or runtime implementation to make the planning candidate internally coherent.

The candidate remains exactly **150 unique Listings Bank records** across five shards:

- `listings.json` — 28;
- `listings--templates-rendering.json` — 30;
- `listings--layout-interactions.json` — 32;
- `listings--security-performance.json` — 30;
- `listings--portability-diagnostics.json` — 30.

Certificate state remains:

- native audit: `NATIVE_AUDITED` — 14 dispositions / 0 unresolved;
- market audit: `MARKET_AUDITED` — 4 primary providers + 2 specialists / 32 family mappings / 8 specialist Bank references / 4 explicit cross-owner or rejected-unsafe dispositions / 0 unresolved;
- Bank Review: `BANK_REVIEWED` candidate — 150 records / 0 unresolved;
- Listings semantic aliases/effective derivations requiring the shared semantic registry: **0**.

## 5. Runtime dependency boundary

This reconciliation does **not** open Listings runtime.

Canonical dependency order remains:

`Fields → Relations → Query → Admin Columns → Dynamic Listings → Status`

Listings runtime therefore remains dependency-blocked until, at minimum:

1. applicable Relations contracts are stable for relation-backed behavior;
2. the authoritative Query runtime and published Query/result/parameter/pagination contracts exist;
3. the accepted Admin Columns sequence gate is satisfied;
4. exact-head Renderer, Data Source, Policy, Asset and Component Blueprint contracts are re-read at implementation entry.

Planning `BANK_REVIEWED` is not `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, release readiness or deployment authority.

## 6. Worker-local validation

This branch adds:

`tests/Smoke/options-bank-listings-review-contract.php`

The standalone validator enforces the current reviewed candidate without mutating shared truth. It checks:

- exact five-shard roster and 150 unique surface-local records;
- shard lifecycle/coverage consistency;
- rejected/deferred/WPE-exceed policy consistency;
- native certificate identity, counts and Query ownership boundaries;
- market provider/family/specialist/disposition counts;
- Bank Review identity and zero unresolved state;
- zero Surface 9 semantic-registry rows;
- preservation of the runtime dependency block and no-authorization-by-visibility UX rule.

The validator intentionally does **not** require current shared `options-bank-progress.json` to already contain the Listings promotion, because that shared file is Supervisor/Integrator-owned and must remain valid both before and after serialized integration.

## 7. Integration Requirements — Supervisor/Integrator only

After this bounded worker PR is accepted, the serialized integration lane should re-read the then-current exact main and:

1. promote Surface 9 in `config/product/options-bank-progress.json` from `UNSEEDED / 0` to `BANK_REVIEWED / 150`;
2. recompute all global lifecycle counters from exact repository truth rather than copying stale arithmetic;
3. from the claim-time shared snapshot only, the expected delta would be seeded surfaces `8 → 9`, native-audited `7 → 8`, market-audited `7 → 8`, Bank-reviewed `7 → 8`, and total Bank records `1,571 → 1,721` — **these values are conditional and must be recomputed if other integrations land first**;
4. update any root/shared progress projection required by governance from the same accepted exact head;
5. keep the shared semantic-relations registry unchanged unless new evidence introduces a genuine Listings alias/effective derivation;
6. if this standalone Listings validator is added to a shared Composer/global smoke aggregator, make that registration in the integrator-owned shared-write step;
7. do not interpret the Bank promotion as permission to start Listings runtime.

## 8. Risks and next safe action

The recovered historical research/planning documents intentionally retain their original stale source/base provenance. This reconciliation record is the current-main bridge and prevents that provenance from being mistaken for the claim base.

Any later movement of `main` must be absorbed non-destructively before final merge certification. Exact-head CI on an older head is not evidence for a synchronized replacement head.

Next safe action for this worker: certify the bounded candidate on the exact current-main-synchronized head, open a PR, and hand serialized shared lifecycle promotion to the Supervisor/Integrator.
