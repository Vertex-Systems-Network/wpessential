# WPEssential — Product Master Plan

Status: **Phase 0 / planning source of truth**  
Last reviewed: 2026-08-27

## 1. Product thesis

WPEssential is a modular WordPress application platform that lets site builders model data, construct admin/front-end experiences, automate work, integrate systems, operate a site, and manage identity/access/membership without installing a fragmented stack of unrelated plugins.

The target is not “more checkboxes than JetEngine/ACF/Gravity Forms/MemberPress.” The target is a **coherent platform** where modules reuse the same schema, query, relation, policy, entitlement, workflow, rendering, job, audit, and integration primitives.

A definition created once should be reusable everywhere. Examples:

- A custom field created for a CPT can become a query filter, admin column, form field target, REST field, workflow condition, email token, notification token, dashboard widget value, AI-readable schema, or import/export mapping.
- A query can power an admin column, listing, table, dashboard card, form options, REST endpoint, scheduled job, email digest, membership segment, or AI ability.
- A workflow action can be invoked by a form, cron schedule, webhook, post transition, membership lifecycle event, REST call, manual admin action, or approved AI agent.
- A membership entitlement can control content, downloads, dashboard routes, forms, menu visibility, notifications, REST/Ability access, and third-party benefits without being reduced to a WordPress role.

## 2. Product suites

Individual modules remain independently enable/disable-able, but navigation is grouped into suites to avoid a 25+ item admin menu.

### Content Model
- Custom Post Types Builder — **Free**
- Taxonomy Builder — **Free**
- Custom Fields Builder — Pro
- Relations Builder — Pro (new; required strategic module)
- Status Manager — Pro

### Data & Query
- Custom Query Builder — Pro
- Custom Tables Builder — Pro
- Admin Columns Builder — Pro
- Dynamic Listings / Template Builder — Pro (new; closes a major JetEngine gap)

### Admin & Experience
- Dashboard Widgets Manager — Pro
- Custom Admin Menu Builder — Pro
- Settings Page Builder — Pro
- Dashboard Builder — Pro
- Builder Widgets Builder — Pro

### Identity, Membership & Access
- User Profile Builder — Pro
- Membership System — Pro (new; plan/enrollment/entitlement/access lifecycle)
- Role & Capability Manager — Pro

### Automation & Communication
- Forms & Workflow Builder — Pro
- Cron Job Builder — Pro
- Notification System — Pro
- Emails Builder — Pro
- Message & Chat System — Pro

### Integration & Data Movement
- REST API Builder — Pro
- Import / Export — Pro
- Webhooks & Connections Manager — Pro (new shared user-facing integration module)

### Operations & Protection
- Backup Manager — Pro
- Reset Manager — Pro
- Protector — Pro
- Watermarker / Media Rules — Pro
- XML-RPC Manager — Pro

### Platform surfaces
- WPEssential Home / Modules
- Documentation
- Changelog
- Support Tickets
- Account & License
- System Status / Diagnostics

## 3. Core services that are not sellable modules

These are platform infrastructure and must not be duplicated per module:

1. Module Registry and lifecycle manager
2. Entity / Data Source Registry
3. Field Schema Registry
4. Query Engine
5. Relation Engine
6. Renderer / Template Engine
7. Conditional Logic Engine
8. Policy / Capability Engine
9. Entitlement Engine
10. Ability Registry (WordPress Abilities API)
11. Event Bus
12. Workflow Runtime
13. Job Queue / Scheduler
14. Credentials / Secrets Vault
15. Integration Adapter Registry
16. Audit Log and observability
17. Definition versioning and migration service
18. Import / Export package service
19. Asset Manifest and scoped loader
20. Feature flags / Labs service
21. Diagnostics / Site Health integration
22. Developer SDK / extension contracts

A feature that can be expressed through one of these shared primitives must not invent a second incompatible mechanism.

### Entitlement Engine rule

Memberships, trials, complimentary grants, commerce purchases, subscriptions, seat assignments and workflow grants may create entitlements. Consumers ask the Entitlement/Policy layer whether an action/resource is allowed; they do not directly inspect a specific payment plugin or misuse WordPress roles as membership state.

## 4. Free / Pro model

### WPEssential Free

The WordPress.org package permanently includes and enables:

- Custom Post Types Builder
- Taxonomy Builder
- shared platform code required for those modules
- account connection UI only as an optional path
- documentation/changelog/support entry points

Free local functionality must work without creating a WPEssential account.

### WPEssential Pro

Premium functionality is shipped as a **separate add-on package outside WordPress.org**. This is required because WordPress.org does not permit a plugin to contain locked functionality that becomes available only after payment, or to disable included functionality after a trial ends.

The Pro add-on can use WPEssential Free as its platform dependency.

### Trial

The requested 30-day trial applies to the **Pro add-on entitlement**, not to hidden premium code embedded in the WordPress.org package.

Activation wizard path:

1. Welcome / what WPEssential does
2. Choose:
   - Continue Free — no account required
   - Connect account / Sign in
   - Create account
3. Optional Pro trial offer
4. If accepted, obtain/install/activate the separately distributed Pro add-on through an allowed distribution flow
5. Choose recommended module preset or custom module selection
6. Finish → WPEssential Home

Forgot password, purchase, renew, plans, account state and support communicate with WPEssential services only after explicit user action/consent.

## 5. Entitlement and expiry behavior

WPEssential product entitlement affects **editing and creation**, not ownership of user data. This product-license entitlement is separate from site-user Membership System entitlements.

On WPEssential Pro expiry:

- Never delete module definitions, settings, content, custom tables, fields, relations, form entries, membership enrollments, backup metadata, or generated assets.
- Admin definitions become read-only when their module is no longer entitled.
- Show the exact affected module and a contextual Upgrade/Renew action.
- Existing public output should continue to render where it is safe and technically possible so a production site does not suddenly break because a billing event occurred.
- Mutating automations that could keep creating costs/data after expiry should stop safely and be marked “Paused — license required.”
- Security and membership access protections must not be silently removed merely because a WPEssential license expired. A safe last-known configuration remains active until the administrator changes it or removes Pro.
- A site owner can export all WPEssential configuration/data regardless of subscription state.

Any future proposal to blank public output or expose protected content on expiry requires a dedicated ADR and product/security/legal review.

## 6. Module lifecycle

Each module exposes a manifest containing at minimum:

- module ID / slug
- human name and description
- module version
- edition (`core`, `free`, `pro`)
- dependencies and conflicts
- minimum WPEssential / WordPress / PHP versions
- required capabilities
- admin routes
- REST/Ability registrations
- migrations
- asset entry points
- health checks
- scheduled tasks
- uninstall policy
- import/export handlers
- extension hooks

States:

- unavailable
- available-disabled
- enabled
- degraded (dependency missing)
- read-only (entitlement)
- paused (runtime safety)
- migration-required
- unhealthy

Disabling a module unregisters nonessential hooks/assets/background work but preserves data. Security/access modules may require a documented minimal enforcement runtime on disable so disabling management UI does not accidentally expose protected resources. “Delete module data” is a separate destructive action.

## 7. AI-native product definition

“AI-native” does **not** mean allowing a model to execute arbitrary PHP or SQL.

WPEssential actions are registered as typed, permission-aware abilities wherever practical. An ability defines:

- stable name
- human description
- JSON-schema input
- JSON-schema output
- permission callback / policy
- read/write/destructive annotation
- idempotency expectations
- dry-run support where meaningful
- audit metadata
- implementation callback

The same action can then be consumed by WPEssential UI, REST, workflow actions, WP-CLI, approved automation, and opt-in AI/MCP adapters without bypassing authorization.

AI features must:

- require explicit opt-in when external providers are involved
- prefer the provider-agnostic WordPress AI Client/Connectors architecture where supported
- never receive secrets unless a narrowly scoped connector explicitly requires them
- never gain more permissions than the authenticated principal
- show preview/diff before destructive generated changes
- leave an audit trail of proposed and executed actions
- support undo/rollback where the underlying operation allows it

## 8. Definition-driven UX

Builders should use a common interaction grammar:

**List → Create/Edit → Configure → Preview/Test → Publish/Enable → Observe → Version/Export**

Every builder should include, where relevant:

- draft/published state
- unsaved-change protection
- validation before save
- preview/test mode
- duplicate
- revision history
- import/export
- contextual docs
- permission controls
- dependencies/usage view (“Used by…”) before deletion
- audit history
- empty/loading/error/success/offline states

### Option-level planning gate

High-level feature bullets are not implementation specifications.

Before any production module code begins:

1. `docs/MODULES/OPTION-INVENTORY.md` must include every known screen, field, toggle, selector, row action and lifecycle behavior for **every module**.
2. The module must satisfy `docs/MODULES/SPECIFICATION-STANDARD.md`.
3. Exact defaults, validation, permissions, data ownership, state transitions, failure behavior, assets, integration contracts and tests must be resolved in the module spec/ADRs.
4. If implementation discovers an unplanned option, the documentation is updated before or in the same coherent change; implementation must not silently invent product behavior.

The Membership System has the first full detailed module spec at `docs/MODULES/MEMBERSHIP-SYSTEM.md`; the same standard applies to every other module.

## 9. Design system direction

Admin UI stack:

- React + TypeScript
- modern bundler selected once in ADR; no mixed Laravel Mix/Vite toolchain
- Untitled UI React **only for components explicitly available under MIT** unless a separate commercial-license review approves more
- Lucide icons (ISC) as the default WPEssential icon set
- WordPress `@wordpress/components` / `@wordpress/dataviews` where native behavior, accessibility, tables/forms, or interoperability is stronger
- scoped styling so WPEssential does not leak CSS into wp-admin or third-party screens

UI requirements:

- WCAG-oriented keyboard/focus behavior
- responsive admin layouts
- no color-only status communication
- predictable destructive confirmations
- command/search palette considered after core workflows are stable
- no custom CSS required from end users for ordinary builder workflows

## 10. Asset loading contract

A module must not enqueue its CSS/JS globally.

Rules:

- Admin assets load only on exact WPEssential/admin integration screens that need them.
- Frontend assets load only when a WPEssential-rendered object is present or explicitly requested.
- Builder adapters load only if that third-party builder is active and the relevant integration is used.
- Shared runtime chunks load once and only when one or more enabled features require them.
- Third-party libraries are tree-shaken/split where practical.
- Every module has an asset budget recorded during implementation.

## 11. Compatibility policy direction

Planning is validated against WordPress 7.1 (released 2026-08-19). Minimum supported WordPress/PHP versions are intentionally not guessed here; ADR-0002 must lock them after compatibility, market-share and dependency testing.

Support matrix must include:

- current stable WordPress
- previous supported major versions per policy
- PHP versions selected by ADR
- single site and multisite where module semantics apply
- MySQL/MariaDB supported by WordPress policy
- common object/page cache configurations
- major builders/integrations only when their adapters are enabled

## 12. Support, documentation and changelog product surfaces

WPEssential Home contains:

- health/status overview
- enabled modules
- recent automation failures
- latest backup status
- membership/access synchronization warnings when Membership is enabled
- documentation search
- changelog
- account/license state (if connected)
- support

Support center supports, via WPEssential service API:

- ticket list
- create ticket
- read ticket
- reply
- update metadata
- close/reopen
- attachment upload subject to strict file policy

Ticket deletion should normally be service-side retention policy rather than arbitrary local deletion if support/legal records must be preserved.

No support request may automatically upload logs, site data, plugin lists, user data, or database samples without a clear preview and consent.

## 13. Product decisions that reject unsafe assumptions

### Arbitrary PHP in Cron Builder
Not a normal feature. `eval()`-style PHP execution is remote-code-execution by design. Standard actions use registered hooks/abilities/workflow actions/HTTP calls. A future developer-only code runner, if ever approved, requires a separate threat model and must not be enabled by default.

### Arbitrary phpMyAdmin-like SQL console
Not a normal admin feature. Custom Tables provides schema design, safe data browsing and query building. SQL console defaults to read-only `SELECT`/`EXPLAIN`; destructive SQL would require a separately gated developer mode, transaction/backup protections, re-authentication and audit logging.

### Elementor/WPBakery HTML as email markup
Not promised. Browser page-builder output is not reliably email-client compatible. Email Builder uses an email-safe schema/renderer and may import dynamic content/tokens from other builders, not blindly ship frontend markup to email clients.

### “Hide login URL = security”
URL obfuscation can reduce automated noise but is not an authorization boundary. Protector must rely on authentication, capabilities, rate limits, access rules and secure recovery paths.

### Reset video recording
Not a core server reset guarantee. Reset Manager produces a restore point: backup reference, environment snapshot, active plugin/theme inventory, selected settings, checksums and audit record. Optional visual capture can be explored separately.

### Membership = WordPress role
Rejected. Roles/capabilities and membership entitlements solve different problems. Role synchronization is optional and one of several membership side effects; it is never the canonical membership state.

### WPEssential as payment-card processor
Rejected. Membership may integrate with billing/commerce systems and consume signed lifecycle events, but WPEssential core does not store/process card credentials.

## 14. Success metrics

Feature count is not the primary KPI. Track:

- time to build a representative application workflow
- number of duplicated concepts a user must configure
- average admin request overhead with all unused modules disabled
- JS/CSS loaded on non-WPEssential screens (target: none from optional modules)
- failed background jobs and recovery rate
- restore success rate
- query latency / expensive-query warnings
- permission/security regression rate
- membership access-rule explainability and sync failure rate
- support tickets per active site by module
- migration success rate
- upgrade rollback incidents
- accessibility defects
- trial-to-paid conversion and renewal without breaking sites

## 15. Phase 0 exit gate

Production feature coding may start only when:

- product/module catalog including Membership is accepted
- **all modules have option-level inventories and detailed specifications under `docs/MODULES/`**
- known option semantics/defaults/validation/permissions are resolved or explicitly blocked by ADR
- core architecture and data ownership boundaries are documented
- commercial distribution model is accepted
- security rules are documented
- compatibility ADR is decided
- build/UI ADR is decided
- background job strategy is decided
- initial CI/test matrix is defined
- first implementation milestone and Definition of Done are checkpointed

Until then the project remains **PARTIALLY COMPLETE — planning only**.
