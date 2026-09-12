# Roles & Capabilities Canonical Read Seam Contract V1

Status: **PLANNED / RUNTIME NOT IMPLEMENTED**  
Surface: **30 — Roles & Capabilities (`roles`)**  
Issue: **#615**  
Planning anchor: `3b20b3bd2a99e44b0cc1c28d6bb5f969a61b2bb9`

## Purpose

Surface 30 is the canonical owner of role/capability truth. This contract defines the minimum read-only service boundary needed by peer surfaces, especially Taxonomy Surface 2, without duplicating WordPress role-store interpretation inside those peers.

Issue #615 defines the seam only. Production runtime implementation requires a later exact-main issue/queue authorization.

## Ownership rule

Peer surfaces must not create their own role/capability engine or directly inspect `wp_roles`, `get_role()`, raw role option storage, or equivalent private WordPress state to answer role-impact questions.

Surface 30 owns:

- role identity and site-context catalog;
- primitive capability entries preserving explicit `true`, explicit `false`, and absence;
- capability provenance/classification where safely knowable;
- multi-role and individual-user-override semantics;
- target-role policy;
- multisite/Super Admin semantics;
- bounded effective-capability explanation.

Peer surfaces remain owners of their own definitions and only consume the read seam.

## Required context

Every query carries an explicit context object containing at least:

- `site_id` for site-scoped role truth;
- `network_scope` / network identifier when network semantics are requested;
- caller/purpose metadata sufficient for canonical Policy to authorize the read;
- optional `user_id` only for a specifically requested effective-explain diagnostic;
- optional object/context identifiers only when a meta-capability explanation requires them.

There is no implicit “current site is probably enough” fallback for peer-facing queries whose answer materially depends on scope.

## Role catalog result

A catalog row must support the following fields without claiming information that cannot be known safely:

- stable role key;
- display name;
- scope/site context;
- provenance classification: core / managed / provider / unknown where safely known;
- primitive capability entries as a map that preserves Boolean `true` and `false` while allowing the caller to distinguish keys that are absent;
- optional read-only diagnostics such as default-role state, editable-by-current-actor state and assigned-user count;
- multisite/Super Admin caveats relevant to the requested context.

Unknown provenance remains `unknown`; it must not be inferred from display names or naming conventions.

## Capability-impact query

Conceptual operation:

`capabilityImpact( primitive_capability, context )`

Minimum result:

- `capability`;
- `scope_context`;
- `explicit_allow_roles[]` — role keys with an explicit `true` entry;
- `explicit_deny_roles[]` — role keys with an explicit `false` entry;
- `absent_roles[]` — catalog roles with no explicit entry;
- `unknown_or_external_roles[]` when role truth is degraded or incomplete;
- `caveats[]`;
- `meta_capability` / `context_required` indicator when the requested key is not safely answerable as a static primitive-role impact.

The query must not collapse Deny and Absent. It also must not infer final authorization for a user solely from this role matrix because individual user overrides, meta-cap mappings, dynamic filters and Super Admin may change an effective result.

## Effective-explain query

Conceptual operation:

`effectiveCapabilityExplain( capability, user_context, object_context? )`

When separately authorized and implemented, the result keeps contributors independently attributable:

- assigned roles;
- role capability entries by role;
- individual user capability override entries;
- primitive/meta classification;
- mapped primitive requirements for the provided object/context;
- selected site/network context;
- Super Admin effect where applicable;
- dynamic/filter caveats;
- diagnostic final result only when enough context is present.

This operation is diagnostic only. It never substitutes for the actual canonical authorization check at the mutation boundary.

## Privacy and data minimization

The peer read seam is bounded. It must not expose a general user directory merely because a role/capability query is made.

- Catalog/impact queries operate on role definitions without unrelated user profile data.
- Assigned-user counts may be returned as aggregate diagnostics.
- User-specific effective explanation requires an explicitly supplied permitted user context.
- No password, token, session or unrelated profile data is returned.

## Security constraints

The seam is **read-only**.

It must not expose methods that:

- add/remove roles;
- grant/deny/remove capability entries;
- assign roles to users;
- mutate individual user capabilities;
- delete or migrate role keys;
- grant/revoke Super Admin;
- impersonate or switch users;
- execute administrator rescue/recovery.

All reads remain behind canonical Ability/Policy checks. Read access is not an authorization bypass.

## Multisite and Super Admin

- Site role catalogs are queried in explicit site context.
- Network context is represented separately from site roles.
- Super Admin is a special multisite authority signal, never fabricated as a normal site role or role capability entry.
- A peer consumer that only needs static role impact receives role-entry truth plus caveats; it does not receive a misleading synthetic Super Admin role.

## Failure / degraded states

The service reports degraded or unavailable state rather than inventing truth when:

- the requested site cannot be resolved;
- role APIs are unavailable;
- provider role metadata cannot be classified;
- a capability is contextual/meta and required object context is missing;
- Policy forbids the requested diagnostic scope.

Peer consumers must render these states as unknown/degraded, not as “no roles affected.”

## Taxonomy Surface 2 consumption contract

Taxonomy role-impact preview may resume only after a later accepted Surface 30 runtime implementation satisfies this seam.

Taxonomy should consume only:

- the scoped role catalog as necessary;
- `capabilityImpact()` for configured taxonomy capability keys;
- caveats/degraded state.

Taxonomy must not:

- grant/revoke capabilities;
- own role assignments;
- infer membership truth;
- directly scan WordPress role storage;
- create a second effective-capability engine.

## Runtime implementation acceptance gate

A future Surface 30 read-only runtime issue must prove at minimum:

- exact `true` / `false` / absent preservation;
- multi-role and user-override separation;
- site/network scope correctness;
- Super Admin special semantics;
- meta-cap context/degraded behavior;
- Policy enforcement;
- peer-consumer tests demonstrating Taxonomy can use the seam without direct WordPress role-store access;
- unit, integration, WordPress runtime, security, compatibility and applicable performance evidence.

Green planning/schema validation alone is not runtime certification.

## Certification statement

This contract establishes the dependency-safe API boundary only. It does **not** implement the service, unblock Taxonomy #474 by itself, or promote `RUNTIME_CERTIFIED` / `PRODUCT_PARITY_CERTIFIED`.
