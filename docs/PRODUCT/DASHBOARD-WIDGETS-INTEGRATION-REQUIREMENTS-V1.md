# Dashboard Widgets — Integration Requirements V1

Status: **module-worker handoff**
Snapshot: **2026-09-01**
Surface: **10 — `dashboard-widgets`**
Original base: `eb61c6bfe9d89af949d70e89ba685597d58e2663`
Latest synchronized main: `88e5e90b273ddc61b4a0e2e36249c541576fc8fc`
Worker branch: `surface/dashboard-widgets-options-bank-v1`

These are shared-file requirements discovered by the Surface 10 worker. They are intentionally not applied by the module-local worker.

## Upstream changes absorbed during stale-branch sync

The branch has absorbed both upstream lifecycle changes that landed after the original base:

- `8ed1c038...` generalized `config/product/options-bank-market-audit.schema.json` to canonical Surface IDs 1–56 while certifying Relations market audit;
- `88e5e90b...` certified Relations `BANK_REVIEWED` and updated shared progress/review wiring.

Dashboard Widgets does not overwrite or fork either shared change.

## IR-DW-001 — global Options Bank progress

Integrator-owned file:
`config/product/options-bank-progress.json`

After reconciling concurrent branches, recompute from repository truth:

- Surface 10 candidate Bank count remains **123**;
- local shard count is now five because the two explicit `DEFERRED` records were separated from the canonical WPE-exceed shard during Bank Review preparation;
- global `seeded_surfaces`, `total_bank_records` and derived README/STATUS values must come from the final integrated tree;
- do not copy stale global totals from this worker PR.

The current full-smoke failure is expected because this module worker correctly leaves shared progress at `UNSEEDED / 0` while Surface 10 shards contain 123 records.

## IR-DW-002 — register the prepared Surface 10 lifecycle contracts

Module-local validators now exist:

- `tests/Smoke/dashboard-widgets-native-audit-contract.php`;
- `tests/Smoke/dashboard-widgets-market-audit-contract.php`;
- `tests/Smoke/dashboard-widgets-review-contract.php`.

Integrator-owned shared/global areas still required:

- `composer.json` smoke registration/aggregation;
- applicable CI workflow/path coverage if current aggregation does not execute the new commands;
- shared lifecycle/progress truth.

Required outcome:

1. register all three Dashboard Widgets validators in the normal exact-head smoke/architecture gates;
2. retain existing Fields and Relations certifications unchanged;
3. execute native and market contracts while their artifacts remain `*_IN_PROGRESS` and promote each only after its exact-head gate passes;
4. execute the Bank Review contract with `REVIEW_BLOCKED` while prerequisites remain incomplete;
5. change the review to `BANK_REVIEWED` only when native=`NATIVE_AUDITED`, market=`MARKET_AUDITED`, review unresolved=0 and canonical progress agrees at 123 records.

The review validator deliberately prevents a false `BANK_REVIEWED` promotion.

## IR-DW-003 — semantic registry only if later review proves a relationship

Shared file:
`config/product/options-bank-semantic-relations.json`

The obvious native aliases were already resolved locally: WordPress `widget_id` maps to canonical `widget.key`, and `widget_name` maps to canonical `widget.title`; no duplicate authored native controls remain.

Current blocked review expects zero Surface 10 alias/effective-derivation entries. Do not add semantic registry noise unless later evidence proves a real relationship.

## IR-DW-004 — lifecycle sequencing

Do not skip stages:

`BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED → UX projection → implementation contract`

The branch contains zero-unresolved native/market research plus a **`REVIEW_BLOCKED`** Bank Review certificate. That certificate is evidence of a closed local review pass with unresolved integration gates; it is not a Bank Review completion claim.

## IR-DW-005 — concurrent shared integration coordination

Open PR #32 (`columns` / Surface 8) currently has the same shared integration requirements: canonical progress reconciliation plus registration of surface-local native/market/review contracts.

A designated integrator must avoid two writers racing on `options-bank-progress.json`, `composer.json`, README/STATUS or CI aggregation. Safe choices are:

- serialize integration after each worker head is exact-head stable; or
- create one designated integration branch that reconciles both exact worker heads and recomputes shared truth once.

Do not independently patch Surface 10 shared counts from stale main while Surface 8 integration is concurrently active.

## Cross-surface no-bypass decisions retained

- Query definitions: Surface 6.
- Listing composition: Surface 9.
- Cron/scheduling engine: Surface 18 / Job Service.
- remote HTTP/RSS/iframe transport policy: Surface 23.
- capability definition / authorization: Surface 30 + shared Policy.
- Platform diagnostics source: Surface 31.
- generic placement/personalization: Surface 38.
- global admin theme/branding: Surface 49.
- arbitrary browser script placement: Surface 50 Safe Script.

Surface 10 stores widget definitions, references, presets and presentation behavior; it does not fork those engines.
