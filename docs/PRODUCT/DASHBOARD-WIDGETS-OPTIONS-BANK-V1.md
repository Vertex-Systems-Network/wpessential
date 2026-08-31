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

The canonical owner is Dashboard Widgets Manager: WordPress Dashboard widget inventory, presets and custom widget definitions. It does not own generic placement or authorization.

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

Its status intentionally remains `NATIVE_AUDIT_IN_PROGRESS`: Dashboard Widgets does not yet have an executable surface audit contract in shared smoke wiring.

## Market audit candidate

`config/product/options-bank-audits/dashboard-widgets-market-ecosystem.json` is schema-valid against the latest generic market-audit schema and maps:

- 3 primary providers: Ultimate Dashboard, WP Adminify, White Label CMS;
- 2 specialists: Dashboard Widgets Suite, Dashboard Welcome for Elementor;
- 8 required capability families;
- 4 explicit extra dispositions;
- 0 unresolved items.

Its status intentionally remains `MARKET_AUDIT_IN_PROGRESS` until the shared Dashboard Widgets market-audit gate and lifecycle promotion exist.

Market-only mechanics are not copied blindly. Raw Script/PHP widgets are rejected for Surface 10; remote transport, Query/Listings, authorization, global admin theme and browser scripts stay with their canonical owners.

## Lifecycle truth

Latest shared truth still says Surface 10 is `UNSEEDED / 0`. This branch prepares a candidate seed plus native/market audit evidence but does not edit integrator-owned `config/product/options-bank-progress.json`.

No `NATIVE_AUDITED`, `MARKET_AUDITED`, `BANK_REVIEWED`, UX-contract, implementation-contract or runtime-parity claim is made.

## Next gate

After integration of shared progress/test wiring and exact-head certification:

1. promote the merged seed truth only if global Bank contracts pass;
2. certify the native audit;
3. certify the market audit;
4. run semantic/ownership Bank Review;
5. only then derive UX projection and the downstream implementation contract.
