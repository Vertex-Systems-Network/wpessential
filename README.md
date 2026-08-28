# WPEssential

WPEssential is a modular, AI-native WordPress application platform for structured content, data, automation, administration, integrations, operations, identity, membership/access, frontend experiences, application composition and developer/operations tooling.

> **Repository status:** Phase 0 — research, product specification and architecture planning. Production feature development has not started and is **not authorized yet**.

Current canonical project state: `PLANNED_EXISTING_PROJECT`  
Current execution mode: `PLANNER_ONLY`

See `docs/PROJECT-STATE-AND-ADOPTION.md`.

## Development consent gate

Production development requires explicit project-owner consent before any runtime/plugin source implementation begins.

Read `/DEVELOPMENT-CONSENT.md`, `docs/APPROVAL-LEDGER.md` and ADR-0014.

Important:
- `continue`, `proceed`, approval of planning, market research, an Accepted ADR, or Phase 0 readiness does **not** authorize coding;
- executable research spikes also count as development and require explicit consent;
- planning, research, threat models, specifications, ADRs, acceptance criteria and documentation-only commits may continue;
- even after owner consent, unresolved Phase 0 blockers must not be bypassed;
- implementation approval is scoped (`TASK`, `MODULE`, `MILESTONE`, `PHASE`, `PROJECT`) and recorded durably;
- ordinary reversible work inside an approved documented milestone should proceed autonomously without repeatedly asking for approval.

## Product model

- **WPEssential Free:** Custom Post Types Builder and Taxonomy Builder, permanently available.
- **WPEssential Pro:** premium modules distributed as a separate add-on outside WordPress.org.
- A Pro trial may unlock the Pro add-on for a limited period, but the WordPress.org Free plugin must not ship locked trialware.
- Disabling a module never deletes its data unless a user explicitly requests deletion.
- License expiry preserves configuration/data. Existing public runtime output and security/access enforcement should degrade safely rather than break or expose a production site.

## Current planning scope

Scope history:
- original product scope: **31 surfaces**;
- ADR-0177 added **12 universal foundations**, producing 43;
- ADR-0183…ADR-0188 added **5 market-driven modules**, producing the current **48 module/platform surfaces**.

Current product planning truth:
- option/product behavior: **48/48 Exhaustive**;
- logical Multisite mapping: **48/48**;
- shared AI Prompt product mapping: **48/48**;
- implementation authorization: **0/48**;
- implemented/runtime verified: **0**.

The 5 latest surfaces are URL Redirection & Routing, Search/Replace & Data Transformation, Dummy Data & Fixture Studio, Link Health & Crawl Intelligence, and Database Maintenance & Cleanup.

S07 Product Discovery/Planning Orchestrator and S08 Market Intelligence Radar are shared services, not denominator rows.

Historical 31/31 and 43/43 statements remain valid for earlier scope snapshots.

## Engineering source of truth

Repository state, runtime/database/config evidence, executed tests, CI results, documentation/ADRs, checkpoints and VCS history are authoritative in the order defined by the project-state baseline. Chat history is not.

Before implementation, read in this order:
1. `DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/PROJECT-STATE-AND-ADOPTION.md`
5. `docs/APPROVAL-LEDGER.md`
6. `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`
7. `docs/RELEASE-INCIDENT-RECOVERY-GOVERNANCE.md`
8. `docs/PRODUCT-MASTER-PLAN.md`
9. `docs/ARCHITECTURE.md`
10. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
11. `docs/OPEN-DECISIONS-REGISTER.md`
12. `docs/MODULES/README.md`
13. `docs/MODULES/SPECIFICATION-STANDARD.md`
14. `docs/MODULES/OPTION-INVENTORY.md`
15. module/suite detailed specifications under `docs/MODULES/`
16. `docs/MODULE-CATALOG.md`
17. relevant architecture/security detail documents
18. `docs/SOLUTIONS/`
19. `docs/AI/`
20. `docs/RESEARCH/`
21. `docs/QUALITY-GATES.md`
22. `docs/ROADMAP.md`
23. `docs/DECISIONS/`

## Existing-project adoption rule

WPEssential is currently a planned existing project, not a greenfield blank slate.

Do not restart it, rewrite accepted architecture merely for stylistic preference, discard unknown work or overwrite the existing plan.

Use:

`Inspect → Baseline → Audit Existing Plan → Compare Plan With Reality → Identify Gaps → Amend Plan → Preserve Existing Work → Continue Safely`

Plan/repository drift and gap classifications are defined in `docs/PROJECT-STATE-AND-ADOPTION.md`.

## Module specification rule

No production module implementation may begin while its product behavior is still only a feature list. Every module first documents every known screen, option, field, toggle, selector, action, default, validation rule, permission boundary, lifecycle state, failure state, dependency, asset boundary, import/export behavior, important negative/MUST-NOT behavior and acceptance test.

If implementation later discovers an unplanned option or behavior, documentation is updated before or in the same coherent change. Development is not allowed to silently invent product semantics.

Current planning coverage: **48/48 module/platform surfaces have reached the Phase 0 Exhaustive product-behavior bar.** This means planned/specified, not implemented, verified, runtime-certified or authorized.

## Solution Blueprint / AI-native planning direction

Complete CRM/ERP/LMS/booking/commerce/developer systems normally compose reusable WPE modules/foundations/adapters through the Solution Blueprint layer rather than generating one separate plugin codebase per system.

Current planning includes:
- 160 curated reference systems across 20 domains;
- 40 reusable application patterns;
- 268,800 raw primary Blueprint combinations before validation/secondary dimensions;
- one shared AI Prompt/Requirement Compiler across all 48 surfaces;
- optional WordPress MCP/Abilities exposure under the same Capability + Policy rules;
- S07 autonomous pre-development planning so an owner request such as `ABC system add karna hai` can be researched/audited/planned before code;
- S08 Market Intelligence Radar with a documented daily GitHub job design. The executable scheduled workflow is not installed before development consent.

## Default engineering lifecycle

Inspect → Understand → Research → Assess → Plan → **Approval/Consent Gate** → Implement → FAST Gate → Review/Attack/Harden → Integrate → FULL Gate when required → Document → Commit → Checkpoint → Report.

The Consent Gate is mandatory and external to technical readiness: a technically ready project still waits for explicit owner authorization.

No feature is complete because its UI works. Completion requires integration, authorization, validation, failure handling, data integrity, tests, observability, documentation, compatibility, rollback/recovery and meaningful VCS history.

## Execution safety

Approved implementation is governed by `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`.

Important rules include:
- small-batch change budgets;
- no unrelated cleanup;
- critical-path prioritization;
- `PARALLEL_SAFE`, `COORDINATED_PARALLEL`, `SERIALIZE`, `BLOCKED` classification;
- shared-surface ownership and WIP limits;
- baseline-failure and flaky-test truthfulness;
- FAST Gate during bounded implementation and FULL Gate at milestone/release boundaries;
- truthful review labels;
- stable work IDs.

## Release/recovery safety

Future release work distinguishes:
`BUILT → DEPLOYED → RELEASED → PRODUCTION_VERIFIED`.

Recovery classes:
- `SIMPLE_ROLLBACK`
- `ROLLBACK_WITH_COMPATIBILITY`
- `FORWARD_FIX_PREFERRED`
- `IRREVERSIBLE`

Production incidents switch to containment/recovery priority and obey stop-the-line rules.

## Compatibility direction

Planning target is WordPress 7.1. Current static recommendation is:
- minimum WordPress candidate: **6.9** because native Abilities API begins there;
- minimum PHP candidate: **8.3**.

These remain Proposed until executable compatibility evidence is explicitly authorized and completed.

## UI direction

React + TypeScript remain product requirements. WPE uses WPE-owned component wrappers over compatible public WordPress primitives and WordPress-provided React rather than hard-coupling to an incompatible third-party React runtime.

Current Proposed direction:
- WPE component wrappers;
- WordPress public design-system/components/DataViews capabilities where compatible;
- premium visual language without incompatible runtime coupling;
- Lucide behind a WPE/WordPress icon abstraction.

See ADR-0005 and compatibility research.

## Build direction

Current Proposed evaluation order:
1. `@wordpress/build` stable capabilities;
2. `@wordpress/scripts` comparison/fallback;
3. Vite only if WordPress-native tooling fails proven requirements;
4. no Laravel Mix carry-forward.

No build spike/package installation is authorized yet. See ADR-0012.

## Architecture principle

WPEssential is **not** a collection of isolated mini-plugins. Modules share typed registries/services for entities, definitions, fields, queries, relations, rendering, conditions, policies, entitlements, abilities, workflows, jobs, credentials, auditing, integrations, import/export, assets, diagnostics, versioning, cache, rate limits, privacy, module lifecycle and AI Prompt orchestration.

Market research must pass a reuse test before adding a new module. Popularity is a demand signal, not architecture authority.

## Membership principle

Membership is deliberately separated from WordPress roles and billing subscriptions:
- User = identity;
- Role/Capability = WordPress authorization primitive;
- Plan = access/product definition;
- Enrollment = lifecycle instance;
- Billing Subscription/Purchase = external source/reference;
- Entitlement = normalized grant;
- Access Policy = resource/action decision.

Raw billing-provider statuses/events never directly authorize a request.

## Planning branch

Detailed research and architecture are developed on `planning/master-architecture` and reviewed through draft PR #1.

Production feature development begins only when:
1. relevant technical planning gates are Accepted;
2. the project owner gives explicit scoped development consent under ADR-0014;
3. approval is recorded in `docs/APPROVAL-LEDGER.md`; and
4. the implementation baseline/adoption gate confirms a safe branch/revision/workspace and first bounded milestone.
