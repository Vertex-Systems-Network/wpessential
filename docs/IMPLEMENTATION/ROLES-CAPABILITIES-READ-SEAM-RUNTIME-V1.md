# Roles & Capabilities Canonical Read-Only Runtime Seam V1

Status: **BOUNDED READ-ONLY RUNTIME IMPLEMENTED / FULL SURFACE RUNTIME NOT CERTIFIED**  
Surface: **30 — Roles & Capabilities (`roles`)**  
Issue: **#618**  
Implementation branch: `agent/roles-read-seam-runtime-v1`

## Scope closed by this slice

This slice implements the dependency seam defined by `ROLES-CAPABILITIES-READ-SEAM-CONTRACT-V1.md` without opening any role, capability, user-role, Super Admin, impersonation, recovery, or migration mutation path.

The canonical Surface 30 runtime boundary now provides:

- a site-scoped role catalog through a Surface 30-owned WordPress adapter;
- exact preservation of explicit Boolean capability entries, including explicit `false` versus absent keys;
- a bounded capability registry with conservative `contextual_meta` versus `primitive_or_provider` classification and `unknown` provenance when ownership cannot be proved;
- a policy-gated `capabilityImpact()` read returning explicit allow roles, explicit deny roles, absent roles, scope context, state, and caveats;
- explicit `healthy`, `degraded`, and `unavailable` read states;
- explicit multisite/network context and a Super Admin special-authority signal without fabricating a Super Admin role;
- canonical Ability Registry / WordPress Ability exposure for catalog and capability-impact reads;
- a peer-consumable service registration at `module.roles.read-service`.

## Runtime ownership

Direct WordPress role-store interpretation is confined to `WordPressRoleRuntimeEnvironment` under Surface 30. Peer surfaces should consume `RolesReadService` rather than inspect `wp_roles()`, `get_role()`, or role option storage privately.

The service performs its own `PolicyEngine` authorization in addition to the Ability Registry authorization path. This prevents a peer from bypassing canonical policy merely by resolving the service from the shared registry.

## Capability semantics

The runtime intentionally does not flatten role entries into a single Boolean authorization result.

For each queried capability:

- explicit `true` entries are returned in `explicit_allow_roles`;
- explicit `false` entries are returned in `explicit_deny_roles`;
- roles without the key are returned in `absent_roles`;
- contextual/meta capability keys known to require object or user context are marked `context_required=true`;
- static role impact is explicitly described as diagnostic role-entry truth, not final user authorization.

Individual user capability overrides, dynamic `map_meta_cap` / `user_has_cap` filters, provider-specific dynamic semantics, and Super Admin authority are not silently folded into the role matrix.

## Multisite and degraded behavior

The environment resolves explicit site context and network identity. On multisite it may switch to the requested site only for the bounded read and always restores the previous blog context.

Failure modes are fail-visible:

- missing WordPress role APIs -> `unavailable`;
- unresolved site -> `degraded`;
- requested network/site mismatch -> `degraded`;
- unproven provenance -> `unknown`, never guessed from a role key or label.

Super Admin remains a network authority caveat/signal and is never emitted as a synthetic site role.

## Security and mutation boundary

No method in this V1 seam can:

- create, clone, rename, or delete roles;
- grant, deny, or remove capability entries;
- assign roles to users or change individual user capabilities;
- change the default role;
- migrate role keys;
- grant or revoke Super Admin;
- impersonate or switch users;
- execute administrator rescue/recovery.

The real-WordPress integration test snapshots the role registry after fixture setup, exercises the canonical Ability and direct-service read paths, and asserts the role definition snapshot is unchanged afterward.

## Evidence

Focused evidence added with this slice:

- unit tests for explicit allow/deny/absence preservation;
- unit test for contextual meta-capability caveats;
- unit tests for unavailable/degraded scope states;
- unit test proving direct service access is Policy-gated;
- real WordPress integration proving module/Ability registration, Surface 30 ownership, non-mutating descriptors, role truth preservation, no role-store mutation, unauthenticated denial, and bounded repeated reads;
- WordPress compatibility matrix execution across supported WP/PHP combinations plus MariaDB baselines.

Exact-head CI remains the merge gate; this document does not substitute for green workflow evidence.

## Remaining Surface 30 gaps

The following remain intentionally outside this bounded dependency seam and require separately accepted work before full Surface 30 runtime completion:

1. role create/clone/rename/delete lifecycle;
2. capability grant / explicit deny / remove-entry mutation workflows;
3. user multi-role assignment mutation and individual user-capability overrides;
4. user-specific effective capability explanation with mapped primitive requirements and object context;
5. default-role mutation;
6. revisions, compare/rollback, portability, and provider synchronization;
7. target-role protection and anti-lockout mutation enforcement;
8. Super Admin grant/revoke workflows;
9. administrator recovery/rescue execution;
10. role-key migration;
11. full admin UX/runtime implementation for Essential/Advanced/Expert editing flows;
12. full Surface 30 runtime and product-parity certification evidence.

## Taxonomy dependency result

After the implementation PR for Issue #618 is accepted and merged, Taxonomy Issue #474 can be reclassified from **dependency-blocked** to **dependency-ready** for a thin read-only role-impact consumer.

That later Taxonomy change must consume the canonical Surface 30 service. It must not add direct `wp_roles()` / `get_role()` inspection or a second role/capability engine.

This Surface 30 slice does not itself modify Taxonomy and does not close Issue #474.

## Certification statement

This is a bounded runtime dependency seam only. It does **not** promote all of Surface 30 to `RUNTIME_CERTIFIED` and does **not** promote `PRODUCT_PARITY_CERTIFIED`.
