# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-10 UTC**  
Canonical audited base anchor: **`main @ 3b36bd4fb6c7d5d7240745bc489f3f6b0bba369e`**  
Project classification: **`ACTIVE_EXISTING_PROJECT`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Development approval: **`GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56`**

## Mandatory work-cycle order

Every `start`, `continue` and `resume` cycle must resolve exact current `main`, reconcile accepted OPEN Issues first, reconcile eligible OPEN PRs/MRs second, inspect deterministic claims + the coordination queue, and only then continue dependency-ready work. No force/reuse of deterministic claim branches. Exact-head applicable CI, latest-main reconciliation and clean review threads are required before promotion.

## Certified bounded implementation gates

- Surface 3 / Fields — **PASS FOR CERTIFIED NATIVE V1 SCOPE**.
- Surface 4 / Relations — **PASS FOR CERTIFIED NATIVE V1 BASELINE**.
- Surface 5 / Status — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 6 / Query — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 8 / Admin Columns — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 9 / Listings — **PASS FOR CERTIFIED BOUNDED V1 BASELINE**.
- Surface 7 / Custom Tables — **ACTIVE / NOT PASS**, bounded runway **90%**.

These bounded states do not imply `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release readiness.

## Surface 7 — Custom Tables safety boundary

Promoted through Issue #463 / merged PR #464 and Issue #465 / merged PR #466. Managed-table execution remains blocked. Still forbidden: managed-target physical DDL; R1/R2/R3/R4 statement execution; live row scans; Backup create/restore side effects; migration jobs/leases/retries; CRUD/Data Source/backfill/dedup/shadow-copy/swap; CT2/PT-D/CT3 conversion; external adoption; public execution mutation; deployment/release/certification claims.

## Surface 1 — CPT Builder

Issue #473 / merged PR #477 remains the latest CPT planning closure: Bank `BANK_REVIEWED` 107, 23 normalized Atomic Option Contracts, deterministic 107/107 projection with `missing=0` and `unclassified=0`, reviewed UX contract and runtime gap matrix. No CPT runtime lane is authorized.

## Surface 2 — Taxonomy Builder

Parent implementation program: **Issue #474 / queue priority 1010 / OPEN**. Planning lifecycle remains **`UX_CONTRACT_COMPLETE`**; runtime/product parity are not certified.

Promoted bounded slices now include merged PRs **#478, #479, #480, #483, #486, #489, #503 and #506**.

### PR #506 — accessible Find Setting search — PROMOTED

Merged as **`3b36bd4fb6c7d5d7240745bc489f3f6b0bba369e`** from exact head **`1da78f03f2d6951deb0d9e01125c5e6a8accd9bd`** after latest-main reconciliation.

Promoted behavior:

- searchable index over existing server-rendered Taxonomy setting labels and IDs;
- matched Advanced controls automatically promote the required editor tier;
- matched controls inside collapsed `<details>` sections are revealed before focus;
- Enter activates the first result and focus moves to the canonical existing control;
- search excludes its own `Find setting` input from the setting index;
- label result names use the visible strong label while technical key/ID text remains searchable;
- no Taxonomy payload, provider execution, Definition persistence or runtime mutation semantics changed;
- packaged Playwright/axe evidence covers tier reveal, collapsed label reveal, keyboard navigation, no-match status and accessibility.

Exact head `1da78f03...` passed all three applicable workflows before merge:

1. Architecture Guards — PASS;
2. Browser E2E Accessibility — PASS;
3. Distributable Package — PASS.

Review threads were empty and the PR was mergeable before exact-head merge. Earlier CI failures were repaired on the same branch: Prettier-only Architecture failures, then a result-label extraction mismatch, then an ambiguous test locator; no failed head was used as merge evidence.

### Remaining Taxonomy Runtime Gap Closure

Issue #474 remains open. Remaining accepted families include provider selector/health UX, capabilities/effective-map UX, default-term and bounded term-query UX, rewrite/REST collision diagnostics, portability/CPT UI compatibility mapping, and separately safety-gated taxonomy-key migration planning. No `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` claim is authorized yet.

## Owner-directed ten-worker topology

Supervisor Issue #492 established ten distinct non-overlapping module lanes. Surface 2 remains the only runtime implementation lane; Surfaces 10–18 remain planning/contract-only.

1. **Worker-01 — Taxonomy / Surface 2 / Issue #474** — PR #506 promoted; broader runtime-gap program remains OPEN.
2. **Worker-02 — Dashboard Widgets / Surface 10 / Issue #493** — Bank `BANK_REVIEWED` 123 / zero unresolved. Branch now contains a schema contract candidate with **18 normalized Atomic Option Contracts**, deterministic five-shard **123-record source projection**, reviewed Essential/Advanced/Expert UX contract and no-runtime-baseline gap matrix. Branch reconciled to `main @ 3b36bd4f...` without force as two-parent merge `fd23ae2c...`. PR #508 is OPEN for exact-head machine validation/review; runtime remains unauthorized.
3. **Worker-03 — Admin Menu / Surface 11 / Issue #494** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Official-source evidence matrix commit `0695290557715b1a697b4a6a805381623798238e` is branch-local evidence only.
4. **Worker-04 — Settings Pages / Surface 12 / Issue #495** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Official-source evidence matrix commit `4a7e69242cce4c48498318401018f5775319dc83` is branch-local evidence only.
5. **Worker-05 — Frontend Dashboard / Surface 13 / Issue #496** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Evidence matrix commit `75fccc4c874fe261e5b414d9858343b05d8cf034` is branch-local evidence only.
6. **Worker-06 — User Profile / Surface 14 / Issue #497** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Evidence matrix commit `1be3ed3229f0f2c6e372993cdb1cc431dd642d06` is branch-local evidence only.
7. **Worker-07 — Membership / Surface 15 / Issue #498** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Evidence matrix commit `23e143ab795ef18b0eb8e0abc0e9646c99ef3752` is branch-local evidence only; payment execution remains unauthorized.
8. **Worker-08 — Builder Widgets / Surface 16 / Issue #499** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Evidence matrix commit `268f41a66adbb0d6f8b448d2fdfb3bff9bef044d` is branch-local evidence only.
9. **Worker-09 — Forms & Workflows / Surface 17 / Issue #500** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Evidence matrix commit `deb5aba409d979efcb6a38d89b0a14462629e997` is branch-local evidence only; workflow execution remains unauthorized.
10. **Worker-10 — Cron / Surface 18 / Issue #501** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Evidence matrix commit `cfd7678698b4ccd8ac310143d3cca2eca36a4629` is branch-local evidence only; WP-Cron is explicitly not represented as guaranteed wall-clock execution.

Branch-local seed candidates and contract candidates do **not** promote main lifecycle truth until their reviewed PR evidence merges and Supervisor reconciliation is completed.

## Planning-worker constraints

Workers 02–10 may produce Bank/Atomic audits, normalized Bank records, schema-valid contracts where prerequisites support them, reviewed UX contracts and runtime gap matrices. They may not edit Supervisor-owned README/CHECKPOINT/queue directly; implement runtime code; promote runtime/product-parity certification; perform destructive provider/database/content/user mutation; or deploy/release.

## Product truth

- Accepted structural scope: **56 / 56 Exhaustive**.
- Multisite planning: **56 / 56**.
- AI Prompt planning: **56 / 56**.
- README closeout must remain **56 / 56 modules listed**.
- Current shared-truth reconciliation: **Issue #507 / supervisor branch `supervisor/taxonomy-post-find-setting-reconciliation-v1`**.
- Current audited main anchor for this reconciliation: **`3b36bd4fb6c7d5d7240745bc489f3f6b0bba369e`**.

## Next work

1. Complete Supervisor Issue #507 by reconciling README 56/56 and coordination queue to `main @ 3b36bd4f...`; do not close #474 or promote runtime/product parity.
2. Reconcile OPEN PR #508 from exact head `fd23ae2c...`: machine validators, clean review threads and latest-main reconciliation are required before merge.
3. Worker-02 / Issue #493: only after #508 evidence proves the contract may Surface 10 planning lifecycle be promoted; runtime remains a separate later gate.
4. Workers 03–10: continue native/platform + market evidence expansion; no `BANK_REVIEWED` claim until zero unresolved is proven and reviewed/merged.
5. Worker-01 / Issue #474: after accepted Issue/PR/shared-truth gates, continue the next bounded Taxonomy runtime gap without destructive key migration or certification claims.

Repository evidence overrides conversational memory.
