# WPEssential — Detailed Module Specifications

Status: **Phase 0 — 56/56 product surfaces Exhaustively specified; supplemental evidence planning remains; development not authorized**

This directory is the product-behavior source of truth. Detailed files define screens, fields, options, defaults, validation, permissions, lifecycle, integrations, failure behavior and evidence expectations.

## Development consent gate

Production development remains prohibited until explicit scoped owner authorization under `/DEVELOPMENT-CONSENT.md` and ADR-0014. `continue`, `resume`, planning/ADR acceptance or Phase 0 completion are not consent.

## Current maturity

- Product-option result: **56/56 Exhaustive**.
- Logical Multisite mapping: **56/56**.
- Module-wide AI Prompt mapping: **56/56**.
- Development authorization: **0/56**.
- Implemented/runtime certified: **none**.

Scope lineage remains 31 → 43 → 48 → 50 → 55 → current 56; earlier denominators remain historical snapshots.

## Evidence-planning progress

Exact universal/adapter planning exists for SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA.

WP113 / ADR-0208 completed RDR/SRT/DMY/LNK/DBM/PDO/MIR: **1,232/1,232 exact / 0 executed**.

WP114 / ADR-0209 completed MPR/RPR/ATM/MDP/STM: **880/880 exact / 0 executed**.

Known remaining exact planning gap: **3,696 definitions / 21 namespaces**.

### WP115 — CURRENT — 1,936
`ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC`

The five new-surface group ranges were already canonical. WP115 preflight found BKX/MRL/PBX/JEX/LHX/HFC had reserved IDs but their explicit 16-group ownership was absent from the Second Competitive master plan; that ownership is now normalized from `SECOND-COMPETITIVE-PARITY-EXISTING-SURFACES-ADDENDUM.md` before exact fixture enumeration.

### WP116 — RESERVED — 1,760
`UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX`

## Current product surfaces

The canonical denominator remains all **56** accepted surfaces from Custom Post Types Builder through Theme Workspace. `OPTION-COVERAGE-MATURITY.md` is the concise current surface/evidence ledger; historical detailed specs remain authoritative for product behavior.

Key current evidence effects:
- Surface 15 Membership: MPR exact / ADR-0209;
- Surface 30 Role & Capability: RPR exact / ADR-0209;
- Surface 49 Admin Theme: ATM exact / ADR-0209;
- Surface 28 Media Performance: MDP exact / ADR-0209;
- Surface 50 Safe Script/Tag: STM exact / ADR-0209;
- Surfaces 51–55 and second-audit owner supplements are current WP115 evidence work;
- Surface 56/third-audit supplements remain WP116.

## Ownership boundaries

- presentation hiding/branding/navigation ≠ authorization;
- Membership, Role/Capability, Policy, Enrollment and Entitlement remain distinct;
- WordPress meta-cap + WPE Policy remain permission authority; Super Admin ≠ ordinary role;
- media performance hints ≠ measured CWV and private media cannot leak through optimization;
- Safe Script/Tag is browser-side only and never enables PHP/eval/server code, CSP/consent weakening or frontend secret interpolation;
- Backup ≠ Staging/Migration; clone ≠ same identity/environment;
- security finding ≠ certainty; font availability ≠ redistribution authority; UDS state ≠ Woo cart/order truth;
- Theme Workspace cannot become arbitrary live PHP execution;
- AI/MCP cannot bypass normal Policy/approval.

## Current conclusion

**Product-option planning:** 56/56 Exhaustive.  
**WP113:** DONE / ADR-0208.  
**WP114:** DONE / ADR-0209.  
**Remaining exact supplemental planning:** WP115–WP116 / 3,696 definitions.  
**Technical/runtime certification:** not reached globally.  
**Development:** not started, not authorized.

Current safe planning work: **WP115 — Second Competitive exact executable-evidence specification (`ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC`, 1,936 fixtures).**