# Dashboard Widgets — Visibility Policy Foundation Transition Audit V1

Surface: **10 / Dashboard Widgets**
Issue: **#1143**
Exact-main baseline: `cc5e0f37d4bc7a3ad791fd5ab951eae93b98d088`

## Verdict

- **BLOCKED_FOR_DIRECT_WORDPRESS_DASHBOARD_REGISTRATION**
- **BLOCKED_FOR_VISIBILITY_POLICY_EVALUATION_V1**
- **READY_FOR_VISIBILITY_POLICY_CONTRACT_FOUNDATION_V1**

## Exact-main runtime baseline

Merged runtime now provides:
- canonical `dashboard-widget` Definition ownership/type validation;
- read-only Definition service and Pro Module/Ability exposure;
- central Pro contribution behind existing fail-closed compatibility and entitlement activation gates;
- a typed, fail-closed native registration descriptor/compiler foundation.

The runtime still has no Dashboard Widget audience-policy descriptor/compiler, no server audience evaluator, no trusted content renderer and no WordPress Dashboard adapter.

## Security architecture

ADR-0051 and the Dashboard Widgets content-trust model require:

`Dashboard Widget Definition → Compiled Widget Descriptor → server visibility Policy → trusted content renderer → WordPress Dashboard adapter`

Skipping the policy boundary would allow privileged wp-admin content to be fetched/rendered before the server has established that the current actor may see it.

The existing shared `PolicyEngine` answers authentication + capability authorization requests. It is not a role/user/Membership/Condition audience evaluator for Dashboard Widgets.

Membership policy also remains a separate restriction layer and cannot replace the base WordPress/security boundary.

## Options Bank evidence

Current P0 audience fields are:
- `widget.visibility.roles`;
- `widget.visibility.capabilities`;
- `widget.visibility.users`.

The Options Bank explicitly states that visibility capability rules are presentation filters only; action authorization remains Policy-owned.

Conditional visibility and super-admin specialization are not required for this smallest prerequisite tranche.

## Smallest safe next tranche

Issue #1144 may create a **typed visibility policy contract/compiler foundation only**.

Required contract:
- owned Surface 10, schema-v1, Published Definitions only;
- optional bounded `visibility` object;
- `roles`: bounded unique machine-role keys;
- `capabilities`: bounded unique capability keys;
- `users`: bounded unique positive WordPress user IDs;
- unknown/malformed visibility metadata fails closed;
- registration compilation also fails closed when supplied visibility metadata is malformed;
- module-local visibility compiler service is exposed for later server evaluation;
- empty/default visibility metadata is deterministic and non-mutating.

## Explicitly not authorized

- role/capability/user visibility evaluation;
- Membership/Entitlement/Condition execution;
- `wp_dashboard_setup`, `wp_network_dashboard_setup`, `wp_add_dashboard_widget`;
- renderer/provider execution;
- source-data fetching;
- content rendering;
- Definition/user-preference mutation;
- shared Platform contract changes;
- entitlement/compatibility semantic changes;
- full-parity `RUNTIME_CERTIFIED`;
- deploy/release.

## Promotion rule

Only after this audit merges may Issue #1144 be claimed on deterministic branch `agent/dashboard-widgets-visibility-policy-contract-foundation-v1`.

A future exact-main audit must separately authorize server visibility evaluation after the contract/compiler foundation exists.
