# WPEssential — Cross-Module Option Ownership & No-Bypass Contract

Status: **Canonical post-P0 integration contract / planning-only / no development authorization**  
Date: **2026-08-29**

## 1. Objective

WPEssential must behave as one composable application platform. A user may encounter similar controls in many modules, but the same semantic behavior MUST NOT be implemented independently in several places.

This contract distinguishes:

- **semantic owner** — defines what an option means;
- **UI owner** — module/screen where the user configures a local binding;
- **storage owner** — owns canonical persisted truth;
- **execution owner** — performs the effect;
- **consumer** — references/invokes the owner through a typed contract;
- **adapter** — translates an external/provider contract without becoming source truth.

A repeated label is not necessarily duplication. Duplication exists when two modules independently own the same business semantics/runtime/storage.

## 2. Mandatory option annotation model

Every implementation-time configurable option MUST be classifiable with:

- `option_key`;
- `surface_owner`;
- `semantic_owner`;
- `ui_context`;
- `storage_owner`;
- `execution_owner`;
- `scope` — site/network/user/resource/external;
- `policy_gate`;
- `consumers`;
- `side_effects`;
- `events/invalidation`;
- `import_export_owner`;
- `evidence_namespace`.

If any item cannot be assigned before implementation, the option is a planning defect and must not be invented in code.

## 3. Canonical shared option families

| Option/behavior family | Canonical semantic/execution owner | Allowed local UI binding | Forbidden duplicate behavior |
|---|---|---|---|
| definition identity/status/revisions/dependencies | Definition Repository | every definition-owning module | module-private revision engines or UUID schemes |
| site/network/resource context | `S04` Context Resolver + Multisite ownership model | every module may display relevant scope controls | trusting request `site_id`/tenant IDs as authority |
| role/capability definitions | Surface 30 Roles | modules declare required capabilities | local module role editors or UI hiding as authorization |
| resource authorization | shared Policy Engine | modules expose policy bindings/explain UI | Boolean visibility rules granting permission |
| field schema/type/validation | shared Field Schema + Surface 3 authoring UI | Forms/Settings/Profile/Tables may embed/link schema | separate incompatible field type engines |
| relations/cardinality/pivot | Surface 4 Relations | fields/forms/listings may select a relation | hidden bidirectional relation storage inside a relationship field |
| structured query/filter/projection | Surface 6 Query + Data Source Registry | listings/columns/forms/options may bind query | raw private SQL/query mini-engines |
| full-text search/index/relevance/facets | Surface 34 Search | listings/directories may bind search profile | Query Builder pretending to be search index |
| persistent manual/entity ordering | Surface 51 Content Order | module list/editor may select sequence | ad-hoc persistent order tables in each module |
| query result sort | Surface 6 Query | consumer controls parameters | storing query sort as Content Order truth |
| layout/component visual order | local UI owner | dashboard/listing/template/component editor | using Surface 51 for purely visual editor layout |
| conditions/visibility predicates | shared Conditional Logic Engine | every module may bind typed conditions | module-specific eval/condition grammar |
| dynamic tokens/values | shared DVR/Renderer | every renderable module | custom global token resolvers per module |
| formula/scoring/decision/ranking | Surface 35 Decision | Forms/Workflow/Search/Blueprints reference definitions | duplicate formula/calculation engines beyond trivial field-local validation |
| money/decimal/unit semantics | `S05` Money/Decimal/Unit Library | modules choose unit/currency profiles | float-based local money semantics |
| state/status transitions | Surface 5 Status | consumer may request transition | workflow/module directly mutating state outside Status contract when a state machine owns it |
| workflow branching/actions/retries | Surface 17 Workflow | modules emit events or invoke workflows | Notifications/Cron/Forms creating separate automation runtimes |
| approval policy | `S06` Approval Policy + Surface 17 execution | high-risk modules select approval profile | each module inventing approver/quorum/delegation semantics |
| job execution/retry/backoff/locks | shared Job Service | Cron/Workflow/Backup/Sync/etc. enqueue jobs | independent queue engines per module |
| wall-clock schedules | Surface 18 Cron/Schedule definitions | modules schedule registered job/ability | schedule timestamps being treated as resource reservation locks |
| resource availability/reservation | Surface 37 Reservation | forms/portals/commerce adapters consume | Cron or generic Workflow owning slot locks/capacity |
| notification occurrence/recipient/channel orchestration | Surface 19 Notifications | modules emit notification request/event | each module implementing recipient/digest/preference engines |
| email-safe template/rendering | Surface 20 Emails | Notification/Workflow selects template | arbitrary browser template as canonical email format |
| chat/conversation | Surface 21 Chat | dashboards/portals embed chat | notifications/messages duplicating conversation store |
| HTTP/OAuth connection lifecycle/Safe HTTP | Surface 23 Connections | modules select connection/action profile | direct `wp_remote_*`/SDK transport implemented independently by Cron/Notification/Workflow/Link/etc. |
| inbound/outbound webhook transport | Surface 23 Connections | Workflow/Notifications consume verified/delivery events | private webhook retry/signature engines elsewhere |
| custom public REST endpoint definitions | Surface 22 REST API Builder | bind Query/Data Source/Ability | endpoint executing unrestricted generic CRUD bypass |
| one-time config/data import/export | Surface 26 Import/Export | modules register types/adapters | private package formats and import runners per module |
| repeated/incremental/bidirectional sync | Surface 41 Sync/ETL | modules/providers supply connector/mapping contracts | Import/Export or Webhooks owning cursor/conflict semantics |
| URL redirect/routing | Surface 44 Redirects | Search/Admin Menu/Link Health/Protector may reference typed redirect profiles | independent redirect tables or loop engines |
| search/replace/data transform | Surface 45 Transform | Migration/Media/Import/DB tools request scoped Transform Plan | local regex/raw-SQL replacement implementations |
| synthetic/demo/test fixture generation | Surface 46 Dummy Data | Blueprint/QA/import may reference fixture profile | production module silently generating fake business truth |
| crawl/link-health classification | Surface 47 Link Health | diagnostics/admin pages display results | module-local crawler/HTTP health semantics |
| DB maintenance/physical cleanup | Surface 48 DB Maintenance | owning module supplies candidate metadata/cleanup hooks | DBM deleting domain records because they look orphaned |
| backup/restore artifacts | Surface 24 Backup | Reset/Staging/Migration request verified restore point/artifact | Reset/Staging owning separate full-backup engines |
| reset/destructive wipe semantics | Surface 25 Reset | modules may register reset scope | Backup/DBM/Import performing site reset semantics |
| staging/environment clone/cutover | Surface 55 Staging | Backup/Transform/Connections used as dependencies | Import/Export or Backup presenting clone as same environment identity |
| attachment/media derivative/performance/replacement | Surface 28 Media Operations | fields/listings/theme/docs reference media profiles | arbitrary media replacement/reference rewrite inside consumers |
| font registry/delivery/subset/preload/provenance | Surface 53 Fonts | Theme/Admin Theme/Email/Documents/Builders reference font IDs | separate uploaded-font stores per presentation module |
| admin branding/theme tokens | Surface 49 Admin Theme | Platform/Admin UI consumes | Theme Workspace or Safe Script owning wp-admin branding |
| frontend theme child/source customization | Surface 56 Theme Workspace | Fonts/Media integration references | Admin Theme or Safe Script editing theme PHP/source |
| browser-side script/tag/CSS/HTML placement | Surface 50 Safe Script | HFC/WLB/placements reference profiles | PHP/eval/server-code or parallel header/footer runtime |
| user favorites/bookmarks/wishlists/custom stores | Surface 54 User Stores | Listings/Woo/Profile reference store | membership/cart/order semantics stored as favorites |
| behavioral analytics | Surface 33 Analytics | all modules emit approved analytics signals | Audit log/Event Bus used as analytics warehouse |
| security/audit history | shared Audit/Observability | modules emit audit events | Analytics being used as immutable security audit |
| security integrity/malware/vulnerability evidence | Surface 52 Scanner | Platform/Operations displays findings | Protector pretending to scan files/vulnerability feeds |
| request/access hardening | Surface 27 Protector | login/admin/frontend paths reference policy | Security Scanner owning request authorization |
| XML-RPC policy | Surface 29 XML-RPC | Protector may impose floor | generic Protector UI claiming full XML-RPC method truth |
| ledger/balance/movement | Surface 36 Ledger | inventory/loyalty/leave/etc. use ledger profiles | Custom Tables direct balance updates |
| documents/issued records | Surface 40 Documents | Forms/Workflow/Email/Portal consume generated artifact | Emails or generic renderer becoming record/issuance authority |
| protected/private file delivery | `S03` Protected Asset | Membership/Documents/HR/etc. bind Policy | public media URL used as protected access mechanism |
| geospatial/location/territory | Surface 42 Geo | Fields/Query/Search/Scheduling use typed geo refs | map fields owning geocoder/territory engines |
| placement/personalization | Surface 38 Placement | components/listings/widgets register eligible content | template modules privately injecting global content |
| experiments/rollouts | Surface 39 Experiments | Placement/UI/feature consumers bind experiment | experiment assignment treated as access/consent |
| AI/model/prompt/knowledge/budget governance | Surface 43 AI Gateway | every module exposes approved prompt/tasks/abilities | module-specific secret/model clients or AI authorization bypass |
| Blueprint/application composition | Surface 32 Solutions | all modules register definition types/dependencies | system-specific private runtime/data stores |
| WooCommerce commerce facts/actions | `A01` Woo adapter | WPE modules consume typed Woo data/events/abilities | second WPE cart/order/payment/tax/shipping/stock engine |
| cache | shared Cache contract | modules declare keys/TTL/invalidation | module caches ignoring principal/site/revision dimensions |
| rate/abuse control | shared Rate/Abuse service | REST/Forms/Login/Webhook/etc. bind profile | inconsistent per-module counters with bypass paths |
| secrets | Vault | module UI stores secret reference only | secret copied into option/export/log/prompt |
| assets | Asset Registry | module route/component declares handles | global eager enqueue or private asset loader |
| simulation/dry-run/replay | `S01` | destructive/high-impact owners provide plans | consumer UI claiming dry-run without owner simulation evidence |
| saga/unknown outcome coordination | `S02` | Workflow/Sync/Commerce/provider actions use | treating timeout as confirmed failure or pretending distributed transaction |

## 4. High-risk overlap resolutions

### 4.1 Redirect ownership

Current product features mention redirects in Admin Menu, Search, Link Health, Protector/login UX and WLB parity.

Canonical resolution:
- **Surface 44 owns redirect/routing rule semantics, chain/loop detection and redirect execution**;
- Surface 11 Admin Menu may choose a post-login/post-logout destination by referencing a typed RDR profile; it does not store another redirect engine;
- Surface 27 Protector owns whether authentication/access is required; it may request an auth-route redirect but does not own generic routing;
- Surface 34 Search may define a zero-result/query redirect by reference to Surface 44;
- Surface 47 Link Health proposes/creates a Surface 44 Fix Plan after approval;
- OAuth callback routing remains adapter-controlled and cannot be rewritten by generic rules without an explicit compatible profile.

### 4.2 HTTP/Webhook ownership

Cron, Workflow and Notifications all need HTTP-related actions/channels.

Canonical resolution:
- Surface 23 owns connection credentials, Safe HTTP, webhook signature/auth, transport retries and delivery attempts;
- Cron schedules a registered Connections/Workflow Ability;
- Workflow invokes a typed Connections action;
- Notification's webhook channel delegates delivery to Connections;
- no peer module implements a second HTTP client policy stack.

### 4.3 Search/Replace ownership

Migration, Media Replacement, Import/Export, Theme/URL changes and DB maintenance may need replacements.

Canonical resolution:
- Surface 45 owns transform grammar, search/replace plan, serialized-value safety, dry-run/diff and mutation evidence;
- Surface 55 Migration supplies source/target/environment mapping and invokes Surface 45;
- Surface 28 Media Replacement supplies reference graph and invokes Surface 45 for approved references;
- Surface 26 Import may invoke transform profiles during mapping but does not own global site mutation;
- Surface 48 may request a scoped transform only when domain ownership permits.

### 4.4 Clone / Duplicate ownership

Do not treat `DUP` as a Content Order runtime.

- module definition duplicate -> module owner + Definition Repository;
- WordPress/data-source entity duplicate -> Data Source adapter Clone Plan;
- relation copy -> Surface 4;
- field/meta copy -> shared Field Schema/storage adapter;
- media copy/reuse -> Surface 28;
- order/hierarchy position -> Surface 51;
- cross-type mapping -> explicit typed mapping;
- source entity Policy is rechecked; target create Policy is separate.

### 4.5 Audit vs Analytics

- shared Audit/Observability = security, admin mutation, decision provenance, operational evidence;
- Surface 33 Analytics = behavioral/session/journey/metric warehouse;
- Event Bus = delivery vocabulary, not storage warehouse;
- `ALX` extends Audit/Observability UI/sinks only;
- an analytics event cannot prove authorization or immutable administrative history.

### 4.6 Query vs Search vs Link Health

- Surface 6 = structured source query;
- Surface 34 = indexed full-text/discovery/relevance;
- Surface 47 = crawl/link occurrence/health intelligence;
- Listings/UI select the appropriate provider; none silently falls back to another semantic engine.

### 4.7 Status vs Workflow

- Surface 5 owns allowed transition and canonical state mutation;
- Surface 17 owns orchestration around transition requests/events;
- a workflow cannot set a protected state by direct record mutation when the entity has a Status state machine;
- status transitions may emit events that trigger workflows after successful mutation.

### 4.8 Forms vs Fields vs Profile

- shared Field Schema defines reusable field semantics;
- Surface 3 authors reusable field/group definitions;
- Surface 17 adds form-only controls/submission lifecycle;
- Surface 14 owns user-profile view/edit composition;
- password/email/session actions use WordPress secure identity flows, never generic field writes.

### 4.9 Backup vs Reset vs Staging vs Import

- Backup = recoverable artifact/restore truth;
- Reset = destructive reset plan/run;
- Staging = environment identity/clone/migrate/cutover;
- Import/Export = packages/runs/mappings;
- Search/Replace = transformation semantics;
- a DB snapshot is not automatically a full Backup;
- clone is never the same environment identity;
- restore cannot roll back external providers.

### 4.10 Theme / Admin Theme / Safe Script / Fonts / Media

- Surface 56 owns child-theme/source/declarative frontend theme workspace;
- Surface 49 owns wp-admin/login visual branding;
- Surface 50 owns runtime browser snippets/tags;
- Surface 53 owns fonts;
- Surface 28 owns media assets/derivatives/replacement;
- no Surface 56 live arbitrary PHP editor;
- no Surface 50 filesystem/theme-source editor;
- Admin Theme never becomes authorization.

## 5. No-bypass execution laws

### 5.1 Read flow

`UI / REST / CLI / Workflow / AI → Context Resolver → Principal → Policy → Query/Data Source/Search owner → source reauthorization → Renderer → Audit/metrics as applicable`

Forbidden:
- UI reading private module tables directly;
- Search index returning data without current source Policy;
- AI reading Vault/private data because a prompt requested it.

### 5.2 Local mutation flow

`Entry → Context → Auth principal → capability → resource Policy → typed Ability → canonical domain owner → validation/version/concurrency → transaction/mutation → domain event → side effects via owning services → audit → cache/index invalidation`

Forbidden:
- consumer module writing another owner's tables/options/meta privately;
- UI handler implementing business mutation directly;
- workflow bypassing the owner's Ability.

### 5.3 Async flow

`Owner Ability → Job Service → idempotent operation identity → owner execution → durable result → event/audit`

Cron defines when; Job Service executes; domain owner decides what the operation means.

### 5.4 External/provider flow

`Domain owner → Connections/Adapter → provider request → provider outcome {success/failure/unknown} → reconciliation when required → canonical local transition → event/audit`

Timeout/connection loss after write is not automatically failure.

### 5.5 AI/MCP flow

`User intent → principal/context → AI Gateway → approved evidence → structured draft → schema validate → simulate/diff → same typed Ability + same Policy → canonical owner → audit`

AI cannot call private service methods or provider SDKs as a privileged bypass.

### 5.6 Blueprint/system flow

`Reference System → Patterns → Surface requirements → adapters/services → dependency resolution → install plan → map/reuse/create canonical definitions → dry run → review → activate definitions`

A Solution Blueprint owns composition/provenance only. It never owns hidden fields/tables/workflows/roles/query engines.

### 5.7 Destructive flow

`Request → owner impact plan → dependencies → backup/restore-point requirement → re-auth/Policy → dry-run/preview → immutable operation identity → execute owner operation → verify → recovery/reconciliation → audit`

Delete/reset/repair/migration must never be hidden behind a generic Save action.

## 6. Cross-module UI law

A module MAY show a contextual control for another owner only when the control is one of:

- **reference selector** — choose existing owner definition;
- **quick-create link** — opens owning module with context prefilled;
- **inline summary/read-only preview** — owner remains obvious;
- **approved embedded editor** — only if it is literally the owner's shared component and persists through owner API.

A module MUST NOT build a private mini-editor that persists a shadow copy of another module's definition.

Examples:
- CPT → “Add Field Group” opens Surface 3 with CPT target prefilled;
- Listing → search profile selects Surface 34 definition;
- Workflow → webhook action selects Surface 23 Connection;
- Theme Workspace → font selector selects Surface 53 font reference;
- Link Health → redirect fix creates/reviews Surface 44 Redirect Plan.

## 7. Dependency direction and cycle rule

Allowed conceptual direction:

`Platform contracts → Domain/shared primitives → Canonical surfaces/foundations → Adapters → Solution Blueprints/Experiences`

Peer surfaces communicate through stable contracts/Abilities/Events, never private classes/storage.

If two surfaces become hard dependencies on each other, STOP and extract the shared semantic behavior into an existing platform/shared service or create a separately accepted shared contract. Do not create a circular boot/runtime dependency.

## 8. Import/export and migration law

Import, migration or competitor parity MUST normalize external definitions into canonical WPE owners.

Never persist competitor callbacks/classes/configuration as executable WPE runtime merely for parity.

Examples:
- Meta Box/CMB2 field → Surface 3 Field Schema;
- JetEngine relation → Surface 4;
- CPT UI definition → Surface 1/2;
- Redux control → Surface 12 + shared Field Schema;
- Header/Footer script → Surface 50 if browser-side and safe; PHP snippet rejected;
- Use Any Font → Surface 53;
- WP Migrate operation → Surface 55 plan using 45/24/23/26 contracts.

## 9. Acceptance gate

Before any implementation milestone starts, its option list MUST pass:

1. every option has one semantic owner;
2. every mutation has one execution owner;
3. every persistent record has one storage owner;
4. every external truth has an adapter/authority boundary;
5. every cross-module control is reference/quick-create/shared owner component, not shadow storage;
6. no duplicate condition/query/search/workflow/job/http/redirect/transform/backup/role/policy engine appears;
7. no bypass path exists for REST/Workflow/AI/CLI/UI;
8. disable/uninstall does not delete peer-owned data;
9. Multisite/site/tenant scope is server-resolved;
10. evidence fixtures reference the same owner.

Failure of any item returns the feature to planning before code.
