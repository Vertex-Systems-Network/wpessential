# WPEssential

WPEssential is a modular, AI-native WordPress application platform for structured content, data, automation, administration, integrations, operations, identity, membership/access, frontend experiences, application composition and developer/operations tooling.

> **Repository status:** Phase 0 — research, product specification, architecture and evidence planning. Production feature development has not started and is **not authorized**.

Current canonical project state: `PLANNED_EXISTING_PROJECT`  
Current execution mode: `PLANNER_ONLY`

## Development consent gate

Production development requires explicit scoped project-owner consent. Read `DEVELOPMENT-CONSENT.md`, `AGENTS.md`, `docs/APPROVAL-LEDGER.md` and ADR-0014.

`continue`, `resume`, research/planning approval, an Accepted ADR or Phase 0 planning completion does **not** authorize coding, executable spikes, package installation, runtime tests, provider/API/AI/MCP calls, migrations, builds or deployment.

## Product model

- **WPEssential Free:** Custom Post Types Builder and Taxonomy Builder, permanently available.
- **WPEssential Pro:** premium modules distributed as a separate add-on outside WordPress.org.
- Disabling a module never deletes its data unless explicitly requested.
- License expiry preserves configuration/data and must not expose protected content or break public output.

## Current planning scope

Scope history:
- original product scope: **31 surfaces**;
- ADR-0177: **43**;
- ADR-0188: **48**;
- ADR-0194: **50**;
- ADR-0195: **55**;
- ADR-0197: current **56 module/platform surfaces**.

Current product planning truth:
- option/product behavior: **56/56 Exhaustive**;
- logical Multisite mapping: **56/56**;
- shared AI Prompt product mapping: **56/56**;
- implementation authorization: **0/56**;
- implemented/runtime verified: **none**.

Historical 31/31, 43/43, 48/48, 50/50 and 55/55 statements remain valid earlier-scope snapshots when explicitly presented as historical.

## Current expansion state

Surfaces 44–50 added/expanded market and access/admin/media/code capabilities. ADR-0195 then added:
- **51 Content Order & Sequence Manager**;
- **52 Security Integrity, Malware & Vulnerability Scanner**;
- **53 Font Library, Typography & Delivery Manager**;
- **54 User Data Stores, Favorites & Collections**;
- **55 Staging, Clone & Migration Manager**.

ADR-0197 added:
- **56 Theme Workspace, Child Theme & Theme Customization Manager** — declarative/theme-source tooling only; no arbitrary live PHP execution.

Competitive parity work extends canonical owners instead of creating duplicate engines.

## Solution Blueprint / AI-native direction

Complete CRM/ERP/LMS/booking/commerce/developer systems compose reusable WPE modules/foundations/adapters through Solution Blueprints rather than creating one private runtime per system.

Current planning includes:
- 160 curated reference systems across 20 domains;
- 40 reusable application patterns;
- 268,800 raw primary Blueprint combinations before validation/secondary dimensions;
- one shared AI Prompt/Requirement Compiler across all **56** surfaces;
- optional WordPress MCP/Abilities exposure under Capability + Policy;
- S07 Product Discovery/Pre-Development Planning Orchestrator;
- S08 Market Intelligence Radar with a documented daily GitHub job design; executable scheduling remains uninstalled before consent.

## Evidence/readiness truth

Detailed exact evidence specifications exist for SBP, ANL, SRH, DEC, LED, RSV, PLC, EXP, DOC, SYN, GEO, AIP and WCA. They are planning-complete but **0 executed** and provide no runtime certification.

ADR-0207 / WP112 found **5,808** exact supplemental fixture definitions missing. ADR-0208 / WP113 has now completed all seven Market Expansion protocols:
- RDR, SRT, DMY, LNK, DBM, PDO and MIR;
- **1,232/1,232 exact fixtures documented**;
- **0 executed**.

Those seven namespaces are now `NO GAP / READY AS PLAN` at the evidence-design layer and remain `RUNTIME EVIDENCE PENDING` operationally.

Known remaining planning gap:
- **WP114 CURRENT** — First Competitive MPR/RPR/ATM/MDP/STM — **880**;
- WP115 — Second Competitive ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC — **1,936**;
- WP116 — Third Competitive UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX — **1,760**;
- total remaining: **4,576 exact definitions across 26 namespaces**.

P0 remains `SPECIFICATION` and is **not yet approval-ready**. After WP116, a new final closure audit must decide whether P0 may move to `AWAITING_DEVELOPMENT_APPROVAL`.

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
12. `docs/QUALITY/P0-FINAL-PREDEVELOPMENT-CLOSURE-READINESS-AUDIT.md`
13. module/Solution/AI/Quality/ADR documentation relevant to the selected milestone.

## Module specification rule

No production module implementation may begin while product behavior is only a feature list. Every module first documents screens, options, fields, defaults, validation, permissions, lifecycle/failure states, dependencies, assets, import/export, negative/MUST-NOT behavior, Multisite, AI Prompt behavior and acceptance/evidence requirements.

Current product-option coverage: **56/56 module/platform surfaces meet the Phase 0 Exhaustive product-behavior bar.** This means specified, not implemented, verified, runtime-certified or authorized.

## Default engineering lifecycle

`Inspect → Understand → Research → Assess → Plan → Approval/Consent Gate → Implement → FAST Gate → Review/Attack/Harden → Integrate → FULL Gate → Document → Commit → Checkpoint → Report`.

The consent gate is mandatory and external to technical readiness.

## Compatibility / UI / Build direction

Planning target is WordPress 7.1; minimum WordPress candidate remains 6.9 and minimum PHP candidate 8.3 pending executable compatibility evidence.

React + TypeScript remain product requirements through WPE-owned wrappers over compatible WordPress public primitives and WordPress-provided React. Build evaluation remains `@wordpress/build` → `@wordpress/scripts` → Vite only for proven gaps; Laravel Mix is not carried forward.

No package/build spike is authorized.

## Current planning work

WP112 final closure/readiness audit is **DONE / ADR-0207**. WP113 Market Expansion exact evidence is **DONE / ADR-0208**.

**Current safe planning package: WP114 — First Competitive exact executable-evidence specification (`MPR/RPR/ATM/MDP/STM`, 880 fixtures).**

Production development authorization remains **NOT GRANTED / 0/56**.