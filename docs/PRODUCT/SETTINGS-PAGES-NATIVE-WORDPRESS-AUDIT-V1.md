# Settings Pages — Native WordPress Audit V1

Surface: **12 / Settings Pages**  
Issue: **#688**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/settings.json` — **15 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE WITH TWO BANK ADDITIONS REQUIRED.** This worker document does not promote `NATIVE_AUDITED`; Supervisor integration must update the Bank and progress atomically.

WordPress provides Settings API registration/sections/fields, Options API site storage, network options, user meta, settings-page registration and settings-error primitives. These are separate native contracts. Tabs, multi-column builders, inheritance, revisions, migrations, Vault secrets and portability are WPE/market semantics rather than native Settings API features.

## Current native sources

- `register_setting()` — https://developer.wordpress.org/reference/functions/register_setting/
- `add_settings_section()` — https://developer.wordpress.org/reference/functions/add_settings_section/
- `add_settings_field()` — https://developer.wordpress.org/reference/functions/add_settings_field/
- `settings_fields()` — https://developer.wordpress.org/reference/functions/settings_fields/
- `do_settings_sections()` — https://developer.wordpress.org/reference/functions/do_settings_sections/
- `add_options_page()` — https://developer.wordpress.org/reference/functions/add_options_page/
- `get_option()` / `update_option()` — https://developer.wordpress.org/reference/functions/update_option/
- `get_site_option()` / `update_site_option()` — https://developer.wordpress.org/reference/functions/update_site_option/
- `get_user_meta()` / `update_user_meta()` — https://developer.wordpress.org/reference/functions/update_user_meta/
- `add_settings_error()` / `get_settings_errors()` — https://developer.wordpress.org/reference/functions/add_settings_error/
- accepted planning evidence: `docs/PRODUCT/SETTINGS-PAGES-NATIVE-MARKET-EVIDENCE-MATRIX-V1.md`

Current `register_setting()` supports registered type, label/description, sanitize callback, optional REST exposure/schema and default value. Those semantics deserve an explicit Bank family rather than being hidden inside generic field composition.

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `settings.page.identity` | **NATIVE-CONFIRMED** | attach settings-page/menu identity provenance |
| `settings.page.navigation` | **NATIVE-PARTIAL** — parent/position/menu placement are native; richer icon/navigation composition is not universal Settings API | keep WPE-composed, document native subset |
| `settings.page.capability` | **NATIVE-CONFIRMED SECURITY INPUT** | capability gates remain server-side and do not replace Policy |
| `settings.page.scope` | **NATIVE-CONFIRMED MULTI-STORE SUBSTRATE** | explicitly distinguish site option, network option and user-meta scopes |
| `settings.layout.tabs-sections` | **MIXED** — sections are native; tabs/panels are WPE/market presentation | preserve one family with capability notes |
| `settings.layout.columns` | **WPE/MARKET-ONLY** | no native promotion |
| `settings.storage.mode` | **NATIVE-CONFIRMED** | map grouped/individual storage to explicit option ownership rather than implicit table access |
| `settings.storage.autoload-defaults` | **NATIVE-CONFIRMED EXPERT SUBSTRATE** — defaults are registered; autoload is Options API/storage behavior | keep expert/bounded; no raw DB exposure |
| `settings.storage.owner` | **NATIVE-CONFIRMED OWNERSHIP CHOICE** | site/network/user owner must be explicit and mutually coherent |
| `settings.storage.inheritance` | **WPE EFFECTIVE-STATE SEMANTIC** | no native claim; deterministic precedence required later |
| `settings.lifecycle.reset` | **WPE/MARKET-ONLY** | reset must be scoped/authorized; no native lifecycle promotion |
| `settings.lifecycle.migration` | **WPE LIFECYCLE** | no native claim |
| `settings.composition.fields` | **NATIVE-PARTIAL + SHARED FIELDS OWNER** | Settings API fields/sections are native presentation hooks; control schemas remain Fields-owned |
| `settings.history.portability` | **WPE/IMPORT-EXPORT OWNED** | retain definition/reference semantics only |
| `settings.security.secret-ref` | **WPE/VAULT PROVIDER BOUNDARY** | secrets never become ordinary portable Settings API values |

## Missing native families to add during integration

### 1. `settings.value.registration`

Normalize registered setting semantics that are currently missing as a first-class family:

- setting/group identity;
- type/default;
- server sanitize callback **by registered provider/reference, not authored executable text**;
- `show_in_rest` + schema exposure policy;
- description/label metadata where used.

Recommended classification: `NATIVE_HARD`, `HARD`, `CURRENT_NATIVE`, adoption to be decided only after integration/review.

### 2. `settings.feedback.errors`

Normalize bounded Settings API validation/error feedback (`add_settings_error()` / `get_settings_errors()`) separately from arbitrary notices. This prevents validation failures from being hidden inside generic UI state.

Recommended classification: `SOFT_NATIVE`, `SOFT`, `CURRENT_NATIVE`.

## Required Supervisor Bank integration

1. Populate `snapshot.native_sources` with the reviewed Settings/Options/User Meta APIs.
2. Add the two missing native families above.
3. Enrich existing page/scope/storage/composition records with native provenance.
4. Preserve tabs/columns/inheritance/reset/migration/portability/secret semantics as WPE/market-owned.
5. Keep site, network and user ownership separate; do not collapse them into one implicit option store.

## Native completeness / unresolved items

After the two additions, this V1 native surface has no remaining known core Settings API family gap. Exact authorization, multisite mutation behavior, migration execution and UI runtime remain later contract/runtime gates.

## Gate boundary

Worker conclusion: **native evidence complete; two Bank additions + metadata integration pending**.  
Not promoted here: `NATIVE_AUDITED`, market review, `BANK_REVIEWED`, Atomic Option Contract, UX or runtime/product certification.