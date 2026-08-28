# WPEssential

WPEssential is a modular, AI-native WordPress application platform for structured content, data, automation, administration, integrations, operations, identity, membership/access, frontend experiences, application composition and developer/operations tooling.

> **Repository status:** Phase 0 — research, product specification and architecture planning. Production feature development has not started and is **not authorized yet**.

Current canonical project state: `PLANNED_EXISTING_PROJECT`  
Current execution mode: `PLANNER_ONLY`

## Development consent gate

Production development requires explicit project-owner consent. Read `DEVELOPMENT-CONSENT.md`, `docs/APPROVAL-LEDGER.md` and ADR-0014.

`continue`, research/planning approval, an Accepted ADR or Phase 0 readiness does **not** authorize coding, executable spikes, package installation, runtime tests, provider calls, AI/MCP execution or deployment.

## Product model

- **WPEssential Free:** Custom Post Types Builder and Taxonomy Builder, permanently available.
- **WPEssential Pro:** premium modules distributed as a separate add-on outside WordPress.org.
- Disabling a module never deletes its data unless explicitly requested.
- License expiry preserves configuration/data and must not expose protected content or break public output.

## Current planning scope

Scope history:
- original product scope: **31 surfaces**;
- ADR-0177: +12 universal foundations → **43**;
- ADR-0183…ADR-0188: +5 market modules → **48**;
- ADR-0189…ADR-0194: +2 new surfaces plus competitive expansion of Membership/Role/Media → current **50 module/platform surfaces**.

Current product planning truth:
- option/product behavior: **50/50 Exhaustive**;
- logical Multisite mapping: **50/50**;
- shared AI Prompt product mapping: **50/50**;
- implementation authorization: **0/50**;
- implemented/runtime verified: **0**.

Historical 31/31, 43/43 and 48/48 statements remain earlier-scope snapshots.

### Latest accepted expansions

Existing surfaces expanded rather than duplicated:
- **15 Membership** — registration/onboarding, verification/approval, private-site profile, restriction defaults, migration/interoperability; MPR 0/176.
- **28 Media Rules** — field-data/LCP priority, responsive delivery, Core-aware `sizes`/lazy behavior, AVIF/WebP, placeholders and CDN delivery; MDP 0/176.
- **30 Role & Capability** — target-role hierarchy, Administrator Rescue, capability provenance, surface-policy integrations and network sync; RPR 0/176.

New user-facing surfaces:
- **49 Admin Theme, Branding & Experience Manager** — semantic/native admin theming, assignment, accessibility, environment identity and branding; ATM 0/176.
- **50 Safe Script, Tag & Code Injection Manager** — governed HTML/CSS/JS/meta/link/JSON-LD/external tags with CSP/consent/environment controls; STM 0/176. **No PHP/eval runtime.**

## Solution Blueprint / AI-native direction

Complete CRM/ERP/LMS/booking/commerce/developer systems normally compose reusable WPE modules/foundations/adapters through Solution Blueprints rather than generating one private plugin/runtime per system.

Current planning includes:
- 160 curated reference systems across 20 domains;
- 40 reusable application patterns;
- 268,800 raw primary Blueprint combinations before validation/secondary dimensions;
- one shared AI Prompt/Requirement Compiler across all **50** surfaces;
- optional WordPress MCP/Abilities exposure under Capability + Policy;
- S07 Product Discovery/Pre-Development Planning Orchestrator;
- S08 Market Intelligence Radar with a documented daily GitHub job design; executable scheduled workflow remains uninstalled before development consent.

## Engineering source of truth

Repository state, runtime/database/config evidence, executed tests, CI results, documentation/ADRs, checkpoints and VCS history are authoritative according to project-state governance. Chat history is not.

Before implementation, read:
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
12. module/Solution/AI/Quality/ADR documentation relevant to the selected milestone.

## Module specification rule

No production module implementation may begin while its product behavior is only a feature list. Every module first documents screens, options, fields, defaults, validation, permissions, lifecycle/failure states, dependencies, assets, import/export, negative/MUST-NOT behavior, Multisite, AI Prompt behavior and acceptance/evidence requirements.

Current planning coverage: **50/50 module/platform surfaces have reached the Phase 0 Exhaustive product-behavior bar.** This means planned/specified, not implemented, verified, runtime-certified or authorized.

## Default engineering lifecycle

`Inspect → Understand → Research → Assess → Plan → Approval/Consent Gate → Implement → FAST Gate → Review/Attack/Harden → Integrate → FULL Gate → Document → Commit → Checkpoint → Report`.

The consent gate is mandatory and external to technical readiness.

## Compatibility / UI / Build direction

Planning target is WordPress 7.1; minimum WordPress candidate remains 6.9 and minimum PHP candidate 8.3 pending executable compatibility evidence.

React + TypeScript remain product requirements through WPE-owned wrappers over compatible WordPress public primitives and WordPress-provided React. Build evaluation remains `@wordpress/build` → `@wordpress/scripts` → Vite only for proven gaps; Laravel Mix is not carried forward.

No package/build spike is authorized.

## Current planning work

Owner-requested WP83…WP89 access/admin/media/code market audit is complete. Current planning resumes:

**P0-M00-WP65 — F03 Search & Indexing detailed executable-evidence specification.**

Production development authorization remains **NOT GRANTED / 0/50**.