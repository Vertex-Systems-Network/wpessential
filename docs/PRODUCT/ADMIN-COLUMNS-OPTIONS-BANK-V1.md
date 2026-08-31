# WPEssential — Admin Columns Options Bank Lifecycle V1

Status: **SURFACE_WORK / RESEARCH_COMPLETE / CERTIFICATION_INTEGRATION_BLOCKED**  
Surface: **8 — Admin Columns (`columns`)**  
Snapshot: **2026-09-01**  
Original base SHA: **`eb61c6bfe9d89af949d70e89ba685597d58e2663`**  
Synced main SHA: **`88e5e90b273ddc61b4a0e2e36249c541576fc8fc`**  
Writer branch: **`planning/options-bank-columns-seed-v1`**

## 1. Work-mode resolution

Detected work mode: `SURFACE_WORK`.

Repository evidence establishes Surface 8 as canonical key `columns`. No approved numbered Admin Columns implementation work package or competing plan exists. This work therefore continues the accepted 56-surface Master Options Bank program.

No `P<phase>-M<milestone>-WP<work-package>-T<task>` ID is recorded for this Surface 8 Bank work. None is invented.

The branch was created from explicit main `eb61c6bfe9d89af949d70e89ba685597d58e2663`. During work, main advanced through the Relations market-audit and Bank Review certifications. The branch was synchronized without force/rebase through two-parent merge commits, preserving both histories; the latest integrated upstream head for this snapshot is `88e5e90b273ddc61b4a0e2e36249c541576fc8fc`.

## 2. Canonical ownership and dependency boundary

Canonical owner:

- Surface 8 owns **list-table column/view/filter/edit presentation definitions**.

Shared required contracts:

- Data Source;
- Renderer;
- Policy.

Important peer integrations:

- Surface 1 CPT;
- Surface 2 Taxonomy;
- Surface 3 Fields;
- Surface 4 Relations;
- Surface 6 Query;
- Surface 7 Custom Tables;
- Surface 28 Media;
- Surface 30 Roles;
- Surface 34 Search;
- Surface 51 Content Order.

Hard peer boot dependencies: none.

Forbidden coupling:

- ownership of displayed source data;
- private query/search engine;
- direct peer table/class bypass;
- visibility treated as authorization;
- arbitrary executable PHP configuration;
- per-row N+1 or remote fan-out without a bounded provider contract.

## 3. Governance audit

Authoritative repository material reviewed before candidate construction included:

- root `AGENTS.md`;
- `CONTRIBUTING.md`;
- current `CHECKPOINT.md`;
- `DEVELOPMENT-CONSENT.md`;
- `docs/PROJECT-STATE-AND-ADOPTION.md`;
- `docs/APPROVAL-LEDGER.md`;
- `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`;
- `docs/QUALITY-GATES.md`;
- `docs/RELEASE-INCIDENT-RECOVERY-GOVERNANCE.md`;
- canonical ownership/no-bypass/dependency registries;
- `config/product/competitor-parity-surfaces.json`;
- `config/product/options-bank-progress.json`;
- Options Bank schemas and existing Fields/Relations lifecycle artifacts;
- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`;
- `docs/PRODUCT/ATOMIC-OPTION-CONTRACTS-WAVE2-EXPERIENCE.md`;
- `docs/MODULES/CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md`;
- `docs/UI/ADMIN-INFORMATION-ARCHITECTURE-V2-56-SURFACES.md`.

No closer-scoped `AGENTS.md` was found under the Surface 8 working paths.

## 4. Native research

WordPress-native adapter primitives rechecked for Surface 8 include:

- `manage_{$screen->id}_columns`;
- `manage_posts_columns`;
- `manage_{$post_type}_posts_columns`;
- `manage_{$post->post_type}_posts_custom_column`;
- `manage_pages_custom_column`;
- `manage_media_columns`;
- `manage_media_custom_column`;
- `manage_users_custom_column`;
- `manage_{$this->screen->taxonomy}_custom_column`;
- `manage_comments_custom_column`;
- `wpmu_users_columns`;
- `manage_users-network_custom_column`;
- `manage_{$this->screen->id}_sortable_columns`;
- `restrict_manage_posts`;
- `pre_get_posts`;
- `get_hidden_columns` / `default_hidden_columns` / `hidden_columns`;
- `list_table_primary_column`.

Conclusions:

1. WordPress supplies list-table extension hooks, hidden preferences, sortable metadata, primary-column behavior and target-specific render/filter integration.
2. WordPress does not provide a reusable named column-set/view definition engine.
3. Sorting/filtering must alter canonical backend query semantics before pagination; rendered HTML sorting is not acceptable.
4. `WP_List_Table` and `_get_list_table()` are private/subject to change and are not canonical WPE architecture dependencies.
5. List-table visibility is not source authorization.

Machine artifact:

- `config/product/options-bank-audits/columns-native-wordpress.json`

Surface-local executable contract:

- `tests/Smoke/options-bank-columns-native-audit-contract.php`

Native research dispositions: **29 total / 0 unresolved**. The certificate remains `NATIVE_AUDIT_IN_PROGRESS` because canonical progress and shared Composer/CI registration/exact-head execution of the Surface 8 contract are integrator-owned.

## 5. Market research

Official current product documentation was checked for:

- Admin Columns Pro;
- Meta Box Admin Columns;
- JetEngine Admin Columns/CCT/Relations.

Research document:

- `docs/PRODUCT/ADMIN-COLUMNS-MARKET-AUDIT-V1.md`

Machine candidate:

- `config/product/options-bank-audits/columns-market-ecosystem.json`

Surface-local executable contract:

- `tests/Smoke/options-bank-columns-market-audit-contract.php`

The upstream Relations certification on main `8ed1c0389ef314c79a60a6808d232ef625de7b25` generalized `config/product/options-bank-market-audit.schema.json`; the former Fields-only schema blocker is therefore resolved.

Surface 8 machine market coverage now records:

- 3 primary providers;
- 1 specialist provider;
- 6 required capability families;
- 15 family mappings;
- 57 Bank record references;
- 4 explicit extra dispositions;
- 0 unresolved research items.

Its status remains `MARKET_AUDIT_IN_PROGRESS` because the Surface 8 contract is not yet registered/executed by the shared exact-head smoke graph. The existing generic market-audit smoke contract remains Fields-specific and Relations has a dedicated Surface 4 gate.

## 6. Options Bank candidate

Surface-local shards:

| Shard | Records | Purpose |
|---|---:|---|
| `columns.json` | 60 | view/target/common controls/native adapter primitives |
| `columns--sources-formatting.json` | 46 | source bindings, display/text/link formats, safe provider extension |
| `columns--sorting-filtering-editing.json` | 46 | current-market sort/filter/search/edit/segment interactions |
| `columns--export-performance-portability.json` | 42 | current-market export/performance diagnostics/portability/adapters |
| `columns--wpe-exceed-market-v1.json` | 20 | future-only WPE exceed/safety guarantees |
| **Total** | **214** | **unique IDs and unique option paths** |

The initial candidate declared the export/performance shard as 39 records. Exact-head CI correctly detected that the array contained 42; the shard metadata and all downstream Surface 8 totals are now reconciled to **214**.

Candidate policy characteristics:

- 0 `UNREVIEWED` records;
- 1 explicit `REJECTED_UNSAFE` record for arbitrary PHP configuration;
- 20 WPE-exceed records, all `WPE_FUTURE / WPE_EXCEED / P1_EXCEED`;
- no duplicate IDs;
- no duplicate option paths.

This surface writer does not edit shared `config/product/options-bank-progress.json`; canonical global progress remains integrator-owned.

## 7. Ownership / duplicate resolution

Resolved no-bypass decisions:

- source values are references; Data Source/entity owners retain record truth;
- relation display/filter references Surface 4;
- reusable query AST/filter/sort execution remains Surface 6;
- full-text relevance/indexing remains Surface 34;
- Field Schema/editor/validation remains Surface 3/shared Field Schema;
- Role definitions remain Surface 30 and authorization remains shared Policy;
- persistent manual ordering remains Surface 51;
- WooCommerce data is accessed through A01/supported Woo APIs;
- inline/bulk/quick edit submits typed mutation intent to the canonical source owner;
- custom callbacks are registered/allowlisted provider descriptors, never arbitrary PHP text;
- hidden columns, role/user conditions and view assignment never grant source access.

No Surface 8 semantic alias/effective-derivation entry is required by the current candidate.

## 8. Native Audit lifecycle gate

Research coverage in `columns-native-wordpress.json`:

- items: 29;
- Bank mappings: 16;
- provider mappings: 4;
- system runtime: 4;
- out of surface: 4;
- core internal: 1;
- unresolved: 0.

The Surface 8-specific validator now exists at `tests/Smoke/options-bank-columns-native-audit-contract.php`. It validates the canonical Surface 8 identity, all 214 Bank records, primary Developer.WordPress.org evidence, disposition/mapping ownership rules, exact coverage counters and zero unresolved research. It accepts the lifecycle-safe transition from `NATIVE_AUDIT_IN_PROGRESS` to `NATIVE_AUDITED`; it does not perform that promotion itself.

Canonical `NATIVE_AUDITED` is not claimed because:

1. shared progress has not been promoted for Surface 8;
2. the Surface 8 contract is not yet registered/executed by the shared exact-head smoke graph;
3. shared Composer/CI/progress wiring is integrator-owned.

## 9. Market Audit lifecycle gate

Market research is normalized into `columns-market-ecosystem.json` and has zero unresolved research dispositions.

The Surface 8-specific validator now exists at `tests/Smoke/options-bank-columns-market-audit-contract.php`. It validates the reviewed provider rosters, six-family disposition matrix, evidence URLs, Bank references, canonical out-of-surface ownership, exact coverage counters and zero unresolved research. It accepts the lifecycle-safe transition from `MARKET_AUDIT_IN_PROGRESS` to `MARKET_AUDITED`; it does not perform that promotion itself.

The previous schema blocker is resolved upstream. Remaining certification blockers are:

1. the Surface 8 contract is not yet registered/executed by the shared exact-head smoke graph;
2. no shared progress promotion exists for Surface 8.

Therefore canonical `MARKET_AUDITED` is not claimed.

## 10. Bank Review

Review record:

- `config/product/options-bank-reviews/columns-bank-review-v1.json`

Surface-local executable contract:

- `tests/Smoke/options-bank-columns-review-contract.php`

Decision: **`REVIEW_BLOCKED`**.

The Bank Review validator is lifecycle-safe: while blocked it requires real unresolved integration gates; a later `BANK_REVIEWED` state is accepted only when native and market audits are certified, review unresolved is zero, canonical progress reports 214 records at `BANK_REVIEWED`, semantic expectations still agree with repository truth, and policy gates remain closed.

The product research has no unresolved product/ownership decision. The review has two unresolved shared certification dependencies:

1. canonical Surface 8 progress plus exact-head native-audit certification;
2. shared registration/execution of the market-audit/Bank-Review contracts and resulting progress promotion.

The review record count is **214**, matching the corrected five-shard Bank inventory.

## 11. UX projection

**NOT ENTERED / BLOCKED BY BANK REVIEW.**

The canonical IA already reserves:

`WPEssential → Data & Intelligence → Admin Columns`

with primary screens `Column Sets, Columns, Sort/Filter, Inline/Bulk Edit, Export, Conditions, Performance`.

A final UX projection must not be frozen until `BANK_REVIEWED` is valid on an exact certified head.

## 12. Implementation contract

**NOT ENTERED / BLOCKED BY BANK REVIEW.**

Existing exhaustive/atomic inventories remain planning inputs only. Runtime implementation must not start from this branch as though the Bank were certified.

When unblocked, the implementation contract must bind:

- canonical Definition persistence/revisions;
- target adapter registry;
- Data Source + Query backend execution;
- Field Schema-derived editors;
- Policy authorization;
- Renderer;
- Job Service for large bulk/export work;
- Audit/Observability;
- no-N+1 batch hydration;
- scoped optional assets;
- supported WordPress list-table integration and multisite scope.

## 13. Integration Requirements

### IR-COLUMNS-001 — canonical progress promotion — OPEN
The designated integrator must recompute shared Options Bank truth from then-current main, register Surface 8 record count as **214**, and promote only lifecycle states whose machine evidence is valid. Global totals must be derived at integration time because other surfaces may merge first.

### IR-COLUMNS-002 — generalized market-audit schema — RESOLVED
Resolved upstream by main `8ed1c0389ef314c79a60a6808d232ef625de7b25`. The shared schema now supports canonical surfaces 1–56 without weakening existing Fields validation.

### IR-COLUMNS-003 — exact-head Surface 8 gates — PARTIALLY RESOLVED
Surface-local native, market and Bank Review validators now exist:

- `tests/Smoke/options-bank-columns-native-audit-contract.php`;
- `tests/Smoke/options-bank-columns-market-audit-contract.php`;
- `tests/Smoke/options-bank-columns-review-contract.php`.

The designated integrator must register and execute these contracts through the shared Composer/CI smoke graph without weakening Fields/Relations gates, then promote only from the resulting exact certified head.

### IR-COLUMNS-004 — dashboard/shared docs — OPEN
After canonical progress is integrated, update shared README/status/dashboard truth from current derived totals. Surface-local workers must not race those files.

### IR-COLUMNS-005 — lifecycle promotion order — OPEN
After IR-COLUMNS-001 and IR-COLUMNS-003 are satisfied, promote strictly in order:

`BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED`

Only then enter/finalize UX projection and implementation-contract work.

## 14. Recovery classification

Planning/data/test-contract change only. No production deployment, provider mutation, database migration, release or destructive operation is performed.

Recovery class: `SIMPLE_ROLLBACK` — revert/remove the Surface 8 planning/test artifacts before integration if review rejects them.

## 15. Current safe state

The branch is a synchronized, research-complete Surface 8 candidate with machine native/market evidence and lifecycle-safe Surface 8 certification contracts, but it is **not a certified completed Options Bank lifecycle**.

Raw local execution of the three new contracts is not claimed: the available execution sandbox could not resolve `github.com` to clone the repository. Exact-head repository CI remains authoritative; existing CI syntax scanning will cover the scripts, while normal shared execution of the contracts requires integrator registration.

Safe next action:

1. verify branch against latest main again;
2. observe exact-head applicable CI and fix only Surface 8-owned defects;
3. keep the PR draft while shared certification dependencies remain open;
4. hand open Integration Requirements to the designated integrator;
5. after shared certification is valid, promote lifecycle in order and only then enter UX projection / implementation contract.
