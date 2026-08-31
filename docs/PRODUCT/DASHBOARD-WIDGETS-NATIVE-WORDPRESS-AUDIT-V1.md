# Dashboard Widgets — Native WordPress Audit V1

Status: **candidate native audit / promotion pending executable shared gate**
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

## Why status is not `NATIVE_AUDITED`

The repository's JSON schema is generic enough for Surface 10, but current executable shared smoke wiring is not: the generic-named `tests/Smoke/options-bank-native-audit-contract.php` is hard-coded to Fields, while Relations has a dedicated Surface 4 contract.

Under the multi-agent contract, this worker does not edit shared `composer.json`/global smoke wiring. Therefore this file stays `NATIVE_AUDIT_IN_PROGRESS` even with zero unresolved dispositions.

Promotion requires an integrator-applied Dashboard Widgets native audit gate and green CI on the exact integrated head.
