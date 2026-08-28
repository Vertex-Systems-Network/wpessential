# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: **2026-08-29**

## Global rule

A surface may be `Exhaustive` and have accepted architecture/evidence design while remaining technically unverified and unauthorized. Implementation requires applicable runtime evidence, compatibility, security, privacy, recovery, performance, build/CI/provider gates and **explicit owner consent under ADR-0014**.

Current owner consent: **NOT GRANTED**.  
Current canonical scope after ADR-0197: **56 surfaces**.  
Authorized: **0/56**.  
Implemented/runtime verified: **none**.

Historical 31-, 43-, 48-, 50- and 55-surface snapshots remain historical truth only.

## Current planning truth

- Product-option maturity: **56/56 Exhaustive**.
- Logical Multisite mapping: **56/56**.
- Shared AI Prompt product mapping: **56/56**.
- Production implementation WIP: **0**.
- Current lifecycle: **SPECIFICATION**.
- Current work: **WP112 — P0 Final Pre-development Closure & Readiness Reconciliation Audit**.
- Latest accepted planning/evidence decision: **ADR-0206**.

## Established shared blockers

| Area | Evidence / certification still required |
|---|---|
| Compatibility | CF 0/112 plus module/provider/version compatibility evidence |
| Multisite / Site lifecycle | MSI 0/160; LC 0/96; runtime certs 0 |
| UI / Build / CI | UI 0/104; BT 0/112; CI 0/120 |
| Job/Cron/Async | JS 0/106 plus Action Scheduler/provider async evidence where applicable |
| Definition / Fields / Relations / Query / Tables | DEF 0/144; FST 0/176; REL 0/160; QRY 0/168; CTB 0/184 |
| Vault / Free↔Pro / OAuth / TUF | VT 0/128; FP 0/144; OA 0/176; TU 0/176 |
| Workflow / Notification / Chat / Connections | WF 0/116; NT 0/142; CH 0/142; WC 0/156; ICP-F 0/176 |
| Audit / Kernel / Policy / Abilities / SDK | AUD 0/176; KPA 0/176 plus applicable module-specific access evidence |
| Privacy / Error / Version / Lifecycle | PDL 0/176; RS 0/176; ERR 0/176; VER 0/176; MLC 0/176 |
| DSR / Assets / Conditions / DVR / Rate / Cache | DSR/ASR/CLG/DVR/RLT/CAC all 0/176 |
| AI Prompt / MCP | AIP 0/176 execution; AIC/MCP runtime certs 0 |
| Provider certification | Email/membership/files/backup/connection and other provider-specific certification remains 0 where not explicitly executed |
| Owner consent | ADR-0014 blocks every executable activity until explicit scoped consent |

## Universal-foundation / adapter evidence state

These protocols are now **detailed/documented**, but **not executed**:

| Foundation / adapter | Planning evidence | Executed | Runtime certification |
|---|---:|---:|---:|
| F01 Solution Blueprint | SBP 176/176 documented | 0/176 | 0 |
| F02 Analytics/Journey | ANL 176/176 documented | 0/176 | 0 |
| F03 Search/Indexing | SRH 176/176 documented | 0/176 | 0 |
| F04 Decision/Formula | DEC 176/176 documented | 0/176 | 0 |
| F05 Ledger | LED 176/176 documented | 0/176 | 0 |
| F06 Reservation | RSV 176/176 documented | 0/176 | 0 |
| F07 Placement/Personalization | PLC 176/176 documented | 0/176 | 0 |
| F08 Experiments/Rollout | EXP 176/176 documented | 0/176 | 0 |
| F09 Documents/Records | DOC 176/176 documented | 0/176 | 0 |
| F10 Sync/ETL | SYN 176/176 documented | 0/176 | 0 |
| F11 Geo/Territory | GEO 176/176 documented | 0/176 | 0 |
| F12 AI Gateway/Copilot | AIP dedicated evidence protocol exists | 0/176 | 0 |
| A01 WooCommerce Adapter | WCA 176/176 documented | 0/176 | 0 |

`documented` does not mean runtime-certified.

## Market-expansion blockers — ADR-0183…ADR-0188

RDR, SRT, DMY, LNK, DBM, PDO and MIR remain planning-only evidence envelopes with **0 executed**. The market-intelligence scheduled GitHub workflow remains documented but **NOT INSTALLED**.

## Competitive-expansion blockers — ADR-0189…ADR-0197

### First/earlier parity layers

- Membership competitive parity: MBR 0/160 + MB-F 0/176 + PC-F 0/176 + MPR 0/176.
- Role & Capability competitive parity: RA 0/176 + RPR 0/176.
- Surface 49 Admin Theme/Branding: ATM 0/176 plus UI/ASR/VER/MLC/MSI/compatibility evidence.
- Surface 28 Media Performance expansion: WM 0/176 + MDP 0/176 + ASR/CAC/PDL/JS/BK/MSI/browser compatibility.
- Surface 50 Safe Script/Tag: STM 0/176 + CLG/ASR/PDL/ERR/VER/MLC/RLT/CAC/Protector/consent/CSP evidence; PHP/eval remains prohibited.

### ADR-0195 surfaces 51–55

- **51 Content Order & Sequence** — ORD 0/176 plus applicable query/cache/concurrency/Multisite evidence.
- **52 Security Integrity/Malware/Vulnerability** — SEC 0/176 plus Protector/security-provider/version/update/evidence isolation requirements.
- **53 Font Library/Typography/Delivery** — FNT 0/176 plus UAF 0/176, asset/cache/privacy/license provenance and delivery compatibility.
- **54 User Data Stores/Favorites/Collections** — UDS 0/176 plus identity/Policy/privacy/cache/Multisite evidence.
- **55 Staging/Clone/Migration** — STG 0/176 plus MIG 0/176, backup/search-replace/provider quarantine/identity/reconciliation evidence.

Supplemental second-audit namespaces BKX, MRL, PBX, JEX, LHX and HFC remain unexecuted.

### ADR-0197 surface 56 + third-audit supplements

- **56 Theme Workspace, Child Theme & Theme Customization Manager** — THM 0/176 plus theme/WP compatibility, filesystem/VCS, asset/build, Multisite and security evidence; arbitrary PHP live execution remains prohibited.
- UAF/MIG/WLB/DUP/ALX/MBX/RSX/RDX/CPTX all remain 0/176.

No existing runtime certification is promoted by these planning protocols.

## Per-surface readiness — 56 current surfaces

| # | Surface | Product maturity | Major blockers | Authorized |
|---:|---|---|---|---|
| 1–14 | Core content/data/admin/profile surfaces | Exhaustive | applicable established shared protocols/provider/runtime evidence | No |
| 15 | Membership | Exhaustive + parity expansion | MBR/MB-F/PC-F/MPR + Policy/RA/PDL/CAC/RLT/MSI/LC | No |
| 16–27 | Builders/automation/integration/operations | Exhaustive | applicable shared/provider/runtime protocols | No |
| 28 | Media Rules + Performance/Responsive Delivery | Exhaustive + parity expansion | WM/MDP/ASR/CAC/PDL/JS/BK/MSI + WP/Core/browser compatibility | No |
| 29 | XML-RPC | Exhaustive | XR/PR/RLT/CAC/KPA/VER/MSI | No |
| 30 | Role & Capability | Exhaustive + parity expansion | RA/RPR/KPA/CAC/ERR/VER/MLC/MSI | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | PLT/FP/OA/TU/RS/VT/KPA/UI/BT/CI/MSI | No |
| 32 | F01 Solution Blueprint | Exhaustive | SBP documented 176; 0 executed | No |
| 33 | F02 Analytics/Journey | Exhaustive | ANL documented 176; 0 executed | No |
| 34 | F03 Search/Indexing | Exhaustive | SRH documented 176; 0 executed | No |
| 35 | F04 Decision/Formula | Exhaustive | DEC documented 176; 0 executed | No |
| 36 | F05 Ledger | Exhaustive | LED documented 176; 0 executed | No |
| 37 | F06 Reservation | Exhaustive | RSV documented 176; 0 executed | No |
| 38 | F07 Placement/Personalization | Exhaustive | PLC documented 176; 0 executed | No |
| 39 | F08 Experiments/Rollout | Exhaustive | EXP documented 176; 0 executed | No |
| 40 | F09 Documents/Records | Exhaustive | DOC documented 176; 0 executed | No |
| 41 | F10 Sync/ETL | Exhaustive | SYN documented 176; 0 executed | No |
| 42 | F11 Geo/Territory | Exhaustive | GEO documented 176; 0 executed | No |
| 43 | F12 AI Gateway/Copilot | Exhaustive | AIP execution 0/176; AIC/MCP runtime certs 0 | No |
| 44 | URL Redirection & Routing | Exhaustive | RDR 0/176 | No |
| 45 | Search/Replace & Transformation | Exhaustive | SRT 0/176 | No |
| 46 | Dummy/Synthetic Data & Fixture Studio | Exhaustive | DMY 0/176 | No |
| 47 | Link Health & Crawl Intelligence | Exhaustive | LNK/LHX 0/176 | No |
| 48 | Database Maintenance & Cleanup | Exhaustive | DBM 0/176 | No |
| 49 | Admin Theme, Branding & Experience | Exhaustive | ATM/WLB 0/176 + shared UI/security evidence | No |
| 50 | Safe Script, Tag & Code Injection | Exhaustive | STM/HFC 0/176; PHP/eval prohibited | No |
| 51 | Content Order & Sequence | Exhaustive | ORD/DUP 0/176 + query/concurrency evidence | No |
| 52 | Security Integrity/Malware/Vulnerability | Exhaustive | SEC 0/176 + security/provider/update evidence | No |
| 53 | Font Library/Typography/Delivery | Exhaustive | FNT/UAF 0/176 + asset/license/delivery evidence | No |
| 54 | User Data Stores/Favorites/Collections | Exhaustive | UDS 0/176 + identity/Policy/privacy evidence | No |
| 55 | Staging/Clone/Migration | Exhaustive | STG/MIG 0/176 + backup/provider quarantine/reconciliation | No |
| 56 | Theme Workspace/Child Theme/Customization | Exhaustive | THM 0/176 + filesystem/VCS/build/security/compatibility | No |

WooCommerce Commerce Domain Adapter is a cross-domain adapter, not an additional numbered product surface. Its WCA evidence is documented 176/176 and executed 0/176.

## Provider/certification truth

- Email transport: ET-F 0/176; no ET-certified provider.
- Membership billing: MB-F 0/176; no MB-certified provider.
- Protected files: PC-F 0/176; no certified protected-file profile recorded.
- Backup providers: BPC-F/BKX unexecuted; no certified recovery target/profile is promoted.
- Connection providers: ICP-F 0/176; no certified high-capability provider profile promoted.
- Commerce/payment/tax/shipping/inventory integrations remain adapter/provider-specific and unexecuted.
- No paper/static evidence is promoted to runtime certification.

## Stop-the-line readiness conditions

The project must not move to `AWAITING_DEVELOPMENT_APPROVAL` merely because product specs are exhaustive. WP112 must first confirm:

- no stale current-scope/current-work governance summary remains;
- every required planning/evidence envelope has an explicit owner, namespace and lifecycle;
- critical security/Policy/privacy/Multisite/recovery/provider compatibility requirements are planned without contradictory ownership;
- AI/MCP has no hidden mutation/authorization path;
- rollback/recovery/provider-unknown outcomes are explicitly modeled;
- no duplicate engines or competing sources of truth are planned;
- implementation sequencing and development approval package can be presented without new architectural planning during development.

## Current planning work

**P0-M00-WP112 — P0 Final Pre-development Closure & Readiness Reconciliation Audit.**

## Development gate

No executable evidence or implementation may run until explicit scoped owner consent. Current implementation authorization: **0/56**.