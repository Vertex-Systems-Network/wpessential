# WPEssential

WPEssential is a modular, AI-native WordPress application platform for structured content, data, automation, administration, integrations, operations, identity, membership/access, and frontend experiences.

> **Repository status:** Phase 0 — research, product specification and architecture planning. Production feature development has not started and is **not authorized yet**.

## Development consent gate

Production development requires explicit project-owner consent before any runtime/plugin source implementation begins.

Read `/DEVELOPMENT-CONSENT.md` and ADR-0014.

Important:
- `continue`, `proceed`, approval of planning, an Accepted ADR, or Phase 0 readiness does **not** authorize coding;
- executable research spikes also count as development and require explicit consent;
- planning, research, threat models, specifications, ADRs, acceptance criteria and documentation-only commits may continue;
- even after owner consent, unresolved Phase 0 blockers must not be bypassed.

## Product model

- **WPEssential Free:** Custom Post Types Builder and Taxonomy Builder, permanently available.
- **WPEssential Pro:** premium modules distributed as a separate add-on outside WordPress.org.
- A Pro trial may unlock the Pro add-on for a limited period, but the WordPress.org Free plugin must not ship locked trialware.
- Disabling a module never deletes its data unless a user explicitly requests deletion.
- License expiry preserves configuration/data. Existing public runtime output and security/access enforcement should degrade safely rather than break or expose a production site.

## Engineering source of truth

Repository state, tests, documentation, ADRs, checkpoints, and Git history are the source of truth. Chat history is not.

Before implementation, read in this order:

1. `DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/PRODUCT-MASTER-PLAN.md`
5. `docs/ARCHITECTURE.md`
6. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
7. `docs/OPEN-DECISIONS-REGISTER.md`
8. `docs/MODULES/README.md`
9. `docs/MODULES/SPECIFICATION-STANDARD.md`
10. `docs/MODULES/OPTION-INVENTORY.md`
11. module/suite detailed specifications under `docs/MODULES/`
12. `docs/MODULE-CATALOG.md` — high-level catalog; detailed specs take precedence when more specific
13. relevant `docs/ARCHITECTURE/` and `docs/SECURITY/` detail documents
14. `docs/RESEARCH/`
15. `docs/SECURITY.md`
16. `docs/COMMERCIAL-DISTRIBUTION.md`
17. `docs/QUALITY-GATES.md`
18. `docs/ROADMAP.md`
19. `docs/DECISIONS/`

## Module specification rule

No production module implementation may begin while its product behavior is still only a feature list. Every module must first document every known screen, option, field, toggle, selector, action, default, validation rule, permission boundary, lifecycle state, failure state, dependency, asset boundary, import/export behavior and acceptance test.

If implementation later discovers an unplanned option or behavior, documentation is updated before or in the same coherent change. Development is not allowed to silently invent product semantics.

Current planning coverage: **31/31 module/platform surfaces have option inventories and Phase 0 behavioral specifications.** This means `Specified`, not `Implemented`, `Verified` or `Authorized`.

## Default engineering lifecycle

Inspect → Understand → Research → Assess → Plan → **Consent Gate** → Implement → Test → Attack → Review → Harden → Document → Commit → Checkpoint → Report.

The Consent Gate is mandatory and external to technical readiness: a technically ready project still waits for explicit owner authorization.

No feature is complete because its UI works. Completion requires integration, authorization, validation, failure handling, data integrity, tests, observability, documentation, compatibility, rollback/recovery, and meaningful Git history.

## Compatibility direction

Planning target is WordPress 7.1. Current static recommendation is:
- minimum WordPress candidate: **6.9** because native Abilities API begins there;
- minimum PHP candidate: **8.3**, replacing the earlier PHP 8.2 preference because PHP 8.2 security support ends 2026-12-31 and WordPress currently recommends PHP 8.3+.

These remain Proposed until executable compatibility evidence is explicitly authorized and completed.

## UI direction

React + TypeScript remain product requirements, but current WordPress/runtime research changed how the design system should be used.

WordPress 7.1 remains on React 18.3 while current Untitled UI React targets React 19.2, and WordPress Core documented real mixed-runtime compatibility failures during its React 19 experiment. Therefore WPEssential should **not** directly adopt current Untitled UI React as the mandatory runtime foundation.

Current Proposed direction:
- WPEssential-owned component wrappers;
- WordPress 7.1 public Design System tokens/theme, stable `@wordpress/components` and `@wordpress/dataviews` where suitable;
- premium visual language inspired by Untitled UI;
- only explicitly MIT/open-source Untitled pieces after React/runtime/license compatibility review;
- no Untitled PRO source redistribution without separate license approval;
- Lucide remains the preferred icon vocabulary behind a WPEssential/WordPress icon abstraction rather than raw module-level dependency coupling.

See ADR-0005 and `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`.

## Build direction

The earlier Vite-first assumption has been revised.

Current Proposed evaluation order:
1. `@wordpress/build` stable build capabilities;
2. `@wordpress/scripts` comparison/fallback;
3. Vite only if WordPress-native tooling fails proven WPEssential requirements;
4. do not carry forward Laravel Mix.

No build spike/package installation is authorized yet. See ADR-0012.

## Architecture principle

WPEssential is **not** a collection of isolated mini-plugins. Modules share typed registries and services for entities, definitions, fields, queries, relations, rendering, conditions, policies, entitlements, abilities, workflows, jobs, credentials, auditing, integrations, import/export, assets, diagnostics and module lifecycle.

Current detailed shared contracts include:
- Definition Repository candidate schema with stable UUID identity, immutable revisions and separate current/published revision pointers;
- Free↔Pro compatibility state machine;
- shared Job Service contract with Action Scheduler as preferred adapter candidate;
- Secrets Vault threat model with external-key separation and no plaintext fallback;
- Membership access-precedence and enrollment-state contracts.

These are planning contracts, not runtime implementations.

## Membership principle

Membership is deliberately separated from WordPress roles and billing subscriptions:
- User = identity;
- Role/Capability = WordPress authorization primitive;
- Plan = access/product definition;
- Enrollment = lifecycle instance;
- Billing Subscription/Purchase = external source/reference;
- Entitlement = normalized grant;
- Access Policy = resource/action decision.

Raw billing-provider statuses/events never directly authorize a request. Provider state must be normalized into valid Enrollment/Entitlement state first.

## Planning branch

Detailed research and architecture are developed on `planning/master-architecture` and reviewed through draft PR #1.

Production feature development begins only when:
1. relevant technical planning gates are Accepted; **and**
2. the project owner gives explicit development consent under ADR-0014.
