# ADR-0007 — License Expiry Runtime Behavior

Status: **Accepted product architecture**  
Date: 2026-08-27

## Context

Initial product assumptions proposed showing an upgrade/permission message on frontend output that was created using a paid module after the 30-day trial/subscription ended.

For a WordPress application platform, premium definitions may power public pages, forms, dashboards, security rules and automation. Replacing already-built production output with an upsell/error because billing state changed can:

- break customer-facing sites;
- damage SEO/conversion/user workflows;
- create support emergencies from transient licensing outages;
- incentivize unsafe “never update license client” behavior;
- make security protections disappear for commercial rather than security reasons.

## Decision

License entitlement controls **creation/editing and premium operational access**, not ownership of user data and not, by default, already-deployed safe public rendering.

### On confirmed Pro expiry

Preserve:
- all definitions/configuration;
- content/form entries/custom tables/relations;
- module history/logs/backups;
- configuration export access.

Admin behavior:
- premium definitions become read-only where entitlement is required;
- exact affected module is shown;
- contextual Renew/Upgrade action;
- no global unrelated admin hijacking.

Runtime behavior:
- existing safe public render output continues where technically possible;
- security protections retain their last-known safe configuration;
- mutating automations/jobs that could keep changing data or consuming premium external resources may enter `Paused — license required`;
- remote Pro services may stop according to their service contract, with a clear degraded state.

### License server unavailable

`verification-unavailable` is not the same as `expired`. Use a cached signed/validated entitlement/grace strategy so a temporary WPEssential service outage cannot break customer sites.

## Consequences

### Positive
- safer production behavior;
- lower risk from billing/API outages;
- user retains ownership/control of local data;
- renewal value comes from continued editing, advanced operation, updates/support and services rather than holding frontend output hostage.

### Commercial cost
A lapsed customer may continue displaying already-created local output. This is accepted in exchange for site reliability/trust. New edits/features/updates/support remain premium value.

## Exception process

Any module that cannot safely continue runtime after expiry must document the reason in its module spec and may require a specific ADR. Security-sensitive exceptions receive security review.

A future decision to replace existing public content with an upgrade message must supersede this ADR and include product, legal, support and operational impact analysis.
