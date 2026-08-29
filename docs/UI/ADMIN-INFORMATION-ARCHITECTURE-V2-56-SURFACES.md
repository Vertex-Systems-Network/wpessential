# WPEssential — Admin Information Architecture V2 — 56 Canonical Surfaces

Status: **Canonical post-P0 UI map / planning-only / no development authorization**  
Date: **2026-08-29**

## 1. Purpose

Map every accepted WPEssential surface into one coherent admin application so:

- all **56/56 surfaces appear exactly once** as canonical navigation owners;
- related functionality is discoverable without creating duplicate module pages;
- cross-module actions open/reference the canonical owner;
- internal shared services and domain adapters do not accidentally become duplicate modules;
- Free/Pro/degraded states stay understandable;
- ordinary navigation remains maximum 3 levels deep.

This V2 supersedes the original 31-surface admin IA for current navigation decisions. The original remains a historical design snapshot.

## 2. Global shell

Single WordPress top-level menu: **WPEssential**.

Global shell persistent destinations:

1. Home
2. Modules
3. Solutions
4. Content & Schema
5. Data & Intelligence
6. Experience & Presentation
7. Identity & Access
8. Automation & Communication
9. Integrations & Data Movement
10. Operations & Security
11. Developer & AI
12. Documentation / Support / Account through Platform surface

### Global utilities
- command/search palette — permission-filtered;
- current site/network context;
- environment indicator;
- module/dependency health;
- background-job/failure indicator;
- documentation/help;
- account/license summary;
- user menu.

Global utilities are shell functions, not independent semantic owners.

## 3. Exact suite-to-surface map

### Suite A — Solutions

| Surface | UI label | Primary screens |
|---:|---|---|
| 32 | Solutions / Application Composer | Blueprint Library, Blueprint Editor, Dependency Map, Install/Map Wizard, Installed Solutions, Drift/Upgrade, Variables, Validation |

Rule: a Solution never embeds private editors for Fields/Queries/Workflows/etc. It references or quick-creates canonical definitions through their owning surfaces.

### Suite B — Content & Schema

| Surface | UI label | Primary screens |
|---:|---|---|
| 1 | Post Types | All Types, Add/Edit, Labels, Visibility, Supports, Rewrite, REST, Capabilities, Dependencies, Revisions |
| 2 | Taxonomies | All Taxonomies, Add/Edit, Labels, Object Types, Rewrite, REST, Capabilities, Dependencies |
| 3 | Fields | Field Groups, Field Canvas, Locations, Validation, Storage, Permissions, Revisions, Usage |
| 4 | Relations | Relations, Endpoints, Cardinality, Pivot Fields, Delete Policy, Data Browser, Integrity |
| 5 | Status & State | Statuses, State Machines, Transitions, Guards, History, Usage |
| 51 | Content Order | Sequence Definitions, Contexts, Manual Order, Hierarchy/Sibling Policy, Term Order, Query Bindings, Conflicts, Revisions |

#### Cross-links
- CPT → Add Field Group / Relation / Status / Order opens owner surface with CPT context.
- Taxonomy → Add Fields / Term Order opens owner surface.
- Relations pivot-field editor uses the shared Field Schema component but persists through Surface 4 relation definition.

### Suite C — Data & Intelligence

| Surface | UI label | Primary screens |
|---:|---|---|
| 6 | Queries | Queries, Source, Filters, Projection, Relations, Sort, Pagination, Cache, Preview/Explain |
| 7 | Custom Tables | Tables, Schema, Columns, Indexes, Data Browser, Migrations, Import/Export, Diagnostics |
| 8 | Admin Columns | Column Sets, Columns, Sort/Filter, Inline/Bulk Edit, Export, Conditions, Performance |
| 9 | Listings & Templates | Listings, Source, Layout, Item Template, Controls, Pagination, Render/SEO, Usage |
| 33 | Analytics & Journeys | Event Catalog, Tracking, Metrics, Funnels, Cohorts, Attribution, Dashboards, Data Quality |
| 34 | Search & Indexing | Indexes, Sources, Analyzer, Synonyms, Ranking, Facets, Rules, Search Logs, Reindex |
| 35 | Decisions & Formulas | Formulas, Scorecards, Decision Tables, Ranking, Thresholds, Simulation, Explain Trace |
| 36 | Ledger & Balances | Ledgers, Accounts, Posting Types, Holds, Transactions, Balances, Reconciliation, Statements |
| 42 | Geo & Territories | Locations, Geocoding Profiles, Territories, Service Zones, Spatial Queries, Provider/Quota, Diagnostics |
| 54 | User Stores | Store Definitions, Favorites/Wishlists/Compare, Guest Merge, Sharing/Teams, Query Integration, Privacy |

#### Distinctions shown in UI
- Query Builder shows **structured query**; Search shows **index/relevance**; Content Order shows **persistent sequence**.
- Analytics screens never present Audit records as behavioral events by default.
- Ledger shows movement/account semantics and does not expose Custom Tables as an alternate balance editor.

### Suite D — Experience & Presentation

| Surface | UI label | Primary screens |
|---:|---|---|
| 10 | Dashboard Widgets | Inventory, Presets, Widget Builder, Conditions, Layout, Schedule, Diagnostics |
| 11 | Admin Menu | Profiles, Tree Editor, Item Editor, Visibility, Destination Redirect References, Recovery |
| 12 | Settings Pages | Pages, Placement, Sections/Tabs, Fields, Storage, Permissions, Frontend Exposure, Revisions |
| 13 | Frontend Dashboards | Dashboards, Routes, Navigation, Page Composition, Access Explain, Responsive, Usage |
| 14 | User Profiles | Profile Templates, Sections/Tabs, Fields, Account Actions, Public Profile, Permissions, Usage |
| 16 | Builder Widgets | Components, Controls, Render, Styles, Assets, Adapter Compatibility, Preview |
| 28 | Media Operations | Overview, **Watermark**, **Delivery & Performance**, **Replacement**, Derivatives, CDN/Offload, Diagnostics |
| 38 | Placement & Personalization | Slots, Experiences, Rules, Audience, Frequency, Schedule, Fallback, Exposure Diagnostics |
| 39 | Experiments & Rollouts | Experiments, Variants, Assignment, Metrics Links, Rollout, Guardrails, Decision Record |
| 49 | Admin Theme & Branding | Themes, Tokens, Typography Reference, Login Branding, Assignments, Accessibility, Revisions, Compatibility |
| 53 | Fonts & Typography | Font Library, Families/Faces/Axes, Upload/Providers, Assignments, Delivery, Subset, License/Provenance, Performance |
| 56 | Theme Workspace | Theme Inventory, Analyzer, Child Themes, CSS Workspace, theme.json, Templates/Parts, Assets/Fonts refs, Drift, Preview, Package, Activation Recovery |

#### Presentation ownership law
- Font selectors anywhere reference Surface 53 font IDs.
- Theme Workspace references Surface 28 media and Surface 53 fonts.
- Admin Theme is wp-admin/login presentation only.
- Safe runtime browser snippets are Surface 50, not Theme Workspace.
- Placement injects registered components/experiences but never bypasses the source renderer/Policy.

### Suite E — Identity & Access

| Surface | UI label | Primary screens |
|---:|---|---|
| 15 | Membership | Overview, Plans, Access Rules, Members/Enrollments, Entitlements, Teams/Seats, Invitations, Billing Links, Reconciliation, Migration |
| 27 | Protector | Protection Profiles, Private/Maintenance Access, Login/Request Hardening, IP/Proxy, Headers, Recovery, Access Logs |
| 30 | Roles & Capabilities | Roles, Capabilities, Assignments, Compare, Provenance, Policy Links, Network/Super Admin, Recovery/Test-as-role |

#### Identity/access law
- Role != membership != resource Policy.
- UI/menu/placement visibility is never authorization.
- Protector decides request/access hardening; Redirects 44 owns generic route redirect semantics.

### Suite F — Automation & Communication

| Surface | UI label | Primary screens |
|---:|---|---|
| 17 | Forms & Workflows | Forms, Entries, Form Editor, **Workflows**, Runs, Triggers, Actions, Approvals, Failures |
| 18 | Schedules / Cron | Scheduled Events, WPE Schedules, Recurrences, Runner Health, Run History, Third-party WP-Cron Inspection |
| 19 | Notifications | Rules, Occurrences, Preferences, Channels, Deliveries, Digests, Escalations |
| 20 | Emails | Templates, Layouts, Components, Branding, Preview/Test, Delivery Evidence, Revisions |
| 21 | Chat & Messaging | Conversations, Messages, Participants, Moderation, Attachments, Retention, Embeds |
| 37 | Reservations & Availability | Resources, Calendars, Availability, Slots, Capacity, Holds, Reservations, Waitlist, External Calendar Reconciliation |
| 40 | Documents & Records | Templates, Renderer Profiles, Generate Jobs, Records, Issued Versions, Amend/Supersede, Protected Delivery, Retention, Signature/Hash Provenance |

#### Automation ownership law
- Workflow owns orchestration; Notification owns notification semantics; Email owns email rendering; Connections owns HTTP transport.
- Cron answers **when**, Job Service answers **execution mechanics**, domain Ability answers **what it means**.
- Reservation owns slot/capacity locks, never Cron.

### Suite G — Integrations & Data Movement

| Surface | UI label | Primary screens |
|---:|---|---|
| 22 | REST APIs | Endpoints, Schemas, Binding, Auth/Policy, Rate/Cache, Preview, Logs, OpenAPI Preview |
| 23 | Connections & Webhooks | Connections, Credentials References, OAuth, Inbound Webhooks, Outbound Delivery, Event Inbox, Retry/Reconcile, Safe HTTP |
| 26 | Import / Export | Config Packages, Data Import, Data Export, Mapping, Dry Run, Runs, Errors, Package Types |
| 41 | Sync & ETL | Pipelines, Source/Destination, Mapping, Full/Incremental, Webhook/Poll, Cursors, Conflicts, Dead Letters, Reconciliation |
| 44 | Redirects & Routing | Redirect Rules, Sources/Targets, Conditions, Chains/Loops, Import, Simulation, Logs/Diagnostics |
| 45 | Search / Replace & Transform | Plans, Scope, Match/Transform Rules, Dry Run/Diff, Serialized Safety, Jobs, Rollback/Reconciliation |
| 55 | Staging / Clone / Migration | Environments, Clone/Create Staging, Push/Pull Plans, Data/Files Selection, Mapping, Provider Quarantine, Drift, Cutover, Recovery |

### Adapter Center inside Integrations

Not numbered surfaces:
- **A01 WooCommerce Commerce Adapter**;
- builder/provider/storage/email/billing/geocoder/routing/etc. adapter profiles.

Adapter Center may expose detection/capabilities/connection/certification status but MUST link business configuration to the owning surface.

### Suite H — Operations & Security

| Surface | UI label | Primary screens |
|---:|---|---|
| 24 | Backup & Restore | Plans, Destinations, Backups, Verify, Restore, Schedules Ref, Provider Health, Recovery Docs |
| 25 | Reset Manager | Profiles, Impact, Restore Point, Execute, Run Journal, Recovery, Development Presets |
| 29 | XML-RPC | Current State, Methods, Policies, Rate/Abuse, Compatibility, Logs |
| 47 | Link Health | Profiles, Occurrences, Check Results, Issues, Saved Views, Fix Plans, Ignore/Snooze, Recheck, Provider Health |
| 48 | Database Maintenance | Health, Cleanup Candidates, Maintenance Plans, Dry Run, Jobs, History, Orphan/Index Diagnostics |
| 52 | Security Scanner | Integrity Baselines, Scans, Findings, Vulnerabilities, Reputation, Quarantine/Repair Plans, Post-hack Workflow, Reports |

#### Operations law
- Link Health proposes fixes; Redirect/Transform/Media owners execute them.
- DB Maintenance cannot delete domain-owned data merely from physical suspicion.
- Scanner findings do not authorize repair/quarantine without recovery/Policy.
- Backup != Reset != Staging.

### Suite I — Developer & AI

| Surface | UI label | Primary screens |
|---:|---|---|
| 43 | AI Gateway & Copilot | Providers, Models, Prompt Tasks, Knowledge Sources, Retrieval, Budgets, Evaluations, Runs, Ability Allowlist, Audit |
| 46 | Fixture Studio | Fixture Definitions, Data Generators, Scenarios, Graphs, Seeds, Preview, Packages, Generation History |
| 50 | Safe Scripts & Tags | Snippets, Placements, Conditions, Consent, CSP/SRI/Origins, Environments, Dependencies, Preview, Revisions, Emergency Pause |

Internal developer diagnostics for schemas/Abilities/events can live under Platform Developer Diagnostics but do not create new semantic owners.

### Suite J — Platform & Support

| Surface | UI label | Primary screens |
|---:|---|---|
| 31 | Platform | Home, Modules, System Status, Diagnostics, Documentation, Changelog, Support, Account & License, Update/Compatibility Health |

## 4. 56/56 exactly-once navigation checksum

Surface IDs by suite:

- Solutions: `32`
- Content & Schema: `1,2,3,4,5,51`
- Data & Intelligence: `6,7,8,9,33,34,35,36,42,54`
- Experience & Presentation: `10,11,12,13,14,16,28,38,39,49,53,56`
- Identity & Access: `15,27,30`
- Automation & Communication: `17,18,19,20,21,37,40`
- Integrations & Data Movement: `22,23,26,41,44,45,55`
- Operations & Security: `24,25,29,47,48,52`
- Developer & AI: `43,46,50`
- Platform & Support: `31`

Count = **56 unique IDs**. A future navigation change fails validation if an ID is missing or appears as canonical owner in more than one suite.

## 5. Standard module route anatomy

Normal depth:

`WPEssential → Suite → Module route`

Inside module, use tabs/subroutes/panels rather than deeper WordPress submenu trees.

Every complex module may expose these route classes as applicable:

1. `Overview` — health/setup/counts;
2. `Definitions/List`;
3. `Editor`;
4. `Operational/Observe` — runs/deliveries/findings;
5. `Integrations` — reference adapters/connections, not duplicate provider engine;
6. `Usage/Dependencies`;
7. `Revisions/Import Export`;
8. `Diagnostics`.

Do not force all classes into every module.

## 6. Universal list/editor patterns

### List
- title/description;
- primary Create action;
- search/filter/sort;
- saved views where justified;
- status/health;
- owner/scope;
- Used By/dependency indicator;
- updated metadata;
- row actions;
- bulk actions only where semantics are safe;
- pagination/cursor;
- permission/degraded state.

### Editor
Header:
- back;
- title/key/status;
- Draft/Published indicator;
- Save Draft;
- Validate/Preview/Simulate as applicable;
- Publish/Enable;
- overflow actions.

Body ordering:
1. General / Identity
2. Structure / Domain semantics
3. Data / Source
4. Rules / Conditions / Permissions
5. Side effects / Integrations
6. Scope / Lifecycle
7. Advanced / Diagnostics

Right/context area:
- scope;
- dependencies/Used By;
- Policy summary;
- revision;
- validation;
- runtime/provider health;
- docs.

## 7. Cross-module control rendering

When Surface A needs Surface B configuration:

### Allowed
- selector for B definition;
- `Create B` action that navigates to B with context prefilled;
- read-only summary of B;
- owner-supplied embedded component that writes through B's API.

### Forbidden
- copy of B's options persisted inside A;
- hidden private B-like definition in A payload;
- cross-module edit that writes B private storage directly;
- identical independent settings in two modules whose conflict is resolved by undocumented priority.

Every cross-module control should display owner context where confusion is plausible, e.g. `Search profile — managed in Search & Indexing`.

## 8. Shared service UI placement

Shared services do not get arbitrary top-level module menus.

- Policy/Capability: configured contextually; Roles surface shows role capability definitions, resource Policy bindings remain in owner screens with common component.
- Vault: connection/secret fields use Vault picker; secret inventory/health may be a protected Platform diagnostics page.
- Job Service: operational queue health in Platform; domain job/runs in their owner module.
- Audit: Platform/Audit console, with contextual audit links from modules.
- Cache/Rate Limit/Assets: owner-facing controls only when product semantics require; global diagnostics under Platform.
- S01 Simulation: invoked from owner Preview/Simulate screens.
- S02 Saga: no user-facing generic transaction builder by default; evidence visible in owner operation/run diagnostics.
- S03 Protected Asset: configured through owner file/document/profile screens; global protected asset diagnostics can live under Platform.
- S04 Context Resolver: no independent menu.
- S05 Money/Unit Library: common control/formatter registry, no competing finance module.
- S06 Approval Policy: reusable policy library may be exposed as a Workflow subview, but execution remains Workflow/owner action.
- S07/S08: planning-only governance/research surfaces, not production module navigation until separately authorized and productized.

## 9. UI access rules

- route discoverability never substitutes permission checks;
- server rejects unauthorized deep links even if route is hidden;
- command palette uses the same permission filters;
- search results do not reveal private definition names/counts;
- read-only/degraded state clearly differs from permission denied;
- Super Admin/network actions are separately signaled;
- high-risk actions expose impact/dry-run/recovery/reauth as required.

## 10. Free / Pro / dependency UX

### Free-only install
Show all licensed Free surfaces normally. Pro surfaces may appear in **Modules catalog**, but should not litter active work routes with disabled fake controls.

### Pro active
Routes appear based on module availability and permissions.

### Pro expired/degraded
- preserve definitions/data;
- read-only/export where policy allows;
- show exact cause;
- no silent deletion/authorization weakening.

### Missing optional dependency
Keep the owner module reachable; show missing integration as degraded/unsupported for only the affected capability.

## 11. Solution-system navigation

Installed Solution Blueprints may expose a **Solution Home** that links to all underlying module-owned resources as a coherent application workspace.

Example CRM Solution Home can show:
- Leads → underlying entity/listing route;
- Pipeline → status/query/listing composition;
- Forms → Surface 17;
- Automations → Surface 17 workflow;
- Analytics → Surface 33;
- Settings → Blueprint variables/bindings.

This Solution Home is a composition/navigation layer. It MUST NOT duplicate module storage or create alternate CRUD semantics.

## 12. Implementation acceptance

UI implementation cannot start until route manifests can prove:

- 56 canonical surface IDs have one canonical owner route each;
- no route registers a parity overlay as a separate module;
- contextual cross-links resolve to owner IDs;
- no hidden private mini-builder persists a second owner definition;
- routes declare capability/Policy and asset entry points;
- navigation depth and responsive/accessibility requirements remain within this contract.
