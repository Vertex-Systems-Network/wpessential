# WPEssential — Open Decisions & Readiness Blocker Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-29

This register separates unresolved *planning decisions* from already-specified runtime/provider evidence. Accepted planning decisions extend through **ADR-0208**. Architecture/protocol acceptance never implies runtime certification or owner development authorization.

Current canonical product scope: **56 surfaces**.  
Authorized: **0/56**.  
Logical Multisite mapping: **56/56**.  
AI Prompt product mapping: **56/56**.  
Current planning work: **WP114**.  
All executable work remains blocked by ADR-0014.

Historical D-001…D-076 identifiers and their original ADR meanings remain valid records; this current register classifies their present readiness state rather than renumbering them.

## A. True planning gaps after WP113 / ADR-0208

ADR-0207 identified 33 reserved namespaces / 5,808 missing exact fixture definitions. ADR-0208 has completed all seven Market Expansion namespaces, closing **1,232** definitions.

Remaining exact planning gap: **26 namespaces / 4,576 fixtures**.

### WP114 — CURRENT — First Competitive / Access-Admin-Media-Code — 880 fixtures
- MPR, RPR, ATM, MDP, STM

### WP115 — Second Competitive — 1,936 fixtures
- ORD, SEC, FNT, UDS, STG, BKX, MRL, PBX, JEX, LHX, HFC

### WP116 — Third Competitive — 1,760 fixtures
- UAF, MIG, WLB, DUP, ALX, MBX, THM, RSX, RDX, CPTX

These remain `PLANNING GAP` because the master plans fix group ownership but not all individual fixture definitions.

## B. Market Expansion — planning complete, runtime pending

ADR-0208 accepts exact 176-fixture protocols for:
- RDR — URL Redirection & Routing;
- SRT — Search/Replace & Data Transformation;
- DMY — Dummy/Synthetic Data & Fixture Studio;
- LNK — Link Health & Crawl Intelligence;
- DBM — Database Maintenance & Cleanup;
- PDO — Product Discovery & Planning Orchestrator;
- MIR — Market Intelligence Radar.

Each is **176/176 exact documented / 0 executed**. They are no longer open planning gaps. Their current class is `NO GAP / READY AS PLAN` for evidence design and `RUNTIME EVIDENCE PENDING` operationally.

## C. Detailed universal/adapter evidence — planning complete, runtime pending

Exact detailed protocols also exist for:
- SBP, ANL, SRH, DEC, LED, RSV, PLC, EXP, DOC, SYN, GEO, AIP, WCA.

They are **not open planning decisions** merely because execution is zero. They remain `RUNTIME EVIDENCE PENDING` until later authorized execution produces real evidence.

## D. Established platform evidence blockers

Compatibility, UI, Jobs, Definition/Fields/Relations/Query/Tables, Vault, Free↔Pro, CI/Build, Workflow, Notifications, Chat, Connections, Audit, Kernel/Policy/Abilities, Privacy, Errors, Versioning, Module Lifecycle, Data Source, Assets, Conditional Logic, Dynamic Values, Rate Limit, Cache, REST/Import/Export, Roles/Users, Protector, XML-RPC, Reset, Settings, Frontend Dashboard, Media and related established evidence families remain governed by their existing ADR/QUALITY protocols.

Where an exact protocol already exists, status is `RUNTIME EVIDENCE PENDING`, not `PLANNING GAP`.

## E. Provider certification blockers

Provider contracts/evidence remain separately pending for applicable transports and external authorities, including:
- email transport;
- membership billing;
- protected-file delivery;
- backup providers;
- connection/integration adapters;
- builder adapters where certification is required;
- geocoder/routing providers;
- Woo payment, tax, shipping and external inventory providers.

Status: `PROVIDER CERTIFICATION PENDING` unless an explicit later certification record says otherwise.

Transport/API success alone never proves business/provider truth. Unknown external outcome remains unknown until reconciled.

## F. Owner-consent blocker

`GOV-OWNER-CONSENT-000` remains PENDING.

Status: `OWNER CONSENT PENDING` for every production source/runtime/build/migration/test/provider/API/AI/MCP action.

`continue`, `resume`, planning acceptance, ADR acceptance and documentation completion are not implementation consent.

## G. Accepted non-duplication decisions

- Membership parity extends Surface 15; no second membership engine.
- Role parity extends Surface 30; WordPress/Policy authority remains canonical.
- Media performance extends Surface 28; Core/provider ownership is detected rather than duplicated.
- Admin Theme is Surface 49; presentation does not become authorization.
- Safe Script/Tag is Surface 50 and remains browser-side; no PHP/eval arbitrary server execution.
- Surfaces 51–55 retain ADR-0195 ownership.
- Surface 56 Theme Workspace retains ADR-0197 ownership and cannot become arbitrary live PHP editing/execution.
- Universal F01–F12, WCA and Market Expansion services compose canonical owners rather than creating duplicate business engines.

## H. Current execution truth

No planning counter is promoted to runtime/provider certification by this register. No WP113 or WP114 fixture has executed.

Detailed evidence remains documented-only unless an explicit later execution record proves otherwise.

## I. Current planning priority

WP112 is **DONE / ADR-0207**. WP113 is **DONE / ADR-0208** with **1,232 exact definitions documented / 0 executed**.

P0 remains open with **4,576 exact definitions / 26 namespaces** still required.

**Current: P0-M00-WP114 — First Competitive exact executable-evidence specification (`MPR/RPR/ATM/MDP/STM`, 880 fixtures).**

WP115–WP116 are reserved follow-ons. After WP116 a new final closure audit must decide whether P0 can move to `AWAITING_DEVELOPMENT_APPROVAL`.

Production development authorization remains **NOT GRANTED / 0/56**.