# Dashboard Widgets — Server Visibility Evaluator Transition Audit V1

Surface: **10 / Dashboard Widgets**
Issue: **#1147**
Exact-main baseline: `3c88e9e2909518a95515da812b06edb0ae71bfa6`

## Combined milestone purpose

This milestone deliberately combines the mandatory #1146 post-merge shared-truth reconciliation with the next exact-main runtime audit so development does not spend another full round-trip on bookkeeping alone.

## Terminal evidence reconciled

### PR #1145 — visibility policy transition audit

- Exact head: `0789b4ff76a5506f24fbd89e79b7d7d82571686e`
- Governance Gate `35641583748` — PASS
- Architecture Guards `35641583781` — PASS
- zero unresolved review threads
- zero commits behind main
- merged as `731a7dcb1242ef056f5980129bc3cb598ee828b2`

### PR #1146 — visibility policy contract/compiler foundation

- Exact head: `79c15c0db85ebecb4fda3753534152f98c00d5be`
- Governance Gate `35647855379` — PASS
- Architecture Guards `35647855338` — PASS
- PHP Quality Toolchain `35647855344` — PASS
- Platform Compatibility Matrix `35647855395` — PASS
- Distributable Package `35647855396` — PASS
- exactly seven authorized files
- zero unresolved review threads
- zero commits behind main
- merged as `3c88e9e2909518a95515da812b06edb0ae71bfa6`

## Exact-main runtime audit

The runtime now has a typed `DashboardWidgetVisibilityDescriptor` and fail-closed compiler for P0 roles, capabilities and user IDs.

Existing reusable security seams:
- `ExecutionContext` and `Principal` bind actor identity and site/network context;
- `CapabilityCheckerInterface` is the canonical capability-check contract;
- production `WordPressCapabilityChecker` fails closed unless the authenticated principal matches the current WordPress user and site;
- the capability checker is already registered under `WordPressAuthorizationServices::CAPABILITY_CHECKER`.

Missing seam:
- there is no canonical current-user role-membership provider suitable for Surface 10 consumption.

The Roles module exposes a site role catalog, not current-user role membership. Reusing that catalog as user membership would be incorrect.

## V1 evaluation semantics

The authored visibility contract currently has no operator field. V1 therefore freezes one deterministic conservative rule:

- **AND across populated dimensions**: every populated roles/capabilities/users dimension must match;
- **ANY within a populated dimension**: one listed role/capability/user is sufficient for that dimension;
- empty dimensions are unconstrained;
- unauthenticated/non-user actors deny;
- current WordPress user/site mismatch denies;
- visibility is presentation filtering only and never authorizes actions or source-data access.

## Verdict

**READY_FOR_BOUNDED_SERVER_VISIBILITY_EVALUATOR_V1**

**BLOCKED_FOR_DIRECT_WORDPRESS_DASHBOARD_REGISTRATION**

**BLOCKED_FOR_TRUSTED_CONTENT_RENDERING**

## Authorized next tranche

Issue #1148 may add:
- typed visibility decision;
- module-local role-membership provider interface;
- fail-closed WordPress current-user role-membership adapter;
- server visibility evaluator using the canonical capability checker;
- module-local evaluator service registration;
- focused unit tests.

## Explicitly not authorized

- WordPress Dashboard hooks or `wp_add_dashboard_widget`;
- Membership/Entitlement/Condition execution;
- renderer/provider/data-source execution;
- content rendering;
- Definition/user-preference mutation;
- shared Platform contract changes;
- authorization bypass;
- full-parity certification/deploy/release.
