# WPEssential

WPEssential is a modular, AI-native WordPress application platform for structured content, data, automation, administration, integrations, operations, identity, membership/access, and frontend experiences.

> **Repository status:** Phase 0 — research, product specification and architecture planning. Production feature development has not started and is **not authorized yet**.

Current canonical project state: `PLANNED_EXISTING_PROJECT`  
Current execution mode: `PLANNER_ONLY`

See `docs/PROJECT-STATE-AND-ADOPTION.md`.

## Development consent gate

Production development requires explicit project-owner consent before any runtime/plugin source implementation begins.

Read `/DEVELOPMENT-CONSENT.md`, `docs/APPROVAL-LEDGER.md` and ADR-0014.

Important:
- `continue`, `proceed`, approval of planning, an Accepted ADR, or Phase 0 readiness does **not** authorize coding;
- executable research spikes also count as development and require explicit consent;
- planning, research, threat models, specifications, ADRs, acceptance criteria and documentation-only commits may continue;
- even after owner consent, unresolved Phase 0 blockers must not be bypassed;
- implementation approval is scoped (`TASK`, `MODULE`, `MILESTONE`, `PHASE`, `PROJECT`) and recorded durably;
- ordinary reversible work inside an already approved documented milestone should proceed autonomously without repeatedly asking for approval.

## Product model

- **WPEssential Free:** Custom Post Types Builder and Taxonomy Builder, permanently available.
- **WPEssential Pro:** premium modules distributed as a separate add-on outside WordPress.org.
- A Pro trial may unlock the Pro add-on for a limited period, but the WordPress.org Free plugin must not ship locked trialware.
- Disabling a module never deletes its data unless a user explicitly requests deletion.
- License expiry preserves configuration/data. Existing public runtime output and security/access enforcement should degrade safely rather than break or expose a production site.

## Engineering source of truth

Repository state, runtime/database/config evidence, executed tests, CI results, documentation/ADRs, checkpoints and VCS history are authoritative in that order defined by the project-state baseline. Chat history is not.

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
16. `docs/MODULE-CATALOG.md` — high-level catalog; detailed specs take precedence when more specific
17. relevant `docs/ARCHITECTURE/` and `docs/SECURITY/` detail documents
18. `docs/RESEARCH/`
19. `docs/SECURITY.md`
20. `docs/COMMERCIAL-DISTRIBUTION.md`
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

No production module implementation may begin while its product behavior is still only a feature list. Every module must first document every known screen, option, field, toggle, selector, action, default, validation rule, permission boundary, lifecycle state, failure state, dependency, asset boundary, import/export behavior, important negative/MUST-NOT behavior and acceptance test.

If implementation later discovers an unplanned option or behavior, documentation is updated before or in the same coherent change. Development is not allowed to silently invent product semantics.

Current planning coverage: **31/31 module/platform surfaces have option inventories and Phase 0 behavioral specifications.** This means `Specified`, not `Implemented`, `Verified` or `Authorized`.

## Default engineering lifecycle

Inspect → Understand → Research → Assess → Plan → **Approval/Consent Gate** → Implement → FAST Gate → Review/Attack/Harden → Integrate → FULL Gate when required → Document → Commit → Checkpoint → Report.

The Consent Gate is mandatory and external to technical readiness: a technically ready project still waits for explicit owner authorization.

No feature is complete because its UI works. Completion requires integration, authorization, validation, failure handling, data integrity, tests, observability, documentation, compatibility, rollback/recovery, and meaningful VCS history.

## Execution safety

Approved implementation is governed by `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`.

Important rules include:
- small-batch change budgets;
- no unrelated cleanup;
- critical-path prioritization;
- `PARALLEL_SAFE`, `COORDINATED_PARALLEL`, `SERIALIZE`, `BLOCKED` work classification;
- shared-surface ownership and WIP limits;
- baseline-failure and flaky-test truthfulness;
- `FAST GATE` during bounded implementation and `FULL GATE` at milestone/release boundaries;
- truthful `INDEPENDENT REVIEW`, `SELF REVIEW`, `AUTOMATED REVIEW` labels;
- stable work IDs for new execution planning.

## Release/recovery safety

Future release work distinguishes:

`BUILT → DEPLOYED → RELEASED → PRODUCTION_VERIFIED`

Recovery is classified:
- `SIMPLE_ROLLBACK`
- `ROLLBACK_WITH_COMPATIBILITY`
- `FORWARD_FIX_PREFERRED`
- `IRREVERSIBLE`

Production incidents switch to containment/recovery priority and obey stop-the-line rules in `docs/RELEASE-INCIDENT-RECOVERY-GOVERNANCE.md`.

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
1. relevant technical planning gates are Accepted;
2. the project owner gives explicit scoped development consent under ADR-0014;
3. the approval is recorded in `docs/APPROVAL-LEDGER.md`; and
4. the implementation baseline/adoption gate confirms a safe branch/revision/workspace and first bounded milestone.