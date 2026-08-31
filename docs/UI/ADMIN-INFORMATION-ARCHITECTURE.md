# WPEssential — Admin Information Architecture

Status: Phase 0 planning. No runtime implementation authorized.

## Goal
Keep 30+ modules usable without turning wp-admin into a flat wall of menu items.

## Top-level WordPress menu
Single parent: **WPEssential**.

Recommended first-level items:
1. Home
2. Modules
3. Content Model
4. Data & Query
5. Admin & Experience
6. Identity & Access
7. Automation & Communication
8. Integrations & Data Movement
9. Operations & Protection
10. Documentation
11. Support
12. Account & License

Only enabled/available suites appear where appropriate, but users can always reach Modules and recovery/status surfaces.

## Suite mapping
### Content Model
- Custom Post Types
- Taxonomies
- Custom Fields
- Relations
- Statuses

### Data & Query
- Queries
- Custom Tables
- Admin Columns
- Listings & Templates

### Admin & Experience
- Dashboard Widgets
- Admin Menu
- Settings Pages
- Frontend Dashboards
- User Profiles
- Builder Widgets

### Identity & Access
- Memberships
- Members / Enrollments
- Access Rules
- Teams / Seats
- Roles & Capabilities

### Automation & Communication
- Forms
- Workflows
- Cron / Schedules
- Notifications
- Email Templates
- Chat

### Integrations & Data Movement
- REST APIs
- Webhooks & Connections
- Import / Export

### Operations & Protection
- Backups
- Reset Manager
- Protector
- Watermark / Media Rules
- XML-RPC
- Diagnostics

## Navigation depth
WPEssential admin application itself should target maximum **3 navigational levels** in normal UX:
- suite
- module
- module tab/subview

Deep object configuration should use tabs/steps/panels inside a route, not more wp-admin submenu nesting.

Frontend Dashboard Builder has its own configurable navigation model and may support up to 5 levels as already specified, with warnings beyond 3.

## Universal builder shell
Definition-driven module screens use the same shell:

### List view
- page title + concise description
- Create action
- search
- filters
- saved views where relevant
- bulk actions
- table/DataView/grid
- status
- Used By/dependency indicators
- updated date/author where useful
- row actions
- pagination

### Editor view
Header:
- Back
- definition title/status
- Save Draft
- Preview/Test
- Publish/Enable
- overflow menu

Body groups:
1. General
2. Structure / Logic
3. Data / Source
4. Conditions / Permissions
5. Integration
6. Advanced

Side/context surface where useful:
- status
- dependency health
- Used By
- revision
- validation/errors
- docs link

### Observe view
Modules with runtime behavior expose a separate operational view rather than mixing logs into the editor:
- runs
- failures
- queue state
- deliveries
- access explanation
- backup status
- import history

## Module Home pattern
Complex modules may have an overview with:
- health/status
- key counts
- recent failures
- quick actions
- setup completion
- relevant integration health

Avoid vanity analytics that do not help an administrator act.

## Module Manager
Modules page supports:
- search/filter by suite/Free/Pro/status
- enable/disable
- dependency/degraded state
- edition badge
- short purpose description
- docs
- settings
- health
- upgrade action for unavailable Pro modules

Enable/Disable does not happen silently when dependencies are affected; show impact preview.

## Global command/search concept
A command/search palette can be introduced after core navigation is stable. Candidate searchable targets:
- module/page navigation
- definitions by name/key
- documentation
- recent failures
- safe registered actions

It must respect permissions; search result visibility cannot leak protected definition names/data.

## Contextual cross-module creation
Cross-module links should preserve context without embedding duplicate mini-builders.

Example from CPT editor:
- “Add Field Group” opens Fields creation with target CPT preselected.
- “Add Admin Columns” opens Columns with object type preselected.
- “Create Listing” opens Listings with source/query context.

The owning module still saves its own definition.

## Draft/publish UX
Where definitions affect production behavior:
- draft changes do not alter published runtime by default;
- unsaved changes warning;
- validation summary before publish;
- show published vs draft revision state;
- impact preview for destructive/structural changes;
- rollback/revision action where supported.

## Empty states
Each module empty state should answer:
- what the module does;
- one primary Create/Set up action;
- optional import/template action;
- docs link;
- dependency/integration requirement if relevant.

Do not fill empty states with marketing clutter for already licensed users.

## Error/degraded states
Distinguish:
- permission denied
- Pro unavailable/expired
- missing dependency
- migration required
- integration disconnected
- validation error
- runtime failure
- network/provider outage

Each needs a specific recovery action rather than generic “Something went wrong.”

## License/Pro UX
Unavailable Pro module:
- visible in Modules catalog with clear Pro badge/benefit summary;
- no fake disabled controls throughout unrelated screens;
- Upgrade/Start Trial route.

Expired Pro definition:
- read-only management where appropriate;
- preserve data/runtime according to ADR-0007;
- show exact module/renewal state;
- export remains accessible.

## Accessibility
- keyboard-complete list/editor/dialog workflows;
- no drag-only interactions;
- semantic headings/landmarks;
- focus restoration after dialogs/navigation;
- status not color-only;
- table/grid alternatives where necessary;
- async save/error announcements;
- destructive confirmation readable by assistive tech.

## Responsive admin behavior
Desktop is primary for complex builders, but ordinary navigation/list/read/edit flows must remain usable on tablet/smaller widths.

On narrower screens:
- side panels become drawers/accordions;
- dense DataViews switch to priority columns/cards where appropriate;
- action bars avoid horizontal overflow;
- no fixed-width canvas required for basic configuration.

## Asset boundary
Each suite/module route declares its asset entry points. Navigating to WPEssential Home must not eagerly download all builder/editor/provider code.

## URL/route stability
Internal routes should use stable semantic slugs. Renaming UI labels must not break saved admin URLs/bookmarks unnecessarily.

## Implementation acceptance later
Before UI implementation is accepted:
- navigation usability tested with all modules enabled;
- keyboard flow verified;
- 1k+ definition list fixture remains usable;
- module-level code splitting/asset isolation measured;
- permissions do not leak hidden routes/results;
- Free-only installation remains uncluttered;
- Pro-expired/degraded states tested;
- RTL/localization verified.