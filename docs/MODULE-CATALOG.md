# WPEssential — Module Catalog

Status: **Phase 0 product specification**  
Legend: **Free** = WordPress.org/free package; **Pro** = separate premium add-on.

Every module below must use the shared architecture in `ARCHITECTURE.md`. Feature lists are target capabilities, not implementation claims.

---

## 1. Custom Post Types Builder — Free

### Goal
Create, inspect and safely manage WordPress custom post type registrations without code.

### Baseline market bar
CPT UI / SCF / JetEngine-style UI for labels, supports, visibility, REST, rewrite and capabilities.

### WPEssential target
- create/edit/duplicate/archive WPEssential-managed CPT definitions
- full label set with intelligent defaults and manual overrides
- public/admin/queryable/exclude-from-search controls
- hierarchical support
- menu icon/position
- supports: title/editor/author/thumbnail/excerpt/comments/revisions/custom-fields/page-attributes/post-formats and version-specific supported features
- archive/rewrite/query vars
- REST controller namespace/base where supported
- capability type, meta-cap mapping, custom capabilities
- taxonomies attachment
- export/import/version history
- “Used by” dependency graph
- generated PHP preview for developers (display only, not eval)
- WP-CLI/Ability read/create/update operations subject to permissions
- advanced compatibility checks before changing an existing registration

### Existing types
WPEssential may **inspect** any registered post type. “Editing” a post type owned by another plugin/theme means applying supported WordPress filters/arguments where feasible, not pretending ownership. Core post types cannot be unregistered casually.

### Cross-module quick actions
From CPT editing, users can create/link:
- taxonomy
- field group
- relation
- status rules
- admin columns
- listing
- form

These create definitions in their owning modules, not private CPT-only copies.

### Security / integrity
- validate post type keys and reserved names
- impact warning for rewrite/capability/public-state changes
- flush rewrite rules only when definitions actually change; never on every request
- block delete when hard dependencies exist unless explicitly resolved

---

## 2. Taxonomy Builder — Free

### Goal
Create and manage taxonomies and connect them to post types.

### Target
- hierarchical/non-hierarchical
- complete labels
- public/admin/nav/query options
- rewrite/query vars
- REST exposure
- show admin column
- sort/show tag cloud where relevant
- term capability mapping
- attach to one or many post types
- default term/settings where WordPress supports them
- inspect existing taxonomies
- revisions/export/import for WPEssential definitions
- dependency graph and usage view

### Security / integrity
Changing a taxonomy slug/rewrite can break URLs and integrations; require impact preview and controlled rewrite flush.

---

## 3. Custom Fields Builder — Pro

### Goal
A field/schema system broad enough to replace common ACF/SCF/Meta Box usage while being reusable by forms, settings, tables and integrations.

### Field families
- text, textarea, number, range
- email, URL, tel
- password/secret (special storage rules)
- select, multi-select, radio, checkbox, button group
- true/false
- date, datetime, time
- color
- image, gallery, file, media
- rich text/editor
- code/JSON editor with schema-aware validation where appropriate
- repeater
- group
- flexible/repeating layouts
- relationship/reference
- taxonomy/term
- user
- post/entity selector
- map/location through adapter
- icon
- hidden/computed/read-only
- separator/tab/message/UI-only fields
- custom registered field types via SDK

### Location targets
- post editor / selected post types
- term create/edit
- user profile/create user
- comment screens where supported
- media/attachment
- settings pages
- WPEssential custom-table records
- frontend forms/profile screens through shared schema

### Features
- AND/OR location rules
- conditional logic
- field-level validation/sanitization
- default/dynamic values
- instructions/help
- required/nullability
- min/max/length/pattern
- role/capability visibility/edit policy
- reusable field groups / clone/reference
- Local JSON-style versionable export package
- field schema versioning and migrations
- storage adapters: meta/options/custom table
- explicit REST/Ability exposure controls
- bidirectional relation should delegate to Relations Engine, not duplicate it

### Differentiator
A field definition is a typed platform schema object, not just editor metadata.

---

## 4. Relations Builder — Pro — NEW

### Why it must exist
The user requires modules to work relationally, and JetEngine/Pods/SCF increasingly expose relational modeling. Hiding relations inside “relationship fields” is insufficient.

### Target
- entity-to-entity relations across supported data sources
- one-to-one / one-to-many / many-to-many
- directional labels
- relation metadata/pivot fields
- ordering
- min/max cardinality rules
- bidirectional traversal
- relation query clauses
- admin relation management
- form actions to attach/detach
- workflow triggers on relation changes
- REST/Ability access subject to policy
- delete policy: restrict/detach/approved cascade
- integrity diagnostics/orphan repair

### Storage
Scale-aware relation tables where appropriate; do not serialize large relation graphs into post meta.

---

## 5. Status Manager — Pro

### Goal
Manage both WordPress post statuses and WPEssential/domain workflow states without conflating them.

### Target
- inspect registered post statuses
- register safe custom post statuses where WordPress APIs support desired behavior
- labels, visibility, protected/private/public flags, admin list behavior
- per-post-type availability rules
- transition permissions
- transition actions/workflows
- state color/icon for UI only, never as sole semantic indicator
- state history/audit
- domain state machines for forms/workflows/tickets/custom-table records that are **not** forced into `register_post_status()`
- guards such as “can move from A → B only if condition X”

### Integrity
Prevent impossible transitions and preserve unknown third-party statuses rather than normalizing them away.

---

## 6. Custom Query Builder — Pro

### Goal
Build simple through relational/aggregate queries in UI and reuse them anywhere.

### Sources
- posts/CPTs
- terms
- users
- comments
- media
- custom tables
- WPEssential module entities
- WooCommerce via adapter
- remote REST sources
- merged/union-like provider where semantics permit

### Target
- typed filters with nested AND/OR groups
- field/meta/taxonomy/date/search conditions
- relation traversal
- joins for supported SQL/custom-table sources
- grouping/aggregates
- computed values
- sorting
- pagination/cursor where provider supports it
- parameters/macros bound at runtime
- context variables: current user/entity/request with explicit allowlist
- preview rows
- execution plan/warnings
- query cache settings with invalidation policy
- duplicate/version/export
- shortcode/block/listing/table/dashboard/form-option/REST/workflow use

### Safety
Canonical format is AST + prepared parameters, not raw SQL. Enforce row/time limits for preview and public endpoints.

---

## 7. Custom Tables Builder — Pro

### Goal
A safe WordPress database schema designer and data manager for application-grade data.

### Schema designer
- table name with WP prefix strategy
- columns/types/length/default/nullability
- unsigned/auto increment where valid
- primary/unique/normal indexes
- generated schema preview
- foreign-key-like logical relations; physical FK support only after compatibility analysis
- migrations: add/rename/change/drop with impact checks
- indexes and query usage insights
- schema revisions

### Data browser
- pagination/search/filter/sort
- create/edit/delete rows subject to schema/policy
- bulk actions
- CSV/JSON import/export
- relation-aware references

### Query console
Default:
- `SELECT`
- `EXPLAIN`
- bounded result sets
- prepared parameter UI

Not default:
- arbitrary DDL/DML
- multi-statement execution

A future destructive developer console requires separate unsafe mode, capability, re-auth, backup/restore point, transaction where supported, audit and explicit SQL classification.

### Key rule
This is not phpMyAdmin embedded into every administrator account.

---

## 8. Admin Columns Builder — Pro

### Goal
Customize WordPress list tables and compatible registered tables using any safe WordPress/WPEssential data source.

### Targets
- posts/pages/CPTs
- users
- media
- comments
- taxonomies/terms
- supported WooCommerce screens via adapter
- WPEssential/custom list adapters

### Column source types
- core object property
- meta/custom field
- taxonomy/terms
- relation value/count
- query result/aggregate
- featured/custom image/media
- computed token/expression
- WPEssential renderer/template
- shortcode render with bounded/sanitized execution
- block render where server rendering is defined and safe
- registered extension callback/ability

### Formatting
- text/HTML-safe text
- image/avatar
- badge/status
- link/actions
- number/currency/percentage
- date/time/relative time
- boolean/icon
- list/relation
- JSON/code preview
- custom registered formatter

### Features to meet/beat Admin Columns Pro
- drag/drop order and width
- sticky/horizontal behavior where needed
- sorting
- smart filtering
- search integration
- quick/inline editing where source is safely writable
- bulk editing
- conditional formatting
- export CSV with spreadsheet-formula injection protection
- multiple column sets/views
- per-user/per-role conditions
- save/duplicate/import/export
- expensive-column lazy loading/caching
- performance warning and query count diagnostics

### Safety
Never execute arbitrary PHP entered by a user. Editing must pass through source adapter validation/authorization.

---

## 9. Dynamic Listings / Template Builder — Pro — NEW

### Why
Without a reusable dynamic display layer, Query Builder/relations require Elementor/JetEngine-like third-party renderers and WPEssential cannot be a coherent platform.

### Target
- listing definition bound to query/data source
- grid/list/table/card modes
- fields/tokens/media/links/actions
- conditional visibility
- empty/loading/error states
- pagination/load more/infinite scroll after performance review
- sorting/filter controls through query parameters
- reusable partials/components
- responsive layout
- block + shortcode rendering first
- builder adapters consume the same listing definition
- server-render-first for SEO/data integrity where practical

### Boundary
Not a full general-purpose page builder in v1.

---

## 10. Dashboard Widgets Manager — Pro

### Goal
Control and create WordPress admin Dashboard widgets.

### Target
- inventory core/plugin widgets
- show/hide by role/user/capability
- reorder/layout rules where WordPress allows
- custom widget types: rich text, stats/query card, list/table, RSS/remote content with SSRF protections, iframe only through explicit allowed URLs, WPEssential listing, shortcode/block render, support/onboarding content
- banner/announcement widgets
- schedule/expiry
- dismissible state
- per-role dashboard presets
- clone/export/import

### Security
Remote/iframe widgets require URL allowlists and protocol validation. Widget HTML is sanitized by role/capability policy.

---

## 11. Custom Admin Menu Builder — Pro

### Goal
Rearrange, hide and add wp-admin navigation without confusing visibility with permission.

### Target
- inspect current menu/submenu tree
- drag/drop reorder
- rename/icon
- move supported entries
- hide/show by role/capability/user rule
- add links to WPEssential pages, existing admin URLs or validated external URLs
- separators/groups
- login/logout redirects handled through separate rule section
- per-role menu profiles
- reset/recovery route
- export/import

### Critical rule
Hiding a menu never grants or revokes access. Authorization remains capability-based at the destination.

---

## 12. Settings Page Builder — Pro

### Goal
Create WordPress admin settings applications without code using the shared Field Schema.

### Target
- top-level or submenu placement
- tabs/sections/groups
- field groups from Custom Fields Engine
- options API/custom storage adapters
- validation/sanitization
- capability control
- network settings mode where supported
- secret/credential fields delegated to Secrets Vault
- conditional fields
- dynamic options/query sources
- import/export
- revisions
- REST/Ability access explicit
- shortcode/block for approved frontend settings surfaces where required

---

## 13. Dashboard Builder — Pro

### Goal
Build role/user-specific frontend application dashboards/portals.

### Navigation
- menus/submenus with route/page definitions
- **hard data-model maximum: 5 levels**
- recommended UX: 2 levels; warn beyond 3
- per-role/user/capability visibility
- active states, badges/counters

### Content sources
- WPEssential listings/components
- Gutenberg block content
- shortcode output
- selected Elementor templates
- selected Bricks/WPBakery/Visual Composer templates via adapters
- custom registered renderers

### Application features
- route guards
- login required/guest routes
- page title/breadcrumb
- responsive shell
- profile/account components
- forms and CRUD screens
- notification/chat integration
- role-specific home pages
- empty/error/loading/permission-denied states

### Boundary
Third-party builder templates are embedded via their supported rendering APIs; WPEssential does not copy their internal document formats.

---

## 14. User Profile Builder — Pro

### Goal
Define admin/frontend user profile experiences by role, user segment or general default.

### Target
- field groups bound to user meta/custom storage
- view/edit modes
- avatar/media
- tabs/sections
- role-specific templates
- privacy controls per field
- capability-controlled fields
- frontend account/profile routes
- password/email change through WordPress secure flows
- custom fields and relation data
- forms integration
- dynamic listings (user posts/orders/etc. through adapters)
- public profile SEO/noindex controls
- shortcode/block/builder adapters

### Safety
Never expose password hashes, application passwords, session tokens or protected user meta through generic dynamic tokens.

---

## 15. Builder Widgets Builder — Pro

### Goal
Create reusable components/widgets in UI and register them with supported builders.

### Shared Component Blueprint
- identity/category/icon
- control schema
- defaults
- dynamic data bindings
- render schema/template
- style controls/tokens
- responsive controls
- conditional controls
- dependencies/assets

### Adapter pages
Separate management screen per builder because registration/control APIs differ:

1. Gutenberg Blocks — canonical `block.json`/server registration where generated package model permits
2. Shortcodes — universal fallback
3. Elementor — widget/control registration adapter
4. Bricks — element adapter
5. WPBakery — shortcode/parameter adapter
6. Visual Composer — element adapter
7. additional builders based on documented APIs and demand

### Limits
A generic blueprint cannot promise every proprietary builder feature. Adapter UI must clearly show supported/unsupported blueprint capabilities.

### Safety
Generated rendering is sanitized and compiled from approved primitives. Arbitrary PHP is not a widget control.

---

## 16. Forms & Workflow Builder — Pro

### Form target
Meet baseline expectations from Gravity Forms/Fluent Forms and extend into application CRUD.

#### Fields / UX
- shared field types plus form-only consent/captcha/submit/page-break constructs
- drag/drop layout
- multi-step
- conditional visibility
- calculations
- repeaters
- dynamic defaults/options
- save draft/continue
- authenticated/guest rules
- scheduling/open-close limits
- entry limits
- accessible validation/errors
- file uploads with MIME/size/storage policy
- spam controls/honeypot/CAPTCHA adapters/rate limits

#### Data actions
- store submission
- create/update posts/CPTs
- create/update users with strict capability/role policy
- create/update terms
- create/update custom-table rows
- attach/detach relations
- update options/settings only when explicitly allowed
- delete actions require heightened policy/confirmation

### Workflow runtime
Triggers and actions form a graph/state machine:

- conditional branches
- delays/waits
- retries/backoff
- idempotency
- manual approval
- parallel branches only after semantics are defined
- timeout/failure paths
- run/step logs
- retry failed step
- cancel run
- compensating action support where practical

### Trigger examples
- form submitted
- entity created/updated/deleted
- status transition
- relation changed
- schedule/cron
- webhook received
- user login/register
- WooCommerce event adapter
- manual run

### Action examples
- CRUD through data source registry
- send email
- send notification
- webhook/HTTP request with SSRF controls
- enqueue another workflow
- relation operation
- change status
- generate document through future adapter
- registered ability/action

### Differentiator
Forms, Cron and automations share one action/condition/event model instead of three separate mini automation engines.

---

## 17. Cron Job Builder — Pro

### Goal
Inspect/schedule/control WordPress scheduled work and WPEssential jobs.

### Target
- list WP-Cron events with hook/next run/recurrence/args source hints
- run now (permission + nonce + audit)
- pause WPEssential-managed schedules
- reschedule/delete WPEssential schedules
- inspect third-party events; modifications clearly warn ownership risk
- custom recurrence intervals
- timezone-aware schedule builder
- one-time/recurring
- cron-expression-like UX compiled to supported schedule semantics where feasible
- system cron/WP-CLI runner instructions/health check for reliable execution
- job history/duration/failure logs for WPEssential jobs

### Allowed actions
- registered WPEssential workflow/action/ability
- WordPress action hook with validated arguments when explicitly configured
- HTTP/webhook action with SSRF controls
- selected shortcode/block render only when output is actually useful to a downstream action

### Not standard
- arbitrary PHP eval
- “server-side JavaScript/HTML cron” as a fake execution model

WP-Cron is traffic-triggered; UI must not promise exact timing when only WP-Cron is available.

---

## 18. Notification System — Pro

### Goal
Unified event-driven notifications, not email-only alerts.

### Channels
- in-app/admin
- frontend dashboard
- email via Email Builder
- webhook
- browser push adapter later
- Slack/Teams/SMS/provider adapters based on demand

### Target
- event/trigger selector
- recipient targeting: users/roles/capabilities/relations/query
- template/tokens
- condition rules
- priority
- digest/batching
- quiet hours/user preferences
- read/unread state for in-app
- delivery logs
- retry/deduplication
- unsubscribe/subscription rules where legally/product appropriate
- escalation workflow

### Architecture
Workflows call notifications; notifications do not reimplement workflow branching.

---

## 19. Emails Builder — Pro

### Goal
Customize WordPress transactional/event emails and create new email templates safely across email clients.

### Target
- inventory/override supported default WordPress email events
- custom templates tied to Notification/Workflow events
- dedicated email-safe drag/drop component schema
- header/footer/branding reusable layouts
- text/image/button/divider/columns/spacer/data table/list components designed for email constraints
- dynamic tokens from shared renderer
- conditional content
- recipient/from/reply-to/CC/BCC policies
- HTML + plaintext
- preheader
- preview desktop/mobile dimensions
- test send
- template revisions
- import/export
- email delivery logs at WPEssential event level (not false claims of inbox delivery unless provider supplies it)

### Builder interoperability
Gutenberg/Elementor/WPBakery/etc. may provide dynamic content sources/templates where safely transformable, but their arbitrary frontend HTML is not guaranteed email compatible.

---

## 20. Message & Chat System — Pro

### Goal
Embeddable frontend messaging for site users/roles.

### Core data
Purpose-built tables for:
- conversations
- participants
- messages
- read state
- attachments metadata

### Target
- 1:1 chat
- group conversations
- role/relation-based initiation rules
- unread counts
- read receipts optional
- reply/edit/delete policies
- attachments
- search with scale limits/index strategy
- mute/notification preferences
- moderation/report/block
- retention policy
- dashboard/widget/shortcode/block embedding
- workflow actions/triggers

### Realtime strategy
Shared WordPress hosting cannot assume WebSockets. Core must work with robust REST/AJAX polling or another universally deployable fallback; WebSocket/realtime services are optional adapters.

### Security
Object-level access checks on every conversation/message operation; IDs must never be sufficient authorization.

---

## 21. REST API Builder — Pro

### Goal
Create typed custom endpoints over approved WPEssential/WordPress data and actions.

### Target
- namespace/version/path/method
- input JSON schema
- output schema/field selection
- query/data source binding
- CRUD action binding
- ability/workflow binding
- cookie+nonce same-site auth
- Application Passwords external baseline
- optional OAuth/JWT adapters after security review
- capability/policy rule
- rate limiting/abuse controls
- CORS configuration with safe defaults
- pagination
- errors
- idempotency key for suitable writes
- response caching only when permission context is safe
- endpoint logs/metrics
- OpenAPI-like export considered after schema model stabilizes

### Safety
No “public write endpoint” checkbox that silently removes authorization. Dangerous auth/policy combinations are blocked.

---

## 22. Webhooks & Connections Manager — Pro — NEW

### Goal
Centralize outbound/inbound integration connections currently scattered across forms, workflows, backups and notifications.

### Target
- credential-backed connection records
- inbound webhook endpoints with signing secrets/replay protection/rate limits
- outbound HTTP connections
- OAuth connection lifecycle via provider adapters
- connection test
- request templates/mapping
- retry policy
- delivery logs
- secret rotation
- reusable connection references from workflows/forms/backups/etc.

### SSRF controls
Block dangerous local/private destinations by default for user-configured outbound HTTP, validate DNS/IP resolution and redirects according to the final threat model.

---

## 23. Backup Manager — Pro

### Goal
Reliable, verifiable backup and restore rather than “zip files and hope.”

### Backup scopes
- database
- WordPress files
- uploads
- themes/plugins
- selected paths
- selected tables
- WPEssential configuration-only package
- non-WP files/external DB only through explicit advanced connectors

### Engine
- chunked/streaming archive generation
- low-memory operation
- manifest
- checksums
- compression options
- encryption for backup archive where supported
- resumable/multipart uploads
- retention/pruning
- full + incremental/differential strategy after correctness design
- multiple destinations
- schedules
- pre-update/manual restore points
- progress
- failure/retry

### Restore
- verify manifest/checksums first
- selective restore
- database prefix/domain/path migration controls
- staging/dry-run validation where feasible
- maintenance lock during critical restore phase
- post-restore health checks
- disaster recovery documentation

### Storage adapter strategy
Do not ship 25 unrelated giant SDK implementations when protocols overlap. Create protocol/provider adapters.

Target catalog can exceed 25 through native and compatible endpoints, including:

1. Local server
2. Browser/manual download
3. FTP
4. FTPS
5. SFTP
6. WebDAV
7. Nextcloud
8. ownCloud
9. Amazon S3
10. generic S3-compatible
11. Cloudflare R2
12. Backblaze B2/S3
13. Wasabi
14. DigitalOcean Spaces
15. MinIO
16. Google Cloud Storage
17. Google Drive
18. Dropbox
19. Microsoft OneDrive
20. OneDrive Business/SharePoint
21. Azure Blob Storage
22. Box
23. pCloud
24. MEGA (only if maintained API/library risk is acceptable)
25. OpenStack Swift/Rackspace-compatible
26. Oracle Object Storage/S3-compatible
27. Akamai/Linode Object Storage
28. Vultr Object Storage
29. Scaleway Object Storage
30. Hetzner Object Storage
31. Storj S3-compatible
32. IDrive e2 S3-compatible
33. Bunny Storage where supported protocol/API is stable

Provider inclusion requires API stability, license, maintenance and automated integration testing. “Listed” does not mean implemented until its adapter passes acceptance tests.

### Gmail/email correction
Email is not a primary large-backup storage system. Email destination may send a small backup, manifest, status, or secure download link within provider size limits. Google Drive is the proper Google storage target.

---

## 24. Reset Manager — Pro

### Goal
Controlled WordPress reset/recovery with a guaranteed understanding of what will be destroyed.

### Reset scopes
- WPEssential-only reset
- content-only selected object types
- settings/options selected scope
- database/site reset profiles
- plugin/theme handling choices
- multisite operations only with dedicated semantics

### Mandatory restore point flow
Before destructive reset unless an explicitly privileged override exists:

1. impact preview
2. create/verify backup or select recent verified backup
3. environment snapshot
4. plugin/theme inventory + versions
5. WPEssential export
6. re-authentication/confirmation
7. execute bounded reset
8. health check
9. audit result

### Screenshots/video
A server-side reset cannot promise reliable video recording. Optional screenshots/client capture are non-core. The audit snapshot is the authoritative record.

---

## 25. Import / Export — Pro

### Two systems

#### A. WPEssential Configuration Package
- all modules or selected modules
- definitions + dependency manifest
- versioned JSON/ZIP package
- checksums
- secrets excluded by default
- preview before import
- conflict strategies: create/skip/replace/map
- dependency resolution
- ID/UUID remapping
- dry-run report
- rollback where feasible

#### B. Data Import/Export
Target competitive capability inspired by WP All Import/Export:
- CSV
- JSON
- XML
- Excel/Sheets only through supported parser/connection strategy
- map source paths/columns to entity fields
- custom fields/taxonomies/media/relations
- create/update matching key
- missing-record behavior
- chunked large imports
- resumable runs
- validation preview
- transformation functions from an allowlisted expression/mapping engine
- import logs/error rows
- scheduled imports via workflow/job engine

Arbitrary inline PHP transformations are not the default extension model.

---

## 26. Protector — Pro

### Goal
Access protection and hardening rules without presenting obscurity as security.

### Target
- maintenance/private-site password gate
- frontend path protection by role/user/capability/password/IP rules
- wp-admin access rules
- login rate limiting/lockout integration strategy
- custom login slug/alias as optional noise reduction
- disable/limit selected endpoints/features through supported hooks
- hide version/info leakage options only where meaningful
- security headers helper only with server/compatibility awareness
- emergency bypass/recovery constant or signed recovery mechanism
- audit/access logs with privacy-safe retention
- trusted proxy/IP handling configuration

### Critical rule
Changing `/wp-admin`/login URLs can break plugins and does not replace authentication. It is optional compatibility-risk functionality.

---

## 27. Watermarker / Media Rules — Pro

### Goal
Apply rule-based watermarks while preserving the original media asset.

### Target
- text watermark
- image/SVG watermark after sanitization
- position presets/custom offsets
- opacity
- scale relative to image
- margins/padding
- repeated/tiled mode
- min dimensions
- file type rules
- post type/taxonomy/folder/context rules
- exclude selected media
- generate derivative attachment sizes/renditions
- preview
- batch regenerate in background
- remove/rebuild derivatives
- responsive/sub-size integration

### Non-destructive contract
Original uploaded file remains unchanged. Watermark is generated into derived files or designated renditions using WordPress image APIs/compatible editor adapters.

---

## 28. XML-RPC Manager — Pro

### Goal
Make legacy XML-RPC exposure understandable and controllable.

### Target
- explain current XML-RPC state
- inventory exposed methods
- allow/deny method groups
- pingback controls
- authenticated-method controls
- IP/rate rules where defensible
- request size/element-limit controls via supported hooks
- logs/diagnostics
- Jetpack/mobile-app compatibility warnings
- recommended security presets

### Important WordPress nuance
The `xmlrpc_enabled` filter does **not** fully disable every XML-RPC method; it controls methods requiring authentication. Granular control uses `xmlrpc_methods` and related hooks. UI wording must reflect that fact.

---

## 29. Role & Capability Manager — Pro

### Goal
Manage WordPress authorization safely and integrate WPEssential module capabilities.

### Target
- list/create/clone/edit/delete custom roles
- capability matrix grouped by source
- assign multiple roles where WordPress behavior supports it
- users-by-role view
- compare role diff
- import/export
- backup/restore roles/capabilities
- WPEssential capability presets
- CPT/taxonomy capability mapping helpers
- per-resource policies belong to Policy engine/other modules, not fake global capabilities
- multisite/Super Admin-aware behavior
- test-as-role simulation with security review

### Anti-lockout
- protect current administrator’s critical access
- require impact preview for privilege escalation/revocation
- maintain recovery mechanism
- flag administrator-equivalent capability combinations
- audit all changes

---

## 30. Support / Docs / Changelog / Account Center — Platform surface

Not sold as a module.

### Pages
- WPEssential Home
- Modules
- Documentation
- Changelog
- Support Tickets
- Account & License
- System Status / Diagnostics

### Support ticket functions
- list
- create
- read
- reply
- close/reopen
- attachments under strict file rules
- status/history

Remote calls require explicit account connection. Diagnostics upload requires preview + consent.

---

# Shared module acceptance checklist

Every user-facing module must answer before implementation:

1. What data/definitions does it own?
2. What shared engines does it reuse?
3. What capabilities protect list/read/create/update/delete/run operations?
4. What Abilities does it register?
5. What assets can it load and exactly where?
6. What tables/files/options does it create?
7. What happens when disabled?
8. What happens when Pro expires?
9. How is it exported/imported?
10. How are migrations/rollback handled?
11. How is failure observable?
12. What are happy, boundary, permission, malicious and recovery tests?
13. What third-party integrations can fail?
14. What is its performance budget?
15. What compatibility matrix applies?
16. What existing competitor behavior is the minimum bar?
17. What WPEssential-specific differentiator justifies the module?

A module that cannot answer these is not ready for implementation.
