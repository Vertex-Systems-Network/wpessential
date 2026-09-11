# Taxonomy Capability Role-Impact Dependency Audit V1

Issue: #611  
Parent: #474  
Exact-main anchor: `733ea0df46ac543493912ff38fd827e32f1e1d92`  
Decision: **BLOCKED ON A CANONICAL SURFACE 30 READ SEAM**

## 1. Scope

This audit reconciles the final reviewed Taxonomy Builder UX residual: a read-only role-impact preview for the effective native taxonomy capability map (`manage_terms`, `edit_terms`, `delete_terms`, `assign_terms`).

The goal is not to build role management in Surface 2. The goal is to determine whether exact current `main` already exposes canonical Surface 30 role/capability truth that Taxonomy can consume without creating a duplicate role engine.

## 2. Exact machine truth

Surface 30 / Roles & Capabilities currently has:

- Master Options Bank: `UNSEEDED / 0` in `config/product/options-bank-progress.json`;
- atomic lifecycle: `ATOMIC_INVENTORY_COMPLETE` in `config/product/atomic-option-contract-progress.json`;
- no schema-valid Surface 30 option-contract promotion;
- no reviewed Surface 30 UX-contract/runtime certification promotion;
- no dedicated Roles & Capabilities runtime module under `frameworks/Modules/`.

These facts prohibit treating the broad Role Manager product surface as implemented merely because WordPress exposes role APIs.

## 3. Canonical ownership

`docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md` assigns role identity, capability matrix/provenance, assignments, comparison, CPT/tax helpers, network/Super Admin semantics, target-role policy and anti-lockout to **Surface 30 Roles & Capabilities**.

Taxonomy Surface 2 owns only the taxonomy capability-name map authored into the Taxonomy Definition. It does not own WordPress role definitions, user role assignments, explicit role capability grants/denials, individual user capability overrides, Super Admin semantics or capability provenance.

The Taxonomy UX contract is consistent with this split: it requires a **read-only role-impact preview** while explicitly stating that role grants remain owned by Roles & Capabilities.

## 4. Surface 30 product requirement

The exhaustive Roles/Capabilities specification already defines the required source semantics:

- capability registry sourced from WordPress core-known capabilities, registered roles, user individual caps, CPT/tax generated caps, WPE modules and extensions;
- per-role capability matrix values distinguish `allow true`, `explicit deny false` and `absent/inherit none`;
- user roles are multi-role;
- individual user overrides are separate from role-derived capabilities;
- effective capability explanation accounts for role entries, user overrides, meta-cap mapping and multisite Super Admin effect;
- permission simulation is diagnostic/read-only and is preferred over impersonation.

Therefore a Taxonomy-specific direct scan cannot be treated as the canonical implementation of these semantics.

## 5. Exact-main runtime seam audit

Exact current `main` does **not** provide a Surface 30 read service suitable for Taxonomy consumption:

- `frameworks/Modules/` contains no Roles/Capabilities business module;
- `frameworks/Platform/Auth/Principal.php` carries only `userId` and actor type; it is not a role/capability catalog;
- current Platform/Auth services provide authorization context/policy decisions, not a role matrix/provenance reader;
- repository code search finds no existing canonical `wp_roles` / `get_role()` based Surface 30 adapter that exposes role-impact data to peer surfaces.

The existing Taxonomy current-user lockout warning is not equivalent to role-impact preview. `current_user_can()` answers one actor's effective check; it does not provide role-by-role grants, explicit denies, absent/inherit state, user overrides or Super Admin context.

## 6. Why direct Taxonomy role inspection is rejected

Surface 2 must not add private calls to WordPress role storage merely to satisfy the UI visually. That would:

1. duplicate Surface 30 semantic ownership;
2. risk treating role names as effective authorization truth;
3. lose explicit-false versus absent capability state;
4. misrepresent multi-role users and individual overrides;
5. mishandle multisite/Super Admin semantics;
6. create a second capability registry/provenance model that later Surface 30 would have to replace;
7. make Taxonomy's preview diverge from other consumers such as CPT, Menu, Dashboard, Profile, Membership and Protector.

This is an ownership/architecture block, not a missing TypeScript panel.

## 7. Minimum prerequisite read seam

Before Taxonomy may implement role-impact preview, Surface 30 must expose a canonical, read-only service/contract with at least:

### 7.1 Role catalog

For the current site scope:

- stable role key;
- display name;
- source/provenance when safely known;
- explicit primitive capability entries preserving `true`, `false`, and absent distinction;
- multisite context marker where applicable.

### 7.2 Capability impact query

Given a bounded list of primitive capability keys, return deterministic read-only impact per capability:

- roles with explicit allow;
- roles with explicit deny;
- roles where the capability is absent/inherited;
- provenance/diagnostic caveats where effective access cannot be inferred from role entry alone.

The seam must state clearly that role-entry impact is not identical to final per-user `current_user_can()` because individual overrides, meta-cap mapping and Super Admin may change effective access.

### 7.3 Safety and ownership

The read seam must:

- perform no role/user mutation;
- not impersonate users;
- not expose secrets or unrelated user data;
- keep role grants/assignments owned by Surface 30;
- preserve site/network scope explicitly;
- be reusable by CPT/Taxonomy and later peer surfaces rather than Taxonomy-specific.

## 8. Dependency-safe Taxonomy implementation after prerequisite exists

Once that canonical Surface 30 reader is promoted, Surface 2 may add a thin read-only projection beside its existing capability editor:

- for each effective taxonomy capability value, query the Surface 30 reader;
- display role impact without editable grant controls;
- retain the existing current-admin lockout warning separately;
- link users to Roles & Capabilities for grant changes rather than writing grants from Taxonomy;
- do not infer final user authorization from role membership alone.

No taxonomy Definition schema change is required merely to display this preview because the authored capability map already belongs to Surface 2.

## 9. Gate decision

**Taxonomy role-impact preview is BLOCKED on a canonical Surface 30 read-only role/capability impact seam.**

Exact current `main` does not contain that seam, and Surface 30 remains `UNSEEDED / 0` with runtime unpromoted. Implementing WordPress role inspection privately inside Taxonomy would violate canonical ownership and create debt that the accepted Roles/Capabilities design explicitly avoids.

Issue #474 must therefore remain open (or explicitly dependency-blocked in coordination truth) until one of these happens:

1. Surface 30 is separately authorized to promote the minimal reusable read seam, after which a bounded Taxonomy consumer slice can be opened; or
2. repository governance explicitly revises the ownership contract with equivalent evidence.

This audit does not authorize Surface 30 runtime work, role/grant mutation, Taxonomy runtime certification, product-parity certification, deployment or release.
