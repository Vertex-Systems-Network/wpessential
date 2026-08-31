# Dashboard Widgets — Integration Requirements V1

Status: **module-worker handoff**
Snapshot: **2026-09-01**
Surface: **10 — `dashboard-widgets`**
Original base: `eb61c6bfe9d89af949d70e89ba685597d58e2663`
Worker branch: `surface/dashboard-widgets-options-bank-v1`

These are shared-file requirements discovered by the Surface 10 worker. They are intentionally not applied on this module-local branch.

## IR-DW-001 — global Options Bank progress

Integrator-owned file:
`config/product/options-bank-progress.json`

After reconciling concurrent branches, recompute from repository truth:

- Surface 10 should move from `UNSEEDED / 0` to the verified candidate stage/count only after exact-head contracts pass;
- candidate local count from this branch: **123**;
- global `seeded_surfaces` and `total_bank_records` must be derived after all active branches are reconciled;
- do not copy historical totals from this document.

Also synchronize README/STATUS only from the verified integrated truth.

## IR-DW-002 — market-audit schema must become surface-generic

Shared file:
`config/product/options-bank-market-audit.schema.json`

Current blocker:
the schema pins `surface.id` to `3` and `surface.key` to `fields`.

Required integration outcome:
- generalize the schema to canonical Surface IDs 1–56 and canonical surface keys, or introduce an explicitly approved per-surface schema pattern;
- preserve the existing Fields audit validity;
- do not weaken provider-family completeness, evidence, unresolved, or ownership checks.

## IR-DW-003 — executable native/market audit validation

Shared/global areas:
- `tests/Smoke/*`;
- `composer.json` smoke wiring;
- applicable CI workflow/path coverage.

Required outcome:
- add or generalize a Dashboard Widgets native audit validator that checks Surface 10 canonical ownership, real Bank record references, Developer.WordPress.org primary evidence, exact coverage counters and unresolved count;
- add a market-audit validator only after IR-DW-002 is solved;
- retain existing Fields and Relations certifications unchanged;
- wire the contracts into the normal exact-head smoke/architecture gates.

## IR-DW-004 — semantic registry only if review proves aliases

Shared file:
`config/product/options-bank-semantic-relations.json`

Current worker finding:
no mandatory same-surface alias/effective-derivation entry is required merely to seed Surface 10. Several items are instead explicit cross-surface references/boundaries.

At Bank Review, if a same-surface duplicate is proven, the integrator should add only the minimum machine relationship required. Do not create duplicate authored controls for native effective state.

## IR-DW-005 — lifecycle sequencing after integration

Do not skip stages:

`BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED → UX projection → implementation contract`

The existing Wave 2 atomic inventory is planning input, not evidence that these Master Options Bank gates are already certified.

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
