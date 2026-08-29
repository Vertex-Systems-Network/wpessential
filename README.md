# WPEssential

WPEssential is a modular, AI-native WordPress application platform for structured content, data, automation, administration, integrations, operations, identity, membership/access, frontend experiences, application composition and developer/operations tooling.

> **Repository status:** Phase 0 — research, product specification, architecture and evidence planning. Production feature development has not started and is **not authorized**.

Current canonical project state: `PLANNED_EXISTING_PROJECT`  
Current execution mode: `PLANNER_ONLY`  
Current lifecycle: `SPECIFICATION`

## Development consent gate

Production development requires explicit scoped project-owner consent under `DEVELOPMENT-CONSENT.md`, `AGENTS.md`, `docs/APPROVAL-LEDGER.md` and ADR-0014.

`continue`, `resume`, research/planning approval, an Accepted ADR or Phase 0 planning completion does **not** authorize coding, executable spikes, package installation, runtime tests, provider/API/AI/MCP calls, migrations, builds or deployment.

## Product model

- **WPEssential Free:** Custom Post Types Builder and Taxonomy Builder, permanently available.
- **WPEssential Pro:** premium modules distributed as a separate add-on outside WordPress.org.
- Disabling a module never deletes its data unless explicitly requested.
- License expiry preserves configuration/data and must not expose protected content or break public output.

## Current planning scope

Scope history:
- original: **31**;
- ADR-0177: **43**;
- ADR-0188: **48**;
- ADR-0194: **50**;
- ADR-0195: **55**;
- ADR-0197 current: **56 module/platform surfaces**.

Current planning truth:
- product-option maturity: **56/56 Exhaustive**;
- logical Multisite mapping: **56/56**;
- shared AI Prompt mapping: **56/56**;
- implementation authorization: **0/56**;
- implemented/runtime verified: **none**.

Historical 31/43/48/50/55 statements remain valid historical snapshots.

## Exact evidence/readiness truth

Exact detailed evidence exists for universal/adapter namespaces SBP, ANL, SRH, DEC, LED, RSV, PLC, EXP, DOC, SYN, GEO, AIP and WCA, all documentation-only and unexecuted.

WP112 / ADR-0207 found **5,808 exact supplemental definitions / 33 namespaces** missing.

### WP113 — DONE / ADR-0208

Market Expansion exact evidence:
- RDR, SRT, DMY, LNK, DBM, PDO, MIR;
- **1,232/1,232 exact fixtures documented / 0 executed**.

### WP114 — DONE / ADR-0209

First Competitive exact evidence:
- MPR, RPR, ATM, MDP, STM;
- each **176/176 exact documented / 0 executed**;
- WP114 total **880/880 documented / 0 executed**.

MPR/RPR/ATM/MDP/STM are now `NO GAP / READY AS PLAN` at evidence-design level and remain `RUNTIME EVIDENCE PENDING` operationally.

Known remaining planning gap:
- **WP115 CURRENT** — ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC — **1,936**;
- WP116 — UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX — **1,760**;
- total remaining: **3,696 exact definitions across 21 namespaces**.

P0 remains `SPECIFICATION` and is **not yet approval-ready**. After WP116, a new final closure audit must decide whether P0 may move to `AWAITING_DEVELOPMENT_APPROVAL`.

## Important ownership boundaries

- User ≠ Role/Capability ≠ Plan ≠ Enrollment ≠ Entitlement ≠ Policy.
- UI/navigation/admin-theme hiding ≠ authorization.
- WordPress capability/meta-cap + WPE Policy remain role/action authority; Super Admin ≠ ordinary role.
- Registration/account creation ≠ verified/approved/enrolled/paid entitlement.
- LCP/priority/viewport inference ≠ measured Core Web Vitals improvement; private media must not leak through optimization.
- Safe Script/Tag is browser-side/declarative only: no PHP/eval/arbitrary SQL/shell/server code; CSP/consent cannot be silently weakened; Vault secrets are not frontend token sources.
- Search/index ≠ source truth; score/formula/rank ≠ Policy/mutation authority.
- Ledger hold ≠ reservation; reservation ≠ payment/order/entitlement.
- Generated document ≠ source/legal/payment truth.
- Sync copy ≠ source truth without explicit authority.
- Geospatial match ≠ verified identity/authorization/serviceability.
- Woo adapter ≠ second commerce engine.
- Backup ≠ Staging/Migration; clone ≠ same identity.
- Theme Workspace cannot become arbitrary live PHP execution.
- AI/MCP cannot create hidden privilege/provider/mutation paths.

## Engineering source of truth

Repository state, executed evidence, documentation/ADRs, checkpoints and VCS history are authoritative. Chat history is not.

Before implementation read `DEVELOPMENT-CONSENT.md`, `AGENTS.md`, `CHECKPOINT.md`, `docs/APPROVAL-LEDGER.md`, `docs/IMPLEMENTATION-READINESS-MATRIX.md`, `docs/WORK-COORDINATION-LEDGER.md`, `docs/OPEN-DECISIONS-REGISTER.md` and relevant module/ADR/evidence documentation.

## Current planning work

WP112 is **DONE / ADR-0207**. WP113 is **DONE / ADR-0208**. WP114 is **DONE / ADR-0209**.

**Current safe planning package: WP115 — Second Competitive exact executable-evidence specification (`ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC`, 1,936 fixtures).**

Production development authorization remains **NOT GRANTED / 0/56**.