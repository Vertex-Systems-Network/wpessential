# Settings Pages — Market Audit V1

Status: `RESEARCH_COMPLETE / SUPERVISOR_INTEGRATION_PENDING`

Surface: 12 — Settings Pages  
Snapshot: 2026-09-12  
Parent: #713  
Worker issue: #715  
Base: `54e4e70095f74a6ebff15da83cc2bbf3d447a49c`  
Worker branch: `agent/settings-pages-market-audit-v1`

## 1. Scope and gate

This evidence-only audit reconciles all 17 current `NATIVE_AUDITED` Settings Pages records with current settings/options-framework products. It does not edit the Options Bank or central lifecycle state.

Canonical boundary: WordPress Settings/Options/User Meta APIs remain native storage/registration substrate. Reusable field/control schemas remain Fields-owned; secret material remains Vault-owned; ordinary settings import/export must never include plaintext provider credentials.

## 2. Current market evidence

### E1 — Meta Box / MB Settings Page
Official evidence:
- https://docs.metabox.io/extensions/mb-settings-page/
- https://docs.metabox.io/field-settings/

Verified capabilities: top-level/submenu settings pages; capability requirements; WordPress-native or left tabs; one/two-column layouts; meta-box sections; custom save messages/buttons/help tabs; Customizer projection; network-wide settings; configurable option name/storage; reusable field composition.

### E2 — Advanced Custom Fields Options Pages
Official evidence:
- https://www.advancedcustomfields.com/resources/options-page/
- https://www.advancedcustomfields.com/resources/acf-add-options-page/

Verified market pattern: registered global options pages/subpages backed by ACF field groups, custom menu placement/capability, option-page data storage and reusable field composition. This is evidence for settings-page composition, not for a new storage owner.

### E3 — Carbon Fields Theme / Network Options
Official evidence:
- https://docs.carbonfields.net/learn/containers/theme-options.html
- https://docs.carbonfields.net/learn/containers/network.html
- https://docs.carbonfields.net/learn/containers/condition-types.html

Verified capabilities: option containers; parent/menu placement, icon/position; field composition; capability/role/user/blog conditions; separate network container and network storage; multiple option pages.

## 3. Capability-family findings

1. **Page identity/navigation/capability and site/network scope are baseline parity.** E1–E3 all build on WordPress admin/menu/capability concepts.
2. **Tabs, panels, columns and conditional visibility are established market UX.** Meta Box explicitly supports tabs, left tabs, meta-box panels and one/two columns; Carbon Fields exposes conditional containers.
3. **Field composition is established but must remain Fields-owned in WPE.** Market products often bundle field registries with settings pages; WPE should compose canonical Fields rather than fork their schema.
4. **Storage ownership remains explicit.** Meta Box exposes option-name selection and network mode; Carbon Fields separates theme options and network containers. WPE’s site/network/user ownership records are therefore correct and should stay explicit.
5. **Inheritance/environment semantics are not consistently standardized.** Conditions exist, but a portable layered inheritance model is a WPE-level contract.
6. **Reset/migration/revision/conflict semantics are fragmented.** These should remain explicit WPE lifecycle/portability contracts instead of being inferred from generic option-framework convenience features.
7. **Vault references remain a stronger safety boundary than market frameworks.** Market frameworks may render password/API-key controls, but that is not evidence to serialize secrets in normal settings definitions or exports.

## 4. Record-by-record reconciliation

| Bank record | Market disposition | Evidence / later Supervisor action |
|---|---|---|
| `settings.page.identity` | KEEP native + market parity | E1–E3 register named settings/options pages. |
| `settings.page.navigation` | MARKET_EVIDENCED | E1/E3 expose parent, menu placement, icon/position. |
| `settings.page.capability` | KEEP native + market parity | E1/E2/E3 expose required capability/access gating. |
| `settings.page.scope` | KEEP native + market parity | E1 network mode and E3 network container prove multisite scope. |
| `settings.layout.tabs-sections` | MARKET_EVIDENCED | E1 explicitly supports tabs, left tabs and meta-box sections. |
| `settings.layout.columns` | MARKET_EVIDENCED | E1 supports one/two columns; E3 supports conditional containers. |
| `settings.storage.mode` | KEEP native + market parity | E1 configurable option storage; framework choices map to Options API semantics. |
| `settings.storage.autoload-defaults` | KEEP native / WPE expert policy | Market UI evidence does not supersede WordPress-native default/autoload truth. |
| `settings.storage.owner` | KEEP native + market parity | E1/E3 distinguish site/network storage; user-scoped values stay explicit references. |
| `settings.storage.inheritance` | KEEP_WPE_HARD | Conditions are market-evidenced, but portable inheritance/environment resolution remains WPE-owned. |
| `settings.lifecycle.reset` | KEEP_WPE_HARD | Provider reset UX is implementation-specific; retain explicit bounded reset semantics and authorization. |
| `settings.lifecycle.migration` | KEEP_WPE_HARD | No reviewed provider establishes a canonical versioned migration contract; keep WPE lifecycle ownership. |
| `settings.composition.fields` | MARKET_EVIDENCED_WITH_OWNER_BOUNDARY | E1–E3 compose reusable fields; WPE must reference Fields rather than duplicate its registry. |
| `settings.history.portability` | KEEP_WPE_HARD / COMPETITIVE | Import/export/revisions vary by framework; retain secret-free conflict-aware portability as WPE contract. |
| `settings.security.secret-ref` | KEEP_PROVIDER_SOFT / WPE_SAFETY | No market evidence justifies raw secret serialization. Keep Vault reference only. |
| `settings.value.registration` | KEEP native | Registered type/default/sanitization/REST schema remains WordPress-native truth. |
| `settings.feedback.errors` | KEEP native | WordPress Settings API error feedback remains canonical; provider notices are presentation. |

Coverage: **17 / 17 current records reconciled**.  
Unresolved research dispositions: **0**.  
Worker Bank/progress mutations: **0**.

## 5. WPE-exceed / safety conclusions

- Preserve separate site/network/user ownership and make inheritance explicit instead of merging stores.
- Treat reset/migration/import as privileged lifecycle operations with previews/conflict diagnostics, not ordinary save operations.
- Keep secret values out of normal Settings definitions and exports; only Vault references may be portable.
- Reuse Fields controls and Policy authorization rather than embedding duplicate registries.

## 6. Supervisor integration requirements

A later Supervisor-only integration may add E1–E3 to `snapshot.market_sources`, market-classify the evidenced layout/navigation/composition families, retain inheritance/migration/portability/secret boundaries as WPE contracts, and promote Surface 12 to `MARKET_AUDITED` only after exact-head Bank reconciliation and CI validation.

No `BANK_REVIEWED`, runtime implementation, destructive reset/migration execution, secret mutation, deployment or release is authorized by this audit.