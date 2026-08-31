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

- `8ed1c038...` generalized `config/product/options-bank-market-audit.schema.json` from Fields-specific constants to canonical Surface IDs 1–56 and generic surface keys while certifying Relations market audit;
- `88e5e90b...` certified Relations `BANK_REVIEWED` and updated shared progress/review wiring.

Dashboard Widgets does not overwrite or fork either shared change.

## IR-DW-001 — global Options Bank progress

Integrator-owned file:
`config/product/options-bank-progress.json`

After reconciling concurrent branches, recompute from repository truth:

- Surface 10 should move from `UNSEEDED / 0` to the verified candidate stage/count only after exact-head contracts pass;
- candidate local Bank count: **123**;
- global `seeded_surfaces` and `total_bank_records` must be derived from the final integrated tree;
- synchronize README/STATUS only from verified integrated truth.

The current full-smoke failure is expected because this module worker correctly leaves shared progress at `UNSEEDED / 0` while the surface-local shards contain 123 records.

## IR-DW-002 — register the prepared native/market audit contracts

Module-local validators now exist:

- `tests/Smoke/dashboard-widgets-native-audit-contract.php`;
- `tests/Smoke/dashboard-widgets-market-audit-contract.php`.

They validate the candidate state without inventing lifecycle completion and remain valid when the corresponding audit status is promoted from `*_IN_PROGRESS` to `*_AUDITED`.

Integrator-owned shared/global areas still required:

- `composer.json` smoke registration/aggregation;
- applicable CI workflow/path coverage if current aggregation does not execute the new commands;
- shared lifecycle/progress truth.

Required outcome:

- register both Dashboard Widgets validators in the normal exact-head smoke/architecture gates;
- retain existing Fields and Relations certifications unchanged;
- run the native validator against Surface 10 canonical ownership, 123 real Bank records, Developer.WordPress.org evidence, exact disposition counters and zero unresolved;
- run the market validator against the generic market schema, eight required families, exact primary/specialist rosters, real Bank references, four reviewed extra dispositions and zero unresolved;
- only then promote the corresponding shared lifecycle state.

The market candidate was also normalized to the existing shared contract convention: the provider-neutral arbitrary-PHP rejection uses provider `ecosystem`, not a synthetic unregistered provider ID.

## IR-DW-003 — semantic registry only if later review proves a relationship

Shared file:
`config/product/options-bank-semantic-relations.json`

The obvious native aliases were already resolved locally: WordPress `widget_id` maps to canonical `widget.key`, and `widget_name` maps to canonical `widget.title`; no duplicate authored native controls remain.

Do not add semantic registry noise unless formal Bank Review proves another same-surface alias or effective derivation.

## IR-DW-004 — lifecycle sequencing after integration

Do not skip stages:

`BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED → UX projection → implementation contract`

The branch contains native and market audit **candidates** with zero unresolved dispositions plus executable module-local validators, but neither audit may be promoted until shared progress/registration is integrated and the applicable exact-head gates are green.

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
