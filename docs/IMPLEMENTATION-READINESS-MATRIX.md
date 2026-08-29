# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: **2026-08-29**

## Global rule

An `Exhaustive` surface or exact evidence protocol is not runtime-certified or authorized. Implementation requires applicable compatibility/security/privacy/recovery/performance/build/provider evidence plus explicit owner consent under ADR-0014.

Current scope: **56 surfaces**  
Product-option maturity: **56/56 Exhaustive**  
Multisite mapping: **56/56**  
AI Prompt mapping: **56/56**  
Authorized: **0/56**  
Implemented/runtime verified: **none**  
Lifecycle: **SPECIFICATION**  
Latest accepted planning/evidence decision: **ADR-0209**

## PLANNING GAP

Known remaining exact fixture expansion:

| Work | Namespaces | Definitions remaining |
|---|---|---:|
| **WP115 CURRENT** | ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC | **1,936** |
| WP116 | UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX | **1,760** |
| **Total** | **21 namespaces** | **3,696** |

WP113 and WP114 exact-definition gaps are closed.

## NO GAP / READY AS PLAN — exact evidence design

Exact numbered evidence now exists for:
- universal/adapter: SBP, ANL, SRH, DEC, LED, RSV, PLC, EXP, DOC, SYN, GEO, AIP, WCA;
- Market Expansion / ADR-0208: RDR, SRT, DMY, LNK, DBM, PDO, MIR;
- First Competitive / ADR-0209: **MPR, RPR, ATM, MDP, STM**.

Every namespace above is **176/176 documented and 0/176 executed** where the 176-fixture contract applies. `NO GAP / READY AS PLAN` means only that evidence-design planning is exact.

## RUNTIME EVIDENCE PENDING

All exact protocols above remain unexecuted. Existing compatibility, Multisite, UI/build/CI, Jobs, Definitions/Fields/Relations/Query/Tables, Vault, Workflow, Notifications, Chat, Connections, Audit, Kernel/Policy/Abilities, Privacy/Error/Version/Lifecycle, Data Source/Assets/Conditions/DVR/Rate/Cache, REST/Import-Export, Roles/Users/Protector/XML-RPC/Reset/Settings/Dashboard/Media and related exact protocols remain execution blockers rather than planning gaps.

## PROVIDER CERTIFICATION PENDING

Applicable email, billing, protected-file, backup, connection, geocoder/routing, browser/media/CDN, Woo payment/tax/shipping/inventory and other external authorities remain uncertified unless later execution evidence explicitly proves otherwise.

## OWNER CONSENT PENDING

ADR-0014 blocks every production source/runtime/build/migration/test/provider/API/AI/MCP activity until explicit scoped owner consent is recorded.

## Per-surface effect

All 56 surfaces are still **Exhaustive / Authorized: No**.

First Competitive parity is no longer a planning gap:
- Surface 15 Membership: MPR exact / ADR-0209;
- Surface 30 Role & Capability: RPR exact / ADR-0209;
- Surface 49 Admin Theme: ATM exact / ADR-0209;
- Surface 28 Media Performance: MDP exact / ADR-0209;
- Surface 50 Safe Script/Tag: STM exact / ADR-0209.

Remaining planning gaps are WP115 surfaces/supplements and WP116 third-competitive supplements.

## Stop-the-line invariants

- UI/branding/navigation hiding ≠ authorization.
- User ≠ Role/Capability ≠ Plan ≠ Enrollment ≠ Entitlement ≠ Policy.
- WordPress meta-cap + WPE Policy remain role/action authority; Super Admin ≠ ordinary role.
- Registration/account ≠ verified/approved/enrolled/paid entitlement.
- LCP/priority inference ≠ measured Core Web Vitals; private media cannot leak through optimization.
- Safe Script/Tag is browser-side only; no PHP/eval/arbitrary SQL/shell/server code; CSP/consent cannot be silently weakened; Vault secrets cannot become frontend tokens.
- Search/index ≠ source truth; formula/rank ≠ authorization/mutation authority.
- Backup ≠ Staging/Migration; clone ≠ same identity.
- AI/MCP ≠ hidden privilege/provider/mutation path.

## Current work

**P0-M00-WP115 — Second Competitive exact executable-evidence specification (`ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC`, 1,936 fixtures).**

After WP116, a new final closure/readiness audit decides whether P0 may transition to `AWAITING_DEVELOPMENT_APPROVAL`.

Production development authorization remains **NOT GRANTED / 0/56**.