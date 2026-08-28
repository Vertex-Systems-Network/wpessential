# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: 2026-08-29

## Global rule

A surface can be Exhaustive and architecturally Accepted while still technically unverified and unauthorized. Implementation requires accepted semantics, executable evidence, quality/security gates, platform/toolchain compatibility and **explicit owner consent under ADR-0014**.

Current owner consent: **NOT GRANTED**.  
Current canonical scope after ADR-0177: **43 surfaces**.  
Authorized: **0/43**.  
Implementation/runtime verified: **none**.

Historical 31-surface readiness records remain valid for the pre-ADR-0177 scope.

## Shared blockers — established platform

| Area | Current paper state | Required evidence |
|---|---|---|
| WP/PHP/DB compatibility | ADR-0002/0123 | CF-01…CF-112 |
| Multisite Scope/Isolation | ADR-0069/0071/0141 | MSI-01…MSI-160; MS0–MS4 |
| Site Lifecycle | ADR-0075/0141 | LC-01…LC-96; SL0–SL4 |
| UI/design system | ADR-0005/0125 | UI-01…UI-104 |
| Job/Cron | ADR-0059/0068/0083/0119 | JS-01…JS-106 |
| Definition Repository | ADR-0073/0092/0132 | DEF-01…DEF-144; final DDL evidence-gated |
| Vault | ADR-0048/0085/0124 | VT-01…VT-128 |
| Free↔Pro | ADR-0010/0070/0072/0076/0091/0128 | FP-01…FP-144 |
| OAuth Account Link | ADR-0034/0101/0170 | OA-01…OA-176 |
| Pro updater/TUF | ADR-0044/0102/0169 | TU-01…TU-176 |
| CI | ADR-0011/0127 | CI-01…CI-120 |
| Build | ADR-0012/0126 | BT-01…BT-112 |
| Query | ADR-0086/0131 | QRY-01…QRY-168; QP1–QP4 separate |
| Relations | ADR-0074/0093/0133 | REL-01…REL-160 |
| Workflow | ADR-0082/0118 | WF-01…WF-116 |
| Membership | ADR-0013…0090/0129/0173/0174 | MBR 0/160 + MB-F 0/176 + PC-F 0/176 + MB0–MB5 + PC0–PC4 |
| Backup | ADR-0021…0100/0130/0175 | BK 0/180 + BPC-F 0/176 + C0–C4/V3 |
| Field Storage | ADR-0022/0087/0134 | FST-01…FST-176 |
| Custom Tables | ADR-0023/0088/0135 | CTB-01…CTB-184 |
| Notification | ADR-0026/0079/0120 | NT-01…NT-142 |
| Message & Chat | ADR-0027/0077/0121 | CH-01…CH-142 |
| Webhooks/Connections | ADR-0040/0055/0080/0122/0176 | WC 0/156 + ICP-F 0/176 + I0–I5 |
| Audit/Observability | ADR-0081/0142 | AUD-01…AUD-176 |
| Kernel/Policy/Abilities/Events/SDK | ADR-0143 | KPA-01…KPA-176 |
| Privacy | ADR-0144/0171 | PDL 0/176 + RS 0/176 |
| Error taxonomy | ADR-0145 | ERR-01…ERR-176 |
| Component Blueprint | ADR-0146 | CBP-01…CBP-176 |
| Versioning | ADR-0147 | VER-01…VER-176 |
| Module lifecycle | ADR-0148 | MLC-01…MLC-176 |
| Entity/Data Source Registry | ADR-0149 | DSR-01…DSR-176 |
| Asset Registry | ADR-0150 | ASR-01…ASR-176 |
| Conditional Logic | ADR-0151 | CLG-01…CLG-176 |
| Dynamic Value Resolver | ADR-0152 | DVR-01…DVR-176 |
| Rate Limit | ADR-0045/0153 | RLT-01…RLT-176 |
| Cache | ADR-0154 | CAC-01…CAC-176 |
| Email transport | ADR-0058/0063/0067/0172 | ET-F 0/176 + ET0–ET5; 6 EE3 / 0 ET-certified |
| Owner consent | ADR-0014 | blocks every executable activity |

## New shared blockers — ADR-0177+ expansion

| Area | Accepted paper state | Required evidence |
|---|---|---|
| Solution Blueprint/Application Composer | ADR-0177/0180/0181 | SBP-001…SBP-176 |
| Analytics/Event/Journey | ADR-0177/0180 | ANL-001…ANL-176; detailed fixture expansion current WP64 |
| Search & Indexing | ADR-0177/0180 | SRH-001…SRH-176 |
| Decision/Formula/Scoring | ADR-0177/0180 | DEC-001…DEC-176 |
| Ledger/Balance/Movement | ADR-0177/0180 | LED-001…LED-176 |
| Resource Scheduling/Reservation | ADR-0177/0180 | RSV-001…RSV-176 |
| Placement/Personalization | ADR-0177/0180 | PLC-001…PLC-176 |
| Experimentation/Rollout | ADR-0177/0180 | EXP-001…EXP-176 |
| Documents/Records | ADR-0177/0180 | DOC-001…DOC-176 |
| Sync/ETL | ADR-0177/0180 | SYN-001…SYN-176 |
| Geo/Territory | ADR-0177/0180 | GEO-001…GEO-176 |
| AI Gateway + Prompt/Requirement Compiler + MCP | ADR-0177/0178/0179/0180 | AIP-001…AIP-176; AIC0–AIC5 and MCP0–MCP4 certifications |
| WooCommerce Commerce Domain Adapter | ADR-0177/0180 | WCA-001…WCA-176; exact Woo/HPOS/Blocks/provider profiles evidence-gated |

## Per-surface readiness — original 31

| # | Surface | Product maturity | Accepted/paper architecture | Major remaining blockers | Authorized |
|---:|---|---|---|---|---|
| 1 | Custom Post Types Builder | Exhaustive | WP registration + ADR-0138 | CF/DEF/KPA/UI/BT/CI/CPTX/VER/MLC/DSR/CAC/MSI/LC/AIP | No |
| 2 | Taxonomy Builder | Exhaustive | WP registration + ADR-0138 | CF/DEF/KPA/UI/BT/CI/CPTX/VER/MLC/DSR/CAC/MSI/LC/AIP | No |
| 3 | Custom Fields Builder | Exhaustive | ADR-0087/0134 | FST/DSR/CLG/DVR/CAC/KPA/PDL/ERR/VER/MLC/MSI/AIP | No |
| 4 | Relations Builder | Exhaustive | ADR-0074/0093/0133 | REL/DSR/CLG/DVR/CAC/physical/KPA/PDL/ERR/VER/MSI/AIP | No |
| 5 | Status Manager | Exhaustive | ADR-0038/0110/0166 | SM/DSR/CLG/DVR/CAC/KPA/ERR/VER/MLC/WF/JS/MSI/LC/AIP | No |
| 6 | Custom Query Builder | Exhaustive | ADR-0086/0131 | QRY/DSR/CLG/DVR/CAC/QP/KPA/PDL/ERR/VER/MSI/AIP | No |
| 7 | Custom Tables Builder | Exhaustive | ADR-0023/0088/0135 | CTB/DSR/PDL/ERR/VER/MLC/MSI/LC/AIP | No |
| 8 | Admin Columns Builder | Exhaustive | ADR-0098/0136 | AC/DSR/CLG/DVR/CAC/KPA/PDL/ERR/MSI/AIP | No |
| 9 | Dynamic Listings/Templates | Exhaustive | ADR-0039/0099/0137/0146 | DL/CBP/DSR/CLG/DVR/ASR/CAC/auth/BW/SEO/MSI/AIP | No |
| 10 | Dashboard Widgets | Exhaustive | ADR-0051/0103/0165 | DW/KPA/CBP/DSR/QRY/CLG/DVR/ASR/CAC/UI/BT/CI/MSI/AIP | No |
| 11 | Admin Menu | Exhaustive | ADR-0037/0104/0164 | AM/KPA/RA/CAC/ASR/ERR/VER/MLC/UI/BT/CI/MSI/AIP | No |
| 12 | Settings Page | Exhaustive | ADR-0036/0089/0112/0162 | ST/VT/DSR/CLG/DVR/CAC/KPA/PDL/ERR/VER/UI/BT/CI/MSI/AIP | No |
| 13 | Frontend Dashboard | Exhaustive | ADR-0031/0108/0146/0163 | FD/CBP/DSR/QRY/CLG/DVR/ASR/CAC/KPA/RA/UP/MBR/UI/BT/CI/MSI/AIP | No |
| 14 | User Profile | Exhaustive | ADR-0030/0096/0113/0158 | UP/FST/DSR/KPA/RA/PDL/ERR/CAC/VER/MLC/UI/BT/CI/MSI/AIP | No |
| 15 | Membership | Exhaustive | ADR-0013…0090/0129/0173/0174 | MBR/MB-F/PC-F/KPA/PDL/ERR/VER/MLC/CLG/DVR/CAC/RLT/RA/MSI/LC/BK/AIP | No |
| 16 | Builder Widgets | Exhaustive | ADR-0035/0109/0146/0167 | CBP/BW/BC/DSR/CLG/DVR/ASR/CAC/BT/CI/MSI/AIP | No |
| 17 | Forms & Workflow | Exhaustive | FRT/WF baselines | FM/WF/JS/DSR/CLG/DVR/RLT/CAC/KPA/PDL/ERR/VER/MLC/MSI/LC/AIP | No |
| 18 | Cron/Jobs | Exhaustive | JobService | JS/KPA/ERR/VER/MLC/MSI/LC/AIP | No |
| 19 | Notification | Exhaustive | NE1/NE2 | NT/JS/WF/DSR/CLG/DVR/CAC/KPA/PDL/ERR/provider/MSI/AIP | No |
| 20 | Emails Builder | Exhaustive | Email IR + ADR-0139/0172 | EBR/ET-F/VT/DSR/CLG/DVR/ASR/CAC/KPA/PDL/ERR/MSI/AIP | No |
| 21 | Message & Chat | Exhaustive | CRT1/CRT2 | CH/DSR/CLG/DVR/CAC/MBR/private-assets/search/realtime/KPA/PDL/ERR/MSI/AIP | No |
| 22 | REST API Builder | Exhaustive | ADR-0155 | REST/QRY/DSR/CLG/DVR/RLT/CAC/KPA/PDL/ERR/VER/MSI/AIP | No |
| 23 | Webhooks & Connections | Exhaustive | ADR-0040/0055/0080/0122/0176 | WC/ICP-F/I0–I5/VT/DSR/CLG/DVR/RLT/CAC/KPA/PDL/ERR/VER/MSI/AIP | No |
| 24 | Backup | Exhaustive | ADR-0130/0175 | BK/BPC-F/C0–C4/V3/VT/JS/KPA/PDL/ERR/VER/MLC/MSI/LC/AIP | No |
| 25 | Reset | Exhaustive | ADR-0047/0106/0161 | RM/BK/RA/UP/JS/DSR/FST/REL/CTB/ERR/PDL/VER/MLC/MSI/LC/AIP | No |
| 26 | Import/Export | Exhaustive | ADR-0156 | IM/DEF/FST/REL/CTB/DSR/VER/CAC/PDL/ERR/KPA/MSI/LC/AIP | No |
| 27 | Protector | Exhaustive | ADR-0045/0105/0159 | PR/RLT/CAC/KPA/ERR/VER/MLC/REST/XR/WC/MSI/AIP | No |
| 28 | Watermarker/Media | Exhaustive | ADR-0046/0107/0168 | WM/DSR/DVR/ASR/CAC/JS/KPA/PDL/ERR/MLC/BK/MSI/AIP | No |
| 29 | XML-RPC | Exhaustive | ADR-0052/0111/0160 | XR/PR/RLT/CAC/KPA/ERR/VER/MLC/SafeHTTP/MSI/AIP | No |
| 30 | Role & Capability | Exhaustive | ADR-0157 | RA/KPA/CAC/ERR/VER/MLC/MBR/MSI/AIP | No |
| 31 | Account/Docs/Support/Diagnostics | Exhaustive | ADR-0140/0171 | PLT/FP/OA/TU/RS/VT/KPA/PDL/ERR/VER/MLC/ASR/DVR/CAC/UI/BT/CI/MSI/AIP | No |

## Per-surface readiness — universal foundations 32–43

| # | Surface | Product maturity | Accepted architecture | Major remaining blockers | Authorized |
|---:|---|---|---|---|---|
| 32 | F01 Solution Blueprint & Application Composer | Exhaustive | ADR-0177/0180/0181 | SBP 0/176 + all referenced component readiness + package/migration/security/Multisite evidence | No |
| 33 | F02 Analytics, Event Tracking & Journey Intelligence | Exhaustive | ADR-0177/0180 | ANL 0/176 + physical event store/identity/privacy/materialization/scale; detailed fixture spec current | No |
| 34 | F03 Search & Indexing | Exhaustive | ADR-0177/0180 | SRH 0/176 + backend/index security/invalidation/relevance/performance certification | No |
| 35 | F04 Decision, Formula, Scoring & Ranking | Exhaustive | ADR-0177/0180 | DEC 0/176 + typed compiler/decimal/unit/determinism/performance evidence | No |
| 36 | F05 Ledger, Balance & Movement | Exhaustive | ADR-0177/0180 | LED 0/176 + physical transaction/lock/idempotency/rebuild/reconciliation evidence | No |
| 37 | F06 Resource Scheduling/Reservation | Exhaustive | ADR-0177/0180 | RSV 0/176 + calendar/DST/atomic hold/capacity/concurrency evidence | No |
| 38 | F07 Experience Placement/Personalization | Exhaustive | ADR-0177/0180 | PLC 0/176 + slot adapters/assets/cache/frequency/privacy evidence | No |
| 39 | F08 Experimentation/Feature Rollout | Exhaustive | ADR-0177/0180 | EXP 0/176 + assignment/exposure/statistics/cache/privacy evidence | No |
| 40 | F09 Documents/Records/Templates | Exhaustive | ADR-0177/0180 | DOC 0/176 + renderer/fonts/assets/private-delivery/records/version evidence | No |
| 41 | F10 Data Sync/ETL | Exhaustive | ADR-0177/0180 | SYN 0/176 + cursor/checkpoint/conflict/idempotency/provider/scale evidence | No |
| 42 | F11 Geospatial/Location/Territory | Exhaustive | ADR-0177/0180 | GEO 0/176 + spatial backend/provider/privacy/query/performance evidence | No |
| 43 | F12 AI Gateway/Knowledge/Copilot | Exhaustive | ADR-0177/0178/0179/0180 | AIP 0/176 + AI Client/provider/model/MCP/knowledge/retrieval/eval/security evidence | No |

## Domain adapter readiness

### A01 WooCommerce Commerce Domain Adapter

Product behavior: Exhaustive.  
Evidence: **WCA 0/176**.  
Still requires exact supported WooCommerce/HPOS/Cart & Checkout Blocks/classic/provider/version profiles, Data Source/Ability mappings, order/cart concurrency, stock/discount/shipping/payment truth boundaries and performance evidence.

A01 is an adapter, not an additional module denominator row.

## AI Prompt readiness across modules

ADR-0178 maps AI Prompt product behavior across **43/43** surfaces. This does not make any surface AI-runtime ready.

Shared blockers include WordPress Abilities runtime/version compatibility, WordPress AI Client/Connectors, structured Requirement/Plan IR, context/PII/secrets Policy, Prompt Session lifecycle, capability-gap workflow, approval/fingerprint/staleness, MCP exposure/auth/session/cache, prompt-injection/tool-use controls and provider/model evaluation/regression.

Evidence: **AIP 0/176**; AIC runtime certifications 0; MCP runtime certifications 0.

## Current expanded evidence counters

SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA are each **0/176**. No expanded runtime certification exists.

## Current planning state

- ADR-0177: expanded Solution/universal-foundation architecture accepted.
- ADR-0178/0179: AI Prompt/Requirement Compiler/MCP architecture + evidence accepted.
- ADR-0180: universal-foundation/Woo adapter evidence master plan accepted.
- ADR-0181: F01 SBP detailed fixtures accepted.
- Current work: `P0-M00-WP64` — F02 Analytics/Event/Journey detailed evidence specification.

Current lifecycle is **SPECIFICATION**, not global `AWAITING_DEVELOPMENT_APPROVAL`, because the owner explicitly requested additional pre-planning and that sequence is active.

Production development authorization remains **NOT GRANTED / 0/43**.