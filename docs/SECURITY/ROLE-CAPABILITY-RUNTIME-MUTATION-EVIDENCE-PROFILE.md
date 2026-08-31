# WPEssential — Role & Capability Runtime Mutation, Anti-Lockout & Evidence Profile

Status: **Phase 0 paper security/runtime profile / no role or capability mutation authorized**  
Date: 2026-08-28  
Related: Role & Capability Anti-Lockout Contract, User Profile ADR-0096, Multisite ADR-0069, Audit ADR-0081, ADR-0014.

## Purpose

Define the runtime authority and mutation transaction boundary for Role & Capability Manager without creating a parallel authorization database or an anonymous recovery backdoor.

## Authority profile

### RA1 — native WordPress role/capability authority — first baseline

WordPress remains source of truth for:
- registered site roles;
- primitive capability grants;
- user role assignments;
- user-specific capability overrides where WordPress supports them;
- mapped/meta capability semantics;
- Multisite Super Admin/network authority.

WPE reads/simulates/applies through certified WordPress APIs and semantics.

WPE does not mirror role/capability truth into a separate persistent authorization engine and then override WordPress globally.

### RA2 — third-party/custom role adapter compatibility profile

Plugin/theme-created roles/caps remain external/native authority even when edited through WPE.

WPE must preserve unknown entries and surface ownership/version drift. A plugin deactivation does not authorize WPE to purge its role/cap metadata automatically.

## WPE control-plane state

WPE may own bounded configuration/operational records separate from authority:
- Role Manager policy/settings;
- administrative-equivalent risk classifier descriptors;
- Change Plan/impact preview fingerprint;
- pre-change authorization snapshot/reverse-diff metadata;
- recovery diagnostics;
- Audit references.

These records explain/guard a mutation but do not become effective capability truth by themselves.

## Change Plan profile

High/critical mutation creates a reviewed immutable/bounded Change Plan containing:
- target site/network scope;
- actor;
- role/user targets;
- normalized proposed diff;
- effective-capability simulation result;
- affected-user count/identities according to privilege/privacy policy;
- current operator impact;
- remaining recovery-principal analysis;
- third-party/WPE dependency impact;
- risk class;
- source authority fingerprint/version;
- required confirmation/recent-auth class;
- optional pre-change snapshot reference.

Execution revalidates the fingerprint. Stale Plan cannot silently apply against changed role/user state.

## Risk classes

- `low` ordinary content capability change;
- `medium` broad editor/manager/access impact;
- `high` role deletion, privileged user/role changes, replace-all roles, explicit user override;
- `critical` self-lockout, last recovery principal, network/Super Admin-sensitive or bulk privileged mutation.

Risk classifier is explanatory/guarding logic, not a fictional total-order privilege score.

## Recovery-principal invariant

Ordinary WPE UI may not knowingly commit a mutation leaving zero viable recovery principals for the affected site/network scope.

Recovery analysis includes:
- effective primitive/mapped capabilities;
- multiple roles;
- user-specific overrides;
- current operator;
- other administrators/recovery users;
- Multisite Super Admin behavior;
- WPE management/recovery capability requirements.

A role name such as `administrator` is not sufficient evidence by itself.

## Mutation transaction profile

Conceptual high-risk apply:
1. resolve current trusted scope;
2. reauthorize actor/recent-auth if required;
3. load reviewed Change Plan;
4. recompute source fingerprint/current effective-cap state;
5. block stale Plan or failed impact scan;
6. enforce recovery-principal invariant;
7. create/verify bounded pre-change snapshot/reference;
8. apply minimal WordPress role/user-cap mutation through native API;
9. re-read effective state;
10. verify expected diff and recovery invariant;
11. commit WPE Plan/result/Audit metadata;
12. invalidate affected Policy/capability caches/generations;
13. emit post-commit domain event.

Exact database transaction behavior is constrained by WordPress options/usermeta semantics and requires executable evidence. WPE must not claim one universal SQL transaction covers all role/user/network changes unless proven.

## Failure after native mutation

A critical ambiguity exists if WordPress authority changes but WPE result/audit/snapshot metadata persistence fails.

Recovery behavior:
- re-read native role/user state as authority;
- compare with Plan fingerprint/expected diff;
- mark operation `applied_metadata_unknown`/reconciliation-required rather than retrying blindly;
- never repeat replace/delete mutation solely because WPE metadata commit failed;
- surface documented privileged recovery path.

## Self-lockout profile

If actor loses effective WPE/core admin reach:
- explicit impact summary;
- recent auth;
- another viable recovery principal must remain;
- stale state rechecked immediately before apply;
- resulting state verified after apply.

Ordinary UI cannot provide “ignore last admin warning” checkbox that bypasses the invariant.

## Role deletion/reassignment

Delete Plan must pin:
- role identity/current caps fingerprint;
- assigned users;
- default-role implications;
- proposed user reassignment/removal behavior;
- resulting effective-cap simulation;
- WPE Definition/policy dependencies;
- third-party ownership warning;
- recovery-principal result.

Delete and reassignment are one reviewed business operation even if underlying WordPress writes occur in steps.

Partial failure cannot be reported as clean success.

## Multiple roles / explicit overrides

Actions remain distinct:
- add role;
- remove role;
- replace role set;
- add explicit user cap;
- add explicit deny where WordPress semantics support it;
- remove explicit override restoring inherited behavior.

Replace-all is high-risk because unrelated plugin roles can be lost.

Effective simulation uses combined roles + individual caps + mapped/meta capability context.

## Super Admin / Multisite

Super Admin is not modeled as an ordinary site role.

Rules:
- site administrator cannot grant/remove Super Admin through site Role Manager;
- network/Super Admin mutations use explicit network action adapter and current core authority;
- site-role changes are scoped to that site;
- recovery invariant is evaluated separately for site and network authority;
- site deletion/user removal does not silently alter unrelated network/global role facts;
- `switch_to_blog()` context does not grant network authority.

## Recovery layers

### RR1 — another authenticated authorized principal
Primary in-product recovery.

### RR2 — WordPress/WP-CLI privileged recovery
Documented break-glass path using native authority. This is preferred over custom anonymous authentication.

### RR3 — WPE recovery mode configuration
May disable WPE overlays/menu/profile restrictions and expose diagnostics to an already WordPress-authorized principal.

RR3 cannot:
- bypass WordPress login;
- mint Administrator/Super Admin;
- expose public recovery URL;
- disable core capability checks globally.

## Snapshot/reverse-diff profile

Critical changes may create bounded WPE snapshot metadata:
- affected role cap maps;
- affected user role/override entries;
- source fingerprint;
- actor/time/scope;
- schema/profile version.

Revert:
- re-simulates current state;
- creates reverse diff;
- aborts/reviews conflicts from newer changes;
- never blindly overwrites full stale role/options/usermeta state.

Snapshot is not a full Backup and does not guarantee rollback of third-party/plugin side effects.

## Cache/generation invalidation

After successful authority change, invalidate/version-bypass:
- request-local WPE Policy memoization;
- persistent capability/Policy result caches;
- Membership/Profile/Dashboard/Listings caches when visibility depends on WP capabilities;
- REST endpoint access caches;
- current operator UI capability state.

No stale cached allow may survive a committed revocation under the accepted generation model.

## Audit

Audit records safe:
- actor/scope;
- target role/user;
- mutation class;
- before/after diff/fingerprint;
- affected-user count;
- risk class;
- recovery invariant result;
- result/reconciliation state;
- correlation ID.

No passwords/session tokens/Application Password secrets.

## Future executable evidence — NOT AUTHORIZED

### Core authority
- create/edit/delete custom role;
- add/remove/replace roles;
- explicit user allow/deny;
- meta/object capability mapping;
- third-party role preservation/drift.

### Anti-lockout
- current operator self-lockout;
- last admin-equivalent removal;
- multiple-role residual recovery;
- user override changing effective recovery;
- stale Change Plan;
- failed impact scan;
- partial native mutation/WPE metadata failure.

### Multisite
- site admin vs Super Admin;
- network mutation adapter;
- site removal/default role behavior;
- nested site-context safety;
- per-site recovery invariant at 100/1k/10k-site metadata scale where relevant.

### Recovery
- RR1 alternate admin;
- WP-CLI documented repair;
- WPE recovery mode does not bypass login/caps;
- snapshot reverse-diff conflict with newer edits.

### Cache/security
- revoked cap immediately denied under accepted generation model;
- Profile/REST/Dashboard/Listings caches;
- mass-assignment attempts through User Profile remain blocked.

Zero-recovery-principal ordinary UI commits required: **0**.  
Unauthorized Super Admin/network grants required: **0**.

Executed Role/Capability security fixtures: **0**.

## Paper recommendation

Use **RA1 native WordPress authorization authority** with WPE Change Plans, effective-capability simulation, anti-lockout/recovery checks, bounded snapshots and reconciliation metadata around it.

WPE enhances safety/observability; it does not replace WordPress authorization or introduce a weaker parallel recovery authentication system.