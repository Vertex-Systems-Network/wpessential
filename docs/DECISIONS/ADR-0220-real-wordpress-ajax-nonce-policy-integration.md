# ADR-0220 — Real WordPress AJAX, Nonce and Policy Integration

Status: **ACCEPTED**  
Date: **2026-08-29**  
Scope: WP121 Platform Foundation / WordPress AJAX / nonce / Policy + Ability execution

## Context

ADR-0216 requires one canonical typed AJAX front door and centralized nonce operations while preserving the rule that a nonce is CSRF protection, not authorization. The platform already had logical AJAX, nonce, WordPress capability and Ability/Policy foundations, but real WordPress-core end-to-end evidence remained pending.

## Decision

### Canonical AJAX front door

`WordPressAjaxGateway` remains the only WPEssential owner of `wp_ajax_*` and `wp_ajax_nopriv_*` hook registration for the canonical action `WPE_AJAX_ACTION = wpessential_dispatch`.

Feature code must register typed allowlisted routes with `AjaxRouteRegistry`; request input cannot choose arbitrary classes or methods.

### Nonce boundary

`NonceManager` remains the shared nonce owner for operation-scoped actions. Real WordPress `wp_create_nonce()` / `wp_verify_nonce()` are used through `NativeWordPressNonceEnvironment`.

Nonce validation remains separate from authentication, capability, Policy, ownership and site/network authorization.

### Policy/Ability-backed AJAX handler

`AbilityAjaxHandler` is added as the canonical bridge for AJAX operations that execute WPEssential Abilities.

It resolves the trusted current WordPress execution context, binds AJAX execution to `ExecutionChannel::Ui`, asks the existing `AbilityRegistry` / `PolicyEngine` to authorize the named Ability, and executes that same Ability when allowed.

The AJAX layer does not create a second authorization engine.

A denied Ability raises a typed `AjaxAuthorizationException`; `AjaxDispatcher` maps it to a stable HTTP 403 `policy_denied` response with a safe reason code. Other unexpected handler failures remain generic `handler_failure` responses.

### Real WordPress fixture

CI downloads a pinned WordPress 7.1 core fixture and boots it against the ephemeral MySQL 8.4 service. The fixture installs a real WordPress site/user model and exercises native WordPress APIs rather than mocked substitutes.

The integration verifies:

- canonical authenticated and nopriv AJAX hooks are registered through `add_action`;
- only explicitly registered request types route;
- unknown type fails closed;
- missing/invalid nonce fails before Ability execution;
- nonce is bound to WPE operation + route scope;
- a real administrator current user passes `manage_options` Policy and executes the Ability;
- Ability context reflects the actual WordPress current user and current site;
- AJAX Ability channel is `ui`;
- a real low-privilege WordPress subscriber with a valid nonce is denied by canonical Policy/capability checks;
- Policy denial is distinct from generic handler failure;
- unauthenticated request is rejected before nonce/handler processing.

## Evidence

Initial hosted run:

- GitHub Actions `33266156181` / run #151 — **FAILED** only at the new real WordPress integration step.
- Root cause: the generated test `wp-config.php` omitted WordPress's canonical `require_once ABSPATH . 'wp-settings.php';`, so core bootstrap was incomplete and `WP_CONTENT_DIR` was undefined.
- No WPE authorization behavior was waived or weakened.

Corrected fixture commit:

`fdee1aaffe026745283ce03fb63a14af7a7862ba`

Corrected hosted run:

- GitHub Actions `33266232577` / run #153 — **SUCCESS**.

Job-level PASS:

- MySQL 8.4 service;
- pinned WordPress 7.1 fixture download/bootstrap;
- Composer metadata;
- canonical architecture validator;
- engineering validator including ADR-0219 release/security invariants;
- PHP 8.2 syntax;
- existing 9/9 smoke suites;
- compiled-registration MySQL integration;
- Definition/Audit MySQL integration;
- **real WordPress AJAX nonce Policy integration**.

## Boundaries / non-certifications

This evidence certifies the current single-site WordPress 7.1 AJAX/nonce/current-user/Policy integration fixture. It does not yet certify:

- Multisite AJAX switching/network-admin combinations;
- every future business Ability/route;
- rate limiting, upload/streaming or large-payload profiles;
- browser JavaScript client behavior;
- production deployment;
- Action Scheduler coexistence/backend behavior;
- durable Job persistence.

No live production site, customer data, production database migration, provider side effect or WordPress.org release was performed.

## Consequences

Business AJAX mutations should compose the shared nonce service and canonical Ability/Policy path instead of inventing feature-local authorization/mutation engines.

Future AJAX route additions must remain allowlisted, typed, server-authorized and evidence-backed.
