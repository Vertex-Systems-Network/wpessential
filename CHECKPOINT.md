# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Canonical project state: **`PLANNED_EXISTING_PROJECT`**  
Execution mode: **`PLANNER_ONLY`**  
Work lifecycle state: **`SPECIFICATION` / Phase 0 planning**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit scoped owner consent is required before runtime/source/build/migration/test implementation, executable spikes/benchmarks, dependency/package setup, WordPress runtime execution, queues, provider/API calls, data mutations, packaging or deployment.

`continue`, `resume`, planning acceptance, ADR acceptance and technical readiness do **not** authorize production development.

Source of truth: `DEVELOPMENT-CONSENT.md`, `AGENTS.md`, `docs/APPROVAL-LEDGER.md`, ADR-0014.

## Product milestone

- **31/31 Exhaustive product-option maturity**
- **31/31 Multisite scope behavior mapped**
- **0/31 Authorized**
- **0 MS1+ runtime-certified surfaces**
- Implemented: none
- Runtime verified: none

## Accepted architecture/evidence milestone

Accepted evidence decisions/refinements now extend through **ADR-0139**.

Recent bounded protocols/refinements:
- ADR-0117 — Forms FM-01…FM-92.
- ADR-0118 — Workflow WF-01…WF-116.
- ADR-0119 — Job/Cron JS-01…JS-106.
- ADR-0120 — Notification NT-01…NT-142.
- ADR-0121 — Message & Chat CH-01…CH-142.
- ADR-0122 — Webhooks/Connections/Event Inbox WC-01…WC-156.
- ADR-0123 — Compatibility CF-01…CF-112.
- ADR-0124 — Vault VT-01…VT-128.
- ADR-0125 — UI UI-01…UI-104.
- ADR-0126 — Build BT-01…BT-112.
- ADR-0127 — CI CI-01…CI-120.
- ADR-0128 — Free↔Pro FP-01…FP-144.
- ADR-0129 — Membership MBR-01…MBR-160.
- ADR-0130 — Backup/Restore BK-01…BK-180.
- ADR-0131 — Query QRY-01…QRY-168.
- ADR-0132 — Definition canonical refinement DEF-01…DEF-144.
- ADR-0133 — Relations canonical refinement REL-01…REL-160.
- ADR-0134 — Field Storage / Custom Fields FST-01…FST-176.
- ADR-0135 — Custom Tables CTB-01…CTB-184.
- ADR-0136 — Admin Columns AC-01…AC-176.
- ADR-0137 — Dynamic Listings DL-01…DL-176.
- ADR-0138 — Free CPT & Taxonomy CPTX-01…CPTX-176.
- **ADR-0139 — Emails Builder rendering/composition EBR-01…EBR-176.**

## Recent completed work packages

### WP14 — Query — DONE
- QRY **0/168**; QP1/QP2/QP3/QP4 certifications **0/0/0/0**.
- final cost thresholds/cache backend/cursor profile remain evidence-gated.

### WP15 — Definition Repository — DONE
- canonical protocol refined in place to DEF **0/144**.
- D1/PT-C remains first benchmark baseline only; final D1–D4 + exact DDL/index/type/collation remain evidence-gated.

### WP16 — Relations — DONE
- canonical protocol refined in place to REL **0/160**.
- R1/PT-D remains first benchmark baseline only; R2/PT-E mandatory comparison; R3 exceptional.

### WP17 — Field Storage / Custom Fields — DONE
- FST **0/176**; runtime/profile certifications 0.
- FS1/FS2/FS3/FS6 certified profiles 0; FS4 Relations-gated; FS5 Vault-gated.

### WP18 — Custom Tables — DONE
- CTB **0/184**; runtime/DDL/migration certifications 0.
- CT1/CT2/CT3 and CM1/CM2/CM3/CM4 certified profiles 0; exact DDL/types/indexes/constraints OPEN.

### WP19 — Admin Columns — DONE
- AC **0/176**; target adapters runtime-certified 0.
- `AC-R/AC-S/AC-F/AC-Q/AC-E/AC-B/AC-X/AC-M/AC-P` certifications 0.

### WP20 — Dynamic Listings — DONE
- DL **0/176**.
- `DL-A1/DL-A2/DL-A3` strategy certifications 0.
- `DL-R/DL-A/DL-P/DL-F/DL-H/DL-C/DL-I/DL-B/DL-S/DL-M/DL-O` certifications 0.

### WP21 — Free CPT + Taxonomy — DONE
- CPTX **0/176**.
- `CPTX-CPT/CPTX-TAX/CPTX-RW/CPTX-REST/CPTX-CAP/CPTX-OWN/CPTX-LC/CPTX-MIG/CPTX-MS/CPTX-COMP` certifications 0.
- published runtime keys remain migration-class identities.
- rewrite flush remains dirty-generation/controlled, never every request.
- Definition disable/delete preserves posts/terms/relationships/meta by default.
- external registration discovery/collision does not establish WPE ownership.

### WP22 — Emails Builder rendering/composition — DONE
Created:
- `docs/QUALITY/EMAILS-BUILDER-RENDERING-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- `docs/DECISIONS/ADR-0139-emails-builder-rendering-evidence-protocol.md`

Evidence:
- EBR **0/176**.
- `EBR-D/EBR-C/EBR-H/EBR-T/EBR-E/EBR-A/EBR-P/EBR-I/EBR-M/EBR-O` certifications 0.
- exact renderer/CSS inliner dependency OPEN.
- exact email-client compatibility matrix OPEN.
- exact email-size/attachment/render budgets OPEN.
- WordPress core email override adapter certifications 0.
- third-party email override adapter certifications 0.
- existing provider/transport truth remains **6 EE3 / 0 ET-certified** and is unchanged.

Preserved truth:
- Email Definition ≠ published Template/Layout revisions ≠ compiled descriptor ≠ authorized context ≠ Email IR ≠ HTML/plaintext ≠ envelope ≠ immutable Rendered Message ≠ Transport Attempt ≠ provider/delivery truth;
- Draft templates/layouts never enter production send path;
- tokens are typed, privacy-classified, Policy-aware and destination-escaped;
- browser/page-builder HTML is not canonical email markup;
- secrets/credentials/protected internals are not generic renderable tokens;
- private assets/attachments need recipient-specific authorization;
- preview/test/production are separate modes and test send cannot mutate production business state;
- deterministic retry reuses frozen Rendered Message unless explicit versioned re-render policy creates a new generation;
- renderer success never promotes ET submission/delivery/inbox/read state;
- network/shared templates do not imply shared sender credentials or recipient datasets.

## Current evidence counters

- P-001 / CF: **0/112**.
- P-002 / UI: **0/104**.
- P-003 / JS: **0/106**.
- P-004 / DEF: **0/144**.
- P-005 / VT: **0/128**.
- P-006 / FP: **0/144**.
- P-007 / CI: **0/120**.
- P-008 / BT: **0/112**.
- P-009 / QRY: **0/168**.
- P-010 / REL: **0/160**.
- P-011 / WF: **0/116**.
- P-012 / MBR: **0/160**.
- P-013 / BK: **0/180**.
- FST: **0/176**; CTB: **0/184**; AC: **0/176**; DL: **0/176**; CPTX: **0/176**; EBR: **0/176**.
- FM: **0/92**; NT: **0/142**; CH: **0/142**; WC: **0/156**.
- OA: **0/32**; TU: **0/44**.
- DW: **0/36**; AM: **0/40**; PR: **0/44**; RM: **0/48**; WM: **0/48**; FD: **0/48**; BW: **0/50**; SM: **0/48**; XR: **0/48**; ST: **0/48**; UP: **0/48**; RA: **0/48**; REST: **0/52**; IM: **0/56**.
- Membership Billing: **4 BE3 / 0 MB-certified**.
- Protected files: **0 PC1+**.
- Email transport/provider: **6 EE3 / 0 ET-certified**.
- Connection adapters: **0 I4/I5**.
- Backup providers: **34 targets / 0 C-certified / 0 C3 Supported; V3 0**.
- Site Lifecycle: **0/40**.
- Multisite: **0 MS1+**.
- Remote privacy: **0/30**.

## VCS / verification state

Verified planning/documentation only:
- branch `planning/master-architecture`;
- **31/31 Exhaustive / 0/31 Authorized**;
- evidence contracts/refinements accepted through ADR-0139;
- direct GitHub branch reads on 2026-08-28 previously showed `main` and `planning/master-architecture` unprotected;
- repository-wide rulesets remain **UNKNOWN** because ruleset access returned 403/plan limitation;
- no package install/build/WordPress runtime/browser/CI/DB/DDL/migration/backfill/provider/file-transfer/archive/restore/query/cache/rewrite-flush/email-send/benchmark execution occurred.

## Next planning-only priority

Current work package: **`P0-M00-WP23` — Platform Account / Docs / Support / Diagnostics executable-evidence reconciliation**.

Reason: surface #31 is Exhaustive and has OAuth/Product License/TUF/remote-service architecture plus partial protocols, but repository verification found no consolidated `PLATFORM-SURFACES-EXECUTABLE-EVIDENCE-PROTOCOL.md`. WP23 will reconcile account link, installation/network/site allocations, entitlement/display state, docs/changelog/support ticket trust, diagnostics bundle/privacy, remote-service failure/offline modes and Multisite/install scope. Existing FP/OA/TU/privacy/service protocols remain authoritative and will not be duplicated.

All gates remain intact. Do not restart from zero. Explicit owner consent is still required before executable work.

## Resume order

1. `DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/PROJECT-STATE-AND-ADOPTION.md`
5. `docs/APPROVAL-LEDGER.md`
6. `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`
7. `docs/WORK-COORDINATION-LEDGER.md`
8. `docs/RELEASE-INCIDENT-RECOVERY-GOVERNANCE.md`
9. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
10. `docs/OPEN-DECISIONS-REGISTER.md`
11. `docs/DECISIONS/README.md`
12. relevant architecture/security/quality/module/provider docs.

Repository evidence overrides conversational memory.