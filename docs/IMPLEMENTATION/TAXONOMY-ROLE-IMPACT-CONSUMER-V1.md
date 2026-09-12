# Taxonomy Role-Impact Consumer V1

Issue: #621  
Parent runtime program: #474  
Surface: **2 / Taxonomy Builder**  
Canonical dependency: **30 / Roles & Capabilities**  
Dependency evidence: Issue #618 / merged PR #620  
Authorization anchor: `main @ 3a939cb76d8596128154fd2c103ddff75dd740ce`

## Decision

Taxonomy now consumes the canonical Surface 30 read-only role/capability seam for its reviewed capability role-impact diagnostics. Surface 2 does not inspect WordPress role storage directly and does not implement a second role/capability engine.

This is a bounded diagnostic consumer. It does **not** promote Taxonomy or Roles & Capabilities to `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED`.

## Canonical data flow

For the effective Taxonomy capability map:

- `manage_terms`;
- `edit_terms`;
- `delete_terms`;
- `assign_terms`;

Surface 2 resolves the authored capability key or the existing native default, then delegates the capability-impact read to `RolesReadService` owned by Surface 30.

The consumer preserves Surface 30 truth without flattening it:

- roles with explicit allow `true`;
- roles with explicit deny `false`;
- roles where the capability entry is absent;
- degraded/unavailable state;
- contextual/meta-capability classification and context-required caveats;
- site/network and Super Admin caveats supplied by Surface 30.

Repeated Taxonomy operations that use the same capability key are deduplicated within one role-impact projection.

## Taxonomy read surfaces

The bounded projection is exposed through existing Surface 2 read ownership:

1. Definition list/get/save/status/import responses expose `read_model.role_impact` beside runtime health and dependency usage.
2. `wpessential/taxonomy/validate` exposes `diagnostics.role_impact` for the candidate effective capability map.
3. The existing current-user lockout warning remains separate. Role-entry impact is not treated as final per-user authorization.

If the canonical Surface 30 service or execution context is unavailable, Taxonomy returns an explicit `unavailable` role-impact diagnostic and does not fall back to `wp_roles()`, `get_role()`, role options, or another private WordPress role scan.

## Safety and ownership boundaries

This slice performs no:

- role create/clone/rename/delete product mutation;
- role capability grant/deny/remove mutation;
- user role or individual-capability mutation;
- Super Admin grant/revoke;
- impersonation or user switching;
- effective-user authorization simulation;
- taxonomy-key migration execution;
- deployment or release.

The real-WordPress integration creates a temporary probe role only as test-fixture setup, snapshots the role registry after fixture setup, proves Taxonomy role-impact reads leave that registry unchanged, and removes the probe role after the assertion.

## Evidence

Focused evidence includes:

- unit coverage for explicit allow/deny/absent preservation;
- unique-capability query deduplication;
- authored Taxonomy capability-map projection;
- contextual/meta-capability caveats;
- explicit unavailable behavior when Surface 30 truth is unavailable or missing;
- real WordPress production-composition validation and Definition read-model evidence;
- role-registry no-mutation proof;
- WP 6.9 / 7.1 × PHP 8.2 / 8.5 compatibility workflow evidence.

## Remaining #474 truth

This slice closes only the previously blocked capability role-impact consumer identified by Issue #611 / merged PR #612 and later authorized by Issue #621.

It does not authorize or close the separately gated taxonomy-key migration execution boundary. Read-only migration planning remains the promoted state; execution requires its own explicit safety authorization and migration/recovery evidence.

Any broader naming/help/reset, lifecycle-history/diff, object-type/dependency presentation, or other residual claimed against #474 must be revalidated against exact current `main` before new implementation is opened. A later exact-main certification audit remains required before any full Taxonomy runtime certification claim.
