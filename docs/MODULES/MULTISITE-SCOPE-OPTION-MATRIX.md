# WPEssential — Multisite Scope & Option Matrix Across 31 Surfaces

Status: **Phase 0 exhaustive planning / no Multisite implementation authorized**  
Date: **2026-08-28**  
Parent architecture: `../ARCHITECTURE/MULTISITE-SCOPE-OWNERSHIP-MODEL.md`.

## 1. Purpose

A module cannot claim “Multisite supported” merely because it does not fatal when network-activated.

For every WPE surface this matrix records:
- default logical scope;
- whether network-scope creation exists;
- inheritance/propagation behavior;
- site-admin vs Network Admin authority;
- site lifecycle behavior;
- cross-site behavior;
- special security/data/backup concerns.

The exact physical tables/hooks remain evidence-gated.

---

# 2. Shared scope controls

Where a definition is allowed to be network-scoped, the editor can expose only the controls relevant to that module:

- **Scope**: Site / Network;
- **Network mode**: Default / Enforced / Template / Shared Runtime, where supported;
- **Target sites**: All eligible / selected sites / site query/filter;
- **Propagation mode**: None / instantiate copy / linked revision / enforced inheritance / scheduled rollout;
- **Site override**: Allowed / denied / selected fields only;
- **New-site behavior**: Do nothing / inherit default / instantiate latest approved revision;
- **Existing-site rollout**: Never automatic unless explicitly enabled;
- **Conflict behavior**: Skip / report / create site override / require review;
- **Unlink behavior**: keep current site copy / revert to network default / block while enforced;
- **Network delete behavior**: impact preview + dependent-site count + safe unlink/migration plan;
- **Audit**: parent network event + target-site child events for propagation/fan-out.

These controls are **not shown globally for every module**. A module supports only modes that have explicit semantics below.

---

# 3. Surface matrix

| # | Surface | Default | Network mode | Core Multisite rule |
|---:|---|---|---|---|
| 1 | Custom Post Types Builder | Site | Template/Enforced registration policy candidate | CPT registration is site-context behavior; network definition must explicitly target sites and handle slug/rewrite conflicts |
| 2 | Taxonomy Builder | Site | Template/Enforced candidate | taxonomy/object-type binding resolved independently per target site |
| 3 | Custom Fields Builder | Site | Template/linked schema candidate | network field schema may be reusable, but values remain resource/site-owned unless data source explicitly network-scoped |
| 4 | Relations Builder | Site | Network relation only explicit advanced mode | relation endpoints cannot cross sites by default; cross-site relation is separate typed capability |
| 5 | Status Manager | Site | Template candidate | WP post statuses execute in site registry; generic network state machines must identify target scope explicitly |
| 6 | Custom Query Builder | Site | Network query definition candidate | query execution cannot read other sites unless data source and Policy explicitly support cross-site scope |
| 7 | Custom Tables Builder | Site | Explicit advanced network table | logical scope is separate from physical table topology; network custom table is high-risk capability |
| 8 | Admin Columns Builder | Site | Template candidate | each WP list table is target-site context; network rollout never assumes identical post types/meta across sites |
| 9 | Dynamic Listings/Templates | Site | Template/linked revision candidate | rendering authorization and Query execute in target site; cache always site-scoped unless resource explicitly network-owned |
| 10 | Dashboard Widgets Manager | Site | Network Admin widgets separate | site dashboard widgets cannot appear in Network Admin by implication; remote-data trust rules remain |
| 11 | Custom Admin Menu Builder | Site | Separate Network Admin rules | site menu rules and network menu rules are different registries/surfaces; hiding remains non-authorization |
| 12 | Settings Page Builder | Site | Network defaults/enforced values supported | `network default → optional lock → site override` per ADR-0036 |
| 13 | Frontend Dashboard Builder | Site | Network template only initially | routes/data/access are site-scoped; cross-site portal requires explicit future profile |
| 14 | User Profile Builder | Site layout/data | Network identity layout/policy candidate | ordinary site fields scoped; email/password/session global identity actions explicitly labeled/protected |
| 15 | Membership System | Site | Network Membership future explicit profile | site A Membership never grants site B by default despite shared user ID |
| 16 | Builder Widgets Builder | Site/portable Blueprint | Network component library candidate | component definition can be shared; runtime data/Policy resolved in target site |
| 17 | Forms & Workflow Builder | Site | Template candidate; network workflow explicit | Form Entries site-owned; network workflow fan-out uses child jobs, not implicit cross-site CRUD |
| 18 | Cron Job Builder | Site | Network coordinator schedule supported | network schedule fans out bounded site Jobs; current site is not target authority |
| 19 | Notification System | Site | Network notification explicit | recipient resolution and preference/permission are scope-aware; one site's preferences do not silently control another unless network policy says so |
| 20 | Emails Builder | Site | Network template/layout candidate | template can be shared; sender profile/transport/recipient data remain scoped and Policy/Vault controlled |
| 21 | Message & Chat System | Site | Network conversation future explicit profile | participants/messages/private assets do not cross sites by default |
| 22 | REST API Builder | Site | Network endpoints separate privileged class | site endpoint cannot accept arbitrary `site_id` to become cross-site; network route explicitly authorized |
| 23 | Webhooks & Connections | Site | Shared network connection can be delegated | site can receive use-right to network connection without secret reveal; Event Inbox records target scope |
| 24 | Backup Manager | Site plan | Network Backup supported high-risk | site backup and network backup are distinct plans, manifests and restore certification profiles |
| 25 | Reset Manager | Site Reset | Network Reset separate extreme-risk class | site reset cannot alter shared users/network policy by default |
| 26 | Import / Export | Site package | Network package separate | package resources carry scope; site import cannot overwrite network resources without network privilege |
| 27 | Protector | Site | Network default/enforced security policy candidate | network policy can set floor; site override cannot weaken enforced floor unless explicitly allowed |
| 28 | Watermarker / Media Rules | Site | Template/default candidate | derivatives/media ownership remains site-scoped; shared source storage does not imply shared media authority |
| 29 | XML-RPC Manager | Site | Network security floor candidate | endpoint is installation-level path but method/integration impact must be evaluated across target network; site UI cannot promise complete network deny |
| 30 | Role & Capability Manager | Site | Super Admin/network authority separate | roles are site-aware; network/Super Admin mutations separate high-risk abilities |
| 31 | Platform Account/Docs/Support/Diagnostics | Network/install + site allocations | Native network scope | product account can be install/network-scoped while license/site allocation and diagnostics are site-aware/minimized |

---

# 4. Per-surface detailed Multisite options

## 4.1 CPT Builder

When Network mode exists:
- target-site selector;
- target-site eligibility filter;
- network template revision;
- instantiate vs linked/enforced registration;
- site label override policy;
- site slug/rewrite override policy;
- REST namespace conflict preflight;
- existing CPT collision behavior;
- future-site inheritance;
- rollout batch size/Job policy;
- de-provision behavior;
- network delete impact count;
- audit child events.

Default remains Site.

## 4.2 Taxonomy Builder

Network options additionally require:
- target object types resolved per site;
- missing object-type behavior: skip/report/block site rollout;
- rewrite/query-var collision preflight;
- term data is **not** copied/shared merely because taxonomy schema is shared;
- network schema unlink keeps/deletes site-local term data only through explicit migration action.

## 4.3 Fields Builder

Network schema options:
- template/link mode;
- site field-group override rules;
- location-rule resolution per site;
- field key collision handling;
- storage adapter compatibility per site;
- network-shared option/default vs site field value distinction;
- field schema update migration impact per site;
- secret field network policy without exposing secret values.

## 4.4 Relations

Default cross-site relation = **Off**.

If future explicit cross-site relation is enabled:
- endpoint type includes network/site coordinates;
- both sites/resources must be authorized;
- delete/orphan policy across site deletion;
- import/clone remapping;
- Backup restore handling;
- Query reverse lookup security;
- cache invalidation across both sites;
- Network Admin-only creation by default.

No cross-site relation implementation is implied by this spec.

## 4.5 Status Manager

- site WP post-status registry;
- network template revision;
- target CPT existence check;
- missing status dependency behavior;
- site-specific transition permissions;
- generic network-state machine only with explicit network entity data source;
- rollout/migration preview.

## 4.6 Query Builder

Multisite-specific controls only for explicit network-capable data source:
- scope: current site / selected site / network aggregate;
- target-site set/filter;
- per-site Policy context;
- max sites per execution;
- fan-out strategy;
- merge/sort semantics;
- per-site timeout/error mode;
- aggregate count semantics;
- cache scope;
- privacy/authorization filtering before aggregate result.

Normal Query has no arbitrary cross-site toggle.

## 4.7 Custom Tables

Default Site logical scope.

Explicit network table controls:
- network ownership;
- Network Admin-only schema mutation;
- site access-policy column/key requirements;
- row scope discriminator where table mixes site-owned rows;
- site deletion retention/cleanup behavior;
- Backup scope;
- export/import scope;
- index strategy evidence requirement;
- no site admin DROP/ALTER network-owned table.

## 4.8 Admin Columns

- target site/Post Type screen;
- network template applicability check;
- missing meta/tax/source graceful state;
- site-specific display override;
- network locked columns optional policy;
- sortable/filter query only where target site source supports it;
- no network-wide row query from normal site table.

## 4.9 Listings/Templates

- network component/template library usage;
- linked vs copied revision;
- target-site data source binding;
- site theme/builder compatibility;
- target-site Policy;
- cache namespace;
- asset version namespace;
- unlink/pin behavior.

## 4.10 Dashboard Widgets

Separate targets:
- Site Dashboard;
- Network Dashboard.

Controls:
- site roles/capabilities;
- Network Admin/Super Admin capability;
- site/network data source;
- target-site aggregate only through authorized Query/Job;
- remote data profile scope;
- widget visibility inheritance;
- no network widget secret exposed to site admins.

## 4.11 Admin Menu

Two rule sets:
- `site_admin_menu_rules`;
- `network_admin_menu_rules`.

Network rules cannot be edited by site admin. Site rule cannot hide/rearrange Network Admin. Recovery mode restores correct native menu for that scope.

## 4.12 Settings Pages

Each setting field declares:
- value scope: site/network;
- network default allowed?;
- network enforcement allowed?;
- site override allowed?;
- site can view inherited value?;
- site can know a shared secret exists without reading it?;
- network change propagation/invalidation behavior;
- site override reset-to-inherited action.

## 4.13 Frontend Dashboard

- route namespace per site;
- site membership/role Policy;
- network template source;
- target site binding;
- site-specific menu tree;
- no route collision assumption across mapped domains;
- cross-site navigation only explicit link/action;
- session/global user identity does not bypass target site membership.

## 4.14 User Profile

Field categories:
- site-owned profile value;
- shared/global WP identity read-only display;
- protected global identity action;
- network custom user attribute only when intentionally network-owned.

UI must show `Affects account across network` for email/password/session actions.

## 4.15 Membership

Default keys include site scope.

Controls:
- plan site scope fixed by default;
- network-shared Plan option unavailable unless network Membership profile exists;
- billing connection site/network source;
- shared network connection use permission;
- role sync target site only;
- site access cache generation;
- site deletion enrollment behavior;
- global user deletion vs site member removal distinction;
- site clone membership copy default Off;
- network Membership future mode separately versioned/certified.

## 4.16 Builder Widgets / Component Blueprint

- component scope: site/network library;
- network library read/use permission;
- copied vs linked component revision;
- local style/token override policy;
- data binding always evaluated in render site's scope unless component explicitly network-data capable;
- builder adapter availability per site.

## 4.17 Forms & Workflow

- Definition Site default;
- Entry always has source site;
- target CRUD cannot specify another site unless action is explicitly cross-site/network-certified;
- network template can instantiate Form/Workflow definitions;
- network workflow coordinator can fan-out explicit site actions;
- approvals record actor + target site;
- save/resume token site-bound;
- uploads/protected files site-owned.

## 4.18 Cron Job Builder

Schedule scope:
- Site;
- Network coordinator.

Network controls:
- target site filter;
- site batch size;
- max concurrent child sites;
- skip archived/spam/deleted states;
- per-site retry;
- aggregate failure threshold;
- pause coordinator vs pause child;
- site-context diagnostics;
- audit parent/child.

## 4.19 Notifications

- Rule scope;
- recipient source scope;
- preference scope;
- network mandatory-security category explicit;
- site notification cannot use another site's recipient dataset by default;
- digest groups cannot mix sites unless explicitly network-level and privacy-safe;
- links/routes generated for correct target site/domain.

## 4.20 Email Builder

- template scope;
- sender profile site/network connection;
- shared transport use permission;
- From/domain validation per provider/account/profile;
- site branding/layout override;
- correlation includes site scope;
- Provider Event maps back to exact site Recipient Delivery;
- global provider suppression fact does not silently mutate unrelated site preference without policy.

## 4.21 Chat

- Conversation site scope default;
- participant membership checked for target site;
- private attachment Protected Asset site-bound;
- network chat future explicit resource type;
- site removal/revoke removes that site's chat access, not network account;
- search candidates reauthorize site scope.

## 4.22 REST Builder

Route classes:
- Site REST route;
- Network REST route.

Site route:
- target site resolved from request/site context;
- arbitrary `site_id` parameter cannot escalate scope.

Network route:
- separate capability/Policy;
- bounded target site fan-out;
- network rate/cost budget;
- explicit response scope metadata where safe;
- no hidden cross-site joins.

## 4.23 Connections/Webhooks

Connection ownership:
- Site connection;
- Network shared connection.

Network connection delegation controls:
- sites allowed to use;
- operations allowed: read/write/event/etc;
- per-site quota/rate policy;
- account/subaccount binding;
- site-specific correlation/metadata;
- no credential reveal;
- revoke one site's use without deleting shared credential;
- audit every site use.

Inbound Event Inbox must resolve event to network/site scope before business action.

## 4.24 Backup

Plan types:
- Site Backup;
- Network Backup.

Site controls:
- include site blog tables;
- uploads;
- site WPE definitions/runtime;
- shared-user references strategy;
- site secrets/recovery placeholders;
- destination policy.

Network controls:
- sites selection/filter;
- include network/global tables;
- include users/usermeta;
- include network WPE resources;
- per-site uploads;
- child Backup jobs;
- aggregate manifest;
- partial-site failure policy;
- network restore authorization;
- site-ID/domain/path remap;
- C3/C4 Multisite restore certification separate.

## 4.25 Reset

Two separate editors/actions:
- Site Reset;
- Network Reset.

Site Reset never exposes network-wide destructive controls.

Network Reset requires:
- Super Admin/network WPE capability;
- network impact list;
- site count;
- global/shared table scope;
- verified network restore point;
- recovery principal;
- typed confirmation;
- journal/fan-out plan.

## 4.26 Import / Export

Package scope:
- Site config/data package;
- Network package.

Controls:
- source network/site identity;
- target site selection;
- UUID remap;
- user mapping;
- network resource mapping;
- conflict between network locked definition and site imported definition;
- cross-site media/provider refs;
- secrets placeholders;
- dry-run by target site;
- parent network import + child site runs.

## 4.27 Protector

Network security floor can govern:
- password/site gate allowed state;
- login protection minimums;
- rate-limit floor;
- trusted proxy policy;
- headers floor;
- XML-RPC security floor;
- allowed site relaxations.

Site cannot weaken a network-enforced security floor.

Network policy change shows affected-site count and recovery impact.

## 4.28 Watermarker

- Rule site scope;
- network template/default;
- source attachment/site ownership;
- derivative registry scope;
- network shared object/offload connection delegation;
- site delete cleanup;
- no cross-site derivative overwrite merely because physical files share storage.

## 4.29 XML-RPC

Because `xmlrpc.php` is an installation-level entry path while method behavior/integrations can be site-aware, WPE exposes:
- network security baseline;
- site compatibility/request impact where feasible;
- method/pingback policy composition;
- Protector endpoint-level rule network impact;
- Jetpack/mobile integration inventory by site;
- no site-admin `Complete Deny whole network` action.

Exact hook/request-site behavior remains runtime evidence.

## 4.30 Role & Capability Manager

Site roles:
- read/create/update/delete role for target site;
- clone role to selected sites through explicit mapping job;
- compare role across sites;
- recovery principal per target site.

Network authority:
- Super Admin management separate;
- site admin cannot create network authority;
- network policy can restrict dangerous role changes;
- site-role clone never copies user memberships implicitly unless separate action selected.

## 4.31 Platform Account / Docs / Support / Diagnostics

Logical remote-service identities:
- Network/install activation;
- Site activation/allocation where commercial package counts sites.

Controls:
- connected WPE account at installation/network level;
- site license allocation list;
- site detach vs network disconnect;
- clone detection/remediation;
- diagnostics scope: current site / network summary / selected sites;
- each diagnostics scope has separate preview/consent;
- support ticket may reference site/network pseudonymous IDs;
- telemetry remains separate opt-in if ever introduced;
- site admin cannot view network billing/account secrets unless explicitly authorized.

---

# 5. Network Admin information architecture

Candidate WPE Network Admin menu:

1. **Overview**
   - WPE version/Free+Pro status;
   - site count;
   - WPE-enabled sites;
   - module-policy exceptions;
   - failed network Jobs;
   - Backup health;
   - security/compatibility warnings.

2. **Sites**
   - site ID/domain/path/status;
   - WPE modules enabled;
   - Pro allocation status;
   - inherited policy state;
   - last WPE health event;
   - actions: inspect, apply approved template, diagnostics, Backup where authorized.

3. **Module Policy**
   - allowed/default/forced enabled/forced disabled;
   - site exceptions;
   - dependency impact;
   - staged rollout.

4. **Network Defaults**
   - settings inheritance;
   - templates/Blueprints;
   - enforced values;
   - site overrides/exceptions.

5. **Shared Connections**
   - connection safe label/provider;
   - sites authorized to use;
   - allowed capabilities;
   - health/certification;
   - no raw secrets.

6. **Jobs**
   - coordinator + child Jobs;
   - site scope;
   - progress;
   - failures/retries;
   - resource pressure.

7. **Backup**
   - site/network plans;
   - remote destination health;
   - coverage;
   - verified restore status;
   - disaster profile.

8. **Audit**
   - network/site filters;
   - actor/ability/module/result;
   - parent/child operations.

9. **Diagnostics**
   - network and per-site health;
   - consent preview for remote Support diagnostics;
   - safe export.

10. **Account & License**
   - installation/network entitlement;
   - site allocations;
   - plan limits;
   - account link/disconnect;
   - no telemetry bundling.

---

# 6. Network policy precedence

Recommended generic precedence for a policy-capable module:

`platform hard security invariant → network enforced policy → network default → site explicit override → module built-in default`

Notes:
- only modules that support these layers use them;
- site override is ignored where network policy explicitly locks the value;
- network default is not automatically equivalent to enforced value;
- security invariants cannot be weakened by network/site configuration.

---

# 7. Site lifecycle behavior matrix

| Site state/event | Default WPE reaction |
|---|---|
| Created/initialized | apply network module/default/template policy through bounded lifecycle process; do not copy live secrets blindly |
| Active | normal site-scoped runtime |
| Archived/deactivated | pause/skip site Jobs according to job type; preserve data unless policy says otherwise |
| Spam | deny/limit external/public WPE runtime according to WordPress site state; preserve auditable data |
| Restored/reactivated | re-evaluate dependencies/module policy; resume only safe Jobs |
| Deleted/uninitialized | cancel site Jobs, tombstone/clean site-owned WPE data per retention, preserve network/shared resources, audit deletion |
| Cloned/restored as new site | create new scope identity/remap; do not duplicate live service activation/secrets blindly |

Exact hook order remains runtime evidence.

---

# 8. Critical Multisite tests — future only

No Multisite fixture is authorized now.

After development consent, every module's test plan inherits relevant cases:
- user exists network-wide but is not member of target site;
- user has Admin A / Subscriber B;
- Super Admin vs Site Admin;
- network-active WPE with site module disabled;
- forced network module vs site attempted disable;
- target site deleted during queued Job;
- site switch exception + restoration;
- cache entry from A queried on B;
- site-specific Membership/cache revoke;
- site REST request with forged other `site_id`;
- shared Connection use without secret reveal;
- site Backup restore preserving global users/network policy;
- network Backup/Reset high-risk confirmations;
- import package scope escalation attempt;
- site clone activation/secret behavior;
- network job fan-out at scale.

Cross-site authorization/cache leakage is a release blocker.

---

# 9. Status

This matrix completes the **product-option Multisite behavior contract** for all 31 surfaces.

It does **not** decide:
- physical global vs per-site WPE tables;
- exact WP hooks;
- exact React Network Admin implementation;
- concrete Job backend behavior;
- Membership/cache schema;
- Backup restore implementation;
- remote-service licensing API.

Those remain consent-gated evidence/implementation work.
