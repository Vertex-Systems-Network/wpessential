# Dashboard Widgets — Post-README-Governance Reconciliation + Trusted Content Transition Audit V1

Surface: **10 / Dashboard Widgets**
Issue: **#1153**
Exact audited main: `b9e971653db0bbab177784c0b501c4e03269ea5d`

## Combined milestone purpose

This milestone combines the mandatory post-merge reconciliation for AI-Native README governance with the next exact-main Surface 10 runtime transition audit. It deliberately avoids a bookkeeping-only round trip.

## Terminal governance evidence reconciled

### Issue #1151 / PR #1152 — mandatory README progress reconciliation V1

Corrected exact head: `c668ed14369c7b9f9dd360a6239ed1085ce4d67a`

- Governance Gate `35654519471` — PASS
- Architecture Guards `35654519447` — PASS
- zero unresolved review threads
- zero commits behind main at merge gate
- merged as `b9e971653db0bbab177784c0b501c4e03269ea5d`
- Issue #1151 closed completed

Historical failed attempt `ff8258cd6a0ae092da81e05bd9434dd7dacf37c0` / Architecture `35654102687` failed only because compact state used invalid `active_pr: "PENDING"`. The corrected head bound `active_pr` to `"#1152"`; no runtime/product source was changed by the correction.

## Exact-main Surface 10 state

Merged bounded runtime prerequisites now include:

- read-only Definition/runtime foundation;
- Module/Ability exposure;
- central Pro activation;
- typed fail-closed native registration descriptor/compiler;
- typed fail-closed P0 visibility descriptor/compiler;
- current authenticated WordPress user/site server visibility evaluator;
- canonical `CapabilityCheckerInterface` reuse plus module-local role-membership adapter.

The module still has an empty `boot()`; current Surface 10 source does not register `wp_dashboard_setup`, `wp_network_dashboard_setup` or call `wp_add_dashboard_widget()`.

## Content-trust audit

Accepted ADR-0051 runtime chain remains:

`Dashboard Widget Definition → Compiled Widget Descriptor → server visibility Policy → trusted content renderer → WordPress Dashboard adapter`

The repository now satisfies the registration-metadata and bounded P0 visibility portions of that chain, but **does not yet contain a trusted content-class/compiler or trusted renderer/provider execution boundary**.

Accepted security/product evidence additionally requires:

- provider identities are registered/allowlisted, never arbitrary executable input;
- arbitrary raw JavaScript and arbitrary PHP remain rejected;
- Query/Listings/Ledger/Forms/Platform execution stays with canonical owners;
- RSS/remote transport delegates to Connections/Safe HTTP;
- iframe/embed execution requires a trusted embed profile with origin/sandbox/CSP policy;
- widget placement never grants source-data or action authorization.

The reviewed Options Bank contains structured authored classes such as `rich_text`, `kpi`, `chart`, `quick_links`, `announcement`, `support_onboarding` and `icon_link`. It also contains provider/remote/embed classes that require additional trust seams before execution.

## Explicit verdicts

- **READY_FOR_BOUNDED_TRUSTED_CONTENT_CLASS_CONTRACT_V1**
- **BLOCKED_FOR_DIRECT_WORDPRESS_DASHBOARD_REGISTRATION**
- **BLOCKED_FOR_WP_ADD_DASHBOARD_WIDGET**
- **BLOCKED_FOR_TRUSTED_CONTENT_RENDERING**
- **BLOCKED_FOR_PROVIDER_SOURCE_DATA_EXECUTION**
- **BLOCKED_FOR_MUTATION_PREFERENCES**
- **BLOCKED_FOR_FULL_PARITY_RUNTIME_PRODUCT_CERTIFICATION**

## Why direct WordPress registration remains blocked

Calling native Dashboard registration now would create an adapter callback obligation before the trusted content side of the accepted chain exists. The current registration descriptor proves native ID/title/context/priority/target metadata and the evaluator proves bounded audience visibility; neither provides a safe render result.

The next safe step must therefore remain non-executing and fail closed.

## Authorized next tranche

Issue **#1154 — Dashboard Widgets: bounded trusted content-class descriptor/compiler foundation V1**

Deterministic branch after this audit merges:

`agent/dashboard-widgets-trusted-content-class-contract-v1`

V1 may only:

- add a typed content-class descriptor/compiler;
- require canonical `widget.type`;
- admit the reviewed non-executable structured types `rich_text`, `kpi`, `chart`, `quick_links`, `announcement`, `support_onboarding`, `icon_link`;
- make registration compilation fail closed on missing/malformed/unknown/not-yet-trusted types;
- register the compiler as a module-local service;
- add focused unit coverage.

V1 must deny/defer iframe, RSS/video transport, cross-surface provider classes, shortcode/block/custom-provider execution and any authored executable callback/script/PHP value.

## Scope boundary

This audit does not authorize:

- WordPress Dashboard hooks or registration;
- content body rendering or HTML output;
- renderer/provider resolution or execution;
- source-data fetching;
- Membership/Entitlement/Condition execution;
- iframe/remote execution;
- Definition or user-preference mutation;
- shared Platform runtime changes;
- certification, deploy or release.

## Promotion condition

Issue #1154 remains dependency-gated until the #1153 audit PR merges from exact current main with terminal green Governance/Architecture, zero unresolved review threads and zero commits behind main.
