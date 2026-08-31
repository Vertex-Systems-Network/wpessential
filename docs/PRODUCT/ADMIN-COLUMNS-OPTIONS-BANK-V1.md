# WPEssential — Admin Columns Options Bank Lifecycle V1

Status: **SURFACE_WORK / PARTIALLY_COMPLETE — INTEGRATION BLOCKED**  
Surface: **8 — Admin Columns (`columns`)**  
Snapshot: **2026-09-01**  
Base SHA: **`eb61c6bfe9d89af949d70e89ba685597d58e2663`**  
Writer branch: **`planning/options-bank-columns-seed-v1`**

## 1. Work-mode resolution

Detected work mode: `SURFACE_WORK`.

Repository evidence on the base SHA shows:
- Surface 8 is canonical key `columns`;
- current global Options Bank progress is `UNSEEDED` with 0 records;
- there is no active Admin Columns Bank branch, issue or approved numbered implementation work package;
- the shared Platform Foundation is already certified for business-module handoff by the current checkpoint;
- the repository already has an exhaustive Admin Columns specification and atomic option inventory, so this work continues the accepted 56-surface Master Options Bank program rather than creating a competing product plan.

No `P<phase>-M<milestone>-WP<work-package>-T<task>` ID is recorded for this Surface 8 Bank work. None is invented here.

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
- Surface 51 Content Order.

Hard peer boot dependencies: none.

Forbidden coupling:
- owning displayed source data;
- private query/search engine;
- direct peer table/class bypass;
- visibility as authorization;
- arbitrary PHP configuration;
- per-row N+1/remote fan-out without a bounded provider contract.

## 3. Initial audit

Authoritative repository material reviewed before the candidate was built:
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

No closer-scoped `AGENTS.md` exists under `config/`, `config/product/`, `config/product/options-bank/`, `docs/`, or `docs/PRODUCT/` on the base SHA.

Existing product truth already required:
- multiple target list tables and named views;
- native/field/taxonomy/relation/query/media/status/computed/provider sources;
- typed display formats;
- backend sorting/filtering/search;
- saved segments/preferences;
- inline/quick/bulk editing;
- CSV export;
- conditional formatting/visibility;
- performance/no-N+1 diagnostics;
- portability and compatibility adapters.

## 4. Native research

Primary WordPress sources were rechecked on 2026-09-01.

Verified adapter primitives include:
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

Key native conclusions:
1. WordPress provides list-table extension hooks, per-screen hidden preferences, sortable metadata, a primary-column contract and target-specific render/filter integration.
2. WordPress does **not** supply a reusable WPE-style named column-set/view definition engine.
3. Sort/filter headers alone do not guarantee backend query correctness; the target/Data Source/Query adapter must alter the canonical query before pagination.
4. `WP_List_Table` and `_get_list_table()` are documented as private/subject to change; WPE must not build its architecture on direct private core coupling.
5. native screen visibility is not a source authorization model.

Machine artifact:
- `config/product/options-bank-audits/columns-native-wordpress.json`.

Its evidence set has zero unresolved research dispositions, but its status remains `NATIVE_AUDIT_IN_PROGRESS` because canonical shared progress and a Surface 8 audit gate are integrator-owned and are not updated by this worker.

## 5. Market research

Current official product documentation was checked for:
- Admin Columns Pro;
- Meta Box Admin Columns;
- JetEngine Admin Columns/CCT/Relations.

Research artifact:
- `docs/PRODUCT/ADMIN-COLUMNS-MARKET-AUDIT-V1.md`.

Market research confirms current parity expectations for views, custom-field/source presentation, sorting/filtering, inline/bulk/quick editing, CSV export, conditional formatting, portability and ecosystem integrations.

The market audit is **research-complete but machine-certification blocked** because `config/product/options-bank-market-audit.schema.json` on current `main` hard-codes `surface.id = 3` and `surface.key = fields`.

The active Relations market-audit branch currently points at the same base SHA and has not yet generalized that shared schema.

## 6. Options Bank candidate

Surface-local shards:

| Shard | Records | Purpose |
|---|---:|---|
| `columns.json` | 60 | view/target/common controls/native adapter primitives |
| `columns--sources-formatting.json` | 46 | source bindings, display/text/link formats, safe provider extension |
| `columns--sorting-filtering-editing.json` | 46 | current-market sort/filter/search/edit/segment interactions |
| `columns--export-performance-portability.json` | 39 | current-market export/performance diagnostics/portability/adapters |
| `columns--wpe-exceed-market-v1.json` | 20 | future-only WPE exceed/safety guarantees |
| **Total** | **211** | **unique IDs and unique option paths** |

Candidate policy characteristics:
- 0 `UNREVIEWED` records;
- 1 explicit `REJECTED_UNSAFE` record for arbitrary PHP configuration;
- 20 WPE-exceed records, all `WPE_FUTURE / WPE_EXCEED / P1_EXCEED`;
- no duplicate IDs;
- no duplicate option paths.

This branch does **not** edit `config/product/options-bank-progress.json`; therefore global canonical status remains unchanged until integration.

## 7. Ownership / duplicate resolution

Resolved no-bypass decisions:

- column source values are references; Data Source/entity owners retain record truth;
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

No Surface 8 semantic alias/effective-derivation entry is required in the shared semantic registry from the current candidate.

## 8. Native Audit lifecycle gate

Research disposition coverage in `columns-native-wordpress.json`:
- items: 29;
- Bank mappings: 16;
- provider mappings: 4;
- system runtime: 4;
- out of surface: 4;
- core internal: 1;
- unresolved: 0.

Certification is intentionally not claimed because:
1. global progress still says Surface 8 is `UNSEEDED / 0`;
2. existing machine audit tests are surface-specific and no Surface 8 exact-head audit contract is wired;
3. shared progress/test wiring is integrator-owned.

## 9. Market Audit lifecycle gate

Research coverage is complete enough for a Surface 8 provider certificate, but the shared market-audit JSON schema is currently Fields-specific.

Integration is required before a valid `columns-market-ecosystem.json` can be added and certified.

## 10. Bank Review

A blocked review record is supplied at:
- `config/product/options-bank-reviews/columns-bank-review-v1.json`.

Decision: `REVIEW_BLOCKED`.

The product research itself has no unresolved ownership decision. Review is blocked by shared certification infrastructure:
- Surface 8 shared progress has not been promoted;
- a Surface 8 native exact-head gate is not wired;
- the shared market-audit schema cannot validate Surface 8;
- therefore no `MARKET_AUDITED` machine certificate exists.

## 11. UX projection

**NOT ENTERED / BLOCKED BY BANK REVIEW.**

The canonical IA already reserves:
`WPEssential → Data & Intelligence → Admin Columns` with primary screens `Column Sets, Columns, Sort/Filter, Inline/Bulk Edit, Export, Conditions, Performance`.

A final UX projection must not be frozen until `BANK_REVIEWED` is valid on an exact certified head.

## 12. Implementation contract

**NOT ENTERED / BLOCKED BY BANK REVIEW.**

Existing exhaustive and atomic inventories remain planning inputs only. Runtime implementation must not start from this branch as though the Bank were certified.

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
- WordPress 7.1 list-table integration and multisite scope.

## 13. Integration Requirements

### IR-COLUMNS-001 — canonical progress promotion
The designated integrator must recompute shared Options Bank truth from the then-current `main`, set Surface 8 record count to **211**, and promote only to the lifecycle state whose machine evidence is valid. Do not hard-code global totals from this branch because other surface branches may merge first.

### IR-COLUMNS-002 — market-audit schema
Generalize or otherwise sanctionedly extend `config/product/options-bank-market-audit.schema.json` so Surface 8 can be validated without weakening the existing Fields contract.

### IR-COLUMNS-003 — exact-head Surface 8 gates
Add/wire shared machine gates for Surface 8 native audit, market audit and Bank Review, or generalize existing surface-specific gates under integrator ownership.

### IR-COLUMNS-004 — dashboard/shared docs
Only after canonical progress is integrated, update README/status/dashboard truth as required from the integrated current totals. The Surface 8 worker must not race those shared files.

### IR-COLUMNS-005 — lifecycle promotion order
After IR-COLUMNS-001..003, promote in order:
`BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED`,
with exact-head applicable CI at each certification boundary or a single integrated exact-head gate that proves all prerequisites.

## 14. Recovery / migration classification

Planning/data-file change only. No production deployment, provider mutation, database migration, release, or destructive operation is performed.

Recovery class for this branch: `SIMPLE_ROLLBACK` — remove/revert the Surface 8 planning artifacts before integration if review rejects them.

## 15. Current safe state

The branch is a Surface 8 candidate, not a certified lifecycle promotion.

Safe next action:
1. write/validate the local artifacts;
2. open a draft PR;
3. observe exact-head CI without weakening failures;
4. hand Integration Requirements to the designated integrator;
5. only then certify downstream lifecycle and enter UX/implementation planning.
