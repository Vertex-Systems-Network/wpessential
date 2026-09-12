# Roles & Capabilities UX Contract V1

Status: **REVIEWED PLANNING CONTRACT — RUNTIME UNPROMOTED**  
Surface: **30 — Roles & Capabilities (`roles`)**  
Issue: **#615**  
Exact-main planning anchor: `3b20b3bd2a99e44b0cc1c28d6bb5f969a61b2bb9`

## Purpose

This contract converts the reviewed Surface 30 Bank and Atomic Option Contract into a deterministic Essential / Advanced / Expert information architecture. It defines what administrators must be able to inspect and eventually manage without implying that production mutation runtime exists yet.

The contract preserves WordPress semantics that are easy to flatten incorrectly: role capability entries may be explicit `true`, explicit `false`, or absent; users may have multiple roles; individual user capabilities are a separate layer; meta capabilities require context; and multisite Super Admin authority is not an ordinary site role.

## Global UX invariants

1. **Capability state is three-state, never binary-only.** The matrix must visibly distinguish **Allow**, **Deny**, and **Absent**. Removing an entry means returning to Absent, not writing an explicit Deny.
2. **Roles and individual user overrides are separate.** A user's effective result may combine multiple roles and individual capability entries. User overrides must never be silently written into role definitions.
3. **Primitive and meta capabilities are labeled.** Meta-capability diagnostics request the required object/user context instead of claiming a static role-matrix answer.
4. **Super Admin is special network authority.** It is shown in multisite context and never represented as a normal role checkbox.
5. **Authorization is capability/policy-based.** The UI must not recommend literal role-name checks as an authorization primitive.
6. **High-impact actions are preview-first.** Delete, remap, bulk assignment, import and future recovery workflows must show affected users, target-role policy, default-role implications and recovery risk before mutation.
7. **No impersonation.** Permission simulation is read-only; it must not change the current session, log in as another user or create a user-switching path.
8. **No hidden direct WordPress role-store access by peer surfaces.** Taxonomy and other modules must consume the canonical Surface 30 read seam once its runtime is separately implemented.
9. **Planning is not runtime.** Controls whose mutations are not yet authorized render as planned/disabled or preview-only until a later exact-main runtime gate promotes them.

## Essential tier

### Role catalog

The default Surface 30 view is a searchable role catalog. Each row exposes:

- role key/slug;
- display name;
- managed / external / missing classification where safely known;
- site/network scope;
- default-role marker;
- editable-by-current-actor state;
- assigned-user count;
- capability summary with Allow / Deny / Absent counts when available;
- risk badges for administrator-equivalent authority or unresolved provider capabilities.

The catalog must not imply that provenance is known when WordPress does not expose a trustworthy owner. Unknown provenance is an explicit state.

### Role detail summary

Opening a role first shows identity, scope, provenance, default/editable state and a compact capability summary. Compare, clone, rename-display and delete-planning entry points are visible only when policy permits the current actor to inspect that action.

### Essential safety language

- Role key changes are not presented as a casual rename. Any future role-key migration is a separate destructive workflow.
- Deletion is never a one-click destructive action.
- A role named `administrator` is not treated as the sole definition of administrative authority.

## Advanced tier

### Three-state capability matrix

The matrix is the canonical authored capability view for a role.

Each capability row contains:

- capability key;
- human label where known;
- group/source/provenance;
- primitive vs meta classification;
- current state: **Allow**, **Deny**, or **Absent**;
- deprecated/unknown/provider badge;
- dependency-impact indicator where available;
- contextual warning when a meta capability cannot be evaluated statically.

The control semantics are exact:

- **Allow** = explicit role entry `true`;
- **Deny** = explicit role entry `false`;
- **Absent** = no role entry for that capability.

Search, group filters and bulk selection must never mutate rows outside the user's visible/confirmed selection.

### Capability registry

Advanced users can inspect capability families from:

- WordPress core;
- CPT-generated capabilities;
- taxonomy-generated capabilities;
- WPE module capabilities;
- plugin/provider capabilities;
- unknown/orphan custom capabilities.

CPT and taxonomy definitions remain owned by their canonical surfaces. Surface 30 only consumes their capability keys as role/capability truth.

### User assignment and overrides

The assignment view distinguishes:

- all roles currently assigned to a user;
- add-role semantics;
- remove-one-role semantics;
- replace-role semantics, with explicit warning that replacement removes prior roles;
- individual user capability overrides in a separate section;
- affected-user counts before bulk operations.

No assignment mutation is executable under Issue #615; the contract defines the future guarded interaction only.

### Compare and diff

Two roles can be compared without mutation. The diff must separate:

- Allow on A / Allow on B;
- explicit Deny differences;
- Absent differences;
- unknown/provider capabilities;
- default/editable/scope differences.

A three-state diff is required; treating Deny and Absent as the same result is invalid.

### Portability

Import/export UX is diff-first. Import preview must show:

- roles to create/update;
- capability state changes;
- unknown/provider capabilities;
- environment-sensitive items;
- affected default-role/recovery concerns;
- items that cannot be safely applied.

Issue #615 does not authorize applying the import.

### Anti-lockout

Before any future high-impact workflow, Surface 30 must calculate and display whether at least one valid recovery path remains. The check is based on effective capability/policy risk, not merely the literal role name `administrator`.

## Expert tier

### Effective capability explain

The explain view shows separate contributors instead of one opaque Boolean:

1. selected site/network context;
2. user's assigned roles;
3. capability entry for each relevant role, preserving true/false/absent;
4. individual user capability overrides;
5. primitive vs meta-cap mapping;
6. object/context inputs required by meta capabilities;
7. Super Admin effect in multisite;
8. dynamic/filter caveats;
9. final diagnostic result when the available context is sufficient.

The result is diagnostic evidence, not a bypass around canonical runtime authorization checks.

### Permission simulator

The simulator accepts a bounded role or user context plus a capability and, where required, object/site/network context. It returns explanation only.

It must not:

- change the authenticated principal;
- create cookies/sessions for another user;
- impersonate another user;
- persist role/capability mutations;
- silently invent missing context.

### Multisite / Super Admin panel

The UI explicitly separates:

- site role registry;
- requested site ID;
- network context;
- Super Admin state;
- target-role policy in the selected context.

Changing site/network context is a read-context switch, not a role mutation.

### Canonical peer read-seam preview

Expert diagnostics expose the shape that peer modules may consume after runtime authorization:

- role catalog rows;
- explicit Allow roles;
- explicit Deny roles;
- Absent roles;
- scope context;
- diagnostic caveats;
- optional bounded effective-explain result.

The UI explicitly states that peer consumers must not query `wp_roles`, `get_role()` or private role stores as a substitute for this Surface 30 service.

## Guarded mutation presentation

Until a later exact-main runtime issue explicitly authorizes mutations, the following remain non-executable planning controls:

- role create/clone/rename/delete;
- capability grant/deny/remove-entry;
- individual/bulk user role assignment;
- individual user capability mutation;
- role import apply;
- restore/recovery apply;
- destructive role-key migration.

Preview/read behavior may be implemented later under a narrower read-only runtime issue without authorizing the mutations above.

## Accessibility and interaction requirements

- The three capability states must not rely on color alone.
- Every state control has an accessible name including capability key and state.
- Search/filter results expose total and visible counts.
- Destructive/high-impact previews retain keyboard focus and announce validation errors.
- Tables/matrices must support keyboard navigation and meaningful row/column headers.
- Expert caveats and risk messages are programmatically associated with the affected control/result.

## Acceptance criteria

This UX contract is complete when:

- every reviewed Atomic Option has a deterministic tier and interaction role;
- Allow / Deny / Absent semantics are explicit;
- multi-role users and individual overrides are not conflated;
- meta capability context and Super Admin semantics are preserved;
- anti-lockout and target-role policy are mandatory for future mutation workflows;
- simulator/read seam are read-only and non-impersonating;
- runtime mutation remains explicitly unpromoted.

This document does **not** promote `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`.
