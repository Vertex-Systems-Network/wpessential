# WPEssential — Module Option Inventory

Status: **Phase 0 planning ledger**

This file records the initial screen/option inventory for every WPEssential module. It exists to prevent implementation from inventing unplanned UI or behavior.

An item being listed here means **it must be specified before implementation**. Exact defaults, validation and side effects are governed by `SPECIFICATION-STANDARD.md`, module-specific specs and ADRs.

If development/research discovers a missing option, add it here/specify it before relying on it.

---

# 1. Custom Post Types Builder — Free

## Screens
- All Post Types
- Add Post Type
- Edit Post Type
- Labels
- Visibility & Queries
- Admin UI
- Features / Supports
- URLs / Rewrite
- REST API
- Capabilities
- Taxonomies
- Integrations
- Dependencies / Used By
- Revisions
- Export / Generated PHP Preview
- Diagnostics

## List controls
- search by name/key
- owner filter: WPEssential/core/plugin/theme
- public/private filter
- REST enabled filter
- hierarchical filter
- status: active/draft/archived/discovered
- sort by label/key/updated/owner
- page size
- saved view

## Identity options
- plural label
- singular label
- post type key
- description
- menu name
- admin bar name
- add-new label family
- edit/view/search/not-found/all-items/archive/attributes/insert/uploaded/featured-image label family
- text domain handling

## Registration options
- public
- publicly queryable
- show UI
- show in menu
- show in admin bar
- show in nav menus
- exclude from search
- hierarchical
- can export
- delete with user
- map meta cap
- query var enabled/custom value

## Supports
- title
- editor
- author
- thumbnail
- excerpt
- trackbacks
- custom fields
- comments
- revisions
- page attributes
- post formats
- version-specific registered supports

## Admin menu
- menu position
- icon source: Dashicon / uploaded SVG subject to sanitization / registered icon
- parent menu attachment where supported
- menu visibility conditions

## Archive/rewrite
- has archive false/true/custom slug
- rewrite enabled
- rewrite slug
- with front
- feeds
- pages
- endpoint mask where supported
- permalink preview
- rewrite impact warning

## REST
- show in REST
- REST base
- REST namespace
- controller class only via registered developer adapter, never arbitrary class string execution
- autosave/revisions controller compatibility where WordPress supports it

## Capabilities
- capability type singular/plural
- use default post capabilities
- custom capability mapping
- map meta cap
- role helper preview
- capability diff/impact

## Actions
- create
- update
- duplicate
- archive
- reactivate
- export
- generated PHP preview/copy
- dependency inspection
- safe delete
- inspect external post type

---

# 2. Taxonomy Builder — Free

## Screens
- All Taxonomies
- Add/Edit Taxonomy
- Labels
- Object Types
- Visibility
- Admin UI
- URLs / Rewrite
- REST
- Capabilities
- Default Term
- Dependencies
- Revisions / Export / Diagnostics

## Identity
- singular/plural/menu labels
- taxonomy key
- description
- complete WordPress taxonomy label set

## Behavior
- hierarchical
- public
- publicly queryable
- show UI
- show in menu
- show in nav menus
- show tag cloud
- show in quick edit
- show admin column
- sort
- meta box callback only through registered adapter

## Object assignment
- select posts/pages/CPTs
- all current compatible object types
- future/discovered object warning
- detach impact preview

## Query/rewrite
- query var
- rewrite on/off
- slug
- with front
- hierarchical rewrite
- endpoint mask where supported

## REST
- show in REST
- base
- namespace
- registered controller adapter

## Capabilities
- manage terms
- edit terms
- delete terms
- assign terms
- role/capability impact helper

## Default term
- enable default term
- name
- slug
- description
- existing term selection where supported

---

# 3. Custom Fields Builder — Pro

## Screens
- Field Groups
- Add/Edit Group
- Fields canvas
- Location Rules
- Presentation
- Storage
- Validation
- Permissions
- REST/Abilities
- Revisions
- Import/Export
- Diagnostics / Field Usage

## Group options
- title
- stable key
- description
- active/draft/archive
- position/order
- style/layout
- label placement
- instruction placement
- hide native editor elements where target supports it

## Location rule options
- AND/OR groups
- object type
- post type
- taxonomy
- term
- page/template
- user role
- user
- attachment/media
- comment type
- settings page
- custom table
- frontend context
- relation/query segment
- custom registered matcher

## Every field common options
- label
- name/key
- type
- instructions
- required
- default
- placeholder
- prepend/append
- wrapper width/class/id with sanitization
- conditional logic
- read-only
- disabled UI
- visibility policy
- edit policy
- storage adapter
- REST exposure
- Ability exposure
- revision/export flags

## Text-like
- min length
- max length
- pattern
- autocomplete
- input mode
- multiline rows
- rich text toolbar/media controls

## Numeric
- min
- max
- step
- precision
- integer/decimal
- formatting

## Choice
- static choices
- dynamic query choices
- allow null
- multiple
- searchable
- return format: value/label/object where safe
- allow custom value where type permits

## Boolean
- default
- UI style
- true/false stored representation

## Date/time
- display format
- return/storage format
- min/max
- timezone handling

## Media
- allowed MIME/types
- library scope
- min/max dimensions
- max file size
- preview size
- return ID/object/URL policy
- multiple/count limits

## Repeater/group/flexible
- min/max rows
- collapsed field
- layout table/block/row
- add-row label
- subfield schema
- clone/reference semantics

## Relationship/entity selectors
- source types
- filters
- min/max selections
- return type
- relation-engine linkage
- bidirectional option delegated to Relations Builder

## Secret field
- vault reference
- masked display
- reveal permission/reauth
- never ordinary export

## Computed
- expression/token source
- recalculation trigger
- persisted vs runtime
- dependency graph/cycle detection

---

# 4. Relations Builder — Pro

## Screens
- Relations
- Add/Edit Relation
- Endpoints
- Cardinality
- Pivot Fields
- Display/Admin Controls
- Delete Policies
- Permissions
- Data Browser
- Integrity Diagnostics
- Import/Export/Revisions

## Identity
- relation name
- key
- description
- active/draft

## Endpoints A/B
- data source
- entity type
- label singular/plural
- role name
- query filter limiting eligible records

## Cardinality
- one-to-one
- one-to-many
- many-to-many
- min links A/B
- max links A/B
- self-relation allowed
- duplicate link allowed false by default

## Direction
- bidirectional traversal
- canonical storage direction
- inverse labels

## Pivot
- enable metadata
- shared Field Schema fields
- unique constraints where meaningful
- ordering field

## Delete policy
- restrict deletion
- detach links
- cascade only after explicit safe model
- orphan warning

## Admin UI
- show relation metabox/panel
- editable from A/B
- sortable/orderable
- quick attach/detach
- bulk attach/detach

---

# 5. Status Manager — Pro

## Screens
- Statuses
- State Machines
- Add/Edit Status
- Transitions
- Guards
- Actions
- Display
- Usage/History
- Diagnostics

## WordPress post status
- label
- key
- public
- internal
- protected
- private
- publicly queryable
- show in admin all list
- show in admin status list
- label count
- applicable post types
- editor visibility compatibility

## Domain state
- name/key
- type/scope
- color
- icon
- terminal state
- initial state
- active/inactive

## Transition
- from
- to
- actor capability/policy
- conditions
- required fields
- confirmation
- reason required
- workflow actions
- notifications
- audit

## Rules
- allowed transitions only
- prevent backward transition toggle where model requires
- automatic transitions through workflow
- timeout transitions

---

# 6. Custom Query Builder — Pro

## Screens
- Queries
- Builder
- Source
- Filters
- Relations/Joins
- Fields/Projection
- Group/Aggregate
- Sort
- Pagination
- Parameters
- Cache
- Preview/Explain
- Usage/Dependencies
- Revisions/Export

## Identity
- name
- key
- description
- status

## Source
- posts/CPT
- users
- terms
- comments
- media
- custom tables
- module entities
- WooCommerce adapter
- remote REST adapter
- merged provider

## Filter node
- field
- operator
- value source
- value
- data type
- case sensitivity where supported
- null handling
- nested AND/OR

## Operators
- equals/not equals
- greater/less/inclusive variants
- contains/not contains
- begins/ends
- in/not in
- between/not between
- exists/not exists
- empty/not empty
- date relative operators
- taxonomy-specific
- relation exists/count

## Parameters
- key
- type
- required
- default
- source allowlist: explicit input/current user/current entity/request query var/route parameter
- validation
- public exposure

## Projection
- all vs selected fields
- aliases
- computed fields
- relation fields
- aggregate fields

## Sort
- multiple sort clauses
- asc/desc
- null ordering where provider supports
- random sort warning

## Pagination
- page/offset
- cursor where supported
- default limit
- max limit
- public override allowed

## Cache
- disabled/default
- TTL
- key context dimensions
- invalidation events
- per-user cache warning

## Preview
- sample parameter values
- max rows
- query duration
- query count
- SQL/execution-plan display only for authorized diagnostics

---

# 7. Custom Tables Builder — Pro

## Screens
- Tables
- Schema Designer
- Columns
- Indexes
- Relations
- Data Browser
- Row Editor
- Query Console
- Migrations
- Import/Export
- Diagnostics

## Table identity
- logical name
- physical table name
- prefix mode
- description
- charset/collation policy inherited by default

## Column
- name
- logical label
- type
- length/precision/scale
- unsigned
- nullable
- default
- auto increment
- primary candidate
- generated/computed only where DB compatibility allows
- sensitive/PII classification

## Types
- integer families
- decimal
- float/double only with warning
- boolean representation
- varchar/char
- text families
- date/time/datetime/timestamp
- JSON only if compatibility floor guarantees behavior, otherwise text+schema adapter
- binary/blob only where justified

## Index
- primary
- unique
- normal
- composite columns/order
- prefix lengths where supported
- estimated impact

## Migration actions
- add column
- rename column
- alter type
- nullability/default change
- add/drop index
- rename table
- drop column
- drop table
- migration preview
- backup checkpoint
- reversible/irreversible classification

## Data browser
- search
- filter
- sort
- page size
- column visibility
- create row
- edit row
- delete row
- bulk delete only with elevated confirmation
- CSV/JSON export

## Query console safe mode
- SELECT
- EXPLAIN
- prepared parameters
- max rows
- timeout
- read-only transaction where applicable
- saved safe queries

---

# 8. Admin Columns Builder — Pro

## Screens
- Column Sets
- Edit Column Set
- Add Column
- Sorting
- Filtering
- Editing
- Export
- Conditions
- Performance

## Target
- object/list table
- role/user applicability
- default view

## Column identity
- label
- key
- width
- alignment
- order
- visibility
- responsive priority
- sticky

## Sources
- core property
- field/meta
- taxonomy
- relation
- query
- media
- computed token
- renderer
- shortcode
- server block
- registered callback/Ability

## Formatting
- text
- sanitized HTML
- image/avatar size
- badge
- link
- actions
- number/precision
- currency
- percentage
- date format/timezone
- relative date
- boolean/icon
- list separator/max items
- JSON/code truncation

## Sort
- enabled
- source-aware sort key
- default direction
- null handling

## Filter
- enabled
- control type
- options source
- operator
- multi-select

## Inline edit
- enabled
- field/input type
- validation
- capability
- save mode

## Export
- include column
- raw vs formatted value
- formula-injection neutralization

## Performance
- lazy load
- cache
- max relation/query items
- warning threshold

---

# 9. Dynamic Listings / Template Builder — Pro

## Screens
- Listings
- Data Source
- Layout
- Item Template
- Controls
- Conditions
- Pagination
- Empty/Error
- Responsive
- SEO/Rendering
- Usage

## Listing identity
- name
- key
- status
- query/data source

## Layout
- grid/list/table/card
- columns desktop/tablet/mobile
- gap
- alignment
- equal-height behavior
- wrapper semantic element

## Item
- field/token
- heading/text
- media
- link
- badge
- button/action
- nested relation/listing with depth guard
- conditional wrapper
- reusable partial

## Query controls
- search
- sort selector
- filters
- reset
- active filter chips
- URL sync

## Pagination
- numbered
- previous/next
- load more
- infinite scroll
- page size
- max public size

## Empty/error/loading
- custom message
- template
- retry action
- skeleton count

## Rendering
- server-render first
- hydration only where interactive
- cache context
- semantic markup
- schema/SEO integration only when valid

---

# 10. Dashboard Widgets Manager — Pro

## Screens
- Dashboard Inventory
- Presets
- Widget Builder
- Conditions
- Layout
- Scheduling
- Diagnostics

## Existing widget controls
- visible/hidden
- role/user/capability conditions
- position/order where supported
- preset assignment
- restore default

## Custom widget identity
- title
- key
- type
- description

## Types
- rich text
- announcement/banner
- stat card
- query/list/table
- RSS
- remote HTTP
- iframe allowlisted
- listing
- shortcode
- server block
- onboarding/support

## Widget behavior
- dismissible
- dismissal scope per user/global
- expiry
- start date
- refresh behavior
- manual refresh
- cache TTL
- max height/scroll
- links/CTA

## Remote security
- URL
- allowed protocol
- host allowlist
- timeout
- redirects policy
- response size
- cache

---

# 11. Custom Admin Menu Builder — Pro

## Screens
- Menu Profiles
- Tree Editor
- Menu Item Editor
- Visibility Rules
- Redirect Rules
- Recovery

## Profile
- name
- priority
- applicable roles/users/capabilities
- active

## Item options
- original item reference
- custom item
- label override
- icon
- position
- parent
- URL target
- open new tab only for external link where sensible
- separator/group heading
- badge via query/token
- visibility conditions

## Actions
- hide
- show
- rename
- move
- duplicate custom item
- restore original

## URL validation
- internal admin route
- frontend URL
- external URL allow/confirm
- javascript/data schemes forbidden

## Redirects
- after login
- after logout
- role/user conditions
- fallback
- loop detection

---

# 12. Settings Page Builder — Pro

## Screens
- Settings Pages
- Add/Edit Page
- Navigation/Placement
- Sections/Tabs
- Fields
- Storage
- Permissions
- Frontend Exposure
- Revisions

## Page identity
- title
- slug
- description
- icon
- menu title

## Placement
- top-level
- parent admin menu
- position
- network admin mode

## Layout
- tabs
- vertical nav
- sections
- groups
- columns where responsive/accessibility remains sound

## Field source
- inline shared Field Schema
- linked reusable field group

## Storage
- single option array
- per-field options
- custom settings table adapter
- network option
- autoload strategy

## Permissions
- view capability
- edit capability
- field overrides

## Save behavior
- save per page/tab/all
- validation summary
- dirty-state warning
- reset section
- reset page
- export

---

# 13. Dashboard Builder — Pro

## Screens
- Dashboards
- Routes
- Navigation
- Page/Route Builder
- Access
- Layout/Shell
- Branding
- Integrations
- Preview
- Usage

## Dashboard identity
- name
- key
- status
- default role/segment

## Route
- label
- slug/path
- parent
- nesting depth max 5
- icon
- badge
- order
- title
- breadcrumb
- visibility condition
- access policy
- guest allowed
- redirect fallback

## Content source
- WPEssential components/listings
- Gutenberg content/template
- shortcode
- Elementor template
- Bricks template
- WPBakery/Visual Composer template
- registered renderer

## Shell
- header on/off
- sidebar on/off
- collapsible nav
- mobile nav
- content width
- account menu
- notification/chat slots
- logout action

## States
- loading
- empty
- 403
- 404
- dependency missing
- offline/request error

---

# 14. User Profile Builder — Pro

## Screens
- Profile Templates
- Sections/Tabs
- Fields
- Privacy
- Access
- Routes
- Display
- Integrations

## Template scope
- default
- role
- membership
- query/segment
- specific users only as exceptional override
- priority/conflict resolution

## Profile modes
- private account
- member directory profile
- public profile
- admin profile augmentation

## Fields
- core user fields
- linked Custom Fields group
- relation/listing data
- computed display field
- read-only field

## Field privacy
- private to user
- admins only
- logged-in users
- selected roles/memberships
- public

## Account actions
- name/profile updates
- email change through WordPress flow
- password change/reset through WordPress flow
- avatar
- delete/export privacy request integration
- active sessions link where WordPress supports it

## Public profile
- slug strategy
- title
- noindex/index
- canonical
- visibility

---

# 15. Membership System — Pro

Full detailed option-level contract: `MEMBERSHIP-SYSTEM.md`.

Inventory includes:
- Overview
- Plans
- Plan Groups
- Members/Enrollments
- Access Rules
- Benefits
- Drip/Expiration
- Upgrades/Downgrades
- Promotions
- Seats/Teams
- Pages/Messages
- Integrations
- Settings
- Audit
- Diagnostics
- Import/Export

The detailed spec defines plan fields, enrollment states, access-rule effects/subjects/resources, protected files, unauthorized behavior, trial/grace/duration, billing adapters, webhook safety, role sync, member-facing routes and entitlement semantics.

---

# 16. Builder Widgets Builder — Pro

## Screens
- Builder Adapters
- Components
- Add/Edit Blueprint
- Controls
- Data Bindings
- Render Template
- Style Controls
- Assets
- Conditions
- Builder Compatibility
- Preview/Test

## Blueprint identity
- name
- key
- category
- icon
- description
- supported builders

## Control
- field type
- key
- label
- default
- required
- responsive
- dynamic value allowed
- conditions
- group/tab

## Rendering
- approved component primitives
- semantic tag
- attributes allowlist
- dynamic tokens
- loops/repeaters with limits
- conditional blocks
- escaping context

## Styling
- spacing
- typography
- colors
- borders/radius
- dimensions
- alignment
- responsive values
- CSS variable/token output
- generated selector scoping

## Assets
- CSS entry
- JS entry
- dependency handles
- frontend/editor only
- conditional enqueue

## Per-builder adapter options
- Gutenberg block name/category/supports
- Elementor widget category/control mapping
- Bricks element mapping
- WPBakery shortcode/param mapping
- Visual Composer element mapping
- unsupported capability warnings

---

# 17. Forms & Workflow Builder — Pro

## Screens
- Forms
- Form Builder
- Fields
- Steps/Layout
- Conditional Logic
- Validation
- Submission
- Actions
- Workflow Graph
- Entries
- Run Logs
- Integrations
- Settings

## Form identity
- name
- key
- status
- description

## Form behavior
- guest/authenticated
- allowed roles/memberships
- open/close dates
- max entries global/per user
- require login
- save draft
- resume token expiry
- duplicate submission strategy

## Layout
- rows/columns
- field width
- multi-step
- step title
- progress indicator
- previous button
- step validation

## Form-only fields
- consent
- CAPTCHA adapter
- honeypot
- submit
- page break
- HTML/message display

## Submission
- store entry yes/no
- entry retention
- IP/user-agent capture disabled by default unless purpose exists
- success message
- redirect internal/validated external
- reset form

## File upload
- types/MIME
- size
- count
- destination
- private/public policy
- malware scanning adapter future

## Workflow trigger/action configuration
- event
- condition
- action
- input mapping
- retry
- timeout
- idempotency
- on failure
- manual approval
- delay/wait
- cancellation

## Entries
- columns
- filters
- search
- view/edit permissions
- notes
- export
- redact/delete

---

# 18. Cron Job Builder — Pro

## Screens
- Scheduled Events
- WPEssential Schedules
- Add/Edit Schedule
- Recurrences
- Run History
- Runner Health
- Diagnostics

## Event inventory
- hook
- source/owner hint
- next run
- schedule
- args preview
- lateness
- status

## WPE schedule
- name
- key
- active/paused
- one-time/recurring
- start date/time
- timezone
- recurrence preset/custom interval
- cron-expression-like input compiled/validated where supported
- missed-run policy
- overlap/concurrency policy
- action/Ability/workflow
- arguments
- timeout
- retry policy

## Actions
- run now
- pause/resume
- reschedule
- duplicate
- delete WPE-owned
- inspect third-party
- export

## Runner health
- WP-Cron enabled
- loopback health
- last runner tick
- late jobs count
- system cron/WP-CLI setup guidance

---

# 19. Notification System — Pro

## Screens
- Notification Rules
- Templates/Content
- Channels
- Recipient Rules
- Preferences
- Delivery Log
- Digests
- Diagnostics

## Rule
- name/key
- active
- event trigger
- conditions
- priority
- channels
- template
- recipients
- dedupe key/window
- schedule/delay

## Recipients
- specific users
- current actor/subject
- roles
- capabilities
- memberships
- relations
- query result
- explicit email/endpoint only with policy

## Channels
- in-app admin
- frontend dashboard
- email
- webhook
- future browser push/SMS/Slack/Teams adapters

## In-app
- title/body
- icon
- link/action
- read/unread
- expiry
- dismissible

## Preferences
- per channel opt-in/out where allowed
- mandatory transactional classification
- quiet hours
- timezone
- digest frequency

## Delivery
- queued/sent/delivered only if provider confirms/failed/suppressed
- retries
- error code
- provider reference

---

# 20. Emails Builder — Pro

## Screens
- Email Events
- Templates
- Template Builder
- Global Brand
- Sender Settings
- Preview/Test
- Delivery Log
- Compatibility

## Template identity
- name/key
- event
- subject
- preheader
- active
- HTML/plaintext

## Sender
- from name
- from address policy
- reply-to
- CC/BCC only where event policy permits

## Email-safe blocks
- text
- heading
- image
- button
- divider
- spacer
- columns
- list
- data table
- logo/header/footer
- dynamic token
- conditional section

## Design
- content width
- background
- font stack
- font size/line height
- link style
- button style
- spacing
- dark-mode-safe considerations

## Test
- sample event context
- recipient
- send test
- render HTML/plaintext
- missing-token warnings
- email client limitations note

## Delivery
- mail transport adapter
- queue
- retry
- logging level
- privacy retention

---

# 21. Message & Chat System — Pro

## Screens
- Conversations
- Moderation
- Chat Settings
- Initiation Rules
- Attachments
- Retention
- Realtime/Transport
- Diagnostics

## Conversation
- type 1:1/group
- title for groups
- participants
- initiator
- status active/archived/blocked
- last activity

## Initiation rules
- who may message whom
- same membership
- relation required
- role rules
- existing conversation reuse
- per-user rate limits

## Message
- text
- attachments
- reply-to
- edit window
- delete window
- read receipts toggle
- mentions future only if notification semantics defined

## Group
- add/remove member capability
- owner/admin role
- max members
- participant history visibility

## Attachments
- allowed MIME
- size/count
- protected storage/delivery
- retention

## Transport
- polling interval bounded
- long polling adapter optional
- WebSocket service adapter optional
- offline/retry behavior

## Safety
- block user
- report message
- moderation status
- retention/delete

---

# 22. REST API Builder — Pro

## Screens
- Endpoints
- Add/Edit Endpoint
- Route & Methods
- Request Schema
- Auth/Policy
- Action/Data Binding
- Response Schema
- Rate Limits
- CORS
- Test Console
- Logs/Metrics
- Export

## Route
- namespace
- version
- path
- HTTP methods
- route parameters

## Request
- query params
- path params
- headers allowlist
- JSON body schema
- required/default/type/min/max/pattern
- unknown field policy

## Auth
- same-site cookie+nonce
- Application Passwords
- registered OAuth/JWT adapter
- public GET only when explicit policy permits

## Policy
- capability
- membership/entitlement
- ownership/object policy
- conditions

## Binding
- Query read
- CRUD Data Source
- Ability
- Workflow

## Response
- status codes
- schema
- field projection
- pagination metadata
- error format

## Limits
- requests/window
- identity/IP key strategy
- max body
- max rows
- timeout

## CORS
- disabled/default same origin
- explicit origins
- methods
- headers
- credentials warning

---

# 23. Webhooks & Connections Manager — Pro

## Screens
- Connections
- Providers
- Inbound Webhooks
- Outbound Requests
- OAuth Connections
- Delivery Log
- Secrets
- Diagnostics

## Connection
- name/key
- provider/type
- base URL
- credential Vault refs
- environment label
- active
- test connection

## Outbound
- method
- URL/path template
- query/header/body mapping
- content type
- auth connection
- timeout
- retry/backoff
- accepted success codes
- response mapping
- max response size

## SSRF
- protocol allowlist
- block localhost/private/link-local/metadata destinations
- DNS re-resolution policy
- redirect policy
- explicit trusted endpoint exceptions privileged/audited

## Inbound
- path
- signing method
- secret
- allowed methods
- timestamp tolerance
- replay ID
- rate limit
- body size
- schema validation
- workflow binding

## OAuth
- client credential secret refs
- scopes
- callback
- token status
- refresh
- revoke/disconnect

---

# 24. Backup Manager — Pro

## Screens
- Backups
- Plans/Schedules
- Create Backup
- Destinations
- Retention
- Restore
- Activity
- Storage Health
- Diagnostics

## Backup scope
- database all/selected tables
- uploads
- themes
- plugins
- WordPress core optional
- selected files/paths
- WPEssential config
- exclusions

## Archive
- compression type/level
- chunk size
- archive split size
- manifest
- checksum algorithm
- archive encryption
- filename template

## Destination
- local/manual
- FTP/FTPS/SFTP
- WebDAV/Nextcloud/ownCloud
- S3 family and compatible providers
- GCS/Drive/Dropbox/OneDrive/SharePoint/Azure/Box/pCloud/OpenStack/etc. per verified adapter catalog
- credential Vault refs
- remote path/bucket/container
- multipart/chunk
- test

## Schedule
- manual
- interval
- daily/weekly/monthly
- time/timezone
- system-runner recommendation
- overlap policy

## Retention
- keep count
- keep days/weeks/months
- per destination
- protect manual/restore-point backup
- storage quota warning

## Restore
- full/selective
- verify checksum first
- DB tables/files selection
- URL/domain/path replacement
- maintenance mode
- dry-run/compatibility check
- pre-restore backup
- health check

---

# 25. Reset Manager — Pro

## Screens
- Reset Profiles
- New Reset
- Impact Preview
- Restore Point
- Execution
- History/Recovery

## Scope
- WPEssential only
- selected post types/content
- selected taxonomies
- users optional with extreme warning
- comments/media
- selected options
- database/site reset profile
- multisite site/network-specific modes

## Theme/plugin behavior
- keep installed
- deactivate
- reactivate selected after reset
- delete only in separately privileged flow
- preserve active theme
- inventory snapshot

## Mandatory safeguards
- impact count
- backup selection/create
- verify backup
- environment snapshot
- reauthentication
- typed confirmation phrase
- maintenance lock
- audit

## Post-reset
- admin account preservation/recovery
- rewrite flush once
- cache clear adapters
- health check
- restore action

---

# 26. Import / Export — Pro

## Screens
- Configuration Export
- Configuration Import
- Data Import
- Data Export
- Mapping
- Transformations
- Schedule
- Runs/Logs

## Config export
- all/select modules
- include dependencies
- include disabled definitions
- include revisions optional
- exclude secrets always default
- package format/version
- checksum

## Config import
- package validation
- schema compatibility
- dry run
- dependency map
- conflict: create/skip/replace/map
- stable UUID remap
- resource mapping
- rollback

## Data source formats
- CSV
- JSON
- XML
- spreadsheet adapter
- remote URL/connection with SSRF safety

## Mapping
- source path/column
- destination field
- default
- required
- lookup/match key
- relation mapping
- taxonomy mapping
- media import policy

## Transformation
- trim
- case
- date parse
- numeric parse
- replace/map values
- concatenate/split
- regex only with bounded/safe engine
- allowlisted expression functions

## Run
- batch size
- resume
- skip/error policy
- create/update/upsert
- missing source record behavior
- error report

---

# 27. Protector — Pro

## Screens
- Protection Rules
- Site Gate
- Admin/Login
- Path Rules
- Rate Limits
- Headers
- Recovery
- Logs

## Site gate
- disabled/password/login/membership/role
- password secret storage
- cookie/session duration
- bypass roles
- bypass IPs trusted-proxy aware
- message/template
- login CTA

## Path rule
- exact/prefix/pattern constrained
- frontend/admin
- allow/deny
- roles/capabilities/memberships/users
- IP/network only when properly parsed
- schedule
- response: 403/404/message/redirect/login

## Admin/login
- wp-admin restrictions
- login rate limit integration
- custom login alias optional
- XML-RPC/REST interaction warnings
- lost-password compatibility

## Rate limit
- action/route
- window
- max attempts
- key identity/IP/user
- lock duration
- trusted proxy config

## Security headers helper
- header
- value/preset
- compatibility warning
- report-only where applicable
- server/CDN ownership detection

## Recovery
- emergency constant/token mechanism
- bypass expiry
- audit

---

# 28. Watermarker / Media Rules — Pro

## Screens
- Watermark Rules
- Add/Edit Rule
- Watermark Assets
- Preview
- Batch Jobs
- Media Diagnostics

## Rule target
- MIME types
- image formats
- min width/height
- post type/context
- taxonomy
- upload user/role
- media folder/adapter
- selected include/exclude media

## Text watermark
- text
- font source restricted/licensed
- size
- weight where supported
- color
- opacity
- rotation

## Image/SVG watermark
- media asset
- SVG sanitization
- opacity
- scale percentage/max size
- rotation

## Position
- 9-point presets
- custom X/Y
- unit px/%
- margins
- tiled/repeat
- spacing

## Output
- derivative only
- apply to selected WordPress image sizes
- custom rendition
- quality
- preserve metadata policy
- WebP/AVIF compatibility adapter

## Batch
- regenerate selected/all matching
- batch size
- pause/resume/cancel
- remove/rebuild derivatives

Original source file is never modified by default contract.

---

# 29. XML-RPC Manager — Pro

## Screens
- Overview
- Methods
- Presets
- Rules
- Logs/Diagnostics

## Overview
- endpoint status
- authenticated XML-RPC enabled state
- pingback state
- detected Jetpack/mobile integrations
- recent request summary where logging enabled

## Method controls
- inventory method name
- source/core/plugin
- auth required
- allow/deny override
- group presets: publishing/media/users/comments/pingbacks/system

## Request rules
- IP allow/deny with proxy awareness
- rate limit
- request body size
- element/depth limits where supported
- logging level

## Presets
- compatibility/default
- block pingbacks
- authenticated methods only policy
- restrictive custom

UI must explain that `xmlrpc_enabled` does not globally remove every method.

---

# 30. Role & Capability Manager — Pro

## Screens
- Roles
- Role Editor
- Capability Matrix
- Users by Role
- Compare
- Presets
- Backup/Restore
- Diagnostics

## Role identity
- name
- slug
- clone from
- custom/core/discovered owner

## Capability matrix
- grouped by WordPress/core
- post types
- taxonomies
- WooCommerce adapter
- WPEssential modules
- third-party discovered
- search
- granted/not granted
- dangerous/admin-equivalent indicator

## Actions
- create
- clone
- rename display label
- grant/revoke capability
- delete custom role
- reassign users before delete
- import/export
- restore snapshot

## User role operations
- add role
- remove role
- primary display concept only if needed; WordPress supports multiple roles internally
- bulk assignment

## Anti-lockout
- protect current actor admin path
- Super Admin awareness
- administrator-equivalent diff warning
- recovery snapshot
- reauth for dangerous change

---

# 31. Support / Docs / Changelog / Account Center — Platform Surface

## WPEssential Home
- setup progress
- module health
- license/account state
- updates
- warnings
- recent activity
- quick create

## Modules
- module card
- Free/Pro badge
- installed/available/disabled/error state
- enable/disable
- dependency warnings
- settings shortcut
- docs shortcut

## Documentation
- searchable index
- module/category filters
- local bundled quick docs
- remote full docs link/content adapter
- version-aware notices

## Changelog
- current version
- release entries
- added/changed/fixed/security/deprecated
- read more

## Account & License
- connect/sign in
- sign up
- forgot password remote flow
- account identity
- license status
- plan
- expiry/renewal
- sites/activation where server supports
- refresh entitlement
- disconnect
- privacy data disclosure

## Support Tickets
- list
- status filter
- create
- subject/category/priority where supported server-side
- description
- attachments
- reply
- close/reopen
- delete local draft vs remote ticket semantics
- ticket history

## System Status / Diagnostics
- WordPress/PHP/DB versions
- memory/upload/time limits
- cron runner health
- REST loopback
- filesystem write checks
- module versions
- integration connection status
- database schema version
- error summary
- copy/download diagnostics with secrets/PII redaction
- optional support upload only after preview + consent

---

# Cross-module options that must exist consistently

Every module list screen should deliberately decide:
- search
- filters
- sorting
- pagination
- page size
- column visibility
- bulk selection
- bulk actions
- saved views where value exists
- import/export
- status filter
- owner/source filter for discovered third-party objects

Every module definition editor should deliberately decide:
- name/title
- stable key/UUID
- description/internal notes
- draft/active/disabled/archive lifecycle
- revisions
- duplicate
- export
- dependency graph
- capability/policy
- Ability exposure
- audit history
- diagnostics

Every destructive action should deliberately decide:
- dependency/impact preview
- backup/snapshot requirement
- reauthentication
- confirmation text
- capability
- background execution
- rollback/undo
- audit

Every remote operation should deliberately decide:
- connection/secret reference
- timeout
- retry
- rate limit
- idempotency
- response/body size
- SSRF/redirect policy
- logging/redaction
- degraded/offline UI

Every frontend renderable module should deliberately decide:
- server-side authorization
- loading/empty/error/403 states
- responsive behavior
- semantic HTML
- keyboard/focus accessibility
- cache context
- no global asset enqueue

# Gate

**Production implementation remains blocked until each module moves from Inventory to Specified/Accepted under `SPECIFICATION-STANDARD.md`.**
