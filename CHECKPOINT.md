# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-10 UTC**  
Canonical audited base anchor: **`main @ 6ee2a39ff84bdcbb0584efd03a9db4bd620a6a35`**  
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

Promoted bounded slices now include merged PRs **#478, #479, #480, #483, #486, #489 and #503**.

### PR #503 — tiered visibility authoring — PROMOTED

Merged as **`6ee2a39ff84bdcbb0584efd03a9db4bd620a6a35`** from exact head **`98cc8317f3cfcf83cff5f93f32ab8d33ec9ee667`** after latest-main reconciliation.

Promoted behavior:

- keyboard-reachable Essential / Advanced / Expert tier navigation;
- authoring for existing canonical optional visibility booleans;
- truthful `Default / inherited`, `Explicit: enabled`, and `Explicit: disabled` state;
- inherited optional values are omitted rather than replaced by guessed defaults;
- hidden Advanced values survive tier changes;
- Expert boundary remains visible while provider/migration execution stays separately gated;
- server-authoritative Validate diagnostics remain the source of effective `register_taxonomy()` arguments;
- packaged Browser E2E proves tier behavior, explicit/inherited values, hidden-value preservation and axe accessibility.

Exact head `98cc8317...` passed all six applicable workflows before merge:

1. Architecture Guards — PASS;
2. Browser E2E Accessibility — PASS;
3. PHP Quality Toolchain — PASS;
4. Taxonomy Runtime — PASS;
5. Distributable Package — PASS;
6. Platform Compatibility Matrix — PASS.

Review threads were empty before merge. The initial Architecture Guards failure was limited to JavaScript lint in the new visibility helper; commit `98cc8317...` removed nested ternaries/Prettier violations without changing behavior, then all six workflows passed.

### Remaining Taxonomy Runtime Gap Closure

Issue #474 remains open. Remaining accepted families include broader help/search/reset behavior, provider selector/health UX, capabilities/effective-map UX, default-term and bounded term-query UX, rewrite/REST collision diagnostics, portability/CPT UI compatibility mapping, and separately safety-gated taxonomy-key migration planning. No `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` claim is authorized yet.

## Owner-directed ten-worker topology

Supervisor Issue #492 established ten distinct non-overlapping module lanes. Surface 2 remains the only runtime implementation lane; Surfaces 10–18 remain planning/contract-only.

1. **Worker-01 — Taxonomy / Surface 2 / Issue #474** — PR #503 promoted; broader runtime-gap program remains open.
2. **Worker-02 — Dashboard Widgets / Surface 10 / Issue #493** — Bank is already `BANK_REVIEWED` with 123 records and zero unresolved. Branch now contains a 123-record projection plan, Essential/Advanced/Expert UX contract and no-runtime-baseline gap matrix. Machine 123-record Atomic Option Contract remains the next gate.
3. **Worker-03 — Admin Menu / Surface 11 / Issue #494** — main Bank remains `UNSEEDED / 0`; branch contains evidence-entry audit, Bank seed plan and an 8-record normalized `BANK_SURFACE_SEEDED` candidate shard. Records remain unreviewed.
4. **Worker-04 — Settings Pages / Surface 12 / Issue #495** — main Bank remains `UNSEEDED / 0`; branch contains evidence-entry audit, Bank seed plan and an 8-record normalized seed candidate. Records remain unreviewed.
5. **Worker-05 — Frontend Dashboard / Surface 13 / Issue #496** — main Bank remains `UNSEEDED / 0`; branch contains evidence-entry audit, Bank seed plan and an 8-record normalized seed candidate. Records remain unreviewed.
6. **Worker-06 — User Profile / Surface 14 / Issue #497** — main Bank remains `UNSEEDED / 0`; branch contains evidence-entry audit, Bank seed plan and an 8-record normalized seed candidate. Records remain unreviewed.
7. **Worker-07 — Membership / Surface 15 / Issue #498** — main Bank remains `UNSEEDED / 0`; branch contains evidence-entry audit, Bank seed plan and an 8-record normalized seed candidate. Records remain unreviewed; payment execution is not authorized.
8. **Worker-08 — Builder Widgets / Surface 16 / Issue #499** — main Bank remains `UNSEEDED / 0`; branch contains evidence-entry audit, Bank seed plan and an 8-record normalized seed candidate. Records remain unreviewed.
9. **Worker-09 — Forms & Workflows / Surface 17 / Issue #500** — main Bank remains `UNSEEDED / 0`; branch contains evidence-entry audit, Bank seed plan and an 8-record normalized seed candidate. Records remain unreviewed; workflow execution is not authorized.
10. **Worker-10 — Cron / Surface 18 / Issue #501** — main Bank remains `UNSEEDED / 0`; branch contains evidence-entry audit, Bank seed plan and an 8-record normalized seed candidate. Records remain unreviewed; WP-Cron is not represented as guaranteed wall-clock execution.

Branch-local seed candidates do **not** promote `config/product/options-bank-progress.json` on main. Each requires native/market evidence, semantic/policy review and a reviewed worker PR before Supervisor may promote Bank lifecycle truth.

## Planning-worker constraints

Workers 02–10 may produce Bank/Atomic audits, normalized Bank records, schema-valid contracts where prerequisites support them, reviewed UX contracts and runtime gap matrices. They may not edit Supervisor-owned README/CHECKPOINT/queue directly; implement runtime code; promote runtime/product-parity certification; perform destructive provider/database/content/user mutation; or deploy/release.

## Product truth

- Accepted structural scope: **56 / 56 Exhaustive**.
- Multisite planning: **56 / 56**.
- AI Prompt planning: **56 / 56**.
- README closeout must remain **56 / 56 modules listed**.
- Current authoritative queue anchor: `6ee2a39ff84bdcbb0584efd03a9db4bd620a6a35`.

## Next work

1. Finish Supervisor Issue #504 closeout and preserve README 56/56.
2. Worker-02: deterministically map all 123 reviewed Dashboard Widgets Bank records to a schema-valid Atomic Option Contract with `missing=0`, `unclassified=0` before any lifecycle promotion.
3. Workers 03–10: add current native/platform and market evidence to their branch-local seed candidates; do not call them `BANK_REVIEWED` until zero unresolved is proven.
4. Worker-01 / Issue #474: after shared-truth closeout, continue the next accepted Taxonomy gap without destructive key migration or certification claims.

Repository evidence overrides conversational memory.
