# WPEssential — Module Dependency & Data Ownership Contract

Status: Phase 0 planning. No runtime implementation authorized.

## Purpose
Prevent circular module design, hidden ownership, duplicate tables, and accidental data deletion. Modules may depend on shared platform services and other modules only through explicit contracts.

## Dependency classes
- **Platform-required** — Module Registry, Definition Repository, Policy/Capability Engine, Asset Registry, Audit/Observability, Import/Export package service.
- **Service-optional** — Query, Relation, Renderer, Condition, Job, Vault, Event Bus, Abilities, Integration Registry.
- **Module-hard dependency** — module cannot operate without another module.
- **Module-soft dependency** — integration becomes available only when peer module is enabled.
- **Adapter dependency** — third-party plugin/provider integration loaded only when detected and enabled.

A module must never reach into another module's private tables/classes when a shared contract or public adapter exists.

## Ownership rule
The component that creates the authoritative record owns its schema, migrations, retention and deletion semantics. Other modules reference it by stable ID/contract only.

### Platform-owned data
- module manifests/state
- definition identities/revisions/dependency edges
- audit/event metadata
- platform compatibility/version metadata
- asset manifest metadata
- shared connection references
- shared secrets references (secret ciphertext owned by Vault)

### Content Model
- **CPT Builder:** WPE-managed CPT definitions only; WordPress posts remain WordPress-owned.
- **Taxonomy Builder:** WPE-managed taxonomy definitions only; terms remain WordPress-owned.
- **Custom Fields:** field/group definitions; actual values are owned by selected storage adapter (post meta, term meta, user meta, options, custom table).
- **Relations:** relation definitions and WPE relation-edge/pivot data when WPE storage is selected.
- **Status Manager:** WPE status/state-machine definitions and WPE domain-state history where applicable; native post status remains WordPress object state.

### Data & Query
- **Query Builder:** query definitions and optional cache metadata; never owns source records.
- **Custom Tables:** WPE-managed table schemas and rows in those tables.
- **Admin Columns:** view/column-set definitions only; never owns displayed source data.
- **Listings/Templates:** display definitions only; never owns source entities.

### Admin & Experience
- Dashboard Widgets, Admin Menu, Settings Pages, Frontend Dashboard, Profiles, Builder Widgets own definitions/layout preferences only. Storage values belong to their configured data source.

### Identity & Access
- **Role Manager:** WPE role/capability change history; WordPress roles/capabilities live in WordPress options/user assignments.
- **Membership:** Plan/Rule definitions in Definition Repository; Enrollment, Entitlement, Override, Team/Seat/Invite and provider-link/event runtime records in Membership-owned transactional storage.
- Membership never owns WordPress user identity or external billing transaction records.

### Automation & Communication
- **Forms:** form definitions; submission/entry records when WPE storage enabled.
- **Workflows:** workflow definitions and run/step execution history.
- **Cron:** schedule definitions for WPE schedules; third-party WP-Cron events remain third-party owned.
- **Notifications:** notification instances/read state/delivery metadata.
- **Email:** template definitions and WPE send-event logs, not mailbox/provider truth.
- **Chat:** conversations, participants, messages, read state and attachment metadata.

### Integration & Data Movement
- **REST API Builder:** endpoint definitions/log metadata.
- **Webhooks/Connections:** connection definitions, delivery metadata; credentials are Vault-owned.
- **Import/Export:** job definitions/mappings/run metadata; imported records remain target-source owned.

### Operations
- **Backup:** backup manifests/catalog/schedule definitions; archives belong to configured storage destination.
- **Reset:** reset profiles/run/audit records, not destroyed site content.
- **Protector:** protection-rule definitions/access logs.
- **Watermark:** rule definitions and derived media metadata; original attachment remains WordPress/media-library owned.
- **XML-RPC Manager:** policy definitions/log metadata only.

## Hard dependency rules
Initial hard module dependencies should be minimized.

- Relations requires the shared Data Source Registry, not Custom Fields specifically.
- Listings requires Query/Renderer platform services but Query Builder UI may remain disabled if an internally generated/provider query is used.
- Dashboard Builder requires shared Renderer/Policy but should not require Membership.
- Membership requires Policy/Entitlement services; Profile/Dashboard/Email/Workflow integrations are soft.
- Forms requires Field Schema primitives; it should not require Custom Fields UI to be enabled.
- Backup/Reset share backup contracts; Reset may require a verified restore-point capability, not necessarily the full Backup UI module if a compatible provider exists.

## Disable semantics
Disabling a module:
1. unregisters UI/hooks/assets/abilities/jobs owned by that module;
2. stops new mutable operations;
3. preserves owned data by default;
4. marks dependent definitions degraded/read-only where needed;
5. never cascades deletion;
6. never silently weakens security/access protections.

## Delete-data semantics
“Delete module data” is separate from Disable and requires:
- dependency graph preview;
- owned-data inventory;
- backup/export option;
- capability + re-auth for high-impact modules;
- confirmation naming affected records/tables;
- audit trail;
- explicit treatment of shared data references.

## Circular dependency prohibition
Direct module cycles are prohibited. If A and B need each other, move the shared behavior into a platform service or adapter contract.

## Acceptance criteria before implementation
Each module spec must identify:
- hard dependencies;
- soft integrations;
- data it owns;
- data it only references;
- disable result;
- uninstall/delete-data result;
- migration authority;
- retention/privacy authority;
- backup/export owner;
- degraded-mode behavior.