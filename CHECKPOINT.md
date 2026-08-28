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

Accepted evidence decisions/refinements now extend through **ADR-0135**.

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
- **ADR-0135 — Custom Tables CTB-01…CTB-184.**

## Recent completed work packages

### WP14 — P-009 Query — DONE
- QRY **0/168**.
- runtime certifications 0.
- QP1/QP2/QP3/QP4 certifications **0/0/0/0**.
- final cost thresholds/cache backend/cursor profile remain evidence-gated.

### WP15 — P-004 Definition Repository — DONE
- existing canonical protocol refined in place to DEF **0/144**.
- D1/PT-C remains first benchmark baseline only.
- final D1/D2/D3/D4 + exact DDL/index/type/collation remain evidence-gated.

### WP16 — P-010 Relations — DONE
- existing canonical protocol refined in place to REL **0/160**.
- R1/PT-D remains first benchmark baseline only; R2/PT-E mandatory comparison; R3 exceptional.
- final endpoint/pivot/DDL/locking profile remains evidence-gated.

### WP17 — Field Storage / Custom Fields — DONE
Created:
- `docs/QUALITY/FIELD-STORAGE-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- `docs/DECISIONS/ADR-0134-field-storage-evidence-protocol.md`

Evidence:
- FST **0/176**.
- Field Storage runtime certifications 0.
- FS1/FS2/FS3/FS6 certified profiles 0; FS4 remains Relations-gated; FS5 remains Vault-gated.
- final routing thresholds and custom index/storage profiles OPEN.

Preserved truth:
- Field Definition ≠ runtime value ≠ editor ≠ presentation;
- Definition publish ≠ value migration completion;
- Q0–Q4 claims require physical evidence;
- null/missing/empty/default remain distinct where schema requires;
- hard uniqueness requires concurrency-safe proof;
- relationships remain Relations-owned;
- secret plaintext never belongs in generic Field Storage;
- projections are rebuildable derived state;
- storage never automatically grants REST/Ability/export/cache visibility.

### WP18 — Custom Tables — DONE
Created:
- `docs/QUALITY/CUSTOM-TABLES-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- `docs/DECISIONS/ADR-0135-custom-tables-evidence-protocol.md`

Evidence:
- CTB **0/184**.
- Custom Tables runtime/DDL/migration certifications 0.
- CT1/CT2/CT3 certified profiles 0.
- CM1/CM2/CM3/CM4 certified operation/recovery profiles 0.
- exact DDL/types/indexes/constraints OPEN.

Preserved truth:
- Table Definition ≠ observed physical schema ≠ Migration Plan ≠ Migration Run ≠ verified applied fingerprint ≠ runtime rows;
- Definition publish never proves physical migration completion;
- typed desired-schema + generated Migration Plan is canonical; raw DDL is not normal product configuration;
- `dbDelta()` is a bounded compiler tool only where evidence proves fit;
- CT1/PT-E is first baseline for ordinary site-owned tables;
- CT2/PT-D is mandatory comparison with trusted scope on every site-owned row path;
- CT3 is genuinely network-owned only;
- stale source fingerprint blocks mutation;
- silent truncation/lossy conversion is forbidden;
- destructive changes require truthful verified recovery boundary;
- Definition deletion never auto-drops physical data;
- unknown external drift is not blindly overwritten.

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
- Field Storage / FST: **0/176**.
- Custom Tables / CTB: **0/184**.
- FM: **0/92**; NT: **0/142**; CH: **0/142**; WC: **0/156**.
- OA: **0/32**; TU: **0/44**.
- DW: **0/36**; AM: **0/40**; PR: **0/44**; RM: **0/48**; WM: **0/48**; FD: **0/48**; BW: **0/50**; SM: **0/48**; XR: **0/48**; ST: **0/48**; UP: **0/48**; RA: **0/48**; REST: **0/52**; IM: **0/56**.
- Membership Billing: **4 BE3 / 0 MB-certified**.
- Protected files: **0 PC1+**.
- Email: **6 EE3 / 0 ET-certified**.
- Connection adapters: **0 I4/I5**.
- Backup providers: **34 targets / 0 C-certified / 0 C3 Supported; V3 0**.
- Site Lifecycle: **0/40**.
- Multisite: **0 MS1+**.
- Remote privacy: **0/30**.

## VCS / verification state

Verified planning/documentation only:
- branch `planning/master-architecture`;
- **31/31 Exhaustive / 0/31 Authorized**;
- evidence contracts/refinements accepted through ADR-0135;
- direct GitHub branch reads on 2026-08-28 showed `main` and `planning/master-architecture` unprotected;
- repository-wide rulesets remain **UNKNOWN** because ruleset access returned 403/plan limitation;
- no package install/build/WordPress runtime/browser/CI/DB/DDL/migration/backfill/provider/file-transfer/archive/restore/query/cache/benchmark execution occurred.

## Next planning-only priority

Current work package: **`P0-M00-WP19` — Admin Columns operational executable-evidence refinement**.

Reason: Query, Field Storage, Relations and Custom Tables now have fixed evidence boundaries. Admin Columns is a major consumer of all four and ADR-0098 already defines the AC1 whole-request/batch-hydration direction, but repository search found no dedicated fixed executable evidence protocol. WP19 will reconcile native list-table hooks, batching/N+1 budgets, real sort/filter behavior, inline/bulk edits, Policy, third-party degradation, export/CSV safety and Multisite behavior before defining evidence.

All existing gates remain intact. Do not restart from zero. Explicit owner consent is still required before executable work.

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