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

Accepted evidence decisions now extend through **ADR-0129**.

Recent bounded protocols:
- ADR-0117 — Forms FM-01…FM-92.
- ADR-0118 — Workflow WF-01…WF-116.
- ADR-0119 — Job/Cron JS-01…JS-106.
- ADR-0120 — Notification NT-01…NT-142.
- ADR-0121 — Message & Chat CH-01…CH-142.
- ADR-0122 — Webhooks/Connections/Event Inbox WC-01…WC-156.
- ADR-0123 — P-001 Compatibility CF-01…CF-112.
- ADR-0124 — P-005 Vault VT-01…VT-128.
- ADR-0125 — P-002 UI/Design System UI-01…UI-104.
- ADR-0126 — P-008 Build Toolchain BT-01…BT-112.
- ADR-0127 — P-007 CI/Quality Matrix CI-01…CI-120.
- ADR-0128 — P-006 Free↔Pro Compatibility FP-01…FP-144.
- **ADR-0129 — P-012 Membership Runtime/Access/Protected Files/Provider MBR-01…MBR-160.**

## WP09 — P-002 UI + P-008 Build — COMPLETE

Work package: **`P0-M00-WP09`** — DONE planning/documentation only.

- UI: **0/104** executed; runtime certification 0.
- BT: **0/112** executed; toolchain certification 0.
- ADR-0005/0012 remain Proposed.
- canonical production build tool not selected.
- minimum WP candidate 6.9 cannot hard-depend on WP 7.1-only UI/theme capability.
- WordPress-provided React is mandatory; duplicate React/ReactDOM/JSX runtime is stop-the-line.
- experimental WordPress UI/build routing features are not foundational contracts.

## WP10 — P-007 CI / Quality Matrix — COMPLETE

Work package: **`P0-M00-WP10`** — DONE planning/documentation only.

- CI fixtures: **0/120** executed.
- CI runtime certification: 0.
- repository workflow implementation verified: NO.
- branch protection/rulesets: **UNKNOWN**.
- untrusted PR code must never receive provider/release secrets.
- FAST/FULL, BASELINE FAILURE, flaky quarantine, artifact provenance and release gating remain mandatory.

## WP11 — P-006 Free↔Pro Compatibility — COMPLETE

Work package: **`P0-M00-WP11`** — DONE planning/documentation only.

- FP fixtures: **0/144** executed.
- P-006 runtime certifications: 0.
- certified Free↔Pro artifact pairs: 0.
- Product License remote-service executions under P-006: 0.
- migrations executed under P-006: 0.
- ADR-0010 remains Proposed.

Preserved separation: package/binary compatibility, Platform API, schema, signed Product Entitlement, remote Product License/account/allocation, Membership authorization and updater trust are distinct truth domains.

## WP12 — P-012 Membership — COMPLETE

Work package: **`P0-M00-WP12`** — DONE planning/documentation only.

Created:
- `docs/QUALITY/P012-MEMBERSHIP-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- `docs/DECISIONS/ADR-0129-p012-membership-evidence-protocol.md`

Current Membership evidence:
- MBR fixtures documented: **160**
- executed: **0/160**
- Membership runtime certifications: **0**
- M1/PT-D vs M2/PT-E physical benchmarks executed: **0**
- billing provider profiles: **4 BE3 paper profiles / 0 MB-certified**
- protected-file certifications: **0 PC1+**
- independent P-012 security review executed: **NO**

Preserved Membership invariants:
- Role ≠ Membership ≠ Billing Source ≠ Entitlement ≠ WPE Product License;
- Enrollment is canonical lifecycle truth and Entitlements are derived/current grants;
- provider webhook/status never directly authorizes a protected request;
- outer security denial cannot be bypassed;
- same-specificity deny wins;
- stale allow after committed revoke/force-deny is a security failure;
- timestamp expiry applies even if Cron/Jobs are late;
- ordinary access hot path makes no provider API call;
- exclusive Plan Groups and Team seats cannot overbook by race;
- role sync is optional/provenance-safe;
- protected-file claims require origin-byte isolation, not merely hidden links/pages;
- Restore/clone reauthorizes/reconciles before stale access or production provider use resumes.

## Current evidence counters

- P-001 / CF: **0/112**; compatibility floor not certified.
- P-002 / UI: **0/104**.
- P-003 / JS: **0/106**.
- P-004: **0 executed**.
- P-005 / VT: **0/128**.
- P-006 / FP: **0/144**.
- P-007 / CI: **0/120**.
- P-008 / BT: **0/112**.
- P-009: **0 executed**.
- P-010: **0 executed**.
- P-011 / WF: **0/116**.
- P-012 / MBR: **0/160; runtime certifications 0**.
- P-013: **0 executed; 34 provider targets / 0 C-certified**.
- WC: **0/156**.
- CH: **0/142**.
- NT: **0/142**.
- FM: **0/92**.
- OA: **0/32**.
- TU: **0/44**.
- DW: **0/36**.
- AM: **0/40**.
- PR: **0/44**.
- RM: **0/48**.
- WM: **0/48**.
- FD: **0/48**.
- BW: **0/50; runtime certifications 0**.
- SM: **0/48**.
- XR: **0/48**.
- ST: **0/48**.
- UP: **0/48**.
- RA: **0/48**.
- REST: **0/52**.
- IM: **0/56**.
- Membership Billing: **4 BE3 / 0 MB-certified**.
- Protected files: **0 PC1+**.
- Email: **6 EE3 / 0 ET-certified**.
- Event/Connection adapters: **0 I4/I5**.
- Site Lifecycle: **0/40**.
- Multisite: **0 MS1+**.
- Remote privacy: **0/30**.

## Verification state

Verified planning/documentation only:
- branch `planning/master-architecture`;
- **31/31 Exhaustive / 0/31 Authorized**;
- evidence contracts accepted through ADR-0129;
- runtime/toolchain blocker ADRs remain unverified until applicable authorized execution;
- no package install/build/WordPress runtime/browser/CI/migration/license/billing/provider/file-transfer/benchmark execution occurred.

## Next planning-only priority

Current work package: **`P0-M00-WP13` — P-013 Backup/Restore artifact/provider/recovery executable evidence refinement**.

Reason: Backup is the remaining critical recovery foundation for migrations, Reset Manager, destructive changes, Vault disaster recovery and operational incident recovery. Existing manifest/crypto/remote-copy/provider certification and artifact profiles must be reconciled into a fixed restore-first adversarial protocol.

All existing evidence gates remain intact. Do not restart from zero. Explicit owner consent is still required before executable work.

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