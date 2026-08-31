# Dashboard Widgets — Master Options Bank V1

Status: **surface-local candidate / blocked Bank Review prepared / shared lifecycle promotion pending integrator**
Snapshot: **2026-09-01**
Canonical surface: **10 — `dashboard-widgets`**

## Purpose

Normalize the current Dashboard Widgets discovery space without restarting the already-merged Wave 2 atomic inventory or claiming runtime implementation. The existing product contracts remain authoritative:

- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`;
- `docs/PRODUCT/ATOMIC-OPTION-CONTRACTS-WAVE2-EXPERIENCE.md`;
- `docs/ARCHITECTURE/CANONICAL-56-SURFACE-OWNERSHIP-REGISTRY.md`;
- `docs/ARCHITECTURE/CANONICAL-56-SURFACE-DEPENDENCY-RELATION-MATRIX.md`.

The canonical owner is Dashboard Widgets Manager: WordPress Dashboard widget inventory, presets and custom widget definitions. It does not own generic placement, transport or authorization.

## Candidate Bank

This branch contains **123 classified discovery records** in five surface-owned shards:

| Shard | Records | Boundary |
| --- | ---: | --- |
| `dashboard-widgets.json` | 33 | definition, native registration/inventory, dashboard targets, presentation and visible states |
| `dashboard-widgets--content-data-actions.json` | 46 | widget types, typed data references, remote-content safety, refresh/cache and Ability-backed actions |
| `dashboard-widgets--preferences-multisite-portability.json` | 30 | presentation visibility, user preferences, role/network presets, multisite, lifecycle and portability |
| `dashboard-widgets--wpe-exceed.json` | 12 | canonical WPE-exceed diagnostics/reliability possibilities only |
| `dashboard-widgets--future-deferred.json` | 2 | explicitly deferred realtime/federation capabilities |

All records are classified; no record is `UNREVIEWED`. Native `widget_id`/`widget_name` semantics are canonicalized onto `widget.key`/`widget.title` instead of being duplicated as authored controls.

The two `DEFERRED` records were split from the WPE-exceed shard during Bank Review preparation so the repository can independently enforce canonical `WPE_EXCEED` and `DEFERRED` policy contracts. Total Bank count remains 123.

## Native audit candidate

`config/product/options-bank-audits/dashboard-widgets-native-wordpress.json` contains **24 explicit dispositions**:

- 14 `BANK_RECORD`;
- 4 `PROVIDER_MAPPING`;
- 6 `SYSTEM_RUNTIME`;
- 0 unresolved.

It covers `wp_add_dashboard_widget()`, site/network dashboard setup, removal/hide, Screen Options/hidden state, per-user reorder/collapse, four dashboard contexts and core configure-control runtime behavior.

The surface-local validator `tests/Smoke/dashboard-widgets-native-audit-contract.php` exists and is syntax-clean in exact-head repository CI. Formal `NATIVE_AUDITED` promotion remains pending integrator registration/execution and shared progress reconciliation.

## Market audit candidate

`config/product/options-bank-audits/dashboard-widgets-market-ecosystem.json` maps:

- 3 primary providers: Ultimate Dashboard, WP Adminify, White Label CMS;
- 2 specialists: Dashboard Widgets Suite, Dashboard Welcome for Elementor;
- 8 required capability families;
- 17 family mappings;
- 7 non-applicable family cells;
- 47 real Bank references;
- 4 explicit extra dispositions;
- 0 unresolved items.

The surface-local validator `tests/Smoke/dashboard-widgets-market-audit-contract.php` exists and is syntax-clean in exact-head repository CI. The provider-neutral arbitrary-PHP rejection uses provider `ecosystem`, matching the shared market-audit convention.

The audit remains `MARKET_AUDIT_IN_PROGRESS` until shared test registration/lifecycle promotion is integrated and certified.

## Bank Review candidate

A lifecycle-safe blocked certificate now exists at:

`config/product/options-bank-reviews/dashboard-widgets-bank-review-v1.json`

Decision: **`REVIEW_BLOCKED`**.

It records:

- 123 reviewed records;
- 0 semantic alias/effective-derivation entries currently required;
- native unresolved: 0;
- market unresolved: 0;
- 0 `UNREVIEWED` records;
- canonical rejected/deferred/WPE-exceed policy expectations;
- 12 pure WPE-exceed records in the WPE-exceed shard;
- 2 canonical deferred future records in a separate shard;
- 2 unresolved integration gates: formal native and market certification/shared progress reconciliation.

The surface-local validator `tests/Smoke/dashboard-widgets-review-contract.php` accepts `REVIEW_BLOCKED` now and only allows a later `BANK_REVIEWED` state when native=`NATIVE_AUDITED`, market=`MARKET_AUDITED`, review unresolved=0 and canonical progress says `BANK_REVIEWED / 123`.

This is a blocked review artifact, not a completion claim.

## Ownership / safety resolution

Market parity is use-case parity, not copying unsafe mechanics.

- raw JavaScript/PHP widgets: rejected in Surface 10; typed renderer/Ability/Safe Script alternatives only;
- remote fetch/credentials/retries/signatures/SSRF policy: Surface 23 Connections/Safe HTTP;
- structured query semantics: Surface 6 Query;
- listing/render composition: Surface 9 Listings;
- scheduling engine: Surface 18 Cron / shared Job Service;
- authorization: shared Policy / Surface 30 capability definitions;
- platform diagnostics source: Surface 31 Platform;
- global admin branding/theme: Surface 49 Admin Theme;
- browser script placement: Surface 50 Safe Script.

## Exact-head evidence before blocked-review addition

Exact head `a788351c0b341158aa291b937f971583a416be2a` established:

- exact checkout/SHA verification PASS;
- architecture/engineering guards PASS;
- JS/style lint, TypeScript and admin build PASS;
- PHP syntax PASS, including Dashboard native/market validators;
- PHPCS, PHPStan and PHPUnit PASS;
- Master Options Bank contract PASS;
- MariaDB integration-only matrix paths PASS;
- full smoke failure isolated to shared progress truth: `dashboard-widgets` declares 0 records while local shards contain 123.

The blocked-review artifact, review validator and deferred-shard normalization are newer than that evidence and require their own exact-head CI before certification.

## Lifecycle truth

Canonical shared progress still says Surface 10 is `UNSEEDED / 0`. This branch prepares the seed, native audit, market audit and blocked Bank Review artifacts but does not edit integrator-owned progress, Composer aggregation or global status files.

No `NATIVE_AUDITED`, `MARKET_AUDITED`, `BANK_REVIEWED`, UX-contract, implementation-contract or runtime-parity claim is made.

## Next gate

Designated integrator sequence:

1. reconcile Surface 10 to the verified 123-record seed in shared progress and derived truth;
2. register/execute the Dashboard native validator and promote only if green;
3. register/execute the Dashboard market validator and promote only if green;
4. register/execute the Dashboard Bank Review validator; resolve the blocked certificate to `BANK_REVIEWED` only when all prerequisites agree;
5. only after `BANK_REVIEWED`, derive UX projection and the downstream implementation contract.
