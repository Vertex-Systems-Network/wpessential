# WPEssential — Open Decisions & Readiness Blocker Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-29

Accepted planning/evidence decisions extend through **ADR-0209**. Architecture/protocol acceptance never implies runtime certification or owner development authorization.

Current scope: **56 surfaces**  
Authorized: **0/56**  
Multisite mapping: **56/56**  
AI Prompt mapping: **56/56**  
Current planning work: **WP115**

## A. True planning gaps

ADR-0207 identified **5,808 / 33 namespaces**. Closed since then:
- WP113 / ADR-0208: **1,232 / 7**;
- WP114 / ADR-0209: **880 / 5**.

Remaining: **3,696 exact definitions / 21 namespaces**.

### WP115 — CURRENT — 1,936
- ORD, SEC, FNT, UDS, STG, BKX, MRL, PBX, JEX, LHX, HFC

### WP116 — RESERVED — 1,760
- UAF, MIG, WLB, DUP, ALX, MBX, THM, RSX, RDX, CPTX

## B. Planning-complete supplemental evidence

ADR-0208 exact-planned namespaces:
- RDR, SRT, DMY, LNK, DBM, PDO, MIR.

ADR-0209 exact-planned namespaces:
- **MPR, RPR, ATM, MDP, STM**.

Each has **176/176 exact fixtures documented / 0 executed** and is `NO GAP / READY AS PLAN` at evidence-design level, `RUNTIME EVIDENCE PENDING` operationally.

## C. Detailed universal/adapter evidence

SBP, ANL, SRH, DEC, LED, RSV, PLC, EXP, DOC, SYN, GEO, AIP and WCA are exact and unexecuted. Existing exact platform/module protocols likewise remain runtime evidence blockers rather than planning gaps.

## D. Provider certification blockers

Applicable email, billing, protected-file, backup, connection/integration, geocoder/routing, media/CDN/browser, Woo payment/tax/shipping/inventory and other external authorities remain `PROVIDER CERTIFICATION PENDING` unless later execution evidence says otherwise.

Unknown external outcome remains unknown until reconciled under the owning contract.

## E. Owner-consent blocker

`GOV-OWNER-CONSENT-000` remains PENDING. All production source/runtime/build/migration/test/provider/API/AI/MCP activity is `OWNER CONSENT PENDING`.

## F. Accepted non-duplication/security decisions

- Membership parity extends Surface 15; no second Membership engine.
- Role parity extends Surface 30; WordPress/meta-cap/Policy authority remains canonical.
- Admin Theme presentation never becomes authorization.
- Media performance extends Surface 28 and composes Core/provider ownership.
- Safe Script/Tag remains browser-side/declarative; no PHP/eval/arbitrary SQL/shell/server execution.
- Surfaces 51–55 retain ADR-0195 ownership.
- Surface 56 Theme Workspace retains ADR-0197 ownership and cannot become arbitrary live PHP execution.
- Backup ≠ Staging/Migration; clone ≠ same identity.
- AI/MCP cannot create hidden privilege/provider/mutation paths.

## G. Current execution truth

No WP113/WP114/WP115 fixture has executed. No runtime/provider certification is promoted by this register.

## H. Current planning priority

WP112 DONE / ADR-0207; WP113 DONE / ADR-0208; WP114 DONE / ADR-0209.

**Current: P0-M00-WP115 — Second Competitive exact executable-evidence specification (`ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC`, 1,936 fixtures).**

After WP116 a fresh final closure/readiness audit must decide whether P0 can move to `AWAITING_DEVELOPMENT_APPROVAL`.

Production development authorization remains **NOT GRANTED / 0/56**.