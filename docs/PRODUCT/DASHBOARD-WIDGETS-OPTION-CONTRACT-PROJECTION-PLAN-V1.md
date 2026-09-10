# Dashboard Widgets — Atomic Option Contract Projection Plan V1

Surface: **10 / Dashboard Widgets**  
Issue: **#493**  
Mode: **planning/contract only**  
Runtime implementation: **NOT AUTHORIZED**

## Evidence baseline

- Options Bank decision: `BANK_REVIEWED`.
- Reviewed Bank record count: **123**.
- Bank review unresolved count: **0**.
- Semantic alias/effective-derivation entries required by the reviewed candidate: **0**.
- Atomic inventory lifecycle: `ATOMIC_INVENTORY_COMPLETE`.
- Dedicated `frameworks/Modules/DashboardWidgets` runtime owner is not present on the audited main baseline.

Canonical source shards:

1. `config/product/options-bank/dashboard-widgets.json`
2. `config/product/options-bank/dashboard-widgets--content-data-actions.json`
3. `config/product/options-bank/dashboard-widgets--preferences-multisite-portability.json`
4. `config/product/options-bank/dashboard-widgets--wpe-exceed.json`
5. `config/product/options-bank/dashboard-widgets--future-deferred.json`

Review/audit evidence:

- `config/product/options-bank-reviews/dashboard-widgets-bank-review-v1.json`
- `config/product/options-bank-audits/dashboard-widgets-native-wordpress.json`
- `config/product/options-bank-audits/dashboard-widgets-market-ecosystem.json`

## Projection rules

Every one of the 123 reviewed Bank records must map deterministically to exactly one of:

- an authored atomic option;
- a user-preference atomic option;
- an integration atomic option;
- effective/diagnostic state;
- runtime implementation evidence;
- an out-of-surface reference;
- a compatibility-provider mapping;
- deferred;
- rejected unsafe;
- WPE exceed.

No record may disappear through grouping. `missing=0` and `unclassified=0` are required before `OPTION_CONTRACT_COMPLETE` can be promoted.

## Contract families to materialize

The machine contract must cover at minimum:

- definition identity, title/key/status/site-network target and lifecycle;
- widget type and registered-provider boundary;
- capability/role/user/conditional visibility;
- admin dashboard context, priority/order, dimensions and density;
- content/data source, query/listing and diagnostic sources;
- refresh, TTL, stale state, retry and last-updated semantics;
- per-user reorder/hide/dismiss/reset preferences;
- site/network defaults and override precedence;
- Ability-backed actions, confirmation and audit requirements;
- import/export and environment-sensitive references;
- accessibility, performance, security and multisite evidence requirements.

## Safety / ownership rules

Dashboard Widgets may compose Query, Listings, Analytics/Ledger, Forms and Ability actions, but must not create private copies of those engines. UI hiding never replaces server authorization. Arbitrary PHP callbacks or executable provider text are prohibited; provider use must be registry/allowlist based.

## Exit for the next pass

1. Build `config/product/option-contracts/dashboard-widgets.json` against `option-contract.schema.json`.
2. Add deterministic source projection for all 123 records.
3. Verify no duplicate source IDs, `missing=0`, `unclassified=0`.
4. Reconcile the UX contract and runtime-gap matrix against the final atomic IDs.
5. Only then request Supervisor lifecycle promotion; runtime remains separately gated.
