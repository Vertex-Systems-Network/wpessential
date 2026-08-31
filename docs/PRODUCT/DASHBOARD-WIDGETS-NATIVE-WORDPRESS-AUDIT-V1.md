# Dashboard Widgets — Native WordPress Audit V1

Status: **candidate native audit / shared registration and promotion pending integrator**
Snapshot: **2026-09-01**
Surface: **10 — `dashboard-widgets`**

## Scope

This audit classifies public/supported WordPress Dashboard and screen/meta-box primitives that materially affect Dashboard Widgets. It does not turn every internal helper into a user setting.

Primary sources:

- https://developer.wordpress.org/apis/dashboard-widgets/
- https://developer.wordpress.org/reference/functions/wp_add_dashboard_widget/
- https://developer.wordpress.org/reference/functions/wp_dashboard_setup/
- https://developer.wordpress.org/reference/functions/remove_meta_box/
- https://developer.wordpress.org/reference/functions/get_hidden_meta_boxes/
- https://developer.wordpress.org/reference/hooks/default_hidden_meta_boxes/
- https://developer.wordpress.org/reference/hooks/hidden_meta_boxes/
- https://developer.wordpress.org/reference/functions/do_meta_boxes/
- https://developer.wordpress.org/reference/functions/wp_dashboard/
- https://developer.wordpress.org/reference/files/wp-admin/includes/screen.php/

## Result

`config/product/options-bank-audits/dashboard-widgets-native-wordpress.json` contains **24 explicit dispositions**:

- 14 `BANK_RECORD`;
- 4 `PROVIDER_MAPPING`;
- 6 `SYSTEM_RUNTIME`;
- 0 unresolved.

Key native mappings include widget ID/name, context/priority, network-dashboard target, native removal/hide, default/user-hidden states, Screen Options, per-user ordering, collapse state and the current four dashboard contexts.

Executable callbacks are provider mappings, not arbitrary PHP configuration. Core nonce/control dispatch and setup hooks are runtime behavior, not duplicate authored options.

## Ownership/security decisions

- `edit_dashboard` and action capabilities are enforced through canonical Policy/WordPress capability contracts. Widget visibility is not authorization.
- Remote/RSS transport is not a Dashboard Widgets transport stack. It delegates to Connections/Safe HTTP and shared caching.
- Native user preference state is preserved as user state; WPE role/network presets resolve around it explicitly rather than silently overwriting user state.
- Default core widget IDs are runtime inventory facts; WPE does not pretend to own those definitions.

## Executable surface contract

The module-local validator now exists at:

`tests/Smoke/dashboard-widgets-native-audit-contract.php`

It verifies:

- canonical Surface 10 ownership;
- exactly 123 unique Dashboard Widgets Bank records;
- all 24 mandatory native disposition IDs;
- Developer.WordPress.org primary evidence;
- real Bank references for `BANK_RECORD` / `PROVIDER_MAPPING` dispositions;
- exact disposition/coverage counters;
- zero unresolved items.

The validator accepts both `NATIVE_AUDIT_IN_PROGRESS` and the later `NATIVE_AUDITED` state so the same test can guard the lifecycle transition without manufacturing it.

Exact-head Architecture Guards at `4aff0c0ae00c59447fe010ce111115bec8c9d694` include this file in the repository-wide PHP syntax scan and report no syntax errors.

## Why status is still not `NATIVE_AUDITED`

The remaining gap is shared registration/lifecycle truth, not absence of a surface validator. The normal smoke aggregation is controlled by integrator-owned `composer.json` / CI wiring, and canonical `options-bank-progress.json` still declares Surface 10 as `UNSEEDED / 0`.

Under the multi-agent contract, this worker does not edit those shared files without integrator assignment. Therefore the audit remains `NATIVE_AUDIT_IN_PROGRESS` even with zero unresolved dispositions and a prepared executable contract.

Promotion requires the integrator to register the Dashboard validator, reconcile the seed/progress truth, execute the applicable exact-head gate, and promote only after it passes.
