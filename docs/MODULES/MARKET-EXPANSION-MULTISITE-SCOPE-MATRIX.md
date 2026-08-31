# WPEssential — Market Expansion Multisite Scope Matrix — Surfaces 44–48

Status: **Phase 0 exhaustive planning / no Multisite implementation authorized**
Date: 2026-08-29
Parent architecture: `../ARCHITECTURE/MULTISITE-SCOPE-OWNERSHIP-MODEL.md`
Accepted by: ADR-0188

## Purpose

ADR-0188 adds five user-facing module/platform surfaces to the pre-existing 43-surface scope. This addendum maps their logical Multisite ownership, network behaviors, lifecycle and security rules so current combined logical scope coverage becomes **48/48**.

This is logical product planning only. Physical tables/topologies/runtime hooks remain evidence-gated.

## Matrix

| # | Surface | Default scope | Network mode | Core rule |
|---:|---|---|---|---|
| 44 | URL Redirection & Routing Manager | Site | Network template / enforced routing floor / explicit network route set | a site rule can never intercept another site's host/path merely because both sites share one network |
| 45 | Search, Replace & Data Transformation | Site Plan | Separate privileged Network Plan | every target site/table is explicit; global/network tables are separate high-risk scope |
| 46 | Dummy Data & Fixture Studio | Site Dataset | Network template/coordinator | generated identities/cleanup ownership remain site-aware; global users and cross-site relations require explicit profiles |
| 47 | Link Health & Crawl Intelligence | Site Scan | Network coordinator | scan sources/issues are site-owned; cross-site links are classified, not implicitly writable |
| 48 | Database Maintenance & Cleanup | Site Maintenance Plan | Separate privileged Network Maintenance Plan | site cleanup cannot delete global users/network data or another site's rows by heuristic |

## 44 — URL Redirection & Routing

### Site scope
- Redirect Definition owned by site ID + stable definition ID.
- Source host/path must belong to target site's recognized domain/routing context unless an explicit external-domain profile exists.
- Site Admin can manage only allowed site redirects.
- Site rule cache namespace includes site/domain mapping generation.
- 404/log records partition by site.

### Network modes
1. **Template** — network Definition can instantiate a site-local copy.
2. **Linked revision** — site follows approved network revision where allowed.
3. **Enforced routing floor** — only explicitly supported security/canonicalization rules; site cannot weaken enforced rule.
4. **Network route set** — privileged rules owned by Network Admin for installation/network-level hosts.

### Network controls
- target sites: all eligible / selected / site query;
- target domains resolved per site;
- slug/permalink conflict preflight;
- priority relative to site rules;
- override allowed/denied;
- future-site inheritance;
- rollout batch size;
- per-site simulation;
- per-site failure reporting;
- unlink behavior;
- network delete impact count.

### Domain mapping
- old/new mapped-domain migrations are explicit;
- one mapped domain cannot be claimed by two rules silently;
- domain transfer between sites fences stale compiled rules;
- HTTPS/scheme canonicalization resolved per mapped domain;
- cross-domain redirect chain shown explicitly.

### Lifecycle
- site archive/delete disables its site-owned route execution before cleanup;
- clone defaults to Draft redirect copies and remaps source/target domain variables;
- transfer requires target-network compatibility scan;
- network template deletion never silently deletes site-local business rules.

## 45 — Search, Replace & Data Transformation

### Site default
- target site fixed in Plan identity;
- current-blog context is never enough to authorize target;
- selected site tables resolved by validated schema registry;
- site backup requirement evaluated for that site;
- report/diff/journal site-scoped.

### Network Plan
Separate Network Admin class with:
- explicit target-site list/filter;
- preview rows/counts per site;
- global tables separate checkbox/profile;
- users/usermeta/global terms where applicable treated as network resources;
- per-site schema fingerprint;
- per-site Dry Run status;
- per-site Backup/rollback requirement;
- concurrency/fan-out budget;
- partial-site failure policy;
- aggregate summary plus site child reports.

### Safety
- a site Plan cannot transform `wp_users`/global network data merely because WP exposes the current user;
- domain migration uses explicit site mapping;
- table-prefix discovery is registry-driven;
- network replace cannot blindly assume identical plugins/fields across sites;
- shared network settings require owning API and network capability.

### Lifecycle
- site delete/transfer fences active Runs;
- clone can generate a new Plan from source mappings but cannot replay a source-site destructive journal as destination authority;
- interrupted network Run records exactly which sites/batches committed.

## 46 — Dummy Data & Fixture Studio

### Site default
- Dataset/Generation Run site-owned;
- generated post/term/comment/media/custom-table identities site-scoped;
- cleanup can only remove identities owned by the Run/site;
- site role assignments explicit.

### Global users
Profiles:
- reuse existing user;
- create global synthetic user + assign target-site role;
- create network test persona used across selected sites;
- no global Super Admin generation through ordinary Dataset.

Generated user cleanup rules:
- removing site membership/role != deleting global user;
- global synthetic user deletion only if Run proves no non-generated ownership/dependencies across network;
- otherwise detach generated memberships and retain account.

### Network Dataset template/coordinator
- select target sites;
- seed strategy: same seed with site namespace / unique site seed / explicit seed map;
- target-site entity counts;
- dependency availability check per site;
- per-site Job/Run child state;
- fairness/concurrency;
- future-site template inheritance optional;
- no cross-site relation by default.

### Lifecycle
- site deletion cancels/fences active generation;
- clone can preserve scenario/seed while regenerating stable identities by default;
- network cleanup shows site/global residuals separately.

## 47 — Link Health & Crawl Intelligence

### Site Scan default
- scan Definition and issue records site-owned;
- source entities resolved under target site's Policy;
- internal links use target site's domain set;
- protected/private content link inventory only visible to authorized actors.

### Cross-site links
Classifications:
- internal same-site;
- internal same-network other-site;
- external network/domain;
- mapped-domain alias.

Cross-site link health can be checked, but source-site user cannot edit target-site content/routing merely because link is in same network.

### Network coordinator
- target sites;
- scan profile/template;
- host/domain dedupe;
- per-host shared rate limit;
- max concurrent sites;
- site-fair job scheduling;
- per-site issue access;
- aggregate report with authorization-aware counts;
- domain mapping inventory.

### Lifecycle
- site delete archives/deletes its source occurrences/issues by retention policy;
- site transfer invalidates same-network classification and rechecks links;
- clone creates new source identities and does not copy stale check authority.

## 48 — Database Maintenance & Cleanup

### Site default
- Plan may target site-owned tables/rows and certified cleanup providers only;
- site-specific revisions/transients/meta/relations/runtime history follow owning provider;
- site admin cannot operate global/network tables unless a dedicated permission/profile allows it.

### Network maintenance
Separate high-risk Plan:
- target sites;
- global/network resource classes;
- user/usermeta handling;
- network options/transients;
- network-owned WPE tables;
- per-site provider availability;
- candidate count/bytes per site;
- backup requirement by scope;
- resource/fairness budget;
- partial failure policy;
- post-clean verification.

### Site lifecycle
- Site Lifecycle Coordinator remains authority for cleanup after site deletion;
- Maintenance module can execute registered post-delete cleanup providers but cannot invent ownership;
- global user deletion is not implied by site deletion;
- orphan certainty must include site/network ownership.

### Scale
Network evidence profiles include:
- 10 sites;
- 100 sites;
- 1k sites;
- 10k sites;
- high-volume global tables;
- noisy-site fairness.

## Cross-module Multisite invariants

For all five surfaces:
- site ID/domain/current-blog context is not sufficient authorization;
- Network Admin capability is separate from Site Admin;
- network template != shared runtime unless explicitly specified;
- target sites are explicit for fan-out;
- per-site errors remain visible;
- child jobs retain target scope;
- caches/indexes include scope generations;
- import/export packages carry scope;
- Audit records parent network action + site child effects;
- clone/transfer/delete use Site Lifecycle Coordinator;
- Pro expiry/module disable preserves definitions/data safely and cannot produce cross-site fail-open behavior.

## Current truth

- Original surfaces 1–31: mapped in `MULTISITE-SCOPE-OPTION-MATRIX.md`.
- Universal foundations 32–43: mapped in `../SOLUTIONS/UNIVERSAL-FOUNDATIONS-MULTISITE-SCOPE-MATRIX.md`.
- Market expansion 44–48: mapped here.
- Combined current logical Multisite coverage: **48/48**.
- Runtime Multisite certifications: **0**.

## Development gate

No network table, cross-site query, redirect, Search/Replace Run, fixture generation, crawl or cleanup action is authorized by this document.
