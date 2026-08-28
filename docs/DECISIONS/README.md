# WPEssential Architecture Decision Records

Status: **Phase 0 planning / no development authorization**  
Last synchronized: 2026-08-29

ADRs preserve long-lived product, architecture, security, data, compatibility and distribution decisions. Accepted ADRs are never silently changed; reversal requires a superseding ADR. Technical acceptance never grants development permission; ADR-0014 remains the hard consent gate.

## Historical authority

ADR-0001…ADR-0176 remain individually authoritative exactly as previously accepted/proposed. Their source files and Git history preserve full detail. This compact index records current expansion/evidence milestones without rewriting historical semantics.

## Current expansion sequence

| ADR | Status | Decision |
|---|---|---|
| ADR-0177 | Accepted expanded product architecture | Solution Blueprint + 12 universal foundations + Woo adapter; 43-surface milestone |
| ADR-0178 | Accepted AI architecture | shared Prompt/Requirement Compiler + WordPress AI Client/Connectors + Abilities + optional MCP |
| ADR-0179 | Accepted AI/MCP evidence | AIP 0/176; AIC/MCP runtime certs 0 |
| ADR-0180 | Accepted universal evidence master plan | SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA envelopes |
| ADR-0181 | Accepted F01 evidence | SBP 0/176 |
| ADR-0182 | Accepted F02 evidence | ANL 0/176 |
| ADR-0183 | Accepted URL Redirection & Routing | Surface 44; RDR 0/176 |
| ADR-0184 | Accepted Search/Replace & Data Transformation | Surface 45; SRT 0/176 |
| ADR-0185 | Accepted Dummy/Synthetic Data & Fixture Studio | Surface 46; DMY 0/176 |
| ADR-0186 | Accepted Link Health & Crawl Intelligence | Surface 47; LNK 0/176 |
| ADR-0187 | Accepted Database Maintenance & Cleanup | Surface 48; DBM 0/176 |
| ADR-0188 | Accepted autonomous planning + market radar | S07 PDO 0/176; S08 MIR 0/176; 48-surface milestone |
| ADR-0189 | Accepted Membership competitive parity expansion | Surface 15 addendum; MPR 0/176 |
| ADR-0190 | Accepted Role & Capability competitive parity expansion | Surface 30 addendum; RPR 0/176 |
| ADR-0191 | Accepted Admin Theme, Branding & Experience Manager | **new Surface 49**; ATM 0/176 |
| ADR-0192 | Accepted Media Performance/Responsive Delivery expansion | Surface 28 addendum; MDP 0/176; WM remains separate |
| ADR-0193 | Accepted Safe Script, Tag & Code Injection Manager | **new Surface 50**; STM 0/176; no PHP/eval |
| ADR-0194 | Accepted consolidated access/admin/media/code scope | **50/50 Exhaustive; 50/50 Multisite; 50/50 AI Prompt; 0/50 authorized** |

## Major historical evidence milestones

ADR-0117…ADR-0176 remain the canonical historical evidence sequence. Representative current counters remain:
- FM 0/92; WF 0/116; JS 0/106; NT 0/142; CH 0/142; WC 0/156;
- CF 0/112; VT 0/128; UI 0/104; BT 0/112; CI 0/120; FP 0/144;
- MBR 0/160; BK 0/180; QRY 0/168; DEF 0/144; REL 0/160; CTB 0/184;
- established 176-fixture protocols remain 0/176;
- ET-F 0/176; 6 EE3 / 0 ET-certified;
- MB-F 0/176; 4 BE3 / 0 MB-certified;
- PC-F 0/176; 0 PC1+;
- BPC-F 0/176; 34 targets / 0 C-certified / V3 0;
- ICP-F 0/176; 0 I4 / 0 I5.

Earlier ADR-0001…ADR-0116 architecture/security/compatibility decisions remain authoritative in their individual files.

## Current product milestone

- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`: **50/50 Exhaustive, 0/50 Authorized**.
- logical Multisite product mapping: **50/50**.
- module-wide AI Prompt product mapping: **50/50**.
- `docs/SOLUTIONS/UNIVERSAL-SYSTEM-CATALOG.md`: 160 curated systems.
- reusable pattern library: 40 patterns.
- raw primary Blueprint composition space: 268,800 before validation/secondary dimensions.

Historical 31/31, 43/43 and 48/48 milestones remain earlier-scope snapshots.

## Current additional evidence truth

Universal/adapter evidence remains unexecuted:
SBP, ANL, SRH, DEC, LED, RSV, PLC, EXP, DOC, SYN, GEO, AIP, WCA — all **0/176**.

Market expansion remains unexecuted:
RDR, SRT, DMY, LNK, DBM, PDO, MIR — all **0/176**.

Access/admin/media/code expansion remains unexecuted:
- MPR 0/176;
- RPR 0/176;
- ATM 0/176;
- MDP 0/176;
- STM 0/176.

No paper/static evidence has been promoted to runtime certification.

## Non-duplication decisions

- Members/WP-Members behavior expands Membership Surface 15 instead of creating a second membership engine.
- User Role Editor-like behavior expands Role Surface 30 while preserving WordPress authority.
- Image Prioritizer/Auto Sizes-like behavior expands Media Surface 28 and detects Core ownership rather than duplicating merged Core behavior.
- Admin Theme/Branding is new Surface 49 because visual theming/assignment is a distinct reusable product primitive.
- Safe Script/Tag is new Surface 50, but generic PHP/eval runtime remains rejected by ADR-0004.

## Current planning state

WP83…WP89 owner-requested access/admin/media/code planning work is DONE.

Current/resumed package:
**WP65 — F03 Search & Indexing detailed executable-evidence specification**.

WP66…WP74 retain their reserved F04→WooCommerce Adapter meanings.

Current lifecycle remains `SPECIFICATION`. No implementation/evidence execution can begin without explicit scoped owner consent under ADR-0014. Current authorization: **0/50**.