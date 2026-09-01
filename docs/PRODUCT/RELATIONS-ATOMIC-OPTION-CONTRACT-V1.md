# Relations Atomic Option Contract V1

Status: Surface 4 product-contract candidate  
Tracker: #95  
Authoritative base: `main @ 4f3628502569c1c413a3919d03c0d00066754b84`

## Purpose

This tranche converts the certified Surface 4 Relations Options Bank (`BANK_REVIEWED / 144`) into an implementation-ready machine contract without treating every reviewed capability, runtime invariant, provider mapping or downstream integration as an authored setting.

It does not implement Relations runtime behavior.

## Source reconciliation

The reviewed Relations Bank contains exactly 144 unique records:

- `relations.json` — 48;
- `relations--lifecycle-bulk.json` — 18;
- `relations--editor-permissions.json` — 18;
- `relations--query-api.json` — 25;
- `relations--portability-integrity.json` — 26;
- `relations--native-audit-v1.json` — 7;
- `relations--market-audit-v1.json` — 2.

The machine contract uses seven matching projection shards. The Surface-local smoke validator requires exact set equality between all 144 Bank record IDs and all 144 projection source IDs; count-only equivalence is insufficient.

## Canonical Atomic Options

The 144 source records normalize to 18 Relations-owned authored Atomic Options:

1. relation identity;
2. cardinality;
3. direction/composition;
4. from endpoint;
5. to endpoint;
6. connection bounds;
7. edge uniqueness;
8. edge ordering;
9. storage mode;
10. storage configuration;
11. pivot enabled;
12. pivot policy;
13. deletion policy;
14. editor policy;
15. permission references;
16. REST exposure policy;
17. multisite scope;
18. definition portability.

All authored options are server-authoritative and retain `relations` as the storage owner. This is a definition contract, not permission to use raw SQL or mutate WordPress core tables directly.

## Projection dispositions

Reviewed Bank records that are not local authored settings remain explicit evidence instead of becoming fake controls:

- WordPress taxonomy/post-parent adapter behavior and storage invariants → runtime implementation evidence;
- Relation Query predicates/sorting/pagination/AST and editor query sources → Query-owned references;
- pivot field schema → Fields-owned reference;
- list/display and Admin Columns integrations → Listings/Columns-owned references;
- frontend mutation/form operations → Forms/Workflows-owned references;
- edge/pivot package movement and ID remapping → Import/Export-owned references;
- GraphQL/provider/storage endpoint adapters → compatibility-provider mappings;
- future diagnostics, migration rollback, transactionality, performance budgets and observability → WPE-exceed evidence, not V1 authored options.

The contract therefore has `missing = 0` and `unclassified = 0` without inflating the authored setting count.

## Safety boundaries

- Relations owns persistent edge, cardinality, direction, endpoint, ordering, storage and pivot behavior.
- Fields owns pivot Field schema/control semantics.
- Query owns structured query/data-source semantics.
- Columns/Listings own their presentation surfaces.
- Roles/shared Policy remains the authorization primitive owner; Relations stores only capability/policy references and enforces relation-specific decisions server-side.
- Provider adapters do not synthesize local authored settings.
- Arbitrary executable PHP/JavaScript/raw SQL configuration and direct core-table modes are prohibited.
- Multisite scope is explicit and trusted runtime scope must be resolved server-side.

## Lifecycle promotion

This tranche advances only Surface 4 from `ATOMIC_INVENTORY_COMPLETE` to `OPTION_CONTRACT_COMPLETE`. The repository-wide option-contract-complete counter advances from 0 to 1. Wave aggregates and every other surface remain unchanged.

`OPTION_CONTRACT_COMPLETE` is a product-contract gate only. It does not claim UX completion, runtime implementation, runtime certification, product parity, release or deployment.

## Verification

The generic shared validator checks schema/lifecycle/source-projection rules. `tests/Smoke/option-contract-relations-contract.php` adds Surface 4-specific guarantees:

- exact 144 Bank IDs = exact 144 projection IDs;
- exactly seven canonical projection shards;
- exactly 18 canonical Relations Atomic Option IDs;
- every authored Atomic Option is server-authoritative and Relations-owned;
- peer-owner mappings remain peer-owned with no local Atomic IDs;
- Query sources are not normalized into Relations-authored query controls;
- provider mappings create no local authored settings;
- bounded storage adapter values exclude direct-core-table/raw-SQL modes.

Exact-head GitHub Actions are authoritative. Any source change after certification invalidates prior evidence.

## Next Gate B action

Only after this exact contract head is certified and merged may Relations runtime implementation begin. The next bounded runtime slice should establish the canonical relation Definition lifecycle plus cardinality/direction/endpoint validation before edge persistence or downstream Query integration.
