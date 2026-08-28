# WPEssential — Open Decisions & Readiness Blocker Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-29

This register separates unresolved *planning decisions* from already-specified runtime/provider evidence. Accepted planning decisions extend through **ADR-0207**. Architecture/protocol acceptance never implies runtime certification or owner development authorization.

Current canonical product scope: **56 surfaces**.  
Authorized: **0/56**.  
Logical Multisite mapping: **56/56**.  
AI Prompt product mapping: **56/56**.  
Current planning work: **WP113**.  
All executable work remains blocked by ADR-0014.

Historical D-001…D-076 identifiers and their original ADR meanings remain valid records; this current register classifies their present readiness state rather than renumbering them.

## A. True planning gaps after WP112 / ADR-0207

Exact individual fixture definitions remain missing for **33 already-reserved namespaces / 5,808 fixtures**. Group ownership and IDs are already fixed; exact cases must now be enumerated.

### WP113 — Market Expansion — 1,232 fixtures
- RDR, SRT, DMY, LNK, DBM, PDO, MIR

### WP114 — First Competitive / Access-Admin-Media-Code — 880 fixtures
- MPR, RPR, ATM, MDP, STM

### WP115 — Second Competitive — 1,936 fixtures
- ORD, SEC, FNT, UDS, STG, BKX, MRL, PBX, JEX, LHX, HFC

### WP116 — Third Competitive — 1,760 fixtures
- UAF, MIG, WLB, DUP, ALX, MBX, THM, RSX, RDX, CPTX

These are `PLANNING GAP`, not merely runtime counters.

## B. Detailed universal/adapter evidence — planning complete, runtime pending

Exact detailed protocols exist for:
- SBP, ANL, SRH, DEC, LED, RSV, PLC, EXP, DOC, SYN, GEO, AIP, WCA.

They are **not open planning decisions** merely because execution is zero. They remain `RUNTIME EVIDENCE PENDING` until later authorized execution produces real evidence.

## C. Established platform evidence blockers

Compatibility, UI, Jobs, Definition/Fields/Relations/Query/Tables, Vault, Free↔Pro, CI/Build, Workflow, Notifications, Chat, Connections, Audit, Kernel/Policy/Abilities, Privacy, Errors, Versioning, Module Lifecycle, Data Source, Assets, Conditional Logic, Dynamic Values, Rate Limit, Cache, REST/Import/Export, Roles/Users, Protector, XML-RPC, Reset, Settings, Frontend Dashboard, Media and related established evidence families remain governed by their existing ADR/QUALITY protocols.

Where an exact protocol already exists, status is `RUNTIME EVIDENCE PENDING`, not `PLANNING GAP`.

## D. Provider certification blockers

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

## E. Owner-consent blocker

`GOV-OWNER-CONSENT-000` remains PENDING.

Status: `OWNER CONSENT PENDING` for every production source/runtime/build/migration/test/provider/API/AI/MCP action.

`continue`, `resume`, planning acceptance, ADR acceptance and documentation completion are not implementation consent.

## F. Accepted non-duplication decisions

- Membership parity extends Surface 15; no second membership engine.
- Role parity extends Surface 30; WordPress/Policy authority remains canonical.
- Media performance extends Surface 28; Core/provider ownership is detected rather than duplicated.
- Admin Theme is Surface 49; presentation does not become authorization.
- Safe Script/Tag is Surface 50 and remains browser-side; no PHP/eval arbitrary server execution.
- Surfaces 51–55 retain ADR-0195 ownership.
- Surface 56 Theme Workspace retains ADR-0197 ownership and cannot become arbitrary live PHP editing/execution.
- Universal F01–F12 and the Woo adapter compose canonical owners rather than creating duplicate business engines.

## G. Current execution truth

No planning counter is promoted to runtime/provider certification by this register. No WP113 fixture has executed.

Detailed evidence remains documented-only unless an explicit later execution record proves otherwise.

## H. Current planning priority

WP112 is **DONE / ADR-0207**. P0 remains open because 5,808 exact supplemental/market fixture definitions remain.

**Current: P0-M00-WP113 — Market Expansion exact executable-evidence specification (`RDR/SRT/DMY/LNK/DBM/PDO/MIR`, 1,232 fixtures).**

WP114–WP116 are reserved follow-ons. After WP116 a new final closure audit must decide whether P0 can move to `AWAITING_DEVELOPMENT_APPROVAL`.

Production development authorization remains **NOT GRANTED / 0/56**.