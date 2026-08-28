# WPEssential Architecture Decision Records

Status: **Phase 0 planning / no development authorization**  
Last synchronized: 2026-08-29

ADRs preserve long-lived product, architecture, security, data, compatibility and distribution decisions. Accepted ADRs are never silently changed; a reversal requires a superseding ADR. Exact implementation profiles can remain evidence-gated even when architecture is Accepted.

**Hard rule:** technical acceptance never grants development permission. `/DEVELOPMENT-CONSENT.md` and ADR-0014 require explicit owner consent before source/build/migration/test/benchmark/provider/AI/MCP implementation.

> ADR-0069 previously appeared twice as a summary row in this index; that duplicate summary was normalized without changing the ADR source or semantics.

## ADR index

Historical ADR-0001…ADR-0176 remain accepted/proposed exactly as previously indexed; their detailed rows and semantics are preserved in the ADR source files and Git history. This compact current index supplements, rather than supersedes, those individual decision records.

### Current expansion sequence

| ADR | Status | Decision |
|---|---|---|
| ADR-0177 | Accepted expanded product architecture | Solution Blueprint layer + 12 universal foundations + Woo domain adapter; 43 current surfaces; 160 curated systems; 40 patterns; 268,800 raw primary Blueprint combinations |
| ADR-0178 | Accepted AI architecture | WordPress-native module-wide AI Prompt/Requirement Compiler + F12 ownership + WordPress AI Client/Connectors + typed Abilities + optional official MCP Adapter + capability-gap requests |
| ADR-0179 | Accepted AI/MCP evidence | AIP-001…AIP-176; executed 0/176; AIC/MCP runtime certifications 0 |
| ADR-0180 | Accepted expanded technical-evidence master plan | SBP/ANL/SRH/DEC/LED/RSV/PLC/EXP/DOC/SYN/GEO/AIP/WCA each fixed 176-fixture envelope, executed 0/176 |
| ADR-0181 | Accepted F01 Solution Blueprint detailed evidence | SBP-001…SBP-176 explicit fixtures; executed 0/176; runtime certification 0 |
| ADR-0182 | Accepted F02 Analytics/Event/Journey detailed evidence | ANL-001…ANL-176 explicit fixtures; executed 0/176; analytics storage/backend topology remains evidence-gated |

## Historical ADR sequence reference

The canonical ADR files `ADR-0001…ADR-0176` remain individually authoritative. Key historical evidence milestones include:
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
- ADR-0141 Multisite/Site Lifecycle — MSI 0/160; LC 0/96;
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
- ADR-0166 Status Manager — SM 0/176;
- ADR-0167 Builder adapters — BW 0/176 + BC0…BC4 certifications 0;
- ADR-0168 Watermarker/Media — WM 0/176;
- ADR-0169 TUF — TU 0/176;
- ADR-0170 OAuth Account-Link — OA 0/176;
- ADR-0171 Remote Privacy — RS 0/176;
- ADR-0172 Email Transport — ET-F 0/176; 6 EE3 / 0 ET-certified;
- ADR-0173 Membership Billing — MB-F 0/176; 4 BE3 / 0 MB-certified;
- ADR-0174 Protected Files — PC-F 0/176; 0 PC1+ runtime-certified;
- ADR-0175 Backup Providers — BPC-F 0/176; 34 targets / 0 C-certified / V3 0;
- ADR-0176 Connection Provider — ICP-F 0/176; 0 I4 / 0 I5 certified.

Earlier ADR-0001…ADR-0116 architecture/security/compatibility decisions remain authoritative in their individual files; this compact section does not alter or supersede them.

## Product specification milestone

- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`: **43/43 Exhaustive, 0/43 Authorized**.
- Original 31/31 milestone remains historical pre-ADR-0177 truth.
- Original + universal foundation Multisite option mapping: **43/43**.
- Module-wide AI Prompt product contract under ADR-0178: **43/43 surfaces mapped**.
- `docs/SOLUTIONS/UNIVERSAL-SYSTEM-CATALOG.md`: **160 curated systems**.
- `docs/SOLUTIONS/REFERENCE-FLOW-AND-OPTION-PATTERNS.md`: **40 reusable application patterns**.
- `docs/SOLUTIONS/100K-SYSTEM-SPACE.md`: **268,800 raw primary Blueprint combinations** before validation/secondary dimensions.

## Major fixed evidence protocols / current truth

Established evidence remains unexecuted as summarized above and in `IMPLEMENTATION-READINESS-MATRIX.md`.

Expanded evidence, all executed **0/176**:
- SBP — F01 Solution Blueprint (fully explicit under ADR-0181);
- ANL — F02 Analytics/Event/Journey (fully explicit under ADR-0182);
- SRH — F03 Search/Indexing;
- DEC — F04 Decision/Formula;
- LED — F05 Ledger;
- RSV — F06 Reservation;
- PLC — F07 Placement;
- EXP — F08 Experimentation;
- DOC — F09 Documents;
- SYN — F10 Sync/ETL;
- GEO — F11 Geo/Territory;
- AIP — F12 AI Prompt/MCP;
- WCA — WooCommerce Domain Adapter.

Canonical protocol paths are under `docs/QUALITY/`, `docs/AI/`, `docs/SOLUTIONS/` or the architecture/provider contract named by the ADR. No fixed evidence matrix has been executed unless explicitly stated otherwise (currently none).

## Current planning state

Completed expanded packages:
- WP60 — ADR-0177;
- WP61 — ADR-0178/0179;
- WP62 — ADR-0180;
- WP63 — ADR-0181 / SBP 0/176;
- WP64 — ADR-0182 / ANL 0/176.

Current: **WP65 — F03 Search & Indexing detailed evidence specification**.

The project remains in `SPECIFICATION`, not a global implementation-approval gate. When the owner-requested expanded planning sequence is complete and audited, lifecycle can move back to `AWAITING_DEVELOPMENT_APPROVAL`.

Remaining executable implementation/evidence cannot begin without explicit scoped owner consent under ADR-0014. A future development authorization does not waive implementation-baseline, recovery, quality, security or evidence gates.