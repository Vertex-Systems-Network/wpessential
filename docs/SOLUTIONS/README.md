# WPEssential — Solution Blueprint Layer

Status: **Phase 0 planning / documentation only / no development authorization**  
Date: 2026-08-28  
Owner-requested planning scope: universal application/system composition and WooCommerce Commerce OS audit.

## 1. Why this layer exists

WPEssential is already a set of reusable application primitives: content/data schemas, relations, states, queries, tables, listings, forms, workflows, jobs, notifications, portals, APIs, integrations, roles, membership/access, operations and shared platform services.

A CRM, LMS, helpdesk, directory, booking system, B2B portal, procurement tool, affiliate system, inventory workflow or WooCommerce growth system should therefore **not automatically become another monolithic WPEssential module**.

The default rule is:

> **Build a Solution Blueprint from existing primitives first. Add a new module only when a missing capability is a reusable platform primitive across multiple unrelated solution domains.**

This prevents WPEssential from becoming hundreds of isolated mini-plugins while still allowing it to produce thousands of complete systems.

## 2. Three product layers

### Layer A — Platform primitives

The existing WPEssential modules and non-sellable shared services remain the canonical building blocks.

Examples:
- CPT / Taxonomy / Fields / Relations / Status;
- Query / Custom Tables / Listings / Admin Columns;
- Frontend Dashboard / User Profile / Builder Widgets;
- Forms / Workflow / Cron / Notifications / Emails / Chat;
- REST / Webhooks / Connections / Import / Export;
- Membership / Roles / Policy / Entitlements;
- Backup / Reset / Protector / Media;
- Definition Repository, Event Bus, JobService, Vault, Audit, Conditional Logic, Dynamic Value Resolver, Cache, Rate Limit and SDK.

### Layer B — Reusable foundation extensions

A small set of cross-domain capabilities is missing or not yet formalized strongly enough for the new universal-system scope. These are documented in `FOUNDATIONAL-MODULE-GAP-PLAN.md`.

They are classified as:
- **new reusable user-facing module**;
- **shared-service enhancement**;
- **domain adapter pack**.

No candidate is treated as implemented or authorized merely because it is planned.

### Layer C — Solution Blueprints

A Solution Blueprint is an installable/composable application definition that can assemble many module definitions into one coherent business system.

Examples:
- CRM;
- real-estate listings + leads + agent portal;
- clinic appointment and patient intake;
- school admissions and student portal;
- HR leave/attendance workflows;
- maintenance ticketing;
- procurement and supplier portal;
- membership academy;
- customer support center;
- WooCommerce loyalty + wallet;
- WooCommerce returns OS;
- WooCommerce B2B quote/deal room.

A blueprint owns **composition**, not duplicate runtimes.

## 3. Buildability classification

Every requested system is classified as one of:

### `DIRECT`
The complete target can be assembled from current WPEssential module/shared-service contracts plus ordinary WordPress APIs.

### `COMPOSABLE_WITH_ADAPTER`
Current modules are sufficient for application behavior, but an exact domain/provider adapter is required (for example WooCommerce HPOS/Blocks/payment/shipping/cart/order abilities).

### `NEEDS_FOUNDATION`
A reusable cross-domain primitive is missing. The system must wait for or explicitly depend on that new foundation.

### `EXTERNAL_AUTHORITY_REQUIRED`
WPEssential can orchestrate/configure the experience but must integrate an authoritative external provider for facts or regulated actions such as payment settlement, carrier labels, tax/duties, identity verification or legally binding e-signature where applicable.

A system can have more than one classification dimension; for example a booking system can be `NEEDS_FOUNDATION` for reservation locking and `EXTERNAL_AUTHORITY_REQUIRED` only if external calendar/payment services are selected.

## 4. Solution Blueprint manifest

Every blueprint must declare at minimum:

- stable blueprint ID and semantic version;
- name, domain and use cases;
- supported personas/actors;
- owned first-class entities;
- referenced WordPress/Woo/external entities;
- required WPEssential modules;
- optional WPEssential modules;
- required shared services;
- required domain/provider adapters;
- roles/capabilities/policies;
- custom tables/CPTs/taxonomies/fields/relations;
- state machines;
- queries/views/listings;
- forms and CRUD screens;
- workflows/jobs/schedules;
- notifications/emails/chat channels;
- dashboard/admin/navigation composition;
- REST/webhook/integration contracts;
- analytics/events/metrics when applicable;
- files/documents/retention/privacy policy;
- import/export mappings;
- sample/seed data policy;
- dependency graph;
- installation preview;
- conflict checks;
- upgrade/migration plan;
- uninstall/deactivation behavior;
- test/evidence requirements;
- AI permissions: read-only, draft-only, approval-required, policy-preauthorized;
- negative requirements / MUST NOT behavior.

## 5. Blueprint installation flow

`Choose Blueprint → Inspect Requirements → Resolve Modules/Adapters → Preview Objects/Fields/Roles/Routes → Map Existing Data → Dry Run → Install Draft Definitions → Validate Dependencies → Review Permissions → Publish/Enable Selected Parts → Observe → Upgrade/Export`

A blueprint installation must not silently overwrite existing definitions or credentials.

## 6. Blueprint ownership rule

A blueprint references canonical module definitions. It does not create hidden private copies of:
- fields;
- relations;
- queries;
- workflows;
- roles;
- notifications;
- APIs;
- credentials;
- policies.

The owning module remains source of truth. The blueprint records dependency and version constraints.

## 7. 100K+ system strategy

WPEssential should not maintain 100,000 independent codebases or giant duplicated specs.

The scalable model is:

1. define reusable platform primitives;
2. define a finite set of application patterns;
3. define domain data packs;
4. define actor/permission patterns;
5. define workflow/state patterns;
6. define UI/portal patterns;
7. define integration patterns;
8. compose validated blueprints from those dimensions;
9. keep a curated reference catalog of common systems;
10. allow AI to draft a blueprint **only into the same deterministic schema**, with human preview/approval.

`100K-SYSTEM-SPACE.md` defines the addressable blueprint space and validation rules. `UNIVERSAL-SYSTEM-CATALOG.md` defines the curated reference systems.

## 8. Documentation rule

Every curated blueprint follows `SYSTEM-BLUEPRINT-SPECIFICATION-STANDARD.md`.

The minimum documentation covers:
- problem;
- actors;
- objects;
- roles;
- screens;
- options;
- primary/alternate/failure flows;
- state machines;
- automation;
- notifications;
- reports;
- integrations;
- AI behavior;
- security/privacy;
- lifecycle;
- module composition;
- missing foundations/adapters;
- acceptance criteria.

## 9. Development gate

This Solution Blueprint layer is **planning only**.

No source/runtime implementation, package installation, provider/API call, DB migration, generated plugin, WooCommerce mutation, benchmark or executable test is authorized by these documents. ADR-0014 and the Approval Ledger remain controlling.