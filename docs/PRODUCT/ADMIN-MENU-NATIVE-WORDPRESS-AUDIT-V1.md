# Admin Menu — Native WordPress Audit V1

Surface: **11 / Admin Menu**  
Issue: **#687**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/admin-menu.json` — **16 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE FOR THE CURRENT V1 SEED, INTEGRATION REQUIRED.** This document does not promote `NATIVE_AUDITED`. The Supervisor must first apply the evidence-backed Bank metadata/record changes below and synchronize canonical progress in one validated integration tree.

WordPress supplies admin-menu registration/removal, separate site/user/network admin menu hooks, capability-gated entries and the Admin Bar node graph. It does **not** supply reusable WPE menu profiles, per-user preview, arbitrary existing-menu reparenting as a first-class Definition API, or a permission engine derived from hidden navigation.

## Current native sources

- `add_menu_page()` — https://developer.wordpress.org/reference/functions/add_menu_page/
- `add_submenu_page()` — https://developer.wordpress.org/reference/functions/add_submenu_page/
- `remove_menu_page()` — https://developer.wordpress.org/reference/functions/remove_menu_page/
- `remove_submenu_page()` — https://developer.wordpress.org/reference/functions/remove_submenu_page/
- `admin_menu` — https://developer.wordpress.org/reference/hooks/admin_menu/
- `user_admin_menu` — https://developer.wordpress.org/reference/hooks/user_admin_menu/
- `network_admin_menu` — https://developer.wordpress.org/reference/hooks/network_admin_menu/
- `WP_Admin_Bar::add_node()` — https://developer.wordpress.org/reference/classes/wp_admin_bar/add_node/
- accepted planning evidence: `docs/PRODUCT/ADMIN-MENU-NATIVE-MARKET-EVIDENCE-MATRIX-V1.md`

`remove_menu_page()` is presentation removal and must run in the correct admin lifecycle; it is not authorization. Capability parameters on menu registration likewise do not remove the need for target-operation Policy/Ability checks.

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `admin-menu.transform.target` | **NATIVE-CONFIRMED** — menu/submenu slugs and parent slugs provide stable native target identity | add native source metadata for menu/submenu registration/removal |
| `admin-menu.transform.rename` | **NATIVE-ADJACENT / WPE-COMPOSED** — titles exist natively, but generic renaming of arbitrary registered entries is not a standalone core Definition API | keep WPE-owned; note native title substrate |
| `admin-menu.transform.order` | **NATIVE-CONFIRMED SUBSTRATE** — menu position/order is native registration state | record native position semantics; do not promise collision-free arbitrary ordering without diagnostics |
| `admin-menu.transform.parent` | **NATIVE-CONFIRMED SUBSTRATE** — submenu parent identity is native | keep WPE transform semantics for moving pre-existing items; cite `add_submenu_page()` |
| `admin-menu.transform.visibility` | **NATIVE-CONFIRMED PRESENTATION** — remove menu/submenu primitives exist | explicitly state hide/show never grants or revokes authorization |
| `admin-menu.transform.role-capability` | **NATIVE-CONFIRMED CAPABILITY INPUT / OWNERSHIP REFERENCE** | capability evaluation must consume canonical Policy/Roles; role mapping stays WPE composition |
| `admin-menu.transform.network` | **NATIVE-GAP IN CURRENT LABEL** — WordPress has distinct site, user-admin and network-admin contexts; Super Admin is not a substitute for context | broaden label/notes to site + user-admin + network-admin scope and Super Admin caveat |
| `admin-menu.custom.route` | **NATIVE-CONFIRMED SUBSTRATE / WPE ROUTE OWNER** | keep internal registered route/provider boundary; no arbitrary callback text |
| `admin-menu.custom.safe-url` | **NATIVE-ADJACENT / WPE-SAFETY** — Admin Bar accepts href; ordinary menu registration is page/slug based | retain validated URL policy; do not claim generic core external-menu-item parity |
| `admin-menu.custom.group` | **MIXED** — Admin Bar groups/parents are native; admin-menu separators/group composition is mostly WPE/market composition | document per-target capability rather than one universal group primitive |
| `admin-menu.profile.definition` | **WPE/MARKET-ONLY** | no native promotion; retain Definition semantics |
| `admin-menu.profile.assignment` | **WPE/MARKET-ONLY + POLICY REFERENCES** | retain role/user/site/network references without owning grants |
| `admin-menu.profile.preview` | **WPE DIAGNOSTIC** | require bounded/redacted actor context; no impersonation or mutation |
| `admin-menu.admin-bar.transform` | **NATIVE-CONFIRMED** | add `WP_Admin_Bar` node/parent/group source metadata |
| `admin-menu.safety.recovery` | **WPE SAFETY** | retain canonical revision/recovery semantics; no native claim |
| `admin-menu.safety.unsafe-url` | **REJECTED-UNSAFE CONFIRMED** | keep `REJECT`; unsafe schemes/executable destinations remain forbidden |

## Required Supervisor Bank integration

1. Populate `snapshot.native_sources` with the reviewed core menu/Admin Bar sources above.
2. Preserve all 16 record IDs; no native evidence requires deleting a seeded family.
3. Enrich `transform.target`, `transform.order`, `transform.parent`, `transform.visibility`, `transform.role-capability` and `admin-bar.transform` with native provenance.
4. Change the semantics of `transform.network` from network-only wording to **site / user-admin / network-admin context + Super Admin caveat**. This may be a label/notes update without an ID change.
5. Keep profile, preview, recovery and safe-URL policy explicitly WPE/market-owned rather than marking them native.
6. Preserve `safety.unsafe-url` as `REJECTED_UNSAFE / REJECT`.

## Native completeness / unresolved items

No additional current WordPress menu family is required for this V1 seed after the site/user/network admin-context correction. Later market review may still add competitor-only profile/composition options. Runtime timing, multisite behavior and Policy enforcement remain runtime-contract concerns, not native Bank-audit blockers.

## Gate boundary

Worker conclusion: **native evidence complete; Bank integration pending**.  
Not promoted here: `NATIVE_AUDITED`, `MARKET_AUDITED`, `BANK_REVIEWED`, Atomic Option Contract, UX, runtime certification or product parity.