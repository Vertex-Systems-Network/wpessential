# WPEssential

WPEssential is a modular, AI-native WordPress application platform for structured content, data, automation, administration, integrations, operations, and frontend experiences.

> **Repository status:** Phase 0 — research and architecture planning. Production feature development has not started.

## Product model

- **WPEssential Free:** Custom Post Types Builder and Taxonomy Builder, permanently available.
- **WPEssential Pro:** premium modules distributed as a separate add-on outside WordPress.org.
- A Pro trial may unlock the Pro add-on for a limited period, but the WordPress.org Free plugin must not ship locked trialware.
- Disabling a module never deletes its data unless a user explicitly requests deletion.
- License expiry preserves configuration/data. Existing public runtime output should degrade safely rather than break a production site.

## Engineering source of truth

Repository state, tests, documentation, ADRs, checkpoints, and Git history are the source of truth. Chat history is not.

Before implementation, read in this order:

1. `docs/PRODUCT-MASTER-PLAN.md`
2. `docs/ARCHITECTURE.md`
3. `docs/MODULE-CATALOG.md`
4. `docs/RESEARCH/COMPETITIVE-LANDSCAPE.md`
5. `docs/SECURITY.md`
6. `docs/COMMERCIAL-DISTRIBUTION.md`
7. `docs/QUALITY-GATES.md`
8. `docs/ROADMAP.md`
9. `docs/DECISIONS/`
10. `CHECKPOINT.md`

## Default engineering lifecycle

Inspect → Understand → Research → Assess → Plan → Implement → Test → Attack → Review → Harden → Document → Commit → Checkpoint → Report.

No feature is complete because its UI works. Completion requires integration, authorization, validation, failure handling, data integrity, tests, observability, documentation, compatibility, rollback/recovery, and meaningful Git history.

## Compatibility direction

Planning is being done against **WordPress 7.1** (released August 19, 2026). A deliberate minimum WordPress/PHP support floor will be locked by ADR before implementation. The WordPress Abilities API (WordPress 6.9+) is a first-class primitive for typed, permission-aware actions and future AI/MCP integration.

## UI direction

React + TypeScript. Use clearly MIT-licensed Untitled UI React components where appropriate and Lucide icons (ISC). Prefer WordPress-native packages such as `@wordpress/components`, `@wordpress/dataviews`, and stable public WordPress APIs where they improve interoperability, accessibility, or reduce compatibility risk. Premium Untitled UI assets/components must not be redistributed without a separately reviewed license.

## Architecture principle

WPEssential is **not** a collection of isolated mini-plugins. Modules share typed registries and services for entities, fields, queries, relations, rendering, policies, abilities, workflows, jobs, credentials, auditing, integrations, import/export, and module lifecycle.

## Planning branch

Detailed research and architecture are developed on `planning/master-architecture`. Feature development begins only after the Phase 0 planning gate is accepted.
