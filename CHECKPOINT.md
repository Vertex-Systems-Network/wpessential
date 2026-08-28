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

Accepted evidence decisions now extend through **ADR-0131**.

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
- ADR-0129 — P-012 Membership Runtime/Access/Protected Files/Provider MBR-01…MBR-160.
- ADR-0130 — P-013 Backup/Restore Artifact/Provider/Recovery BK-01…BK-180.
- **ADR-0131 — P-009 Query Compiler/Cost/Cache/Security QRY-01…QRY-168.**

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
- direct GitHub branch reads on 2026-08-28 report `main` and `planning/master-architecture` branch protection as **disabled/unprotected**.
- repository-wide rulesets state remains **UNKNOWN** because the rulesets endpoint is plan/access restricted (403).
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

## WP13 — P-013 Backup / Restore — COMPLETE

Work package: **`P0-M00-WP13`** — DONE planning/documentation only.

Created:
- `docs/QUALITY/P013-BACKUP-RESTORE-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- `docs/DECISIONS/ADR-0130-p013-backup-restore-evidence-protocol.md`

Current Backup evidence:
- BK fixtures documented: **180**
- executed: **0/180**
- Backup/Restore runtime certifications: **0**
- planned provider targets: **34**
- provider C-certified: **0**
- provider C3 Supported: **0**
- V3 Restore Tested production-profile certifications: **0**
- independent disaster-recovery/security review executed: **NO**

Preserved Backup invariants:
- generated/uploaded does not mean restore-ready;
- V2 Remote Verified does not mean V3 Restore Tested;
- required missing/corrupt capture cannot be presented as fully verified;
- provider success/checksum does not replace WPE manifest/integrity/restore truth;
- static SE evidence never grants C certification;
- provider certification is exact provider/profile/adapter/environment scoped;
- the only recovery key cannot live solely beside/inside the ciphertext it unlocks;
- integrity/authentication failure aborts before destructive restore;
- hostile archive/parser/path/symlink/decompression input is bounded and fail-safe;
- unknown remote-delete outcome is not completed deletion;
- restore/clone must revalidate Vault/provider/commercial state;
- stale Membership derived/cache state cannot resurrect revoked/expired access;
- Reset/migration/destructive flows cannot claim a restore point merely because a Backup job started.

## WP14 — P-009 Query — COMPLETE

Work package: **`P0-M00-WP14`** — DONE planning/documentation only.

Created:
- `docs/QUALITY/P009-QUERY-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- `docs/DECISIONS/ADR-0131-p009-query-evidence-protocol.md`

Current Query evidence:
- QRY fixtures documented: **168**
- executed: **0/168**
- P-009 runtime certifications: **0**
- QP1/QP2/QP3/QP4 certified provider/profile counts: **0 / 0 / 0 / 0**
- independent P-009 security review executed: **NO**
- final numeric cost thresholds: **OPEN / evidence-gated**
- final persistent cache backend/default: **OPEN / evidence-gated**
- final cursor encoding/profile: **OPEN / evidence-gated**

Preserved Query invariants:
- Query Definition/Revision, invocation, compiled provider operation and result/cache entry are distinct truths;
- Draft edits cannot silently alter published consumer semantics;
- no raw SQL/arbitrary PHP callback/eval in normal Query AST;
- parameters are typed/untrusted values and identifiers are registered schema references;
- unsupported provider semantics fail before execution rather than silently degrade;
- row/resource/field/scope authorization is server-side and independent from authentication;
- count/aggregate metadata cannot leak hidden rows/cohorts;
- persistent shared cache is disabled when authorization/invalidation dependencies cannot be represented safely;
- committed revoke/policy-generation change cannot keep serving stale protected results;
- cursor state is untrusted and bound/revalidated against revision/provider/scope/order/parameters/authorization;
- remote QP4 results are reauthorized locally through Connections/Safe HTTP/Vault boundaries;
- normal relation/list execution has zero tolerance for unbounded N+1;
- performance cannot override correctness, scope or authorization.

## Current evidence counters

- P-001 / CF: **0/112**; compatibility floor not certified.
- P-002 / UI: **0/104**.
- P-003 / JS: **0/106**.
- P-004: **0 executed; existing fixed Definition evidence protocol requires completeness audit**.
- P-005 / VT: **0/128**.
- P-006 / FP: **0/144**.
- P-007 / CI: **0/120**.
- P-008 / BT: **0/112**.
- P-009 / QRY: **0/168; runtime certifications 0**.
- P-010: **0 executed**.
- P-011 / WF: **0/116**.
- P-012 / MBR: **0/160; runtime certifications 0**.
- P-013 / BK: **0/180; runtime certifications 0; 34 provider targets / 0 C-certified / 0 C3 Supported; V3 certifications 0**.
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
- evidence contracts accepted through ADR-0131;
- runtime/toolchain blocker ADRs remain unverified until applicable authorized execution;
- no package install/build/WordPress runtime/browser/CI/migration/license/billing/provider/file-transfer/archive/restore/query/cache/benchmark execution occurred.

## Next planning-only priority

Current work package: **`P0-M00-WP15` — P-004 Definition Repository evidence completeness / physical proof audit**.

Reason: Definition Repository is a shared control-plane foundation for versioned module definitions, dependencies, published revisions, imports and migrations. Unlike P-009, an existing fixed P-004 protocol and ADR-0092 already exist, so the next task is to audit that protocol against the accepted Definition Repository relational/physical architecture, Multisite scope, migration/versioning and current governance. Only material gaps should create a refinement/new ADR; duplicate protocols are forbidden.

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