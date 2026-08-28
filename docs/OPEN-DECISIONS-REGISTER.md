# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-29

This register tracks unresolved runtime/physical/provider/evidence decisions. Accepted planning decisions extend through **ADR-0194**. Architecture/protocol acceptance never implies runtime certification or owner development authorization.

Current canonical product scope: **50 surfaces**.  
Authorized: **0/50**.  
Logical Multisite mapping: **50/50**.  
AI Prompt product mapping: **50/50**.  
All executable work remains blocked by ADR-0014.

## A. Established platform executable blockers

D-001…D-050 remain the previously accepted blockers for compatibility, UI, Jobs, Definition, Vault, Free↔Pro, CI/Build, Query, Relations, Workflow, Membership, Backup, TUF, Dashboards, Builders, Status, XML-RPC, Settings, Profile, Roles, REST, Import, Forms, Notifications, Chat, Connections, Fields, Tables, Admin Columns, Listings, CPT/Taxonomy, Emails, Platform surfaces, Multisite/Lifecycle, Audit, Kernel, Privacy, Errors, Component Blueprint, Versioning, Module Lifecycle, DSR, Assets, Conditional Logic, DVR, Rate Limit, Cache, Remote Privacy and Email Transport.

Exact evidence IDs/counters remain authoritative in `IMPLEMENTATION-READINESS-MATRIX.md` and associated ADR/QUALITY protocols.

## B. Universal system / AI expansion blockers

D-051…D-064 remain active for F01–F12, WooCommerce Domain Adapter and shared Prompt/Requirement Compiler. Current counters remain SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA **0/176** unless otherwise explicitly recorded; F03 Search/Index detailed evidence is current WP65.

## C. Market-expansion blockers — ADR-0183…ADR-0188

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-065 | ADR-0183 | URL Redirection & Routing — RDR-001…RDR-176; 0/176 |
| D-066 | ADR-0184 | Search/Replace & Data Transformation — SRT-001…SRT-176; 0/176 |
| D-067 | ADR-0185 | Dummy/Synthetic Data & Fixture Studio — DMY-001…DMY-176; 0/176 |
| D-068 | ADR-0186 | Link Health/Crawl Intelligence — LNK-001…LNK-176; 0/176 |
| D-069 | ADR-0187 | DB Maintenance/Cleanup — DBM-001…DBM-176; 0/176 |
| D-070 | ADR-0188 | S07 Product Discovery/Planning Orchestrator — PDO-001…PDO-176; 0/176 |
| D-071 | ADR-0188 | S08 Market Intelligence Radar — MIR-001…MIR-176; 0/176; executable daily job not installed |

## D. Access / Admin / Media / Code blockers — ADR-0189…ADR-0194

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-072 | ADR-0189 | Membership registration/private-site/restriction-default/migration parity — MPR-001…MPR-176; 0/176, plus existing MBR/MB-F/PC-F |
| D-073 | ADR-0190 | Role target-hierarchy/rescue/provenance/surface-policy/network-sync parity — RPR-001…RPR-176; 0/176, plus existing RA |
| D-074 | ADR-0191 | Surface 49 Admin Theme/Branding token/version/assignment/accessibility/Multisite compatibility — ATM-001…ATM-176; 0/176 |
| D-075 | ADR-0192 | Surface 28 media field metrics/LCP/lazy/responsive/format/placeholder/Core-coexistence — MDP-001…MDP-176; 0/176, plus existing WM |
| D-076 | ADR-0193/0194 | Surface 50 Safe Script/Tag placement/consent/CSP/origin/environment/revision/Multisite security — STM-001…STM-176; 0/176; PHP/eval remains prohibited |

## E. Accepted reuse / non-duplication decisions

- Membership parity extends Surface 15; no second membership engine.
- Role parity extends Surface 30; WordPress remains native authorization authority.
- Media performance extends Surface 28; WPE must detect Core/Performance-Team ownership instead of duplicating merged behavior.
- Admin Theme is new Surface 49 because visual token/branding/assignment lifecycle was not owned by existing Admin Menu/Settings/Dashboard modules.
- Safe Script/Tag is new Surface 50 because user-configured browser tags/consent/CSP/environment lifecycle is distinct from Asset Registry, while arbitrary PHP/eval remains rejected.
- Existing Query Monitor/Health Check/WP Crontrol/activity/media-replace patterns continue to extend their existing WPE owners instead of inflating the module denominator.

## F. Evidence execution truth

New/supplemental counters:
- MPR 0/176;
- RPR 0/176;
- ATM 0/176;
- MDP 0/176;
- STM 0/176.

All established/universal/market counters remain unexecuted as recorded in Readiness/Checkpoint. No runtime certification exists for the new scope.

## G. Current planning priority

Owner-requested WP83…WP89 planning interrupt is DONE. Current work returns to:

**P0-M00-WP65 — F03 Search & Indexing detailed executable-evidence specification.**

WP66…WP74 remain reserved for the earlier F04→WooCommerce Adapter sequence.

## H. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Research current public sources when requested/needed.
3. Distinguish source facts, market facts, inference and WPE decisions.
4. Map reuse before adding a module.
5. Resolve static semantics by ADR when sufficient.
6. Predefine bounded evidence when runtime proof is required.
7. Never promote paper evidence to runtime/provider certification.
8. No code/build/DB/user-role-membership mutation/provider/AI/MCP/media rewrite/script injection/test execution before explicit consent.
9. Keep checkpoint/ledger/readiness/open-decisions/ADR index/Draft PR synchronized.

Production development authorization remains **NOT GRANTED / 0/50**.