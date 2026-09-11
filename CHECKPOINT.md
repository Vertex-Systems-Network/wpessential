# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-10 UTC**  
Canonical audited base anchor: **`main @ d9204049a163bd79e41ad989af6d66c87e5388ae`**  
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

Promoted bounded slices now include merged PRs **#478, #479, #480, #483, #486, #489, #503, #506, #512 and #515**.

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

### PR #512 — default-term + bounded object-term query authoring — PROMOTED

Merged as **`d2594d3be0e5c9c0b3968fd9693f2e1538211263`** from exact head **`c06001d738b50faa5765c6f7f30d6085e8a19535`**, with `behind=0`, exactly two changed files and no unresolved review threads.

Promoted behavior:

- Expert-tier authoring for canonical `default_term` name, slug and description;
- bounded `sort` authoring without introducing a private persistent term-ordering engine;
- allowlisted object-term query authoring for `args.orderby`, `args.order` and `args.fields` using the existing server-authoritative projector allowlists;
- blank/default controls remove authored overrides rather than materializing guessed defaults;
- hydration/reset semantics preserve the existing payload model and expose the promoted controls through Find Setting tier reveal/focus;
- invalid partial default-term authoring is rejected by existing server validation (`default_term.name` required);
- packaged browser evidence covers authored/effective diagnostics, reset/removal semantics, invalid state and axe accessibility;
- no provider execution, rewrite/REST expansion, destructive term mutation, key migration, deployment or certification claim was introduced.

Exact head `c06001d7...` passed all three applicable workflows before merge:

1. Architecture Guards **#1207 — PASS**;
2. Browser E2E Accessibility **#295 — PASS**;
3. Distributable Package **#740 — PASS**.

Earlier current-lane failures were repaired rather than bypassed: a Prettier-only Architecture failure and a Playwright visibility assertion against a closed diagnostics disclosure. The final test opens the disclosure through the user-facing interaction before retaining the same JSON assertion.

### PR #515 — redacted runtime-provider catalog health — PROMOTED

Merged as **`d9204049a163bd79e41ad989af6d66c87e5388ae`** from exact head **`b5753db7963fbf963e7ddbeaf6fcc6ffbeeba29d`**, with `behind=0`, exactly two changed files and no unresolved review threads.

Promoted behavior:

- adds a read-only `TaxonomyRuntimeProviderRegistry::catalog()` inventory for the accepted `rest_controller`, `meta_box`, `meta_box_sanitize` and `term_count` slots;
- exposes only deterministic provider IDs plus a boolean current-runtime availability state;
- keeps implementation class/callback descriptors redacted from catalog output;
- does not execute a provider while computing the catalog;
- preserves the existing last-responsible runtime `apply()` resolution and provider-ID persistence semantics unchanged;
- focused PHPUnit evidence proves deterministic ordering, JSON-safe output, availability reporting and descriptor redaction;
- this is a backend prerequisite only: no raw provider-ID authoring field or admin provider selector UI was introduced.

Exact head `b5753db7...` passed all six applicable workflows before merge:

1. Architecture Guards **#1208 — PASS**;
2. PHP Quality Toolchain **#635 — PASS**;
3. Taxonomy Runtime **#59 — PASS**;
4. CPT Runtime **#112 — PASS**;
5. Platform Compatibility Matrix **#816 — PASS**;
6. Distributable Package **#741 — PASS**.

Review threads were empty and the PR was mergeable before squash merge. The cross-surface CPT and Platform regressions are material because the changed registry is a shared WordPress registration component. No lifecycle/certification promotion follows from this green evidence.

### Remaining Taxonomy Runtime Gap Closure

Issue #474 remains open. Remaining accepted families include controlled provider selector UX using only the redacted registered catalog, capabilities/effective-map UX, rewrite/REST collision diagnostics, portability/CPT UI compatibility mapping, and separately safety-gated taxonomy-key migration planning. No `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` claim is authorized yet.

## Surface 10 — Dashboard Widgets planning closure

Issue **#493** is completed through merged PR **#508**. The merged planning evidence contains:

- reviewed Options Bank `BANK_REVIEWED` with **123** records and zero unresolved Bank review items;
- **18 normalized Atomic Option Contracts**;
- deterministic five-shard **123/123 source projection** with reviewed dispositions;
- reviewed Essential/Advanced/Expert UX contract;
- no-runtime-baseline runtime gap matrix and entry audit;
- unsafe raw script/arbitrary PHP inputs rejected and cross-surface ownership preserved.

PR #508 was reconciled to `main @ cdc4454b8223eca0e499814f38617081a8e32f88` without force. Exact reconciled head **`ae0416783370adcf2463c7644facf4a0fe0c8f56`** passed:

1. Architecture Guards **#1202 — PASS**;
2. Platform Compatibility Matrix **#813 — PASS**.

Review threads were empty before squash merge as **`7e9c5af1bf6c90b934f1c5587b263c429f758558`**. Planning lifecycle is promoted to **`UX_CONTRACT_COMPLETE`** by merged Supervisor Issue #510 / PR #511. Dashboard Widgets runtime remains **unpromoted and unauthorized**; no runtime/product-parity/deployment claim follows from this closure.

## Owner-directed ten-worker topology

Supervisor Issue #492 established ten distinct non-overlapping module lanes. Surface 2 remains the only runtime implementation lane. Surface 10 has completed its planning/contract gate; Surfaces 11–18 remain planning/contract-only.

1. **Worker-01 — Taxonomy / Surface 2 / Issue #474** — PR #515 promoted; broader runtime-gap program remains OPEN and provider selector UI is still outstanding.
2. **Worker-02 — Dashboard Widgets / Surface 10 / Issue #493** — **planning/contract gate complete** via merged PR #508; lifecycle `UX_CONTRACT_COMPLETE`; runtime remains unauthorized and no new runtime lane is opened.
3. **Worker-03 — Admin Menu / Surface 11 / Issue #494** — main Bank remains `UNSEEDED / 0`; 8-record branch seed remains unreviewed. Native/menu/admin-bar evidence was expanded on branch head `ed2df4e182d8c1f850c2f730d301bb1f9434c258`; no lifecycle promotion.
4. **Worker-04 — Settings Pages / Surface 12 / Issue #495** — main Bank remains `UNSEEDED / 0`; 8-record branch seed remains unreviewed. Site/network/user persistence evidence was expanded on branch head `9642872f1876e84b7af7a83492ce62b7eab25f20`; no lifecycle promotion.
5. **Worker-05 — Frontend Dashboard / Surface 13 / Issue #496** — main Bank remains `UNSEEDED / 0`; 8-record branch seed remains unreviewed. Native routing/auth ownership evidence was expanded on branch head `70c6bc4eed72db86a839f015f06c53fe02990109`; no lifecycle promotion.
6. **Worker-06 — User Profile / Surface 14 / Issue #497** — main Bank remains `UNSEEDED / 0`; 8-record branch seed remains unreviewed. User-meta/profile lifecycle and authorization evidence was expanded on branch head `076f898f003d4fdfe63ca3ad4e43b5b87e38b64d`; no lifecycle promotion.
7. **Worker-07 — Membership / Surface 15 / Issue #498** — main Bank remains `UNSEEDED / 0`; 8-record branch seed remains unreviewed. User/role plus membership plan/enrollment/payment-boundary evidence was expanded on branch head `5d6bab5899e86ebc7d1a2c7d4f64bf21a9d41aee`; payment execution remains unauthorized.
8. **Worker-08 — Builder Widgets / Surface 16 / Issue #499** — main Bank remains `UNSEEDED / 0`; 8-record branch seed remains unreviewed. Block metadata/context/dynamic-render and adapter-boundary evidence was expanded on branch head `d5631be6157ddcf284cf7aa319fb0b1e5206e459`; runtime adapter registration remains unauthorized.
9. **Worker-09 — Forms & Workflows / Surface 17 / Issue #500** — main Bank remains `UNSEEDED / 0`; 8-record branch seed remains unreviewed. Submission authorization/validation/lifecycle evidence was expanded on branch head `11144f3931e1913e1136135b6167b293fbf588ed`; workflow execution remains unauthorized.
10. **Worker-10 — Cron / Surface 18 / Issue #501** — main Bank remains `UNSEEDED / 0`; 8-record branch seed remains unreviewed. Hook+args event identity, duplicate scheduling and timing/provider truth were expanded on branch head `8421c401b2c2723952bda787a413198f9b53cab9`; WP-Cron is explicitly not represented as guaranteed wall-clock execution.

Workers 03–10 were cleanly reconciled to `main @ 42303a97...` before these evidence writes and each remained limited to exactly four surface-local planning artifacts. PR #515 then moved main to `d9204049...`, so Worker-01 and Workers 03–10 must be reconciled again without force after this Supervisor shared-truth closeout before their next writes or PR promotion. Branch-local seed candidates do **not** promote main lifecycle truth until reviewed PR evidence merges and Supervisor reconciliation is completed.

## Planning-worker constraints

Workers 03–10 may produce Bank/Atomic audits, normalized Bank records, schema-valid contracts where prerequisites support them, reviewed UX contracts and runtime gap matrices. They may not edit Supervisor-owned README/CHECKPOINT/queue directly; implement runtime code; promote runtime/product-parity certification; perform destructive provider/database/content/user mutation; or deploy/release.

## Product truth

- Accepted structural scope: **56 / 56 Exhaustive**.
- Multisite planning: **56 / 56**.
- AI Prompt planning: **56 / 56**.
- README closeout must remain **56 / 56 modules listed**.
- Surface 2 machine planning lifecycle remains **`UX_CONTRACT_COMPLETE`**; PRs #512 and #515 add bounded runtime-gap implementation evidence but do not promote certification.
- Surface 10 machine planning lifecycle remains **`UX_CONTRACT_COMPLETE`** after Issue #493 / merged PR #508 and Supervisor Issue #510 / PR #511.
- Current shared-truth reconciliation: **Issue #516 / supervisor branch `supervisor/taxonomy-provider-catalog-reconciliation-v1`**.
- Current audited main anchor for this reconciliation: **`d9204049a163bd79e41ad989af6d66c87e5388ae`**.

## Next work

1. Complete Supervisor Issue #516 by reconciling README, CHECKPOINT and coordination queue to merged PR #515 while keeping Taxonomy lifecycle/certification boundaries unchanged.
2. Reconcile Worker-01 and Workers 03–10 deterministic branches to exact latest main without force after #516 promotion; Dashboard Widgets remains completed planning truth only.
3. Workers 03–10: continue native/platform + market evidence expansion; main Bank is still `UNSEEDED / 0` for Surfaces 11–18, so no `BANK_REVIEWED` or option-contract promotion is valid until zero unresolved is proven and reviewed/merged.
4. Worker-01 / Issue #474: after Issue #516 shared-truth promotion, continue the controlled provider selector/health UX using only the registered redacted catalog; do not expose raw callbacks/classes or destructive key migration.
5. Dashboard Widgets has no authorized runtime lane; any runtime transition requires a later exact-main Supervisor gate.

Repository evidence overrides conversational memory.
