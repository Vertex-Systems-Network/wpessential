# WPEssential Architecture Decision Records

Status: **Phase 0 planning / no development authorization**  
Last synchronized: 2026-08-29

Planning acceptance never grants development permission; ADR-0014 remains the hard consent gate. Historical ADRs and scope snapshots retain their accepted semantics.

## Current accepted evidence state

- current product denominator: **56 surfaces**;
- product-option maturity **56/56 Exhaustive**;
- Multisite **56/56**;
- AI Prompt **56/56**;
- implementation authorization **0/56**;
- runtime-certified/implemented **none**.

Key current ADRs:
- ADR-0207 — WP112 closure audit; 5,808 exact supplemental definitions identified;
- ADR-0208 — WP113 Market Expansion exact evidence; **1,232/1,232 / 0 executed**;
- **ADR-0209 — WP114 First Competitive exact evidence; MPR/RPR/ATM/MDP/STM = 880/880 / 0 executed**.

Exact evidence design complete so far includes universal/adapter SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA, ADR-0208 RDR/SRT/DMY/LNK/DBM/PDO/MIR and ADR-0209 MPR/RPR/ATM/MDP/STM. All remain unexecuted.

## Remaining planning gap

**3,696 exact definitions / 21 namespaces**:
- **WP115 CURRENT** — ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC — 1,936;
- WP116 — UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX — 1,760.

WP115 master plan now explicitly fixes all 11 group envelopes; supplemental BKX/MRL/PBX/JEX/LHX/HFC ranges were normalized from the accepted addendum before exact fixture enumeration.

## Preserved boundaries

- UI/branding/navigation hiding ≠ authorization.
- WordPress meta-cap + WPE Policy remain role/action authority.
- Safe Script/Tag remains browser-side/declarative; no PHP/eval/arbitrary server execution.
- Backup ≠ Staging/Migration; clone ≠ same identity/environment.
- Security finding ≠ certainty; font delivery ≠ redistribution authority; UDS state ≠ Woo cart/order truth.
- Theme Workspace cannot become arbitrary live PHP execution.
- AI/MCP cannot create hidden privilege/provider/mutation paths.

## Current work

WP112 DONE / ADR-0207; WP113 DONE / ADR-0208; WP114 DONE / ADR-0209.

**WP115 CURRENT — Second Competitive exact executable-evidence specification, 1,936 fixtures.**

After WP116, a new closure/readiness audit determines whether P0 may move to `AWAITING_DEVELOPMENT_APPROVAL`.

Lifecycle remains `SPECIFICATION`; current authorization **0/56**.