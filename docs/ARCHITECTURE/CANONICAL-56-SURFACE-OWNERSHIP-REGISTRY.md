# WPEssential — Canonical 56-Surface Ownership Registry

Status: **Post-P0 canonical integration map / planning-only / no development authorization**  
Date: **2026-08-29**

## 1. Purpose

This is the current canonical **surface identity and ownership index** for the accepted 56-surface WPEssential product.

It exists because historical catalog documents were created at different scope milestones and are intentionally preserved as historical snapshots. Those files remain useful specifications, but they MUST NOT be used to infer current surface numbers, current ownership, or current navigation when this registry says otherwise.

Rules:

- one surface ID has one canonical semantic owner;
- a feature may be consumed by many surfaces, but its canonical semantics/storage/runtime remain with its owner;
- shared platform services are internal contracts, not extra user-facing modules;
- competitive parity namespaces are evidence/profile overlays, not independent runtimes unless this registry explicitly lists a surface;
- Solution Blueprints are compositions of these surfaces/services/adapters and MUST NOT create hidden private engines;
- no entry below grants implementation authorization.

## 2. Canonical 56 surfaces

| # | Canonical surface | Canonical key | Primary semantic ownership | Does NOT own |
|---:|---|---|---|---|
| 1 | Custom Post Types Builder | `cpt` | WPE-managed CPT definition lifecycle and safe WordPress registration adapter | post content records, fields, relations, listings, workflows |
| 2 | Taxonomy Builder | `taxonomy` | WPE-managed taxonomy definition lifecycle and object-type binding | term content, arbitrary term ordering, generic fields |
| 3 | Custom Fields Builder | `fields` | field/group definition UX over shared Field Schema primitives | source record ownership, relation engine, complex formula engine |
| 4 | Relations Builder | `relations` | relation definitions, relation-edge/pivot semantics where WPE storage is selected | generic fields/query source records |
| 5 | Status Manager | `status` | status/state-machine definitions and transition semantics | workflow side-effect orchestration |
| 6 | Custom Query Builder | `query` | typed structured query AST/parameters/projection/filter/sort/pagination definitions | search indexing/relevance, source records, arbitrary SQL runtime |
| 7 | Custom Tables Builder | `tables` | WPE-managed custom table schema/migrations/rows | generic relation semantics, ledger semantics, arbitrary DBA console |
| 8 | Admin Columns Builder | `columns` | list-table column/view/filter/edit presentation definitions | source data, query/search engines |
| 9 | Dynamic Listings / Template Builder | `listings` | reusable data-display/listing definitions and renderer composition | source data/query truth, full page-builder runtime |
| 10 | Dashboard Widgets Manager | `dashboard-widgets` | WordPress Dashboard widget inventory/presets/custom widget definitions | generic placement engine, authorization |
| 11 | Custom Admin Menu Builder | `admin-menu` | wp-admin menu presentation/order/labels/visibility profiles | authorization, canonical URL redirect engine |
| 12 | Settings Page Builder | `settings` | settings-page IA/sections/storage binding and page value policy | field type engine, secrets, arbitrary PHP callbacks |
| 13 | Dashboard Builder | `dashboard` | frontend portal shell/routes/navigation/page composition | profile data, membership entitlement, source CRUD semantics |
| 14 | User Profile Builder | `profiles` | profile/account presentation and governed user-update compositions | password/session stack, membership/role truth |
| 15 | Membership System | `membership` | plans, enrollments, entitlements, access-rule compositions, teams/seats/invites/provider links | WordPress user identity, role truth, external billing truth |
| 16 | Builder Widgets Builder | `builder-widgets` | neutral Component Blueprint + builder registration adapters | listing/query/source truth, arbitrary builder document cloning |
| 17 | Forms & Workflow Builder | `forms-workflows` | forms/submissions plus workflow graph/orchestration through typed actions | scheduling clock, notification/email transport, HTTP connection engine |
| 18 | Cron Job Builder | `cron` | schedule definitions, WP-Cron inspection and scheduling of registered jobs/abilities | workflow business logic, direct private HTTP engine, reservation locks |
| 19 | Notification System | `notifications` | notification rules/occurrences/recipient preferences/channel orchestration | email template engine, webhook transport implementation |
| 20 | Emails Builder | `emails` | email-safe template/rendering and WPE email send evidence | generic notification routing, mailbox/provider truth |
| 21 | Message & Chat System | `chat` | conversations/messages/participants/read state | email/notification engine, authorization outside object Policy |
| 22 | REST API Builder | `rest-api` | custom typed REST endpoint definitions bound to approved Query/Data Source/Ability contracts | generic CRUD bypass, authentication authority |
| 23 | Webhooks & Connections Manager | `connections` | credentials references, Safe HTTP, inbound/outbound webhook transport and connection lifecycle | sync conflict/cursor semantics, workflow branching |
| 24 | Backup Manager | `backup` | backup plans/manifests/artifacts/destination/restore verification | staging environment lifecycle, reset semantics |
| 25 | Reset Manager | `reset` | reset profiles/impact/run/recovery coordination | backup storage engine, staging/migration |
| 26 | Import / Export | `import-export` | portable config/data package/run/mapping/import-export lifecycle | recurring sync, arbitrary transform engine, staging clone lifecycle |
| 27 | Protector | `protector` | access-protection/hardening/request-gate policies | malware/vulnerability scanner, menu visibility, URL routing truth |
| 28 | Media Operations — Watermark / Performance / Replacement | `media` | media derivative rules, delivery/performance policies and attachment replacement lifecycle | font registry, generic search/replace engine, source content records |
| 29 | XML-RPC Manager | `xml-rpc` | XML-RPC method/exposure/compatibility policy | generic firewall/security scanner |
| 30 | Role & Capability Manager | `roles` | WordPress role/capability definitions, assignments, provenance and anti-lockout | Membership entitlement, resource Policy, UI visibility |
| 31 | Platform Surfaces | `platform` | Home, Modules, diagnostics, docs/support/account/license and shared platform governance UI | module business logic |
| 32 | Solution Blueprint & Application Composer | `solutions` | blueprint composition/install/upgrade/drift of canonical definitions | private copies of module engines/data |
| 33 | Analytics, Event Tracking & Journey Intelligence | `analytics` | behavioral analytics events/sessions/metrics/funnels/cohorts/attribution | Audit log, authorization, operational Event Bus truth |
| 34 | Search & Indexing Engine | `search` | search indexes/analyzers/relevance/facets/autocomplete/search analytics hooks | structured Query ownership, source truth, authorization |
| 35 | Decision, Formula, Scoring & Ranking Studio | `decision` | deterministic formula/score/decision/ranking definitions and traces | Policy/authorization, ledger mutation, arbitrary eval |
| 36 | Ledger, Balance & Movement Engine | `ledger` | append-oriented movements/postings/holds/balance projection/reconciliation | provider settlement, Woo order/payment truth, reservations |
| 37 | Resource Scheduling, Availability & Reservation Engine | `reservations` | resources/calendars/availability/holds/reservations/capacity | Cron scheduling, payment truth, ledger balance |
| 38 | Experience Placement & Personalization Manager | `placement` | registered placement slots/rules/audience/frequency/personalization composition | authorization, renderer/source truth |
| 39 | Experimentation & Feature Rollout Manager | `experiments` | experiment assignment/exposure/variant/rollout semantics | consent, entitlement, causal certainty |
| 40 | Documents, Records & Template Generation | `documents` | document templates/rendering/artifact/issued-record/version/amendment lifecycle | source business truth, legal-signature authority, payment/order truth |
| 41 | Data Sync, ETL & Integration Pipelines | `sync` | recurring full/incremental/bidirectional sync, cursor/conflict/reconciliation semantics | one-time import ownership, connection credentials, source truth by default |
| 42 | Geospatial, Location & Territory Engine | `geo` | typed locations/geocoding/geometry/territory/distance/service-zone semantics | verified identity/address authority, guaranteed serviceability |
| 43 | AI Gateway, Knowledge & Copilot Studio | `ai` | provider/model/prompt/knowledge/retrieval/budget/AI-run governance and typed-action drafting | authorization, arbitrary PHP/SQL, hidden provider bypass |
| 44 | URL Redirection & Routing Manager | `redirects` | canonical URL redirect/routing rule definitions, simulation, chains and route transformations | access enforcement, search index, menu visibility |
| 45 | Search, Replace & Data Transformation | `transform` | scoped typed search/replace/transform plans, dry-run and reversible mutation coordination | migration environment lifecycle, arbitrary SQL/PHP |
| 46 | Dummy Data & Fixture Studio | `fixtures` | synthetic fixture definitions/generation plans/provenance | production truth, Blueprint runtime, import transport |
| 47 | Link Health & Crawl Intelligence | `link-health` | occurrence discovery/crawl/check result provenance/health classification/fix-plan proposals | redirect/replace/media mutation execution |
| 48 | Database Maintenance & Cleanup | `db-maintenance` | physical cleanup/maintenance candidate analysis and bounded maintenance plans | domain record deletion authority, Reset, Backup |
| 49 | Admin Theme, Branding & Experience Manager | `admin-theme` | wp-admin/login visual branding/theme tokens/accessibility assignments | authorization, frontend theme source, arbitrary CSS/JS runtime |
| 50 | Safe Script, Tag & Code Injection Manager | `safe-script` | browser-side typed JS/CSS/HTML/meta/tag placement under consent/CSP/SRI/environment controls | PHP/eval/SQL/shell/server execution, theme source files |
| 51 | Content Order & Sequence Manager | `content-order` | persistent contextual entity/manual sequence semantics and ordering adapters | entity clone semantics, hierarchy ownership, generic query sorting |
| 52 | Security Integrity, Malware & Vulnerability Scanner | `security-scanner` | integrity/malware/vulnerability/reputation evidence and remediation plans | Protector request policy, certainty without provenance, destructive repair without recovery |
| 53 | Font Library, Typography & Delivery Manager | `fonts` | font family/variant/axis registry, delivery, subsetting, preload, provenance/license metadata | legal licensing authority, admin-theme/theme source ownership |
| 54 | User Data Stores, Favorites & Collections | `user-stores` | favorites/bookmarks/wishlists/compare/recent/custom user collection state | Membership entitlement, Woo cart/order/payment/stock truth |
| 55 | Staging, Clone & Migration Manager | `staging` | environment topology, staging clone/push/pull/migration/cutover/drift lifecycle | Backup artifact truth, Search/Replace semantics, provider identity cloning |
| 56 | Theme Workspace, Child Theme & Theme Customization Manager | `theme-workspace` | parent/child theme analysis, child-theme source/declarative customization, drift/package/activation recovery | live arbitrary PHP editor, admin theme, font registry, Safe Script runtime |

## 3. Internal shared services — not extra surfaces

These services MAY be used by many modules, but they MUST NOT become hidden user-facing systems with their own unrelated business flows.

### Core platform/domain services
- Module Registry / Service Registry;
- Definition Repository + revisions/dependency graph;
- Entity / Data Source Registry;
- Field Schema Registry;
- Relation engine contract;
- Query compiler/executor contracts;
- Conditional Logic Engine;
- Dynamic Value / Token Resolver;
- Renderer;
- Policy/Capability Engine;
- Ability Registry;
- Event Bus;
- Job Service;
- Audit/Observability;
- Secrets Vault;
- Asset Registry;
- Integration Registry;
- shared Cache and Rate/Abuse controls.

### Accepted shared-service enhancements
- `S01` Simulation & Historical Replay;
- `S02` Transaction / Saga Coordination;
- `S03` Protected Asset Service;
- `S04` Context Resolver;
- `S05` Money / Decimal / Unit Type Library;
- `S06` Approval Policy Profile;
- `S07` Product Discovery & Pre-Development Planning Orchestrator;
- `S08` Market Intelligence & Capability Radar.

`S07/S08` are planning/research services and are not ordinary production business-flow engines.

## 4. Domain adapters — not extra surfaces

### `A01` WooCommerce Commerce Domain Adapter

A01 exposes WooCommerce facts/actions through WPE Data Source/Ability/Event/Placement contracts. WooCommerce remains commerce truth. A01 MUST NOT become a second cart/order/payment/tax/shipping/inventory engine.

Future domain/provider adapters follow the same rule: adapter != domain authority.

## 5. Competitive-parity namespaces are NOT independent modules

The following evidence namespaces/profile overlays MUST compile into the listed canonical owners rather than create separate runtimes or menus:

| Overlay | Canonical ownership mapping |
|---|---|
| `MPR` | 15 Membership + 14 Profile + 17 Forms + 30 Roles + 27 Protector as explicitly mapped |
| `RPR` | 30 Roles/Capabilities; Policy remains shared platform authority |
| `ATM` | 49 Admin Theme |
| `MDP` | 28 Media Operations |
| `STM` | 50 Safe Script |
| `BKX` | 24 Backup |
| `MRL` | 28 Media Operations; reference mutations delegate to 45 Transform |
| `PBX` | 14 Profile with 17 Forms / 15 Membership / 30 Roles / OAuth adapters as consumers |
| `JEX` | existing owners 3 Fields, 4 Relations, 6 Query, 7 Tables, 9 Listings, shared DVR/Conditions/Reference Data, 32 Blueprint |
| `LHX` | 47 Link Health; fixes delegate to 44/45/28 as applicable |
| `HFC` | 50 Safe Script |
| `UAF` | 53 Fonts |
| `MIG` | 55 Staging plus 45 Transform, 24 Backup, 26 Import/Export, 23 Connections according to operation |
| `WLB` | composite profile only: 49 branding, 10 dashboard welcome, 11 menu presentation, 44 auth-destination redirects, 27 force-login/rate/login-alias, OAuth/Profile for social login, 50 browser snippets |
| `DUP` | cross-surface Entity/Definition Clone contract; source-owning module owns the duplicate action; 51 only owns copied order/sequence semantics |
| `ALX` | shared Audit/Observability + Platform diagnostics UI; NOT Analytics 33 |
| `MBX` | 3 Fields plus 4 Relations/7 Tables/12 Settings/14 Profile/17 Forms/22 REST/16 builder adapters as mapped |
| `THM` | 56 Theme Workspace |
| `RSX` | 25 Reset; restore point uses 24 Backup capability |
| `RDX` | 12 Settings + shared Field Schema; fonts delegate to 53; typed CSS output is declarative, not PHP |
| `CPTX` | 1 CPT + 2 Taxonomy; listing/filter/columns consume 9/6/8; Multisite uses shared scope model |

### DUP clarification

`DUP` was introduced during a parity expansion adjacent to Surface 51. **It MUST NOT create an entity-cloning runtime inside Content Order.**

Canonical rules:
- duplicate a module definition -> owning module + Definition Repository duplicate/revision contract;
- duplicate a content/entity record -> owning Data Source adapter executes an explicit Clone Plan;
- fields/meta -> 3/shared Field Schema mapping;
- relations -> 4;
- media -> 28;
- hierarchy/order -> 51 only for the order/sequence portion;
- cross-type remap -> typed Data Source mapping;
- no generic PHP callback cloning.

## 6. Current numbering authority

Historical `docs/MODULE-CATALOG.md` predates later scope insertions/expansions and MUST be treated as a historical feature catalog, not the current surface-number authority.

Current surface numbers are those in this registry and the accepted scope ADR lineage:

`31 → 43 → 48 → 50 → 55 → 56`.

Any implementation manifest, route, capability registry, telemetry dimension, license entitlement or documentation link that needs a surface ID MUST use this registry.

## 7. Acceptance rule

No future module, option, Blueprint, UI page, Ability, event, migration, AI tool or provider adapter may introduce a new semantic owner without first updating this registry and passing the ownership/no-bypass audit.
