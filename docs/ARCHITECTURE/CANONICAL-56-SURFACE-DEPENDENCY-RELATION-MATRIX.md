# WPEssential — Canonical 56-Surface Dependency & Relation Matrix

Status: **Canonical post-P0 dependency graph / planning-only / no development authorization**  
Date: **2026-08-29**

## 1. Goal

Make module relationships explicit enough that implementation cannot create circular boot dependencies, peer-private storage access or duplicate engines.

Dependency vocabulary:
- **Shared required** — platform/domain contract required by the surface but not another user-facing module UI.
- **Peer hard** — another numbered surface whose runtime semantics are genuinely required for this product function. Keep rare.
- **Peer soft** — optional integration activated only if the peer is available.
- **Consumer** — typical surfaces/systems consuming this surface.
- **Forbidden coupling** — dependency/bypass that must not exist.

Default law: **prefer shared-contract dependency over peer hard dependency**.

## 2. Matrix

| # | Surface | Shared required | Peer hard | Important peer soft integrations | Forbidden coupling |
|---:|---|---|---|---|---|
| 1 | CPT | Definitions, Policy, Ability/Event, WP registration adapter | none | 2,3,4,5,8,9,17,22,51 | private Fields/Relations/Listing/Form copies |
| 2 | Taxonomy | Definitions, Policy, Ability/Event, WP taxonomy adapter | none | 1,3,8,9,17,22,51 | owning term content/order engines |
| 3 | Fields | Field Schema, Definitions, Data Source, Policy | none | 1,2,4,7,12,14,17,22,28,42,53 | relation engine, source-value ownership, arbitrary eval |
| 4 | Relations | Data Source, Definitions, Policy, integrity contracts | none | 3,6,7,9,17,22,51 | hidden field relationship storage as separate engine |
| 5 | Status | Definitions, Data Source mutation, Policy, Event | none | 1,7,17,19,20,32,systems | workflow setting state through private write |
| 6 | Query | Data Source, typed AST, Policy, Cache | none | 4,8,9,12,13,17,22,33,34,35,42,51,54 | Search34 or raw SQL bypass as hidden alternate |
| 7 | Custom Tables | DB/migration, Data Source, Policy | none | 3,4,6,8,17,22,26,36 | ledger semantics, arbitrary universal DBA console |
| 8 | Admin Columns | Data Source, Renderer, Policy | none | 1,2,3,4,6,7,28,30,51 | owning displayed data or independent query engine |
| 9 | Listings | Renderer, Data Source, Policy, Assets | none | 6,34,38,39,42,54,13,16 | source/query ownership; private page-builder data model |
| 10 | Dashboard Widgets | Renderer, Policy, Assets | none | 6,9,18,23,31,49 | remote HTTP stack, authorization by visibility |
| 11 | Admin Menu | WP admin adapter, Policy for edit access | none | 30,44,49,31 | access authorization or private redirect engine |
| 12 | Settings | Field Schema, Definitions, Data Source/options, Policy | none | 3,6,22,30,49,53,56 | Vault data copy, PHP callbacks/eval |
| 13 | Dashboard | Renderer, Context, Policy, Assets | none | 9,14,15,16,17,19,21,30,38,39,54 | source CRUD/entitlement/private profile engine |
| 14 | Profiles | User Data Source, Field Schema, Policy | none | 3,9,13,15,17,19,21,30,54 | second password/session/auth stack |
| 15 | Membership | Definitions, Policy/Entitlement, Event, Cache, Job | none | 13,14,17,19,20,23,30,40,S03,A01 billing adapter | WordPress identity/roles or external billing truth ownership |
| 16 | Builder Widgets | Component Blueprint, Renderer, Assets, Policy | none | 3,6,9,13,38,53,56,builders | copying proprietary builder documents/private renderer engines |
| 17 | Forms/Workflow | Field Schema, Conditions, Ability, Event, Job, Policy | none | 3,4,5,18,19,20,23,30,35,37,40,41,42 | direct peer storage/provider writes; separate HTTP/notification/schedule engines |
| 18 | Cron | Job Service, Event, Policy | none | 17,23,24,33,41,47,48 | owning business action semantics/reservation locks/direct HTTP policy |
| 19 | Notifications | Event, Context, Policy, Job | none | 17,20,21,23,33,37,40 | private email/webhook transport engines |
| 20 | Emails | Email renderer, Field/DVR, Policy, Job | none | 19,23,40,53,28 | generic notification recipient engine/provider delivery claims |
| 21 | Chat | Policy, Job, Protected Asset refs, Event | none | 13,14,19,30,34 | notification/email replacement or ID-only auth |
| 22 | REST API | Ability, Query/Data Source, Policy, Rate/Cache | none | 6,17,23,30,41,43 | generic CRUD/auth bypass or private source mutation |
| 23 | Connections/Webhooks | Vault, Safe HTTP, Job, Rate, Event Inbox | none | 17,18,19,20,22,24,33,34,40,41,42,43,47,55 | business-domain truth, sync cursor/conflict, workflow branching |
| 24 | Backup | Job, Vault, Provider adapter, audit | none | 18,23,25,26,45,48,55 | staging identity, reset semantics, provider outcome guess |
| 25 | Reset | Policy, Job, audit, recovery journal | none | 24,48,31 | own backup engine, unverified destructive success |
| 26 | Import/Export | Data Source, Definitions, mapping, Job, Policy | none | all definition owners,23,24,45,55 | recurring sync/cursor or environment clone semantics |
| 27 | Protector | Policy, Rate/Abuse, request/auth hooks, Audit | none | 29,30,44,49,52,23 | malware scanner, UI hiding as access, generic redirect engine |
| 28 | Media Operations | WP Media adapter, Assets, Job, Policy | none | 3,9,16,20,38,40,45,47,49,53,56 | font registry, global transform grammar, source content ownership |
| 29 | XML-RPC | WP XML-RPC adapter, Policy, Rate/Audit | none | 27,30,31 | claiming complete security firewall ownership |
| 30 | Roles | WordPress roles/caps, Policy/Audit, recovery | none | virtually all surfaces consume capabilities; 15,27,49 | membership entitlement/resource Policy/UI visibility |
| 31 | Platform | Module Registry, diagnostics, account/license, docs/support | none | all module health summaries | business logic or duplicate module settings |
| 32 | Solutions | Definitions, dependency graph, Import/Export contract, Policy | none | **all 1–56**, adapters, S01–S06 | private runtime/storage or copied module definitions |
| 33 | Analytics | analytics event/session store, Policy/privacy, Job | none | 6,9,13,19,34,35,38,39,43 | Audit/Event Bus as warehouse or client event authorization |
| 34 | Search | Search index contract, Data Source/Policy, Job | none | 6,9,13,33,35,38,42,44,54 | structured Query replacement/source truth/Policy bypass |
| 35 | Decision | typed formula grammar, DVR, S05, Policy for execution context | none | 6,17,33,34,36,37,38,39,41,42,systems | Policy/mutation authority, arbitrary eval |
| 36 | Ledger | S05, transaction/idempotency, Policy, Audit | none | 6,17,33,35,37,40,41,A01/systems | reservation engine, direct external settlement/order truth |
| 37 | Reservations | transaction/lock/idempotency, Context, Policy, Job | none | 13,17,18,19,35,36,42,A01 | Cron locks, payment/order/ledger truth |
| 38 | Placement | Renderer/Assets/Context/Policy | none | 9,13,16,33,34,39,42,49,54,A01 | authorization, private renderer/data source |
| 39 | Experiments | assignment/exposure contracts, Context, Policy/privacy | none | 33 metrics,38 placements,9/13/16 consumers | consent/access/causal certainty |
| 40 | Documents | Renderer, S03 Protected Asset, Policy, Job, S05 as needed | none | 3,6,17,19,20,28,35,36,53,23 external signing | source-business/payment/legal authority |
| 41 | Sync | Job, Event, idempotency, Policy, S02 reconcile | none | 6,18,23,26,35,45,55,all Data Sources | source truth by copy, one-time import semantics, credential ownership |
| 42 | Geo | geometry types, Context, Cache/Rate, Policy | none | 3,6,9,23,34,35,37,38,41 | identity/address/serviceability authority |
| 43 | AI | Vault, Policy, Ability allowlist, Audit, Rate/Budget | none | **all surfaces by approved tasks**,34 retrieval,23 provider transport | direct private methods/SQL/PHP/secrets/authorization |
| 44 | Redirects | Definitions, request routing adapter, Policy, Cache | none | 11,27,34,45,47,50,55 | access-control semantics or scattered redirect tables |
| 45 | Transform | typed transform grammar, Data Source, Job, S01, Policy | none | 24,26,28,44,47,48,55 | environment migration ownership, arbitrary SQL/PHP |
| 46 | Fixtures | Field/Data Source schemas, deterministic seed, Job, Policy | none | 3,4,7,26,32,45 | production/business truth or private target writer |
| 47 | Link Health | Safe HTTP23, Job, Query/occurrence registry, Policy | none | 19,33,44,45,28,31 | executing fixes privately, classifying inconclusive as broken |
| 48 | DB Maintenance | DB diagnostics, Job, Policy, owner cleanup hooks | none | 24,25,45,31,all owners as candidate sources | deleting domain data or reset/backup ownership |
| 49 | Admin Theme | WP admin design adapter, Assets, Policy | none | 10,11,31,53,50 | authorization, frontend theme source, independent font store |
| 50 | Safe Script | Assets, Conditions, Policy, CSP/consent contracts | none | 38,44,49,56,WLB/HFC profiles | PHP/eval/SQL/shell/server source editor |
| 51 | Content Order | Definitions, Data Source refs, Policy, version/concurrency | none | 1,2,6,8,9,17,34,54 | entity clone engine, query sort engine, hierarchy owner |
| 52 | Security Scanner | file/package provenance, Job, provider feed adapter, Policy/Audit | none | 24 recovery,27 hardening,31 reporting,48 diagnostics | request authorization or destructive repair without recovery |
| 53 | Fonts | Asset Registry, provider adapter, Policy/licensing metadata | none | 9,12,16,20,28,38,40,49,56 | legal license authority, presentation module private font stores |
| 54 | User Stores | user/guest identity, Data Source, Policy, Cache | none | 6,9,13,14,15,33,34,38,A01 | cart/order/payment/entitlement truth |
| 55 | Staging | environment identity, Job, S02, Policy | none | 23,24,26,41,44,45,48,52,56 | backup artifact ownership, copied live credentials/webhooks, same identity clone |
| 56 | Theme Workspace | WP theme APIs/filesystem package contract, Policy, recovery | none | 24,28,49,50,53,55 | Admin Theme ownership, live arbitrary PHP editor, independent font/media stores |

## 3. Canonical dependency direction

Preferred conceptual dependency direction:

`Kernel/Platform → shared contracts/domain primitives → canonical surface → optional peer contract/adapters → Solution Blueprint/system experience`

A peer surface can reference/invoke another peer's public contract but MUST NOT import/boot against private implementation classes when an interface/Ability/Event/Data Source contract exists.

## 4. Hard dependency audit

At current product-design level, **no peer user-facing module is intended to be a universal hard boot dependency of another peer module**. Hard semantics normally rely on shared contracts so modules can degrade independently.

Examples:
- Forms requires Field Schema primitives, not the Fields UI module.
- Listings requires Renderer/Data Source/Query capability, not necessarily Query Builder UI.
- Dashboard requires Renderer/Policy, not Membership.
- Reset requires a verified restore-point capability for high-risk profiles, which can be fulfilled by Backup or a certified compatible provider; Reset UI does not own Backup.
- Theme Workspace can function without Admin Theme; each owns different presentation domain.

Any implementation proposal introducing a new peer hard dependency must document why a shared interface cannot express the dependency.

## 5. Cycle-risk audit

High-risk apparent cycles and their required breakpoints:

### Forms ↔ Workflow ↔ Notification/Email
Forms and Workflow share Surface17 product family but remain internally layered. Notification19 and Email20 are downstream services; notification events may trigger Workflow only through Event contracts, not direct recursive service calls.

### Membership ↔ Profile ↔ Roles ↔ Dashboard
Membership15 consumes user/role/context and Dashboard/Profile render membership-aware experiences, but:
- Roles30 remains authorization-role owner;
- Profile14 remains presentation;
- Dashboard13 remains portal;
- Membership15 owns entitlement.
No boot cycle is required.

### Query ↔ Search ↔ Listings
Search34 builds/queries indexes from Data Sources; Listings9 renders either Search or Query6 results. Query6 does not depend on Listings; Search34 does not depend on Listings.

### Backup ↔ Reset ↔ Staging
Reset25/Staging55 may request Backup24 capability. Backup must not depend on Reset/Staging to create an artifact. Staging may consume Backup but does not redefine it.

### Link Health ↔ Redirect ↔ Transform
Link47 detects and proposes. Redirect44/Transform45 execute. Redirect/Transform may emit change events for Link recheck but do not call Link private services.

### AI ↔ every module
AI43 consumes registered Ability/evidence contracts. No other surface hard-depends on AI to execute canonical business behavior.

### Blueprint ↔ every module
Solution32 composes manifests from registered module descriptors. Numbered surfaces do not hard-depend on Solution32 to operate independently.

## 6. Disable/degraded relation rules

When an optional peer is disabled:
- owner definition remains intact;
- reference is marked degraded/unsupported;
- consumer cannot silently copy/fork the peer semantics to stay "working";
- user gets owner/dependency recovery route;
- runtime falls back only to an explicitly specified safe fallback.

Examples:
- Search disabled -> Listing bound to Search is degraded; it cannot silently reinterpret Search definition as Query.
- Connections disabled -> webhook/remote action cannot silently make raw HTTP itself.
- Fonts disabled -> consumer may use certified system/default font fallback, not copy font binary into private storage.
- Workflow disabled -> Form may still submit/store if its form semantics allow, but workflow actions stop explicitly.

## 7. Dependency graph implementation gate

Before code, the Module Registry manifest for every surface must declare:
- shared service contracts required;
- optional peer capabilities by stable interface/Ability family;
- provider adapters;
- degraded mode;
- disable result;
- dependency health checks;
- no private-table/class access.

A static graph check must reject direct cycles and undocumented peer dependencies.
