# WPEssential Architecture Decision Records

Status: **Phase 0 planning / no development authorization**
Last synchronized: 2026-08-29

ADRs preserve long-lived product, architecture, security, data, compatibility and distribution decisions. Accepted ADRs are never silently changed; reversal requires a superseding ADR. Technical acceptance never grants development permission; ADR-0014 remains the hard consent gate.

## Historical authority

ADR-0001…ADR-0176 remain individually authoritative exactly as previously accepted/proposed. Their source files and Git history preserve full detail. This compact index records the current expansion sequence and major evidence milestones without rewriting historical semantics.

## Current expansion sequence

| ADR | Status | Decision |
|---|---|---|
| ADR-0177 | Accepted expanded product architecture | Solution Blueprint layer + 12 universal foundations + Woo domain adapter; 43-surface milestone; 160 curated systems; 40 patterns; 268,800 raw primary Blueprint combinations |
| ADR-0178 | Accepted AI architecture | shared module-wide Prompt/Requirement Compiler; F12 provider ownership; WordPress AI Client/Connectors; typed Abilities; optional official MCP Adapter; capability-gap requests |
| ADR-0179 | Accepted AI/MCP evidence | AIP-001…AIP-176; 0/176; AIC/MCP runtime certs 0 |
| ADR-0180 | Accepted universal evidence master plan | SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA each fixed 176-fixture envelope, all 0/176 |
| ADR-0181 | Accepted F01 detailed evidence | SBP-001…SBP-176; 0/176 |
| ADR-0182 | Accepted F02 detailed evidence | ANL-001…ANL-176; 0/176 |
| ADR-0183 | Accepted URL Redirection & Routing architecture | new surface 44; typed redirect/routing engine; RDR-001…RDR-176, 0/176 |
| ADR-0184 | Accepted Search/Replace & Data Transformation architecture | new surface 45; format-aware Dry Run/Plan/Run/Journal/rollback; SRT-001…SRT-176, 0/176 |
| ADR-0185 | Accepted Dummy Data & Fixture Studio architecture | new surface 46; deterministic synthetic datasets/scenarios/cleanup ownership; DMY-001…DMY-176, 0/176 |
| ADR-0186 | Accepted Link Health & Crawl Intelligence architecture | new surface 47; Safe HTTP scans/graph/issues/Fix Plans; LNK-001…LNK-176, 0/176 |
| ADR-0187 | Accepted Database Maintenance & Cleanup architecture | new surface 48; owner-aware retention/cleanup/storage health; DBM-001…DBM-176, 0/176 |
| ADR-0188 | Accepted autonomous planning + market radar architecture | S07 Product Discovery/Planning Orchestrator + S08 Market Intelligence Radar + disabled daily Git job plan; PDO/MIR 0/176; current scope 48, authorization 0/48 |

## Major historical evidence milestones

- ADR-0117 Forms — FM 0/92;
- ADR-0118 Workflow — WF 0/116;
- ADR-0119 Job/Cron — JS 0/106;
- ADR-0120 Notification — NT 0/142;
- ADR-0121 Chat — CH 0/142;
- ADR-0122 Connections/Event Inbox — WC 0/156;
- ADR-0123 Compatibility — CF 0/112;
- ADR-0124 Vault — VT 0/128;
- ADR-0125 UI — UI 0/104;
- ADR-0126 Build — BT 0/112;
- ADR-0127 CI — CI 0/120;
- ADR-0128 Free↔Pro — FP 0/144;
- ADR-0129 Membership core — MBR 0/160;
- ADR-0130 Backup core — BK 0/180;
- ADR-0131 Query — QRY 0/168;
- ADR-0132 Definition — DEF 0/144;
- ADR-0133 Relations — REL 0/160;
- ADR-0134 Field Storage — FST 0/176;
- ADR-0135 Custom Tables — CTB 0/184;
- ADR-0136 Admin Columns — AC 0/176;
- ADR-0137 Dynamic Listings — DL 0/176;
- ADR-0138 CPT/Taxonomy — CPTX 0/176;
- ADR-0139 Emails renderer — EBR 0/176;
- ADR-0140 Platform surfaces — PLT 0/176;
- ADR-0141 Multisite/Lifecycle — MSI 0/160; LC 0/96;
- ADR-0142 Audit — AUD 0/176;
- ADR-0143 Kernel/Policy/Abilities/Events/SDK — KPA 0/176;
- ADR-0144 Privacy — PDL 0/176;
- ADR-0145 Errors — ERR 0/176;
- ADR-0146 Component Blueprint — CBP 0/176;
- ADR-0147 Versioning — VER 0/176;
- ADR-0148 Module Lifecycle — MLC 0/176;
- ADR-0149 DSR — DSR 0/176;
- ADR-0150 Assets — ASR 0/176;
- ADR-0151 Conditional Logic — CLG 0/176;
- ADR-0152 DVR — DVR 0/176;
- ADR-0153 Rate Limit — RLT 0/176;
- ADR-0154 Cache — CAC 0/176;
- ADR-0155 REST — REST 0/176;
- ADR-0156 Import/Export — IM 0/176;
- ADR-0157 Roles — RA 0/176;
- ADR-0158 User Profile — UP 0/176;
- ADR-0159 Protector — PR 0/176;
- ADR-0160 XML-RPC — XR 0/176;
- ADR-0161 Reset — RM 0/176;
- ADR-0162 Settings — ST 0/176;
- ADR-0163 Frontend Dashboard — FD 0/176;
- ADR-0164 Admin Menu — AM 0/176;
- ADR-0165 Dashboard Widgets — DW 0/176;
- ADR-0166 Status — SM 0/176;
- ADR-0167 Builder adapters — BW 0/176 + BC0…BC4 certs 0;
- ADR-0168 Watermarker/Media — WM 0/176;
- ADR-0169 TUF — TU 0/176;
- ADR-0170 OAuth Account-Link — OA 0/176;
- ADR-0171 Remote Privacy — RS 0/176;
- ADR-0172 Email Transport — ET-F 0/176; 6 EE3 / 0 ET-certified;
- ADR-0173 Membership Billing — MB-F 0/176; 4 BE3 / 0 MB-certified;
- ADR-0174 Protected Files — PC-F 0/176; 0 PC1+;
- ADR-0175 Backup Providers — BPC-F 0/176; 34 targets / 0 C-certified / V3 0;
- ADR-0176 Connection Providers — ICP-F 0/176; 0 I4 / 0 I5.

Earlier ADR-0001…ADR-0116 architecture/security/compatibility decisions remain authoritative in their individual files.

## Current product milestone

- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`: **48/48 Exhaustive, 0/48 Authorized**.
- logical Multisite mapping: **48/48** across original, universal-foundation and market-expansion matrices.
- module-wide AI Prompt product mapping: **48/48** across base + market addendum.
- `docs/SOLUTIONS/UNIVERSAL-SYSTEM-CATALOG.md`: 160 curated systems.
- `REFERENCE-FLOW-AND-OPTION-PATTERNS.md`: 40 reusable patterns.
- `100K-SYSTEM-SPACE.md`: 268,800 raw primary Blueprint combinations before validation/secondary dimensions.

## Current evidence truth

Universal/adapter evidence, all unexecuted:
- SBP 0/176; ANL 0/176; SRH 0/176; DEC 0/176; LED 0/176; RSV 0/176; PLC 0/176; EXP 0/176; DOC 0/176; SYN 0/176; GEO 0/176; AIP 0/176; WCA 0/176.

Market expansion, all unexecuted:
- RDR 0/176;
- SRT 0/176;
- DMY 0/176;
- LNK 0/176;
- DBM 0/176;
- PDO 0/176;
- MIR 0/176.

No paper/static evidence has been promoted to runtime certification.

## S07/S08 planning services

- S07 Product Discovery & Pre-Development Planning Orchestrator converts minimal owner requests such as `ABC system add karna hai` into repo audit → research → capability map → exhaustive Draft planning → evidence/ADR/governance artifacts. It does not start implementation.
- S08 Market Intelligence Radar discovers/updates public ecosystem signals, dedupes against WPE and hands high-value candidates to S07.
- Exact daily GitHub Actions design is documented but **no executable scheduled workflow is installed** before development consent.

## Market-driven reuse decisions

Instead of unnecessary new modules:
- Query Monitor-like diagnostics → Platform Diagnostics/Audit;
- Health Check/Troubleshooting → Platform Diagnostics shared service;
- User Switching → controlled Support Impersonation;
- WP Crontrol → Cron/JobService enhancement;
- Simple History → Audit/Observability presentation;
- media replacement/regeneration → Watermarker/Media enhancement;
- generic Code Snippets arbitrary execution → rejected by ADR-0004.

## Current planning state

Universal packages WP60…WP64 are DONE. WP65 F03 Search & Indexing remains the current planned work.

Owner-requested market interrupt packages WP75…WP82 are DONE. WP66…WP74 retain their reserved F04→Woo-adapter scopes and are not reused.

Current lifecycle remains `SPECIFICATION`.

No implementation/evidence execution can begin without explicit scoped owner consent under ADR-0014. Current authorization: **0/48**.
