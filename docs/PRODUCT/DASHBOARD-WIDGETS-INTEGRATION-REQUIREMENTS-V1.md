# Dashboard Widgets — Integration Requirements V1

Status: **module-worker handoff**
Snapshot: **2026-09-01**
Surface: **10 — `dashboard-widgets`**
Original base: `eb61c6bfe9d89af949d70e89ba685597d58e2663`
Latest synchronized main: `8ed1c0389ef314c79a60a6808d232ef625de7b25`
Worker branch: `surface/dashboard-widgets-options-bank-v1`

These are shared-file requirements discovered by the Surface 10 worker. They are intentionally not applied by the module-local worker.

## Upstream change absorbed during stale-branch sync

Latest `main` generalized `config/product/options-bank-market-audit.schema.json` from Fields-specific constants to canonical Surface IDs 1–56 and generic surface keys while certifying Relations market audit. The earlier Dashboard Widgets schema blocker is therefore resolved upstream and must not be reintroduced.

## IR-DW-001 — global Options Bank progress

Integrator-owned file:
`config/product/options-bank-progress.json`

After reconciling concurrent branches, recompute from repository truth:

- Surface 10 should move from `UNSEEDED / 0` to the verified candidate stage/count only after exact-head contracts pass;
- candidate local Bank count: **123**;
- global `seeded_surfaces` and `total_bank_records` must be derived from the final integrated tree;
- synchronize README/STATUS only from verified integrated truth.

The current exact-head smoke failure is expected because this module worker correctly leaves shared progress at `UNSEEDED / 0` while the surface-local shards contain 123 records.

## IR-DW-002 — executable native/market audit validation

Shared/global areas:
- `tests/Smoke/*`;
- `composer.json` smoke wiring;
- applicable CI workflow/path coverage.

Required outcome:
- add or generalize a Dashboard Widgets native-audit validator that checks Surface 10 canonical ownership, real Bank references, Developer.WordPress.org primary evidence, exact coverage counters and unresolved count;
- add a Dashboard Widgets market-audit validator against the now-generic shared market-audit schema, including eight required families, primary/specialist evidence, real Bank references, extra dispositions and zero-unresolved certification;
- retain existing Fields and Relations certifications unchanged;
- wire both contracts into the normal exact-head smoke/architecture gates.

## IR-DW-003 — semantic registry only if later review proves a relationship

Shared file:
`config/product/options-bank-semantic-relations.json`

The obvious native aliases were already resolved locally: WordPress `widget_id` maps to canonical `widget.key`, and `widget_name` maps to canonical `widget.title`; no duplicate authored native controls remain.

Do not add semantic registry noise unless formal Bank Review proves another same-surface alias or effective derivation.

## IR-DW-004 — lifecycle sequencing after integration

Do not skip stages:

`BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED → UX projection → implementation contract`

The branch contains native and market audit **candidates** with zero unresolved dispositions, but neither may be promoted until shared progress and executable exact-head gates are integrated and green.

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
