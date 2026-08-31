# WPEssential — Development Roadmap

Status: **Phase 0 planning**

This roadmap is dependency- and risk-ordered, not a promise of calendar dates. A later phase does not start because a date arrived; it starts because the preceding exit gate is verified.

## Sequencing principle

Do **not** build all requested modules in parallel. That would create duplicate schemas, authorization, query logic, UI patterns and migrations before the common platform is stable.

Build the shared primitives once, then make later modules composition-heavy rather than foundation-heavy.

---

## Phase 0 — Research, architecture, engineering system — CURRENT

### Deliverables
- product master plan
- module catalog
- competitive/platform research
- architecture
- security model
- commercial/distribution model
- quality gates
- engineering/AI rules
- ADRs
- checkpoint
- implementation roadmap
- detailed module specification standard
- option-level inventory for every module
- detailed specification for every module before source implementation

### Decisions still required before code
- minimum WordPress/PHP
- Composer/Node/build toolchain
- WordPress React-package externalization strategy
- definition storage schema
- entitlement/policy boundary
- background job engine
- secret encryption/key model
- Free/Pro compatibility protocol
- CI matrix

### Module specification gate

Before production source implementation starts, **every module** must move from Inventory → Specified/Accepted under `docs/MODULES/SPECIFICATION-STANDARD.md`.

Each specification must resolve its screens, controls, defaults, validation, permissions, lifecycle, data ownership, cross-module contracts, assets, failure behavior, observability, import/export and acceptance tests. Development must not become the place where product semantics are invented.

### Exit gate
All above decisions and module specifications accepted/documented. No unresolved conflict about distribution, unsafe PHP/SQL features, license-expiry runtime behavior, membership access safety, or module data ownership.

---

## Phase 1 — Platform kernel + Free product

### 1A. Repository/tooling skeleton
- plugin bootstrap
- Composer autoload
- React/TypeScript admin shell
- lint/static analysis/test tooling
- CI
- build/package scripts
- version consistency checks
- coding conventions

### 1B. Kernel
- Module Registry
- service/contracts layer
- capability setup
- Policy baseline
- Entitlement contract baseline
- Asset Registry
- Definition Repository v1
- definition revisions
- dependency graph v1
- audit log baseline
- REST/admin API baseline
- Abilities bridge baseline
- diagnostics

### 1C. Free modules
- Custom Post Types Builder
- Taxonomy Builder
- export/import for their definitions
- cross-link UI
- first-run wizard with Continue Free path
- documentation/changelog shell

### Why first
This produces a legitimately useful free plugin, validates the platform contracts with two foundational WordPress concepts, and creates the acquisition surface for Pro without prematurely implementing the hardest operational systems.

### Exit gate
- Free install/upgrade/uninstall tested
- no Pro code in Free artifact
- CPT/Taxonomy competitive baseline complete
- asset isolation verified
- permissions/security verified
- current/minimum compatibility matrix green

---

## Phase 2 — Structured Data Pro foundation

Build in this order:

1. **Custom Fields Builder**
2. **Relations Builder**
3. **Custom Query Builder**
4. **Admin Columns Builder**
5. **Dynamic Listings / Template Builder**
6. **Status Manager**

### Platform work completed here
- Field Schema Registry
- Relation Engine
- Query AST + compilers
- Conditional Logic Engine
- Dynamic Value/Renderer
- data-source adapters
- query diagnostics/cache rules

### Why this is the first Pro commercial milestone
This bundle directly competes with the core reason users buy JetEngine/ACF/Meta Box/Admin Columns and demonstrates WPEssential’s central advantage: the same field/relation/query powers admin, frontend and future automation.

### Commercial packaging direction
Market it as a coherent **Data Application Suite**, not six unrelated mini-plugins.

### Exit gate
Build a reference application (e.g. directory/real-estate/project database) entirely from WPEssential definitions and verify:
- model
- relations
- query
- admin management
- frontend listing
- permissions
- import/export
- performance

---

## Phase 3 — Admin, Identity & Portal Experience

Build:

1. Settings Page Builder
2. Dashboard Widgets Manager
3. Custom Admin Menu Builder
4. Role & Capability Manager
5. User Profile Builder
6. Dashboard Builder
7. Builder Widgets Builder — Gutenberg/shortcode first, then adapters

### Shared work
- resource policy rules
- Component Blueprint
- dashboard/navigation schema
- role-aware definition targeting
- reusable admin DataViews patterns
- account/profile primitives required by Membership

### Adapter order
1. Gutenberg
2. Shortcode
3. Elementor
4. Bricks
5. WPBakery
6. Visual Composer
7. demand-led adapters

Do not claim an adapter complete without automated compatibility tests against the supported builder versions.

### Exit gate
Create two reference portals with different roles and prove server-side access controls, responsive UX, builder rendering and safe menu/profile behavior.

---

## Phase 4 — Membership & Entitlements

### 4A. Entitlement / access foundation
- normalized entitlement model
- plan/enrollment domain contracts
- explainable access evaluation
- entitlement cache/invalidation
- enrollment status state machine
- access-rule precedence

### 4B. Membership System core
- plans
- plan groups
- memberships/enrollments
- manual/complimentary grants
- content and partial-content restriction
- protected downloads
- drip/expiration/grace/trial
- benefits
- upgrade/downgrade path model
- member account/dashboard surfaces

### 4C. Initial grant/billing adapters
Start with adapter contracts, not a built-in payment processor:
1. manual/admin grant
2. Workflow/Form grant
3. WooCommerce product/order
4. WooCommerce Subscriptions lifecycle
5. SureCart purchase/subscription
6. signed generic webhook

### 4D. Advanced membership
- seats/teams/corporate memberships
- role-sync side effects
- member segmentation via Query
- commerce benefits/discount adapter
- migration adapters after stable API/data research

### Security gates
- no protected-content exposure when module/license state changes
- direct protected-file URL tests
- webhook signature/replay/idempotency
- object-level enrollment authorization
- stale entitlement cache revocation tests
- seat-allocation concurrency tests
- role-sync anti-lockout tests

### Commercial reason
Membership provides a second major acquisition/conversion pillar beyond structured data. The differentiator is not another isolated paywall: membership entitlements can drive Dashboards, Forms, Listings, Notifications, REST/Abilities, Profiles and Workflows through the same Policy engine.

### Exit gate
Reference membership application must prove free + paid-source grants, multiple plans, exclusive/multi groups, drip, upgrades, protected files, profile/dashboard access, revocation, Pro-expiry safe enforcement and reconciliation failure recovery.

---

## Phase 5 — Forms, Automation & Communication

Build foundation first:

### 5A. Event / Workflow runtime
- typed events
- trigger/action registry
- workflow run persistence
- condition branching
- retries/backoff
- idempotency
- manual approval
- failure recovery
- job abstraction

### 5B. Forms & Workflow Builder
- forms schema/renderer
- entry storage
- CRUD actions
- relation actions
- membership grant/revoke/change actions
- file/spam/security

### 5C. Cron Job Builder
- WP-Cron inventory
- WPEssential schedules
- reliable runner guidance/adapter
- job/run logs

### 5D. Notification System
- in-app/frontend
- recipient targeting
- membership/entitlement targeting
- preference/digest/logs

### 5E. Emails Builder
- email-safe templates
- WordPress and membership event templates
- test/preview

### 5F. Webhooks & Connections Manager
- reusable connections
- inbound/outbound webhooks
- OAuth/provider connection lifecycle

### Exit gate
Reference workflow must demonstrate:
form → validated CRUD → relation → membership/entitlement condition or change → conditional branch → delayed job → email/in-app notification → webhook, with retry/audit/idempotency tests.

---

## Phase 6 — Data movement & Operations

Risk order:

1. Import / Export data engine
2. Backup Manager local + one S3-compatible + one OAuth drive provider
3. Backup provider adapter expansion
4. Reset Manager
5. Watermarker / Media Rules
6. Protector
7. XML-RPC Manager

### Backup rollout rule
Do not implement 30 providers at once. Prove the adapter contract with representative protocols:
- local/filesystem
- S3-compatible
- SFTP
- WebDAV
- OAuth drive provider

Then expand to provider-specific branded adapters while reusing protocols.

### Exit gate
- verified restore fixtures
- interrupted/resumed backup tests
- destructive reset restore-point tests
- import large-data/resume tests
- no original image mutation in watermark tests
- Protector recovery path verified

---

## Phase 7 — Chat & Advanced integration

### Message & Chat System
Build after notifications, policy, jobs, membership and frontend dashboards exist; it can depend on all of them.

- conversation/message schema
- membership/relation-based initiation rules
- polling fallback
- optional realtime adapter
- moderation
- attachment policy
- search/indexing
- notifications/workflows

### REST API Builder advanced release
The low-level REST/Abilities platform exists earlier; the **user-facing no-code endpoint builder** matures here after data source/policy/workflow/membership schemas have proven stable.

Add:
- endpoint versioning
- schema-driven docs/export
- advanced rate controls
- membership/entitlement policies
- connection/webhook integrations

### Exit gate
Security review focused on IDOR, abuse/rate limits, SSRF, endpoint policy, membership entitlement bypass and realtime authorization.

---

## Phase 8 — AI-native experience layer

Abilities are architectural from Phase 1. User-facing AI automation comes later so AI composes mature operations rather than driving unstable internals.

### Target
- WPEssential AI command/search assistant
- natural-language draft of queries/forms/workflows/fields/membership rules
- explain existing definitions and access decisions
- impact analysis
- suggested fixes from diagnostics
- opt-in MCP server/adapter configuration
- read-only default AI role
- preview/diff/approval for generated mutations

### Provider direction
Prefer current WordPress AI Client/Connectors architecture where compatible. Do not hardwire WPEssential to one AI vendor.

### Exit gate
Prompt/model output treated as untrusted; permissions and destructive-action policies proven independent of the model.

---

## Phase 9 — Ecosystem / SDK / scale

- documented extension SDK
- third-party field/data/query/workflow/membership/provider adapters
- integration certification suite
- CLI expansion
- multisite optimization
- marketplace strategy only after extension security/distribution rules are defined
- advanced analytics/diagnostics

---

# Cross-phase release trains

## Security train
Runs continuously: dependency advisories, threat model updates, privilege regressions, vulnerability response.

## Compatibility train
Test upcoming WordPress/PHP releases before stable release; update builder and commerce/membership integrations against current versions.

## Performance train
Maintain large fixtures and prevent query/asset/job/access-evaluation regressions.

## Documentation train
Every public module ships user docs + developer extension docs where applicable.

## Migration train
Every release with schema change includes upgrade fixture and recovery notes.

---

# What should NOT be prioritized early

### 30+ backup providers
Commercially attractive, but a weak first proof of the platform and a large integration/support burden. Build adapter correctness first.

### Full realtime chat
It introduces storage, privacy, moderation, realtime and scaling concerns. Build after policy/notification/dashboard/membership infrastructure.

### Arbitrary PHP/SQL execution
Not a “power user shortcut” worth destabilizing the security model.

### Every page builder on day one
Prove Component Blueprint using Gutenberg + Elementor, then expand by demand.

### AI-generated mutations before stable abilities
AI should compose well-tested actions; it should not define the architecture.

### Native payment-card processing
Membership needs billing adapters, not a new PCI/payment processor.

---

# Reference demo applications

Each major phase should ship/test against realistic reference blueprints.

1. **Directory:** CPT + taxonomy + fields + relation + filters + listing
2. **Client portal:** roles + frontend dashboard + profile + CRUD form + notifications
3. **Membership portal:** plans + entitlements + protected content/downloads + profile + dashboard + external billing source
4. **Operations workflow:** form → approval → scheduled action → email/webhook
5. **Data app:** custom table + relation + query + admin columns + REST
6. **Recovery drill:** backup → destructive fixture change → restore → checksum/data verification

These are QA/reference blueprints, not demo-only shortcuts.

# Roadmap success rule

Do not declare “WPEssential beats X” based on feature count. A module reaches competitive status only after its documented market baseline, WPEssential differentiator, option-level specification, security gate, performance gate and integration/reference workflow are verified.
