# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**
Last synchronized: 2026-08-29

This register tracks unresolved runtime/physical/provider/evidence decisions. Accepted planning decisions extend through **ADR-0188**. Architecture/protocol acceptance never implies runtime certification or owner development authorization.

Current canonical product scope: **48 surfaces**.
Authorized: **0/48**.
Logical Multisite mapping: **48/48**.
AI Prompt product mapping: **48/48**.
All executable work remains blocked by ADR-0014.

## A. Established platform executable blockers

D-001…D-050 remain the previously accepted blockers for compatibility, UI, Jobs, Definition, Vault, Free↔Pro, CI/Build, Query, Relations, Workflow, Membership, Backup, TUF, Dashboards, Builders, Status, XML-RPC, Settings, Profile, Roles, REST, Import, Forms, Notifications, Chat, Connections, Fields, Tables, Admin Columns, Listings, CPT/Taxonomy, Emails, Platform surfaces, Multisite/Lifecycle, Audit, Kernel, Privacy, Errors, Component Blueprint, Versioning, Module Lifecycle, DSR, Assets, Conditional Logic, DVR, Rate Limit, Cache, Remote Privacy and Email Transport.

Exact evidence IDs/counters remain authoritative in `IMPLEMENTATION-READINESS-MATRIX.md` and the associated ADR/QUALITY protocols.

## B. Universal system / AI expansion blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-051 | ADR-0177/0180/0181 | F01 Solution Blueprint — SBP-001…SBP-176; 0/176 |
| D-052 | ADR-0177/0180/0182 | F02 Analytics/Event/Journey — ANL-001…ANL-176; 0/176 |
| D-053 | ADR-0177/0180 | F03 Search/Index — SRH-001…SRH-176; detailed fixture specification current WP65 |
| D-054 | ADR-0177/0180 | F04 Decision/Formula/Scoring — DEC-001…DEC-176; 0/176 |
| D-055 | ADR-0177/0180 | F05 Ledger — LED-001…LED-176; 0/176 |
| D-056 | ADR-0177/0180 | F06 Reservation — RSV-001…RSV-176; 0/176 |
| D-057 | ADR-0177/0180 | F07 Placement/Personalization — PLC-001…PLC-176; 0/176 |
| D-058 | ADR-0177/0180 | F08 Experiments/Rollout — EXP-001…EXP-176; 0/176 |
| D-059 | ADR-0177/0180 | F09 Documents/Records — DOC-001…DOC-176; 0/176 |
| D-060 | ADR-0177/0180 | F10 Sync/ETL — SYN-001…SYN-176; 0/176 |
| D-061 | ADR-0177/0180 | F11 Geo/Territory — GEO-001…GEO-176; 0/176 |
| D-062 | ADR-0177/0178/0179/0180 | F12 AI Gateway + Prompt/MCP — AIP-001…AIP-176; AIC/MCP runtime certs 0 |
| D-063 | ADR-0177/0180 | WooCommerce Domain Adapter — WCA-001…WCA-176; 0/176 |
| D-064 | ADR-0178/0179 | Prompt/Requirement Compiler execution across current surfaces — AIP 0/176; provider/model/MCP/security evidence pending |

## C. Market-expansion blockers — ADR-0183…0188

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-065 | ADR-0183 | URL Redirection & Routing matching/normalization/conditions/actions/loops/404/logging/server-export/cache/security/Multisite — RDR-001…RDR-176; 0/176 |
| D-066 | ADR-0184 | Search/Replace serialized/JSON/block safety, schema validation, Dry Run/Plan/Journal/Backup/concurrency/charset/Multisite — SRT-001…SRT-176; 0/176 |
| D-067 | ADR-0185 | Dummy Data deterministic generators/relations/media/PII safety/scenarios/cleanup/adapters/scale — DMY-001…DMY-176; 0/176 |
| D-068 | ADR-0186 | Link Health source extraction/Safe HTTP/status truth/chains/media/graph/jobs/fix plans/privacy/Multisite/scale — LNK-001…LNK-176; 0/176 |
| D-069 | ADR-0187 | DB Maintenance owner-aware cleanup/orphan certainty/autoload/table health/Dry Run/Backup/jobs/Multisite/security — DBM-001…DBM-176; 0/176 |
| D-070 | ADR-0188 | S07 Product Discovery/Planning Orchestrator provenance/dedupe/classification/spec/ADR/VCS/AI-MCP safety — PDO-001…PDO-176; 0/176 |
| D-071 | ADR-0188 | S08 Market Intelligence Radar sources/change detection/scoring/S07 handoff/Git issue-PR/schedule/security — MIR-001…MIR-176; 0/176; executable daily job not installed |

## D. Accepted market-driven existing-surface enhancements

These require later evidence refinement under their existing owner; they do not create new module denominator rows:
- deep request/query/hook/REST/asset diagnostics → Platform Diagnostics/Audit;
- per-operator Troubleshooting Session Mode → Platform Diagnostics;
- controlled Support Impersonation → User Profile/Role/Platform Support;
- native WP-Cron inspection → Cron/JobService;
- human-readable Activity History → Audit;
- media source replacement/regenerate derivatives → Watermarker/Media;
- generic arbitrary Code Snippets → rejected under ADR-0004.

Detailed product behavior is in `docs/MODULES/MARKET-RESEARCH-EXISTING-SURFACE-ENHANCEMENTS.md`.

## E. Current evidence execution truth

Expanded/universal/market counters:
- SBP 0/176; ANL 0/176; SRH 0/176; DEC 0/176; LED 0/176; RSV 0/176; PLC 0/176; EXP 0/176; DOC 0/176; SYN 0/176; GEO 0/176; AIP 0/176; WCA 0/176;
- RDR 0/176; SRT 0/176; DMY 0/176; LNK 0/176; DBM 0/176; PDO 0/176; MIR 0/176.

Established evidence counters remain as recorded in Readiness/Checkpoint. No runtime certification exists for the new scope.

## F. Daily market job truth

The exact daily GitHub Actions design is documented in `docs/OPERATIONS/MARKET-INTELLIGENCE-DAILY-GITHUB-JOB.md`.

It is **not installed/enabled** as an executable `.github/workflows` file. Scheduled automation remains implementation work and requires development consent plus CI/security review.

## G. Current planning priority

Current work returns to the interrupted existing package:

**P0-M00-WP65 — F03 Search & Indexing detailed executable-evidence specification.**

Owner-requested market packages WP75…WP82 are DONE planning work. WP66…WP74 remain reserved for the earlier F04→Woo-adapter sequence.

## H. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Research current public sources when requested/needed.
3. Distinguish source facts, market facts, inference and WPE decisions.
4. Map reuse before adding a module.
5. Resolve static semantics by ADR when sufficient.
6. Predefine bounded evidence when runtime proof is required.
7. Never promote paper evidence to runtime/provider certification.
8. No code/build/DB/provider/AI/MCP/crawl/transform/generation/cleanup/scheduled-workflow execution before explicit consent.
9. Keep checkpoint/ledger/readiness/open-decisions/ADR index/Draft PR synchronized.

Production development authorization remains **NOT GRANTED / 0/48**.
