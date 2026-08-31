# WPEssential — Platform Architecture

Status: **proposed architecture; Phase 0**  
No implementation is authorized by this document alone.

## 1. Architectural goal

WPEssential must behave as one extensible platform, not as a directory of independent plugins sharing a logo.

The architectural test for every new feature is:

> Can this capability be expressed through an existing WPEssential schema, registry, service, policy, ability, event, job, renderer, or adapter before a new mechanism is created?

## 2. Distribution topology

### `wpessential` — Free platform plugin

Contains:

- bootstrap/kernel
- module registry
- schema/definition infrastructure required by free modules
- CPT Builder
- Taxonomy Builder
- shared admin shell required by free features
- public extension contracts required for the Pro add-on

### `wpessential-pro` — Premium add-on

Depends on a compatible WPEssential Free version and registers premium modules into the same Module Registry.

The Pro add-on must not fork core services or maintain a parallel admin application.

### Optional first-party integration packages

Only create separate integration add-ons when one of these is true:

- dependency is very large
- integration has a materially different release cadence
- third-party licensing requires separation
- integration should not increase the base Pro package size

Otherwise use lazy adapters inside Pro.

## 3. Layering

### Layer A — Bootstrap

Responsibilities:

- environment checks
- constants/path setup
- Composer autoload
- kernel creation
- activation/deactivation routing
- fatal-safe compatibility notice

No module business logic belongs here.

### Layer B — Kernel / platform services

Owns:

- module registry
- service registry/container
- definition repository
- migrations
- capability/policy service
- ability registration bridge
- event bus
- job service
- audit logger
- secrets vault
- asset registry
- integration registry
- diagnostics

### Layer C — Domain engines

Reusable domain services:

- entity/data source engine
- field/schema engine
- relations
- query AST/compiler/executor
- condition engine
- renderer/template tokens
- workflow runtime
- import/export mapping

### Layer D — Modules

Modules compose platform/domain services. A module should contain:

- manifest
- domain/application services unique to the module
- admin route definitions
- REST/Ability registrations through platform services
- module migrations
- integration adapters
- tests

### Layer E — Presentation

- wp-admin React application
- native WordPress integration surfaces (list tables, metaboxes, profile screens, Site Health, etc.)
- frontend renderers
- builder adapters
- CLI/REST/MCP presentation layers

Presentation code must not become the source of business rules.

## 4. Namespaces and code organization direction

Proposed PHP root namespace:

`WPEssential\`

Avoid the old repo pattern where everything lives below `WPEssential\Plugins\` and where module PSR-4 mappings can drift independently.

Conceptual layout:

```text
wpessential/
  wpessential.php
  src/
    Bootstrap/
    Kernel/
    Contracts/
    Platform/
      Modules/
      Abilities/
      Assets/
      Audit/
      Auth/
      Capabilities/
      Database/
      Definitions/
      Diagnostics/
      Events/
      Jobs/
      Secrets/
      Integrations/
    Domain/
      Entities/
      Fields/
      Relations/
      Queries/
      Conditions/
      Rendering/
      Workflows/
      DataTransfer/
    Modules/
      PostTypes/
      Taxonomies/
  admin-ui/
  build/
  tests/
  docs/
```

Exact layout is locked only after the build ADR.

## 5. Module Manifest contract

Every module must declare metadata rather than relying on scattered hooks.

Conceptual fields:

```text
id
name
version
edition
description
dependencies
conflicts
minimum_platform_version
minimum_wp_version
minimum_php_version
capabilities
admin_routes
assets
migrations
abilities
health_checks
scheduled_jobs
import_export_types
uninstall_policy
```

The registry resolves dependency order, edition state, compatibility and activation state before module boot.

A module with missing dependencies enters `degraded`, not a fatal error.

## 6. Definitions as versioned configuration

Builder-created objects are **definitions**. Examples:

- CPT definition
- taxonomy definition
- field group
- relation
- query
- admin column set
- form
- workflow
- API endpoint
- dashboard/menu/profile definition
- email template

Every definition carries:

- immutable UUID
- human slug
- definition type
- schema version
- module owner
- status (`draft`, `published`, `disabled`, etc.)
- canonical JSON payload
- revision/version number
- created/updated metadata
- dependency references by UUID where possible
- checksum

### Storage principle

Do not put all runtime site data into a generic JSON/EAV table.

A generic definition repository may store **configuration definitions**, while actual content remains in the most appropriate storage:

- WP posts/meta/options/terms/users when WordPress semantics are desired
- purpose-built module tables for high-volume operational data
- user-created custom tables through Custom Tables Builder

## 7. Definition dependency graph

WPEssential must be able to answer:

- What uses this field?
- What breaks if this query is deleted?
- Which forms write to this table?
- Which dashboards use this listing?
- Which endpoints expose this data source?

Therefore definitions expose declared dependencies. Deletion flow:

1. resolve dependents
2. show impact
3. block destructive delete when hard dependencies exist, unless a supported cascade/migration exists
4. allow disable/archive when deletion is unsafe
5. audit the decision

## 8. Entity / Data Source Registry

A single contract describes readable/writable data sources.

First-party adapters should cover:

- posts/CPTs
- terms/taxonomies
- users
- comments
- media
- options/settings
- custom tables
- WooCommerce entities only through optional integration
- REST/remote sources for read/query where configured
- module-owned entities such as form entries

Each adapter declares capabilities such as:

- get one
- list/query
- create
- update
- delete
- schema
- searchable
- sortable
- filterable
- transactional semantics if any
- authorization policy

UI and AI never infer writable operations merely because a source can be read.

## 9. Field Schema Registry

A field definition is portable across modules where semantics match.

Common contract:

- type
- label/name
- data type
- default
- required/nullability
- validation rules
- sanitization strategy
- output escaping context
- choices/options source
- conditional visibility
- repeatability
- storage mapping
- REST/Ability exposure flags
- privacy classification

UI controls and stored data types must be separate concepts. A date picker is presentation; its canonical value format is data schema.

## 10. Relation Engine

Relations are first-class, not hidden inside one field type.

Support direction:

- one-to-one
- one-to-many
- many-to-many
- directional labels
- optional bidirectional traversal
- sortable relation items where needed
- relation metadata/pivot fields
- delete behavior: restrict / detach / cascade only where explicitly safe
- relation querying and counts
- REST/Ability exposure

Storage is selected by scale and semantics; large many-to-many relationships should not be forced into serialized post meta.

## 11. Query Engine

The Query Builder produces an internal **query AST**, not raw SQL strings as its canonical format.

AST concepts:

- source
- selected fields
- filters/conditions
- relation traversal
- joins where adapter supports them
- grouping
- aggregates
- ordering
- pagination
- distinct
- computed fields
- parameters/placeholders

Compilers translate the AST to:

- `WP_Query`
- `WP_Term_Query`
- `WP_User_Query`
- safe `$wpdb` prepared SQL for supported custom-table queries
- remote REST request adapters
- other registered query providers

Raw SQL, when available in developer tooling, is an explicit escape hatch and never the default generated definition.

Query preview must show execution time, row count estimate/limit where possible, cache state, and warnings for obviously expensive patterns.

## 12. Rendering / dynamic values

One token/value resolver supplies dynamic data to:

- listings
- dashboards
- columns
- emails
- notifications
- forms
- builder adapters
- shortcodes/blocks

Renderer contexts declare escaping rules:

- HTML text
- HTML attribute
- URL
- JavaScript data
- email HTML
- plain text
- JSON

A value provider must not return “already safe HTML” by default.

## 13. Conditional Logic Engine

Shared condition representation is used by:

- form visibility
- field visibility
- workflow branches
- admin column conditions
- dashboard/menu visibility
- notifications
- template/listing visibility
- role/user targeting

Conditions use typed operators. Arbitrary PHP is not a condition type.

## 14. Policy and capabilities

WordPress capabilities remain the authorization foundation.

WPEssential adds named capabilities at platform/module/action granularity, for example conceptually:

- `wpe_manage_platform`
- `wpe_manage_post_types`
- `wpe_manage_taxonomies`
- `wpe_manage_queries`
- `wpe_run_backups`
- `wpe_restore_backups`
- `wpe_manage_roles`
- `wpe_manage_secrets`

High-risk operations use separate capabilities; `manage_options` alone is not a blanket design strategy.

Policies can add resource-aware checks, but must resolve to the authenticated principal and cannot override a denied core capability without an explicit safe design.

Multisite Super Admin boundaries require dedicated handling.

## 15. WordPress Abilities integration

WordPress 6.9+ Abilities API is the preferred discoverable action contract.

WPEssential creates an internal ability descriptor compatible with WordPress abilities. On supported WordPress versions, descriptors register through the core API.

Ability classes:

- query/read
- create/update
- delete/destructive
- operation/job
- diagnostic

Every ability has permission logic and input/output schema. Public REST/AI exposure is **opt-in per ability** and is not implied by internal registration.

Where minimum WordPress support eventually includes pre-6.9 versions, a compatibility adapter may provide internal use without pretending core Abilities exist.

## 16. AI / MCP boundary

MCP is an adapter over approved abilities, not a privileged back door.

Rules:

- disabled unless explicitly enabled
- expose allowlisted abilities only
- same principal authorization applies
- read-only defaults for new AI connections
- destructive operations require confirmation policy where human-in-the-loop mode is enabled
- audit actor, ability, inputs (with secret redaction), result and correlation ID
- no raw database shell, PHP eval, file editor, secret dump, or arbitrary filesystem ability

## 17. Event Bus

Use typed event objects for internal domain events rather than passing undocumented positional arrays through custom hooks.

Examples:

- `DefinitionPublished`
- `FormSubmitted`
- `EntityCreated`
- `EntityUpdated`
- `WorkflowFailed`
- `BackupCompleted`
- `LicenseStateChanged`

WordPress actions can bridge to/from typed events at defined extension points.

Events do not automatically imply asynchronous execution.

## 18. Jobs / Scheduler

Separate **schedule definition** from **job execution**.

WP-Cron is traffic-triggered and cannot guarantee exact wall-clock execution. WPEssential therefore needs a job abstraction that supports:

- enqueue now
- schedule once
- recurring schedule
- retries/backoff
- idempotency key
- lock/claim
- timeout
- progress
- cancellation
- failure state
- logs
- WP-CLI/system-cron runner option

Action Scheduler is a serious candidate because it is built for distributable WordPress background queues and provides traceable actions; dependency adoption is not final until ADR review.

Cron Builder should manage schedules and inspect existing WP-Cron hooks without claiming that WP-Cron is an exact system cron.

## 19. Database and migrations

Core migration rules:

- explicit schema version
- ordered migration classes
- forward migration tests
- rollback where practical; otherwise documented recovery path
- no destructive migration without backup/impact strategy
- indexes designed from query patterns
- foreign keys only after compatibility analysis with WordPress hosting realities
- large migrations chunked/backgrounded
- multisite table scope explicit

Do not treat `dbDelta()` as a complete migration framework for every complex change.

## 20. Module-owned tables

Likely operational tables (names illustrative, not locked):

- definitions / definition revisions
- audit log
- job metadata only if selected queue engine requires WPEssential tables
- workflow runs/steps
- notification deliveries
- chat conversations/participants/messages
- encrypted credential records/metadata

Tables are introduced only with a documented ownership, retention and uninstall policy.

## 21. Secrets Vault

Provider credentials and tokens are not ordinary settings fields.

Vault requirements:

- secrets redacted in API/log/UI
- encrypted at rest where the runtime provides a defensible key strategy
- key material must not be stored beside ciphertext in a way that provides no security benefit
- provider scopes minimized
- token refresh centralized
- connection test without revealing the secret
- rotation/revocation support
- exports exclude secrets by default
- support diagnostics never include secret values

If strong at-rest encryption cannot be guaranteed on a given host, the UI/documentation must state the limitation rather than claim false security.

## 22. REST architecture

Same-site React admin should use WordPress REST authentication with logged-in cookies + REST nonce.

External baseline authentication is WordPress Application Passwords over HTTPS. Other auth methods are adapter-based and separately reviewed.

Every endpoint has:

- namespace/version
- schema/validation
- permission callback
- bounded pagination
- output shaping
- safe error contract
- rate/abuse controls where appropriate
- audit for sensitive mutations

REST API Builder cannot create unauthenticated mutation endpoints by accident; insecure combinations require explicit prevention, not warnings alone.

## 23. Admin application architecture

One WPEssential React shell owns routing/layout/navigation. Modules contribute route manifests and screens.

Principles:

- route-level code splitting
- module-level bundles
- typed API client generated/derived from endpoint schemas where practical
- WordPress packages externalized where supported to avoid duplicate runtime copies
- no global DOM monkey-patching of wp-admin
- native screens are enhanced through documented hooks rather than iframe recreation

Untitled UI and WordPress components may coexist behind WPEssential wrapper components so the product can change underlying UI libraries without rewriting domain screens.

## 24. Asset Registry

Every asset declares:

- handle
- module owner
- admin/frontend scope
- route/screen match
- dependencies
- version/hash
- load strategy

A test must detect optional module assets accidentally loaded on unrelated wp-admin screens.

Frontend assets should support discovery during render/block registration rather than site-wide enqueues.

## 25. Page-builder adapter architecture

Builder Widget definitions use a neutral **Component Blueprint**:

- settings/control schema
- dynamic data bindings
- render template/component
- style tokens/controls
- responsive options
- dependencies

Adapters compile/register that blueprint for a builder where feasible.

Priority:

1. Gutenberg / native block registration
2. WordPress shortcode/dynamic render fallback
3. Elementor
4. Bricks
5. WPBakery
6. Visual Composer
7. other adapters based on demand and documented APIs

Separate admin screens per builder may exist, but the underlying component definition and rendering contracts stay shared.

## 26. Email rendering boundary

Email templates use an email-specific component schema and renderer. Browser-builder output is not the canonical email format.

Renderer requirements:

- inline/safe CSS strategy
- table/layout compatibility where necessary
- responsive constraints
- sanitized dynamic content
- plaintext fallback
- preheader
- test send
- preview modes
- provider-independent output before `wp_mail`/SMTP integration

## 27. Caching

Caching is opt-in by evidence and has explicit invalidation.

Potential cache targets:

- definition registry
- compiled query plan
- relation lookup
- expensive admin column values
- remote query result
- rendered listing fragments where context permits

Cache keys include relevant definition/version/permission context. Never cache privileged output and serve it to a less-privileged user.

## 28. Observability

Platform logs structured events with correlation IDs where meaningful.

Log classes:

- platform errors
- module health
- workflow/job events
- integration failures
- security/audit events

Requirements:

- production-safe messages
- no secrets
- configurable retention
- user-facing diagnostics for actionable failures
- Site Health integration
- support bundle generation with preview/redaction

Do not unconditionally `error_log()` every admin request as the legacy prototype does.

## 29. Installation / upgrade / uninstall

### Activation

- check environment
- install/upgrade platform schema idempotently
- register capabilities safely
- mark first-run state
- redirect to wizard only on intentional admin activation flow, never during bulk/network activation in a way that breaks UX

### Deactivation

- stop WPEssential-owned recurring dispatch where appropriate
- preserve data/config
- do not treat deactivation as uninstall

### Uninstall

Default: preserve data unless user has previously enabled “delete data on uninstall” with explicit warnings.

Even then, module-owned generated tables/files must be enumerated and deletion bounded to WPEssential ownership.

## 30. Failure isolation

One optional module failure should not take down WordPress.

Where feasible:

- validate module compatibility before boot
- catch recoverable integration initialization errors
- mark module unhealthy/degraded
- provide diagnostics
- keep kernel/free modules operational

PHP fatal errors cannot always be recovered; therefore module boot paths must remain small and compatibility-tested.

## 31. Extension SDK

First-party code is not the only consumer. Provide documented extension points for:

- field types
- data sources
- query providers/operators
- relation storage adapters
- workflow triggers/actions
- notification channels
- backup storage providers
- builder adapters
- import/export mappings
- abilities
- module registrations

Public contracts follow semantic versioning/deprecation policy and receive integration tests.

## 32. Architectural anti-patterns

Do not introduce:

- raw `eval()` for user-defined PHP
- arbitrary SQL as the canonical query format
- global asset enqueues
- one giant admin JS bundle
- module-specific duplicate auth systems
- secrets in `wp_localize_script`
- serialized mega-options containing unrelated module state
- generic EAV storage for every content type
- direct DB writes that bypass the selected data source contract without reason
- business logic only in React
- permission checks only in UI
- “hidden URL” as authorization
- silent remote telemetry/account calls

## 33. Architecture review gate

Before the first source-code milestone, ADRs must finalize:

1. WordPress/PHP compatibility floor
2. frontend/admin build toolchain
3. React/WordPress package externalization strategy
4. job queue implementation/dependency
5. core definition storage schema
6. secret storage/key strategy
7. Free ↔ Pro compatibility/version protocol
8. test/CI matrix

Until those decisions are committed, architecture remains **proposed** rather than “done.”
