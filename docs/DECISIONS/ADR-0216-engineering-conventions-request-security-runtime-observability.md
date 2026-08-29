# ADR-0216 — Engineering Conventions, Request Security, Compiled Registration & Runtime Observability

Status: **Accepted**  
Date: **2026-08-29**  
Scope: **WPEssential-wide implementation contract / WP121 Platform Foundation**

## Context

The project owner established mandatory conventions for all WPEssential development covering public hooks, namespace/source layout, global symbols/constants, AJAX request routing, nonce reuse, dynamic WordPress registration performance and plugin-native debugging/flow inspection.

The implementation baseline initially used `src/` as the PSR-4 root and therefore conflicted with the newly explicit `frameworks/` requirement. WP121 also already contained several later foundation tranches that were not reflected accurately in the stale checkpoint/Linear description.

## Decision

WPEssential adopts the canonical contract documented in:

`docs/ARCHITECTURE/ENGINEERING-CONVENTIONS-AJAX-NONCE-COMPILED-REGISTRATION-OBSERVABILITY.md`

### Source and symbol contract

- namespace: `WPEssential`;
- canonical PSR-4 source root: `frameworks/`;
- retired parallel runtime root: `src/`;
- global functions: `wpessential_*`;
- global constants: `WPE_*`;
- canonical version/AJAX/nonce constants are defined in the plugin entrypoint.

### Hook contract

- custom filters: exact owner-prescribed prefix `wpesential/apply_*`;
- custom actions: `wpessential/hook_*`.

The asymmetric filter spelling is intentional public API and may not be silently corrected.

### Request security contract

WPE AJAX uses one canonical WordPress AJAX action. Every request must resolve an allowlisted logical `type`; arbitrary class/method dispatch from input is prohibited.

Nonce handling is centralized by operation (`apply`, `create`, `update`, `reset`, `delete`) and route/scope. Nonce verification remains CSRF protection only and cannot replace authentication, capability, Policy, ownership or tenancy checks.

### Dynamic registration contract

Post types, taxonomies, metaboxes and settings pages will use compile-on-write active manifests/generations. Ordinary runtime registration may consume the active compiled generation but must not scan and normalize the complete historical definition set on every request.

The current in-memory compiled store is explicitly a reference/test adapter. Production certification requires a persistent atomic generation store, recovery/last-known-good behavior, site/network isolation and executable 10K/100K performance evidence.

### Runtime observability contract

The canonical debug foundation is a bounded/redacted flow trace with correlation identity, class/component nodes, data/call edges, ordered checkpoints and a failure boundary from the last successful checkpoint to the failed component/operation.

Debug is off by default. Secrets/nonces/protected metadata must be redacted. Debug traces are diagnostic evidence, not authorization or business truth.

## Machine enforcement

WP121 adds:

- `tools/architecture/validate-engineering-contracts.php`;
- `tests/Smoke/engineering-contracts-smoke.php`;
- FAST/CI integration for the engineering guard;
- smoke paths migrated to `frameworks/`;
- the legacy `src/` tree removed.

The guard is expected to stop future drift in source root, namespace/global naming, exact hook prefixes, bootstrap constants, centralized AJAX ownership and required request/registration/observability foundations.

## Evidence status at acceptance

Static/source evidence:
- `frameworks/` is the canonical source tree;
- `src/` removed from the implementation branch;
- Composer PSR-4 points to `frameworks/`;
- engineering validator passes on hosted PHP 8.2;
- PHP syntax stage passes on hosted PHP 8.2;
- engineering-contract smoke passes in hosted diagnostic execution;
- all other existing smoke suites except one stale Platform Core assertion passed in that same diagnostic run;
- the stale assertion expected prose `not exposed` while the implementation correctly emits canonical denial reason `channel_not_exposed`; the test has been corrected without weakening authorization behavior.

Final hosted green status is recorded only after the post-fix workflow completes successfully. This ADR does not fabricate runtime certification while that run is pending/failing.

## Consequences

Positive:
- future modules share one stable integration vocabulary;
- AJAX and nonce logic is centralized and testable;
- large dynamic configuration sets can move toward bounded runtime cost;
- debugging can explain class/data flow and exact break boundaries without exposing secrets;
- machine guards prevent accidental regression to the old source tree or hook conventions.

Costs / remaining work:
- persistent compiled-registration backend still requires implementation/certification;
- real WordPress AJAX integration tests remain necessary;
- Runtime Observatory admin visualization, Policy and retention controls remain downstream WP121 work;
- module code must register typed routes and compiled definitions rather than bypass these platform owners.

## Safety / privilege boundary

This ADR is within active `GOV-OWNER-CONSENT-001` source-development scope. It does not authorize production deployment, destructive live-site mutation, live payment/provider side effects, irreversible external operations or release/merge gates requiring separate privileged approval.
