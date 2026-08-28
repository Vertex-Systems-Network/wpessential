# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-29

This register tracks unresolved runtime/physical/provider/evidence decisions. Accepted planning/evidence decisions now extend through **ADR-0182**. Architecture/protocol acceptance never implies runtime certification or owner development authorization.

Current canonical product scope after ADR-0177: **43 surfaces**.  
Authorized: **0/43**.  
All executable work remains blocked by ADR-0014.

## A. Established platform executable blockers

D-001…D-050 remain the previously accepted executable blockers for compatibility, UI, Jobs, Definition, Vault, Free↔Pro, CI/Build, Query, Relations, Workflow, Membership, Backup, TUF, Dashboards, Builders, Status, XML-RPC, Settings, Profile, Roles, REST, Import, Forms, Notifications, Chat, Connections, Fields, Tables, Admin Columns, Listings, CPT/Taxonomy, Emails, Platform surfaces, Multisite/Lifecycle, Audit, Kernel, Privacy, Errors, Component Blueprint, Versioning, Module Lifecycle, DSR, Assets, Conditional Logic, DVR, Rate Limit, Cache, Remote Privacy and Email Transport.

Their exact evidence IDs/counters remain unchanged in `docs/IMPLEMENTATION-READINESS-MATRIX.md` and the corresponding ADR/QUALITY protocols. No historical blocker is superseded by the universal-system expansion.

## B. Universal system / AI expansion blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-051 | ADR-0177/0180/0181 | F01 Solution Blueprint install/upgrade/drift/security/package/Multisite — SBP-001…SBP-176; executed 0/176 |
| D-052 | ADR-0177/0180/0182 | F02 Analytics/Event/Journey collection/identity/privacy/metrics/funnels/cohorts/attribution/storage/scale — ANL-001…ANL-176; executed 0/176 |
| D-053 | ADR-0177/0180 | F03 Search/Index backend/security/relevance/invalidation/scale — SRH-001…SRH-176; detailed fixture specification current WP65 |
| D-054 | ADR-0177/0180 | F04 Decision/Formula/Scoring typed compiler/decimal/unit/determinism/performance — DEC-001…DEC-176 |
| D-055 | ADR-0177/0180 | F05 Ledger transaction/idempotency/holds/rebuild/reconciliation — LED-001…LED-176 |
| D-056 | ADR-0177/0180 | F06 Reservation calendar/DST/atomic hold/capacity/concurrency — RSV-001…RSV-176 |
| D-057 | ADR-0177/0180 | F07 Placement/Personalization slots/context/frequency/cache/privacy/adapters — PLC-001…PLC-176 |
| D-058 | ADR-0177/0180 | F08 Experiments assignment/exposure/statistics/rollout/cache/privacy — EXP-001…EXP-176 |
| D-059 | ADR-0177/0180 | F09 Documents renderer/fonts/assets/private delivery/record integrity — DOC-001…DOC-176 |
| D-060 | ADR-0177/0180 | F10 Sync/ETL cursor/checkpoint/conflicts/idempotency/provider drift/scale — SYN-001…SYN-176 |
| D-061 | ADR-0177/0180 | F11 Geo/Territory spatial storage/query/provider/privacy/scale — GEO-001…GEO-176 |
| D-062 | ADR-0177/0178/0179/0180 | F12 AI Gateway + shared Prompt/MCP runtime — AIP-001…AIP-176; AIC and MCP runtime certifications 0 |
| D-063 | ADR-0177/0180 | WooCommerce Commerce Domain Adapter HPOS/cart/checkout/Blocks/order/stock/shipping/payment/provider/version evidence — WCA-001…WCA-176 |
| D-064 | ADR-0178/0179 | Module-wide Prompt coverage execution across 43/43 surfaces, Requirement/Plan IR, capability gaps, MCP exposure, prompt-injection and provider/model regression — AIP-001…AIP-176 |

## C. Accepted expanded planning/evidence

- ADR-0177 — Solution Blueprint/universal foundations/Woo adapter architecture; 43 surfaces; 160 curated systems; 40 patterns; 268,800 raw primary Blueprint combinations.
- ADR-0178 — WordPress-native AI Prompt/Requirement Compiler + optional MCP architecture; 43/43 Prompt product mapping.
- ADR-0179 — AIP-001…AIP-176; executed 0/176; AIC/MCP certs 0.
- ADR-0180 — universal foundations/Woo adapter master evidence envelopes; each 0/176.
- ADR-0181 — F01 SBP-001…SBP-176 explicit fixtures; executed 0/176.
- ADR-0182 — F02 ANL-001…ANL-176 explicit fixtures; executed 0/176.

## D. Current evidence execution truth

Established evidence remains unexecuted as recorded in the Readiness Matrix/Checkpoint. Expanded counters:
- SBP 0/176
- ANL 0/176
- SRH 0/176
- DEC 0/176
- LED 0/176
- RSV 0/176
- PLC 0/176
- EXP 0/176
- DOC 0/176
- SYN 0/176
- GEO 0/176
- AIP 0/176
- WCA 0/176

No expanded runtime certification exists.

## E. Current planning priority

Current work package: **`P0-M00-WP65` — F03 Search & Indexing detailed executable-evidence specification**.

Planned sequence afterward: F04 Decision/Formula → F05 Ledger → F06 Reservation → F07 Placement → F08 Experiments → F09 Documents → F10 Sync → F11 Geo → Woo Adapter detailed evidence → expanded-scope consistency audit.

This is planning order only. It authorizes no execution.

## F. Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine/refine bounded executable protocol when proof is required.
4. Prefer canonical in-place refinement over duplicates.
5. Do not execute code/build/DDL/migration/benchmark/provider/AI/MCP/runtime/data mutations before explicit owner consent.
6. Never promote paper evidence to runtime/provider certification.
7. Keep checkpoint/ledger/readiness/open-decisions/ADR index/Draft PR synchronized.

Production development authorization remains **NOT GRANTED / 0/43**.