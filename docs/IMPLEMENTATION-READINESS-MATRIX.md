# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**
Last synchronized: 2026-08-29

## Global rule

A surface may be Exhaustive and have Accepted architecture while remaining technically unverified and unauthorized. Implementation requires applicable evidence, compatibility, security/quality/recovery gates and **explicit owner consent under ADR-0014**.

Current owner consent: **NOT GRANTED**.
Current canonical scope after ADR-0188: **48 surfaces**.
Authorized: **0/48**.
Implemented/runtime verified: **none**.

Historical 31- and 43-surface snapshots remain historical truth for their earlier scope.

## Established shared blockers

| Area | Evidence / certification still required |
|---|---|
| WP/PHP/DB compatibility | CF 0/112 |
| Multisite isolation | MSI 0/160; MS0–MS4 runtime certs 0 |
| Site lifecycle | LC 0/96; SL0–SL4 runtime certs 0 |
| UI/design system | UI 0/104 |
| Job/Cron | JS 0/106 |
| Definition Repository | DEF 0/144; final physical profile evidence-gated |
| Vault | VT 0/128 |
| Free↔Pro | FP 0/144 |
| OAuth Account Link | OA 0/176 |
| TUF updater | TU 0/176 |
| CI | CI 0/120 |
| Build | BT 0/112 |
| Query | QRY 0/168; QP1–QP4 certifications separate |
| Relations | REL 0/160 |
| Workflow | WF 0/116 |
| Field Storage | FST 0/176 |
| Custom Tables | CTB 0/184 |
| Notification | NT 0/142 |
| Chat | CH 0/142 |
| Webhooks/Connections | WC 0/156 + ICP-F 0/176; I4/I5 certs 0 |
| Audit/Observability | AUD 0/176 |
| Kernel/Policy/Abilities/Events/SDK | KPA 0/176 |
| Local/Remote privacy | PDL 0/176 + RS 0/176 |
| Error taxonomy | ERR 0/176 |
| Component Blueprint | CBP 0/176 |
| Versioning | VER 0/176 |
| Module lifecycle | MLC 0/176 |
| Entity/Data Source Registry | DSR 0/176 |
| Asset Registry | ASR 0/176 |
| Conditional Logic | CLG 0/176 |
| Dynamic Value Resolver | DVR 0/176 |
| Shared Rate Limit | RLT 0/176 |
| Shared Cache | CAC 0/176 |
| AI Prompt/MCP | AIP 0/176; AIC/MCP runtime certs 0 |
| Owner consent | ADR-0014 blocks every executable activity |

## Universal-foundation / adapter blockers

| Foundation/adapter | Evidence |
|---|---|
| F01 Solution Blueprint | SBP 0/176; ADR-0181 detailed protocol |
| F02 Analytics/Event/Journey | ANL 0/176; ADR-0182 detailed protocol |
| F03 Search/Indexing | SRH 0/176; detailed fixture specification current WP65 |
| F04 Decision/Formula | DEC 0/176 |
| F05 Ledger | LED 0/176 |
| F06 Reservation | RSV 0/176 |
| F07 Placement/Personalization | PLC 0/176 |
| F08 Experiments | EXP 0/176 |
| F09 Documents/Records | DOC 0/176 |
| F10 Sync/ETL | SYN 0/176 |
| F11 Geo/Territory | GEO 0/176 |
| F12 AI Gateway | AIP 0/176 + provider/model/knowledge/eval evidence |
| A01 WooCommerce Domain Adapter | WCA 0/176; Woo/HPOS/Blocks exact profiles evidence-gated |

## Market-expansion blockers — ADR-0183…0188

| Surface/service | Evidence / certification still required |
|---|---|
| URL Redirection & Routing | RDR 0/176 |
| Search, Replace & Data Transformation | SRT 0/176 |
| Dummy Data & Fixture Studio | DMY 0/176 |
| Link Health & Crawl Intelligence | LNK 0/176 |
| Database Maintenance & Cleanup | DBM 0/176 |
| S07 Product Discovery/Planning Orchestrator | PDO 0/176 |
| S08 Market Intelligence Radar | MIR 0/176; daily Git workflow not installed |

## Per-surface readiness — 48 current module/platform surfaces

| # | Surface | Product maturity | Primary technical blockers | Authorized |
|---:|---|---|---|---|
| 1 | CPT Builder | Exhaustive | CPTX/CF/DEF/KPA/UI/BT/CI/VER/MLC/DSR/CAC/MSI/LC/AIP | No |
| 2 | Taxonomy Builder | Exhaustive | CPTX/CF/DEF/KPA/UI/BT/CI/VER/MLC/DSR/CAC/MSI/LC/AIP | No |
| 3 | Custom Fields | Exhaustive | FST/DSR/CLG/DVR/CAC/KPA/PDL/ERR/VER/MLC/MSI/AIP | No |
| 4 | Relations | Exhaustive | REL/DSR/CLG/DVR/CAC/KPA/PDL/ERR/VER/MSI/AIP | No |
| 5 | Status Manager | Exhaustive | SM/DSR/CLG/DVR/CAC/KPA/ERR/VER/MLC/WF/JS/MSI/LC/AIP | No |
| 6 | Query Builder | Exhaustive | QRY/DSR/CLG/DVR/CAC/QP/KPA/PDL/ERR/VER/MSI/AIP | No |
| 7 | Custom Tables | Exhaustive | CTB/DSR/PDL/ERR/VER/MLC/MSI/LC/AIP | No |
| 8 | Admin Columns | Exhaustive | AC/DSR/CLG/DVR/CAC/KPA/PDL/ERR/MSI/AIP | No |
| 9 | Dynamic Listings | Exhaustive | DL/CBP/DSR/CLG/DVR/ASR/CAC/BW/MSI/AIP | No |
| 10 | Dashboard Widgets | Exhaustive | DW/KPA/CBP/DSR/QRY/ASR/CAC/UI/BT/CI/MSI/AIP | No |
| 11 | Admin Menu | Exhaustive | AM/KPA/RA/CAC/ASR/ERR/VER/MLC/UI/BT/CI/MSI/AIP | No |
| 12 | Settings Pages | Exhaustive | ST/VT/DSR/CLG/DVR/CAC/KPA/PDL/ERR/VER/UI/BT/CI/MSI/AIP | No |
| 13 | Frontend Dashboard | Exhaustive | FD/CBP/DSR/QRY/ASR/CAC/KPA/RA/UP/MBR/UI/BT/CI/MSI/AIP | No |
| 14 | User Profile | Exhaustive | UP/FST/DSR/KPA/RA/PDL/ERR/CAC/VER/MLC/UI/BT/CI/MSI/AIP | No |
| 15 | Membership | Exhaustive | MBR/MB-F/PC-F/KPA/PDL/ERR/VER/MLC/CLG/DVR/CAC/RLT/RA/MSI/LC/BK/AIP | No |
| 16 | Builder Widgets | Exhaustive | CBP/BW/BC/DSR/DVR/ASR/CAC/BT/CI/MSI/AIP | No |
| 17 | Forms & Workflow | Exhaustive | FM/WF/JS/DSR/CLG/DVR/RLT/CAC/KPA/PDL/ERR/VER/MLC/MSI/LC/AIP | No |
| 18 | Cron/Jobs | Exhaustive | JS/KPA/ERR/VER/MLC/MSI/LC/AIP + native WP-Cron inspector evidence | No |
| 19 | Notifications | Exhaustive | NT/JS/WF/DSR/DVR/CAC/KPA/PDL/ERR/provider/MSI/AIP | No |
| 20 | Emails | Exhaustive | EBR/ET-F/VT/DSR/DVR/ASR/CAC/KPA/PDL/ERR/MSI/AIP | No |
| 21 | Chat | Exhaustive | CH/DSR/DVR/CAC/MBR/private-assets/search/realtime/KPA/PDL/ERR/MSI/AIP | No |
| 22 | REST Builder | Exhaustive | REST/QRY/DSR/DVR/RLT/CAC/KPA/PDL/ERR/VER/MSI/AIP | No |
| 23 | Webhooks/Connections | Exhaustive | WC/ICP-F/VT/DSR/DVR/RLT/CAC/KPA/PDL/ERR/VER/MSI/AIP | No |
| 24 | Backup | Exhaustive | BK/BPC-F/C0–C4/V3/VT/JS/KPA/PDL/ERR/VER/MLC/MSI/LC/AIP | No |
| 25 | Reset | Exhaustive | RM/BK/RA/UP/JS/DSR/FST/REL/CTB/ERR/PDL/VER/MLC/MSI/LC/AIP | No |
| 26 | Import/Export | Exhaustive | IM/DEF/FST/REL/CTB/DSR/VER/CAC/PDL/ERR/KPA/MSI/LC/AIP | No |
| 27 | Protector | Exhaustive | PR/RLT/CAC/KPA/ERR/VER/MLC/REST/XR/WC/MSI/AIP | No |
| 28 | Watermarker/Media | Exhaustive | WM/DSR/DVR/ASR/CAC/JS/KPA/PDL/ERR/MLC/BK/MSI/AIP + source-replace/regenerate evidence | No |
| 29 | XML-RPC | Exhaustive | XR/PR/RLT/CAC/KPA/ERR/VER/MLC/SafeHTTP/MSI/AIP | No |
| 30 | Role & Capability | Exhaustive | RA/KPA/CAC/ERR/VER/MLC/MBR/MSI/AIP + support-impersonation evidence | No |
| 31 | Platform Account/Docs/Support/Diagnostics | Exhaustive | PLT/FP/OA/TU/RS/VT/KPA/PDL/ERR/VER/MLC/ASR/DVR/CAC/UI/BT/CI/MSI/AIP + diagnostics/troubleshooting evidence | No |
| 32 | F01 Solution Blueprint | Exhaustive | SBP 0/176 + referenced component readiness | No |
| 33 | F02 Analytics/Journey | Exhaustive | ANL 0/176 + event-store/backend certification | No |
| 34 | F03 Search/Indexing | Exhaustive | SRH 0/176 + backend/index/security/relevance/scale; WP65 current | No |
| 35 | F04 Decision/Formula | Exhaustive | DEC 0/176 + deterministic compiler/decimal/unit evidence | No |
| 36 | F05 Ledger | Exhaustive | LED 0/176 + transaction/lock/rebuild/reconciliation | No |
| 37 | F06 Reservation | Exhaustive | RSV 0/176 + atomic hold/capacity/calendar/DST | No |
| 38 | F07 Placement/Personalization | Exhaustive | PLC 0/176 + slot/cache/assets/frequency/privacy | No |
| 39 | F08 Experiments/Rollout | Exhaustive | EXP 0/176 + assignment/exposure/statistics | No |
| 40 | F09 Documents/Records | Exhaustive | DOC 0/176 + renderer/private delivery/records/versioning | No |
| 41 | F10 Sync/ETL | Exhaustive | SYN 0/176 + cursor/conflict/idempotency/provider/scale | No |
| 42 | F11 Geo/Territory | Exhaustive | GEO 0/176 + spatial/provider/privacy/performance | No |
| 43 | F12 AI Gateway/Copilot | Exhaustive | AIP 0/176 + provider/model/MCP/knowledge/eval/cost/security | No |
| 44 | URL Redirection & Routing | Exhaustive | RDR 0/176 + request-hook/cache/server-export/404/privacy evidence | No |
| 45 | Search/Replace & Transformation | Exhaustive | SRT 0/176 + serialization/charset/plan/journal/backup/concurrency evidence | No |
| 46 | Dummy Data & Fixture Studio | Exhaustive | DMY 0/176 + deterministic generation/cleanup/adapters/scale evidence | No |
| 47 | Link Health & Crawl Intelligence | Exhaustive | LNK 0/176 + Safe HTTP/crawl/graph/jobs/privacy/scale evidence | No |
| 48 | Database Maintenance & Cleanup | Exhaustive | DBM 0/176 + provider ownership/dry-run/backup/jobs/Multisite/destructive safety evidence | No |

## Existing provider/certification truth

- Email transport ET-F 0/176; **6 EE3 / 0 ET-certified**.
- Membership billing MB-F 0/176; **4 BE3 / 0 MB-certified**.
- Membership protected files PC-F 0/176; **0 PC1+**.
- Backup provider BPC-F 0/176; **34 targets / 0 C-certified / V3 0**.
- Connection provider ICP-F 0/176; **0 I4 / 0 I5**.
- No paper/static evidence is promoted to runtime certification.

## Multisite / AI Prompt scope truth

- product-option maturity: **48/48 Exhaustive**;
- logical Multisite mapping: **48/48**;
- shared AI Prompt product mapping: **48/48**;
- runtime Multisite/AI/MCP certifications: **0**.

## Current planning work

Current active planning package after the completed owner-requested market interrupt:

**P0-M00-WP65 — F03 Search & Indexing detailed evidence specification.**

Market packages WP75…WP82 are DONE planning artifacts under ADR-0183…0188.

## Development gate

No executable evidence or implementation may run until explicit scoped owner consent. Current implementation authorization: **0/48**.
