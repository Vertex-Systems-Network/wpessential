# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-09-10 UTC**  
Canonical audited base anchor: **`main @ d2594d3be0e5c9c0b3968fd9693f2e1538211263`**  
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

Promoted bounded slices now include merged PRs **#478, #479, #480, #483, #486, #489, #503, #506 and #512**.

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

### Remaining Taxonomy Runtime Gap Closure

Issue #474 remains open. Remaining accepted families include provider selector/health UX, capabilities/effective-map UX, rewrite/REST collision diagnostics, portability/CPT UI compatibility mapping, and separately safety-gated taxonomy-key migration planning. No `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` claim is authorized yet.

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

1. **Worker-01 — Taxonomy / Surface 2 / Issue #474** — PR #512 promoted; broader runtime-gap program remains OPEN.
2. **Worker-02 — Dashboard Widgets / Surface 10 / Issue #493** — **planning/contract gate complete** via merged PR #508; lifecycle `UX_CONTRACT_COMPLETE`; runtime remains unauthorized and no new runtime lane is opened.
3. **Worker-03 — Admin Menu / Surface 11 / Issue #494** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Original evidence head `06952905...` is preserved and branch reconciled non-force to `main @ e45f21d4...` as `f61a4edd738a9a4ab731f5cfadb46467b0fe1c45` before PR #512 moved main.
4. **Worker-04 — Settings Pages / Surface 12 / Issue #495** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Original evidence head `4a7e6924...` is preserved and branch reconciled non-force as `5e3a76f8ea6ad1e9087a350e956717d26bfbf9fb`.
5. **Worker-05 — Frontend Dashboard / Surface 13 / Issue #496** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Original evidence head `75fccc4c...` is preserved and branch reconciled non-force as `b9e311faa07062e889aab8a939702233b0c2af11`.
6. **Worker-06 — User Profile / Surface 14 / Issue #497** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Original evidence head `1be3ed32...` is preserved and branch reconciled non-force as `d0fc7b87d078cb89bc5f8ca1a8e3a9a7d10fb42b`.
7. **Worker-07 — Membership / Surface 15 / Issue #498** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Original evidence head `23e143ab...` is preserved and branch reconciled non-force as `b720c516fc7fdc1137c89664ee4af6002c0630cf`; payment execution remains unauthorized.
8. **Worker-08 — Builder Widgets / Surface 16 / Issue #499** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Original evidence head `268f41a6...` is preserved and branch reconciled non-force as `c0c0d0bcfb0ca4f0c57caf0d50464c720d8e8c1c`.
9. **Worker-09 — Forms & Workflows / Surface 17 / Issue #500** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Original evidence head `deb5aba4...` is preserved and branch reconciled non-force as `5ce6a6288a57baca46801aea75e9c6dad7e91b99`; workflow execution remains unauthorized.
10. **Worker-10 — Cron / Surface 18 / Issue #501** — main Bank remains `UNSEEDED / 0`; branch seed remains unreviewed. Original evidence head `cfd76786...` is preserved and branch reconciled non-force as `ec21d742c5d7104571ab8b44ed5afd2ee8b5351e`; WP-Cron is explicitly not represented as guaranteed wall-clock execution.

PR #512 moved main after those reconciliations, so Workers 03–10 must merge/reconcile current `main @ d2594d3b...` again without force immediately before their next writes or PR promotion. Branch-local seed candidates do **not** promote main lifecycle truth until reviewed PR evidence merges and Supervisor reconciliation is completed.

## Planning-worker constraints

Workers 03–10 may produce Bank/Atomic audits, normalized Bank records, schema-valid contracts where prerequisites support them, reviewed UX contracts and runtime gap matrices. They may not edit Supervisor-owned README/CHECKPOINT/queue directly; implement runtime code; promote runtime/product-parity certification; perform destructive provider/database/content/user mutation; or deploy/release.

## Product truth

- Accepted structural scope: **56 / 56 Exhaustive**.
- Multisite planning: **56 / 56**.
- AI Prompt planning: **56 / 56**.
- README closeout must remain **56 / 56 modules listed**.
- Surface 2 machine planning lifecycle remains **`UX_CONTRACT_COMPLETE`**; PR #512 adds a bounded runtime-gap implementation slice but does not promote certification.
- Surface 10 machine planning lifecycle remains **`UX_CONTRACT_COMPLETE`** after Issue #493 / merged PR #508 and Supervisor Issue #510 / PR #511.
- Current shared-truth reconciliation: **Issue #513 / supervisor branch `supervisor/taxonomy-runtime-defaults-reconciliation-v1`**.
- Current audited main anchor for this reconciliation: **`d2594d3be0e5c9c0b3968fd9693f2e1538211263`**.

## Next work

1. Complete Supervisor Issue #513 by reconciling README, CHECKPOINT and coordination queue to merged PR #512 while keeping Taxonomy lifecycle/certification boundaries unchanged.
2. Reconcile Worker-01 and Workers 03–10 deterministic branches to exact latest main without force before further writes; Dashboard Widgets remains completed planning truth only.
3. Workers 03–10: continue native/platform + market evidence expansion; main Bank is still `UNSEEDED / 0` for Surfaces 11–18, so no `BANK_REVIEWED` or option-contract promotion is valid until zero unresolved is proven and reviewed/merged.
4. Worker-01 / Issue #474: after Issue #513 shared-truth promotion, continue the next bounded Taxonomy runtime gap without destructive key migration or certification claims.
5. Dashboard Widgets has no authorized runtime lane; any runtime transition requires a later exact-main Supervisor gate.

Repository evidence overrides conversational memory.
