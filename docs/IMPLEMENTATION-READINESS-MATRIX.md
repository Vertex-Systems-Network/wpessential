# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: 2026-08-29

## Global rule

A surface may be Exhaustive and have Accepted architecture while remaining technically unverified and unauthorized. Implementation requires applicable evidence, compatibility, security/quality/recovery gates and **explicit owner consent under ADR-0014**.

Current owner consent: **NOT GRANTED**.  
Current canonical scope after ADR-0194: **50 surfaces**.  
Authorized: **0/50**.  
Implemented/runtime verified: **none**.

Historical 31-, 43- and 48-surface snapshots remain historical truth for their earlier scopes.

## Established shared blockers

| Area | Evidence / certification still required |
|---|---|
| Compatibility | CF 0/112 |
| Multisite / Site lifecycle | MSI 0/160; LC 0/96; runtime certs 0 |
| UI / Build / CI | UI 0/104; BT 0/112; CI 0/120 |
| Job/Cron | JS 0/106 |
| Definition / Fields / Relations / Query / Tables | DEF 0/144; FST 0/176; REL 0/160; QRY 0/168; CTB 0/184 |
| Vault / Free↔Pro / OAuth / TUF | VT 0/128; FP 0/144; OA 0/176; TU 0/176 |
| Workflow / Notification / Chat / Connections | WF 0/116; NT 0/142; CH 0/142; WC 0/156 + ICP-F 0/176 |
| Audit / Kernel / Policy / Abilities / SDK | AUD 0/176; KPA 0/176 |
| Privacy / Error / Version / Lifecycle | PDL 0/176; RS 0/176; ERR 0/176; VER 0/176; MLC 0/176 |
| DSR / Assets / Conditions / DVR / Rate / Cache | DSR/ASR/CLG/DVR/RLT/CAC all 0/176 |
| AI Prompt / MCP | AIP 0/176; AIC/MCP runtime certs 0 |
| Owner consent | ADR-0014 blocks every executable activity |

## Universal-foundation / adapter blockers

SBP, ANL, SRH, DEC, LED, RSV, PLC, EXP, DOC, SYN, GEO, AIP and WCA all remain **0/176**. F03 Search/Indexing detailed fixture specification is current WP65.

## Market-expansion blockers — ADR-0183…ADR-0188

RDR, SRT, DMY, LNK, DBM, PDO and MIR all remain **0/176**. The daily market Git workflow remains planned/documented but not installed.

## Access / Admin / Media / Code blockers — ADR-0189…ADR-0194

| Surface/expansion | Evidence still required |
|---|---|
| Membership competitive parity | MBR 0/160 + MB-F 0/176 + PC-F 0/176 + **MPR 0/176** |
| Role & Capability competitive parity | RA 0/176 + **RPR 0/176** |
| Surface 49 Admin Theme/Branding | **ATM 0/176** + UI/ASR/VER/MLC/MSI/compatibility evidence |
| Surface 28 Media Performance expansion | WM 0/176 + **MDP 0/176** + ASR/CAC/PDL/compatibility/performance evidence |
| Surface 50 Safe Script/Tag | **STM 0/176** + CLG/ASR/PDL/ERR/VER/MLC/RLT/CAC/Protector/consent/CSP evidence |

No existing runtime certification is promoted by these supplemental protocols.

## Per-surface readiness — 50 current surfaces

| # | Surface | Product maturity | Major blockers | Authorized |
|---:|---|---|---|---|
| 1–14 | Core content/data/admin/profile surfaces | Exhaustive | applicable established shared protocols | No |
| 15 | Membership | **Exhaustive + parity expansion** | MBR/MB-F/PC-F/**MPR** + Policy/RA/PDL/CAC/RLT/MSI/LC | No |
| 16–27 | Builders/automation/integration/operations | Exhaustive | applicable established shared/provider protocols | No |
| 28 | Media Rules + Performance/Responsive Delivery | **Exhaustive + parity expansion** | WM/**MDP**/ASR/CAC/PDL/JS/BK/MSI + WP/Core/browser compatibility | No |
| 29 | XML-RPC | Exhaustive | XR/PR/RLT/CAC/KPA/VER/MSI | No |
| 30 | Role & Capability | **Exhaustive + parity expansion** | RA/**RPR**/KPA/CAC/ERR/VER/MLC/MSI | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | PLT/FP/OA/TU/RS/VT/KPA/UI/BT/CI/MSI | No |
| 32 | F01 Solution Blueprint | Exhaustive | SBP 0/176 | No |
| 33 | F02 Analytics/Journey | Exhaustive | ANL 0/176 | No |
| 34 | F03 Search/Indexing | Exhaustive | SRH 0/176; WP65 current | No |
| 35 | F04 Decision/Formula | Exhaustive | DEC 0/176 | No |
| 36 | F05 Ledger | Exhaustive | LED 0/176 | No |
| 37 | F06 Reservation | Exhaustive | RSV 0/176 | No |
| 38 | F07 Placement/Personalization | Exhaustive | PLC 0/176 | No |
| 39 | F08 Experiments/Rollout | Exhaustive | EXP 0/176 | No |
| 40 | F09 Documents/Records | Exhaustive | DOC 0/176 | No |
| 41 | F10 Sync/ETL | Exhaustive | SYN 0/176 | No |
| 42 | F11 Geo/Territory | Exhaustive | GEO 0/176 | No |
| 43 | F12 AI Gateway/Copilot | Exhaustive | AIP 0/176 | No |
| 44 | URL Redirection & Routing | Exhaustive | RDR 0/176 | No |
| 45 | Search/Replace & Transformation | Exhaustive | SRT 0/176 | No |
| 46 | Dummy/Synthetic Data & Fixture Studio | Exhaustive | DMY 0/176 | No |
| 47 | Link Health & Crawl Intelligence | Exhaustive | LNK 0/176 | No |
| 48 | Database Maintenance & Cleanup | Exhaustive | DBM 0/176 | No |
| 49 | Admin Theme, Branding & Experience | **Exhaustive** | ATM 0/176 | No |
| 50 | Safe Script, Tag & Code Injection | **Exhaustive** | STM 0/176; PHP/eval prohibited | No |

Detailed per-module blockers remain authoritative in their module specs, ADRs and QUALITY protocols; this matrix is the canonical readiness summary.

## Provider/certification truth

- Email transport: ET-F 0/176; 6 EE3 / 0 ET-certified.
- Membership billing: MB-F 0/176; 4 BE3 / 0 MB-certified.
- Protected files: PC-F 0/176; 0 PC1+.
- Backup providers: BPC-F 0/176; 34 targets / 0 C-certified / V3 0.
- Connection providers: ICP-F 0/176; 0 I4 / 0 I5.
- No paper/static evidence is promoted to runtime certification.

## Multisite / AI Prompt scope truth

- product-option maturity: **50/50 Exhaustive**;
- logical Multisite mapping: **50/50**;
- shared AI Prompt product mapping: **50/50**;
- runtime Multisite/AI/MCP certifications: **0**.

## Current planning work

Owner-requested WP83…WP89 audit/expansion work is DONE planning. Current active package resumes:

**P0-M00-WP65 — F03 Search & Indexing detailed evidence specification.**

## Development gate

No executable evidence or implementation may run until explicit scoped owner consent. Current implementation authorization: **0/50**.