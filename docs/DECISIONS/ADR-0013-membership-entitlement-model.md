# ADR-0013 — Membership, Billing, Roles and Entitlements Are Separate Domains

Status: **Accepted product architecture**  
Date: 2026-08-27

## Context

WPEssential now includes a Membership System. A common WordPress implementation shortcut is to represent membership directly as a WordPress role, or to treat an external billing subscription as the membership record itself.

That shortcut is incompatible with the target product because WPEssential must support:
- multiple simultaneous memberships;
- exclusive and multi-plan groups;
- complimentary/manual access;
- trials/grace/fixed-duration access;
- external billing providers;
- plan changes;
- seats/teams;
- non-billing entitlements and overrides;
- access to non-content resources such as dashboards, forms, downloads and Abilities;
- historical/reconciliation data;
- optional role synchronization.

It also creates dangerous failure modes: billing integration downtime or role edits could accidentally remove or grant access without a normalized local state/history.

## Decision

WPEssential will model these as separate concepts:

1. **WordPress User** — identity/account.
2. **Role / Capability** — WordPress authorization primitive.
3. **Membership Plan** — product/access configuration.
4. **Membership Enrollment** — a user's instance/lifecycle in a plan.
5. **Billing Subscription / Purchase Source** — external provider contract/reference.
6. **Entitlement** — normalized benefit/access grant.
7. **Access Rule / Policy** — maps resource/action/context to required/denied entitlements and other conditions.

The Membership System owns Plans and Enrollments. External commerce/billing adapters own their provider interactions and map verified lifecycle events into Enrollment state. The Entitlement Engine derives or stores normalized grants consumed by Policy.

A WordPress role may be synchronized as an optional side effect but is not the source of truth for membership.

## Billing boundary

WPEssential Membership core will not process or store payment-card credentials.

Commerce/billing providers remain authoritative for:
- card/payment method handling;
- charging;
- taxes;
- provider invoice/payment records;
- provider-native proration calculations;
- refunds where provider owns transaction.

WPEssential stores only the minimum external references/status metadata required for membership synchronization, diagnostics and reconciliation.

## Entitlement examples

Examples of normalized grants may include:
- `content.view`
- `download.access`
- `dashboard.route.view`
- `form.submit`
- `listing.view`
- `discount.eligible`
- `support.priority`
- `ability.execute:<ability>`
- registered third-party entitlement keys.

Exact naming/schema requires the Entitlement Engine implementation ADR; these are conceptual examples.

## Access evaluation

Consumers must not inspect WooCommerce/SureCart/etc. directly to decide access. They ask the Policy/Entitlement layer using a typed subject/resource/action/context request.

Access evaluation should be explainable so diagnostics can identify:
- matching enrollment(s);
- entitlement(s);
- matching allow/deny rule;
- precedence;
- time/drip condition;
- final result/reason.

## Multiple memberships

A user may have more than one active Enrollment. Plan Group rules determine whether memberships within a group are exclusive or can coexist.

Entitlement union/conflict resolution must be deterministic. Exact allow/deny precedence is a separate blocking ADR/spike and must be resolved before Membership runtime implementation.

## Role synchronization

Role sync:
- disabled by default;
- is owned by Membership mapping configuration using Role Manager as capability/role catalog;
- can add/remove/replace roles only under explicit semantics;
- must include anti-lockout checks;
- must preserve history/audit;
- does not imply reverse synchronization.

Directly adding a WordPress role does not create a Membership Enrollment unless a separate explicit workflow is configured.

## External billing events

Provider adapters must implement:
- signature/authentication verification;
- idempotency;
- replay protection;
- out-of-order event handling/reconciliation;
- raw external status/reference preservation;
- normalized Enrollment-state mapping;
- safe retry behavior.

The local membership state must not blindly trust event arrival order.

## License/module failure safety

WPEssential's own Pro entitlement is a different domain from a site's member entitlements.

If WPEssential Pro expires or Membership management becomes unavailable:
- local membership data remains;
- safe last-known access enforcement continues;
- previously protected content/downloads must not become public solely because WPEssential's commercial license expired;
- management can become read-only according to commercial policy;
- reconciliation/mutating tasks may pause only when doing so does not expose resources.

## Consequences

### Positive
- supports multiple billing/grant sources;
- supports multiple memberships and non-billing grants;
- isolates WordPress role quirks from membership data;
- makes access reusable across all WPE modules;
- enables explainability/audit/reconciliation;
- makes future provider migrations possible.

### Costs
- more tables/domain objects than role-only solutions;
- requires Entitlement cache/invalidation design;
- requires synchronization/reconciliation jobs;
- requires explicit conflict/precedence rules;
- requires careful migration tooling.

These costs are accepted because the simplified alternatives cannot satisfy the product scope safely.

## Rejected alternatives

### Membership stored only as user role
Rejected: cannot model multiple memberships, billing lifecycle, trial/grace/history/seat semantics robustly and risks role conflicts.

### External subscription is the membership
Rejected: couples the product to a billing provider, breaks manual/free grants, and makes outages/migrations dangerous.

### Each protected module checks membership plugin directly
Rejected: duplicates access logic and prevents a coherent platform Policy layer.

### WPEssential native card/payment processor
Rejected for core Membership: unnecessary PCI/payment complexity and duplicates mature commerce/payment platforms.

## Follow-up blockers

Before Membership implementation, create/accept decisions or evidence for:
- Entitlement schema/storage/derivation;
- allow/deny precedence;
- entitlement cache invalidation/revocation latency;
- Membership runtime tables/indexes;
- protected-file delivery;
- initial billing adapters;
- privacy/retention;
- role-sync conflict rules.

## Review triggers

Review this ADR only if product scope changes to require WPEssential to become a first-party billing/payment processor, or if WordPress introduces a native membership/entitlement primitive that materially changes these boundaries.
