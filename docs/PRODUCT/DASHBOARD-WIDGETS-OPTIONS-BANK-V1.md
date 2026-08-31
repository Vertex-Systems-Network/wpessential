# Dashboard Widgets — Master Options Bank V1

Status: **surface-local candidate / shared lifecycle promotion pending integrator**
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

This branch adds **123 classified discovery records** in four surface-owned shards:

| Shard | Records | Boundary |
| --- | ---: | --- |
| `dashboard-widgets.json` | 33 | definition, native registration/inventory, dashboard targets, presentation and visible states |
| `dashboard-widgets--content-data-actions.json` | 46 | widget types, typed data references, remote-content safety, refresh/cache and Ability-backed actions |
| `dashboard-widgets--preferences-multisite-portability.json` | 30 | presentation visibility, user preferences, role/network presets, multisite, lifecycle and portability |
| `dashboard-widgets--wpe-exceed.json` | 14 | future exceed/deferred diagnostics and reliability ideas |

All records are classified; no record is `UNREVIEWED`. Native `widget_id`/`widget_name` semantics are canonicalized onto `widget.key`/`widget.title` instead of being duplicated as authored controls.

## Native audit candidate

`config/product/options-bank-audits/dashboard-widgets-native-wordpress.json` contains **24 explicit dispositions**:

- 14 `BANK_RECORD`;
- 4 `PROVIDER_MAPPING`;
- 6 `SYSTEM_RUNTIME`;
- 0 unresolved.

It covers `wp_add_dashboard_widget()`, site/network dashboard setup, removal/hide, Screen Options/hidden state, per-user reorder/collapse, four dashboard contexts and core configure-control runtime behavior.

The surface-local validator `tests/Smoke/dashboard-widgets-native-audit-contract.php` now exists. Exact-head Architecture Guards confirm that it is syntactically valid. It is intentionally not registered in shared `composer.json` by this module worker, so formal `NATIVE_AUDITED` promotion remains pending integrator registration and exact-head execution.

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

The surface-local validator `tests/Smoke/dashboard-widgets-market-audit-contract.php` now exists and is syntax-clean on exact-head CI. The provider-neutral arbitrary-PHP rejection is normalized to provider `ecosystem`, matching the shared market-audit convention.

The audit remains `MARKET_AUDIT_IN_PROGRESS` until shared test registration/lifecycle promotion is integrated and certified.

Market-only mechanics are not copied blindly. Raw Script/PHP widgets are rejected for Surface 10; remote transport, Query/Listings, authorization, global admin theme and browser scripts stay with their canonical owners.

## Current exact-head CI evidence

Exact branch head at this update: `4aff0c0ae00c59447fe010ce111115bec8c9d694`.

Architecture, engineering contracts, PHP syntax (including both new Dashboard validators), PHPCS, PHPStan, PHPUnit, JS/CSS lint, TypeScript and admin build pass. Full smoke stops at the valid shared progress guard because canonical progress still declares Surface 10 as `UNSEEDED / 0` while these shards contain 123 records.

The guard is not weakened or bypassed.

## Bank Review readiness

Formal Bank Review is **not yet certifiable**. The current review contract requires upstream native and market audits to be formally `NATIVE_AUDITED` / `MARKET_AUDITED` and canonical progress to agree with the Bank record count.

Local review evidence is otherwise prepared for the next integrator cycle:

- 123 reviewed records;
- 0 `UNREVIEWED`;
- native unresolved: 0;
- market unresolved: 0;
- obvious native aliases already collapsed onto canonical controls;
- unsafe/foreign behaviors explicitly rejected or routed to canonical owners.

Do not create a `BANK_REVIEWED` certificate until the upstream lifecycle gates are actually green.

## Lifecycle truth

Latest shared truth still says Surface 10 is `UNSEEDED / 0`. This branch prepares a candidate seed plus native/market audit evidence and validators but does not edit integrator-owned `config/product/options-bank-progress.json` or shared `composer.json`.

No `NATIVE_AUDITED`, `MARKET_AUDITED`, `BANK_REVIEWED`, UX-contract, implementation-contract or runtime-parity claim is made.

## Next gate

Integrator sequence:

1. reconcile Surface 10 to the verified 123-record seed in shared progress and derived truth;
2. register and execute the prepared Dashboard native validator, then promote only if green;
3. register and execute the prepared Dashboard market validator, then promote only if green;
4. run formal semantic/ownership Bank Review;
5. only after `BANK_REVIEWED`, derive UX projection and the downstream implementation contract.
