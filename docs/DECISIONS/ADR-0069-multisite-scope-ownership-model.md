# ADR-0069 — WordPress Multisite Scope, Ownership & Isolation Model

Status: **Accepted logical/security architecture / physical/runtime evidence pending**  
Date: 2026-08-28

## Context

Multisite appears throughout WPEssential's requirements and evidence blockers, but a platform-wide scope contract was missing. Without one, different modules could make incompatible assumptions such as:
- network-active plugin code means network-global data;
- a shared WordPress user ID means shared authorization across sites;
- `switch_to_blog()` itself authorizes cross-site access;
- a site Backup owns shared users/network settings;
- network defaults and enforced settings are the same thing;
- a network-shared secret may be revealed to every site that can use it;
- a background job can iterate every site in one unbounded worker request;
- site Membership automatically applies network-wide.

Current WordPress Multisite semantics distinguish shared/global users and network data from per-site content/options/roles/context. Modern WordPress also exposes site-targeted capability APIs.

## Decision

The authoritative architecture is:
- `docs/ARCHITECTURE/MULTISITE-SCOPE-OWNERSHIP-MODEL.md`
- `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`

### Canonical logical scope

Every scope-aware WPE resource uses explicit logical coordinates:

`scope_type + network_id + site_id|null`

Initial scope types:
- `site`;
- `network`.

Current request/blog context can be a UI default but is never the sole persisted ownership/security identity.

### Default scope

User-created WPE application definitions/data are **site-scoped by default**.

Network scope is opt-in, privileged and only available to resource types with explicit network semantics.

Network activation of WPE code does not automatically network-enable every module/definition/data source.

### Logical scope vs physical storage

This ADR does **not** decide whether WPE physical data uses global tables with site discriminators, per-site tables or a hybrid.

Logical ownership is fixed now; exact DDL/table topology remains P-004/module-specific evidence.

### Authorization

A network-global WordPress user identity does not imply authorization on every site.

Cross-site site capability checks use explicit target-site WordPress capability APIs plus WPE Policy. `switch_to_blog()` is a context mechanism, not an authorization primitive.

Super Admin/Network Admin remains privileged but does not bypass WPE audit, destructive-operation, secret/privacy or recovery invariants.

### Site switching

Every successful site switch must restore context. Future implementation uses a bounded context-guard pattern so exceptions/early returns cannot leave a dirty switch stack.

Switching site context never changes logical ownership or silently grants access.

### Network definitions/defaults

Network resources declare explicit semantics such as:
- template/instantiate;
- linked revision;
- network default;
- network enforced/locked policy;
- shared runtime resource;
- scheduled propagation.

One mutable network definition does not silently overwrite every site merely because it is network-scoped.

### Settings

Network Settings compose as:

`hard platform invariant → network enforced policy → network default → allowed site override → module default`

Only modules supporting those layers expose them. Network default and network enforced value are distinct.

### Vault/connections

Site and network secrets are distinct.

A site can be granted permission to **use** a network-shared connection without permission to reveal/export the underlying credential. Usage is audited with target site scope.

### Jobs

Every Job/Schedule has explicit target scope.

Network-wide work uses:

`Network Coordinator → paginated site enumeration → bounded site child Jobs → aggregate result`

No unbounded all-sites loop is the normal model. One site's failure does not corrupt all-network state. JobService backpressure/fairness remains authoritative.

### Events/Abilities/APIs

Event envelopes and Ability invocations carry target site/network scope.

A site endpoint cannot become a network/cross-site endpoint merely by accepting an arbitrary `site_id` parameter.

### Cache isolation

Site-owned authorization/data caches include site/network scope. Cross-site cache/access leakage is Critical.

### Membership

Membership Plan/Enrollment/Entitlement is **site-scoped by default**, even though WordPress user identity is shared across the network.

A Site A Enrollment never grants Site B by default.

Network-wide Membership requires an explicit future profile/product behavior and is not accepted by implication.

### User/Profile/roles

Site Profile fields/layouts may be site-scoped, but shared WordPress identity actions such as email/password/session remain protected global identity operations.

Roles are site-aware. Super Admin/network authority is a separate high-risk action class.

### Backup/Reset

Site Backup/Restore and Network Backup/Restore are separate scope/risk classes.

A site restore/reset must not silently overwrite/delete shared users, network settings, other sites or network resources.

Network Reset/Restore requires separate Network Admin/Super Admin capability, recovery-principal invariant, verified recovery point, durable journal and stronger impact confirmation.

### Import/Export

Portable resources carry source scope. Site package import cannot overwrite network policy/resources without an explicit privileged network package/action.

### Remote service/licensing

Installation/network activation identity and licensed site allocations are separate commercial/resource dimensions where the plan counts sites. Site/network clone must not silently duplicate live service activation identity.

### Multisite UI

WPE gets a distinct Network Admin architecture for network Overview/Sites/Module Policy/Defaults/Shared Connections/Jobs/Backup/Audit/Diagnostics/Account allocation. Site Admin remains a separate authorization surface with inherited/locked-state indicators.

## Current module coverage

`MULTISITE-SCOPE-OPTION-MATRIX.md` maps all **31/31** product surfaces to explicit Multisite behavior.

This mapping does not mean runtime Multisite certification has passed.

## Consequences

Positive:
- scope becomes a shared platform primitive instead of module-specific convention;
- cache/IDOR/cross-site authorization risk is reduced structurally;
- network activation no longer conflates code loading with data ownership;
- Membership/Backup/Reset/import semantics become safer;
- shared connections can be delegated without secret disclosure;
- large-network work has bounded Job orchestration semantics.

Cost:
- every physical schema/cache/job/Ability must carry/derive scope correctly;
- Multisite testing becomes a cross-cutting release requirement;
- Network Admin requires distinct UX/policies;
- physical storage decisions remain nontrivial and require evidence.

## Evidence still required

After explicit owner development consent:
- single-site regression + Multisite subdirectory/subdomain profiles;
- network vs per-site plugin activation;
- Super Admin/site-admin/different-role/site-membership capability cases;
- site-targeted meta-capability checks;
- scope/cache/REST/Ability attack fixtures;
- safe `switch_to_blog()` nesting/restoration;
- site lifecycle create/archive/spam/delete/reactivate;
- bounded network Job fan-out/scale;
- physical table scope/migrations;
- Membership cache/revoke isolation;
- site/network Backup/Restore/Reset;
- site clone/remap;
- privacy/export/erasure;
- product license network/site allocation and clone behavior.

No Multisite site, table, Job, capability test, Backup, Reset, import or service call was created/executed to accept this ADR.
