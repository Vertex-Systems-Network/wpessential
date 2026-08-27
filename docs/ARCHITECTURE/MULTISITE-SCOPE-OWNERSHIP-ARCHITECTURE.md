# WPEssential — Multisite Scope & Ownership Architecture

Status: **Phase 0 planning only / no development authorized**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0022, ADR-0036, ADR-0048, ADR-0049, ADR-0059, P-001…P-013.

## 1. Purpose

WPEssential is designed to work on single-site WordPress and WordPress Multisite without silently confusing:

- one site/blog's runtime data;
- network-wide policy/configuration;
- user identity shared by the network;
- site-specific roles/capabilities;
- Super Admin/network authority;
- per-site jobs/caches/files;
- network-wide definitions/services.

Multisite support is a cross-cutting platform contract, not a checkbox added independently to 31 modules.

## 2. Canonical scope vocabulary

Every WPE-owned persisted definition/runtime record must declare one explicit scope class:

- `scope.site` — belongs to exactly one WordPress site/blog ID;
- `scope.network` — belongs to exactly one WordPress network ID;
- `scope.global_installation` — installation-level technical state only when truly independent of site/network business ownership;
- `scope.external` — authoritative state lives outside WordPress and local records are references/cache only.

`current site` is execution context, **not durable ownership**.

Durable records must never infer ownership later from whichever blog happens to be current.

## 3. Explicit identifiers

Where applicable persisted/runtime entities carry:

- `network_id`;
- `site_id` when site-scoped;
- stable WPE UUID;
- definition/revision/provider/profile identifiers;
- source/environment identity where external.

A site-scoped row is invalid without a valid owning `site_id`.

A network-scoped row must not be duplicated into every site merely because a UI is rendered from individual sites.

## 4. WordPress context switching rule

`switch_to_blog()` is treated as a scoped database/cache context operation only.

Rules:
1. WPE records the original site ID before switching.
2. Every switch is paired with `restore_current_blog()` in a bounded scope/finally-style cleanup path.
3. No WPE code assumes a plugin/theme/function becomes available merely because blog context switched.
4. Long-running jobs persist intended target site/network IDs explicitly; they never rely on the site that happened to enqueue the worker process.
5. Nested switches are treated as stack operations and restored in reverse order.
6. Errors/exceptions must not leave the process in the wrong blog context.

## 5. Site vs Network configuration

### Site configuration
Use native site options/settings or WPE site-scoped runtime stores when the value belongs to one site.

Examples:
- site-specific Dashboard layout;
- site-specific CPT/Taxonomy/Field definitions when configured locally;
- site Membership Plans when not centrally governed;
- site Email sender/Notification preferences;
- site Backup schedule/destination references.

### Network configuration
Use network-scoped storage when the policy is intended to govern the network.

Examples:
- network module allow/deny policy;
- network defaults;
- shared provider policy/profile references;
- network-level retention/security policy;
- centrally governed definition templates;
- network-wide platform diagnostics policy.

`get_site_option()`/network-option semantics are treated as network-level configuration, not per-site configuration.

## 6. Inheritance model

Where a module supports central governance, inheritance is explicit:

`Network Default → Site Override → Effective Value`

Each setting declares one of:
- `network_only`;
- `site_only`;
- `network_default_site_override`;
- `network_locked`;
- `not_multisite_applicable`.

UI always shows the origin of an inherited value.

A site admin cannot override `network_locked` values.

Removing a site override restores inherited network behavior; it does not copy the current network value locally.

## 7. Definitions and revisions

Definition Repository scope is first-class.

A definition has one owning scope:
- site definition;
- network definition/template.

A site may reference a network definition only through an explicit reference/assignment relationship.

Network definition edits do not silently mutate a site's published runtime unless the product contract says `follow current network revision`.

Supported assignment modes can include:
- snapshot/clone;
- follow published network revision;
- pinned network revision.

Draft network revisions never change live site behavior.

## 8. Users, roles and capabilities

WordPress users can be network-shared while roles/capabilities are site-contextual.

Rules:
- WPE stores user identity by WordPress user ID, but authorization checks include target site/network scope.
- A user being administrator on Site A does not grant administration on Site B.
- Super Admin is a WordPress network authority signal, not a replacement for WPE resource policies.
- High-risk WPE operations still pass dedicated capability + Policy checks.
- Network-management screens require appropriate network authority and WPE policy.
- Site-management screens authorize against the target site, even when opened from Network Admin.

## 9. Membership

Default Membership ownership is site-scoped.

Network Membership becomes a separate explicit mode, never inferred because users are network-shared.

Network Membership must define:
- participating sites;
- network Plan ownership;
- site-specific content rules;
- cross-site entitlement materialization;
- site removal behavior;
- billing source authority;
- user deletion/site deletion consequences.

An entitlement for Site A must not authorize Site B unless a network rule explicitly grants it.

## 10. Jobs / Cron / Workflow

Every Job record carries target scope.

Site job:
- `network_id` + `site_id`;
- executes after establishing target site context;
- restores prior context before completion.

Network job:
- network-scoped coordinator;
- cannot assume Action Scheduler provides network-wide fairness/orchestration;
- fans out bounded site work through JobService when needed.

Network fan-out is chunked/backpressured; a 10,000-site network must not enqueue unbounded work in one request.

Business idempotency keys include scope identity.

## 11. Cache isolation

Every cache key that can vary by site/network must include relevant scope identity.

Site authorization/listing/query caches include site ID.

Network policy caches include network ID.

Switching blogs requires correct cache context handling; WPE must not keep one site's resolved policy/query result and reuse it for another site.

Membership revoke-to-deny generations are site/network scoped according to entitlement ownership.

## 12. Custom Tables

Physical table strategy remains evidence-gated, but logical ownership is fixed.

Permitted future strategies:
- per-site WordPress-prefixed table where strongly justified;
- global WPE table with explicit `network_id/site_id` columns.

No strategy may omit logical scope identity.

Global tables require composite indexes/unique constraints beginning with relevant scope columns where query patterns demand it.

Per-site tables require reliable create/upgrade/delete behavior for newly created and existing sites.

Exact choice is per runtime domain, not one universal rule.

## 13. Relations

Every relation endpoint declares scope.

Cross-site relation is **off by default**.

If a relation type explicitly supports cross-site edges, each endpoint identity includes site ID and Policy reauthorizes both sides.

Network/global relation queries may not leak objects from sites the viewer cannot access.

## 14. Query Builder / Listings / Admin Columns

Default query scope is current authorized site.

Cross-site/network query requires:
- explicit query mode;
- bounded participating sites;
- capability/Policy authorization;
- per-site source compatibility;
- pagination/count semantics across sites;
- performance budget;
- cache isolation;
- result-level authorization.

A generic Query AST cannot use implicit `switch_to_blog()` loops as an unlimited network search engine.

## 15. REST / Abilities / AI

Every site-sensitive Ability/REST operation accepts or resolves an explicit target scope.

Rules:
- request site context cannot be silently changed by a body parameter without authorization;
- target `site_id` is validated as a resource identifier;
- network actions require network scope/capability;
- AI/MCP receives no multisite bypass;
- destructive cross-site/network Abilities remain AI-disabled by default unless separately approved in a future policy.

## 16. Backup

Backup Set declares scope:
- one site;
- selected sites;
- whole network.

Network Backup manifest explicitly records included site IDs/domains and shared/global artifacts.

Restore distinguishes:
- site restore into same site;
- site restore into another site;
- selected-site restore;
- full-network disaster restore.

User tables/network tables/shared uploads/domain mappings cannot be treated as ordinary per-site files.

Network restore remains a distinct P-013 certification profile.

## 17. Import / Export

Portable configuration package carries ownership intent, not source DB blog IDs as stable identity.

Import target must choose:
- current site;
- selected site;
- network scope where supported.

Cross-network import maps source scope identifiers to target scope explicitly.

No importer silently creates network-wide definitions from site-scoped packages or vice versa.

## 18. Secrets Vault

Vault secret references declare owning scope and sharing policy.

Modes:
- site-private secret;
- network-shared secret;
- network-managed/site-usable reference.

A site admin cannot read/export plaintext network-shared credentials merely because their site can use the connection.

Network credential rotation updates references without copying secret plaintext to sites.

## 19. Remote service / licensing

WPE product entitlement identifies installation/site/network according to commercial contract, separately from WordPress Membership.

Multisite license topology is explicit and must not infer site count from hidden telemetry.

Remote transmission remains subject to ADR-0060 minimization/consent rules.

## 20. Module activation/deactivation

Plugin network activation does not imply every Pro module is enabled for every site.

Module state can be:
- platform available network-wide;
- network policy allowed/blocked;
- site enabled/disabled;
- entitlement permitted/locked.

Effective state is computed, not represented by one boolean.

Disabling a module at network level preserves site definitions/data according to lifecycle policy unless explicit deletion is separately requested.

## 21. Site creation lifecycle

When a new site is created, WPE may apply only explicit network templates/defaults.

New-site provisioning must be:
- idempotent;
- revision/version aware;
- recoverable;
- bounded;
- safe if module/plugin state differs.

Existing sites are not retroactively overwritten when network defaults change unless the setting/definition explicitly follows current network state.

## 22. Site deletion/archive lifecycle

Site deletion/archival requires an impact plan for WPE-owned site records:
- definitions;
- runtime rows;
- jobs;
- caches;
- Membership enrollments/entitlements;
- private assets;
- audit/history;
- Backup references;
- external integrations.

Deletion of WordPress site data does not automatically justify deletion of required audit/commercial/security records.

Retention policy remains authoritative.

## 23. Network deletion / migration

Whole-network destructive work is Level-3 impact.

Requires:
- explicit network authority;
- impact summary;
- verified recovery point where applicable;
- durable journal;
- no ambiguous partial-success claim;
- post-operation verification.

## 24. Admin UX

Network Admin gets only screens that genuinely manage network scope.

Site Admin retains site-scoped screens according to policy.

Every editor/list screen visibly identifies scope:
- Site: `<site label>`;
- Network: `<network label>`;
- Inherited from Network;
- Network Locked.

Bulk cross-site operations show site count and affected scopes before execution.

## 25. Audit / observability

Audit records include:
- network ID;
- site ID when applicable;
- actor user ID;
- original request site;
- target scope;
- action/ability;
- result;
- correlation/job/run ID.

Cross-site context changes are diagnosable without logging secrets/private payloads.

## 26. Performance guardrails

- no unbounded `get_sites()` + `switch_to_blog()` loops in interactive requests;
- network fan-out uses JobService chunks;
- site lists paginate;
- cross-site queries require explicit budgets;
- per-site asset loading remains scoped;
- network cache invalidation is generation-based/bounded where practical;
- large networks are first-class future fixtures, not assumed to behave like 3-site demos.

## 27. Security invariants

1. Site A admin never gains Site B data by changing a request ID.
2. Network UI visibility never substitutes server authorization.
3. Super Admin does not bypass WPE high-risk Policy/audit contracts.
4. Network-shared credentials never become site-readable plaintext.
5. Cross-site query/search results are resource-authorized.
6. Jobs cannot execute against stale/wrong site context.
7. Cache keys cannot cross-contaminate sites.
8. Site deletion cannot orphan privileged active jobs/credentials unnoticed.
9. AI/REST/CLI use the same scope policies.
10. Switch/restore failures are treated as platform-critical bugs.

## 28. Future evidence protocol — NOT AUTHORIZED

After explicit owner consent, Multisite evidence must include at minimum:
- subdirectory and subdomain-style networks;
- Network Admin vs Site Admin permissions;
- user admin on Site A but not Site B;
- Super Admin high-risk operations;
- network defaults/site overrides/locks;
- switch/restore under exceptions;
- nested blog switches;
- cache isolation;
- Definition publish/inheritance;
- Vault network-shared/site-private references;
- site creation/deletion hooks;
- JobService site/network fan-out and fairness;
- network-size fixtures (small + large synthetic);
- Membership cross-site deny/grant;
- REST/Ability IDOR attempts;
- Backup selected-site/full-network manifest/restore fixtures;
- uninstall/deactivation/network activation;
- Free↔Pro version skew across network lifecycle.

No fixture has been executed.

## 29. Development gate

This document fixes logical architecture only.

No Multisite tables, hooks, migrations, site lifecycle handlers, network UI, queue fan-out, tests or benchmarks may be implemented/executed before explicit owner development consent under ADR-0014.
