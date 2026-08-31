# ADR-0069 — Multisite Scope & Ownership Architecture

Status: **Accepted architecture / runtime evidence pending**  
Date: 2026-08-28

## Context

WPEssential spans definitions, runtime data, jobs, Membership, Backup, Settings, Roles, Vault, REST/Abilities, integrations and destructive operations. WordPress Multisite adds separate concepts for network-wide state, site/blog state, shared users, site-contextual roles/capabilities and Network Admin authority.

Treating `current blog` as durable ownership, assuming network activation means all modules are enabled everywhere, or using Super Admin as a universal product-policy bypass would create cross-site data leaks, authorization errors and destructive-operation risk.

WordPress also documents that `switch_to_blog()` switches database/cache site context; it does not load site-specific plugin/theme code, and proper restore is required after switching.

## Decision

WPEssential adopts explicit first-class scope/ownership semantics.

Canonical durable scopes are:
- `scope.site`;
- `scope.network`;
- `scope.global_installation` only for genuine installation-level technical state;
- `scope.external` where authority lives outside WordPress.

`current site/blog` is execution context only and must never be treated as durable ownership.

Site-scoped records carry explicit site identity; network-scoped records carry network identity. Runtime jobs, audit events, cache keys, definitions, policy checks and external references preserve their target scope.

Where central governance is supported, configuration inheritance is explicit:

`Network Default → Site Override → Effective Value`

with declared modes such as `network_only`, `site_only`, `network_default_site_override` and `network_locked`.

Network definition sharing uses explicit assignment/reference modes; draft network revisions never silently change live sites.

Cross-site relations and cross-site queries are disabled by default and require explicit resource authorization, bounded scope and performance budgets.

Super Admin/network authority remains a WordPress authority signal but does not bypass dedicated WPE high-risk capability/Policy/audit contracts.

Network activation does not imply each module is site-enabled. Effective module state composes platform availability, network policy, site state and entitlement.

Network-wide work is coordinated through bounded JobService fan-out rather than unbounded interactive `switch_to_blog()` loops.

Multisite Backup, Membership, Vault sharing, Import/Export, site lifecycle and whole-network destructive operations have explicit scope semantics and separate future evidence requirements.

## Security invariants

- Site A authority cannot access Site B by changing a target ID.
- Network screen visibility is not authorization.
- Site-sensitive cache keys include scope identity.
- Shared network credentials cannot become site-readable plaintext.
- Jobs establish and restore the intended site context explicitly.
- AI/REST/CLI use the same scope-aware Policy contracts.
- Cross-site search/query results are reauthorized.
- Whole-network destructive operations require highest-impact review/recovery contracts.

## Consequences

Positive:
- one consistent Multisite model across all modules;
- lower IDOR/cross-site cache leakage risk;
- explicit site/network data ownership and lifecycle;
- compatible with network defaults without cloning configuration everywhere;
- bounded large-network background processing;
- clearer Backup/Membership/Vault behavior.

Costs:
- more scope columns/indexes and policy context in physical implementations;
- cross-site features require explicit adapters and performance work;
- site/network lifecycle needs dedicated tests;
- some domains may require different physical table strategies.

## Evidence still required

After explicit owner development consent:
- P-001 Multisite compatibility matrix;
- Network Admin/Site Admin/Super Admin permission fixtures;
- site switch/restore exception and nested-switch tests;
- cache-isolation tests;
- network default/site override tests;
- site creation/archive/delete lifecycle;
- JobService large-network fan-out/fairness;
- Membership cross-site authorization;
- Vault shared-reference isolation;
- REST/Ability cross-site IDOR;
- selected-site/full-network Backup/Restore;
- network activation/deactivation/uninstall;
- Free↔Pro version-skew lifecycle.

No Multisite runtime evidence has been executed.

## Development gate

Acceptance of this architecture does not authorize implementation. ADR-0014 still requires explicit owner consent before source, schema, migration, hook, test, queue, benchmark or deployment work.
