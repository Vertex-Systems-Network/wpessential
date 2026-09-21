# Dashboard Widgets — Read-Only Runtime Foundation V1

Issue: **#1120**
Surface: **10 / Dashboard Widgets**
Exact implementation base: `main@780d0301ba63b132e6b944aec8dc15e1afdf397a`

## Scope

This tranche implements the smallest reversible Dashboard Widgets runtime owner foundation authorized by the exact-main transition audit in Issue #1119 / merged PR #1121.

Added runtime ownership is deliberately limited to:

- canonical Surface 10 Definition owner/type validation;
- deterministic projection of canonical Definition identity, lifecycle, revision, dependencies and payload;
- read-only `get(id)` and deterministic `catalog()` operations through the existing shared `DefinitionRepositoryInterface`;
- focused unit evidence for owned reads, deterministic ordering, missing definitions and fail-closed owner/type mismatch.

## Canonical Definition boundary

The Surface 10 Definition type for this bounded foundation is `dashboard-widget`, owned by canonical surface id `10`.

The new read service does not create, save, publish, archive or otherwise mutate Definitions. It consumes only the shared Definition Repository and therefore creates no private persistence mechanism.

Foreign-owner or foreign-type Definitions fail closed before their payload is exposed.

## Ownership preserved

This foundation does not absorb peer semantics:

- Query execution remains Surface 6 Query;
- Listing rendering remains Surface 9 Dynamic Listings;
- analytics/journey semantics remain Surface 33 Analytics;
- role/capability grants remain Surface 30 Roles & Capabilities;
- Forms runtime remains Surface 17 Forms & Workflows;
- business actions remain owned by their canonical Ability owner;
- shared authorization, Multisite and diagnostic infrastructure remain Platform-owned.

## Explicit non-goals

Not included:

- `DashboardWidgetsModule` or Ability registration;
- WordPress `wp_dashboard_setup` / `wp_add_dashboard_widget` integration;
- admin UI or AJAX/REST routes;
- Definition mutation;
- per-user reorder/hide/dismiss/reset preferences;
- refresh/cache/retry runtime;
- Query/Listings/Analytics/Form/provider execution;
- remote fetch or credentials;
- arbitrary PHP/callback/class/script/SQL execution;
- shared Platform changes;
- `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release claims.

## Verification target

Merge requires focused unit/static verification plus all path-applicable exact-head repository gates and zero unresolved review threads.

A successful merge certifies only this bounded read-only owner foundation. Module/Ability exposure and WordPress dashboard integration remain later separately reviewed tranches.
