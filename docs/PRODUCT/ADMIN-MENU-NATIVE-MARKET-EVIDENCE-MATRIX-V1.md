# Admin Menu — Native & Market Evidence Matrix V1

Surface: **11 / Admin Menu**  
Issue: **#494**  
Research anchor: **`main @ 42303a97938f1a80f9c1c9088fa70d40ba359279`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress `add_menu_page()` | https://developer.wordpress.org/reference/functions/add_menu_page/ | target identity, title/icon/order, capability-gated menu entry; render callbacks still require independent capability checks |
| WordPress `add_submenu_page()` | https://developer.wordpress.org/reference/functions/add_submenu_page/ | parent/child placement, capability gate, stable menu slug and site/user/network admin hook distinctions |
| WordPress `remove_menu_page()` | https://developer.wordpress.org/reference/functions/remove_menu_page/ | hide/remove semantics for existing top-level menu items; presentation removal is not authorization |
| WordPress `WP_Admin_Bar::add_node()` | https://developer.wordpress.org/reference/classes/wp_admin_bar/add_node/ | admin-bar node identity, parent/group relationships, destination and bounded presentation metadata |
| Admin Menu Editor | https://wordpress.org/plugins/admin-menu-editor/ | rename, reorder, URL/icon, capability/role visibility, submenu movement, custom menu items, multisite/global editing |
| WP Adminify Admin Menu Editor | https://wpadminify.com/docs/adminify/admin-menu/admin-menu-editor | rename/reorder, role/user visibility, custom menu items and submenu management |

## Native decisions added in this pass

- WordPress explicitly separates `admin_menu`, `user_admin_menu` and `network_admin_menu`; WPE site/network/user-admin targeting must therefore be modeled as explicit scope, not inferred from one shared menu tree.
- A menu capability controls whether WordPress includes an item, but the page callback must still enforce authorization. `admin-menu.visibility.policy` remains presentation/navigation policy and cannot grant the target Ability.
- Admin Bar nodes are a related native navigation surface with their own ID/parent/group graph; they should be represented through a typed transform family rather than arbitrary HTML/callback input.
- URLs and menu slugs may be authored only through validated internal route or safe URL contracts. Raw executable callbacks remain outside the Bank candidate.

## Seed disposition evidence

- `admin-menu.transform.target` — supported by native menu slug/menu arrays plus market target-selection behavior.
- `admin-menu.transform.presentation` — supported by native title/icon/position semantics and market rename/reorder capabilities.
- `admin-menu.visibility.policy` — capability/role/user presentation rules are valid candidates, but UI hiding **must never replace Policy/Ability authorization**.
- `admin-menu.custom-item` — valid market-parity candidate; destination and callback/execution must be registered/allowlisted rather than arbitrary executable input.
- `admin-menu.profile.assignment` — market evidence supports reusable role/user menu configurations; exact WPE ownership/storage remains to be normalized.
- `admin-menu.admin-bar.transform` — now has native node/parent/group evidence; removal/update timing and multisite behavior still require broader review.
- `admin-menu.preview.actor` — WPE diagnostic candidate only; target-user data and effective capabilities must be bounded/redacted.
- `admin-menu.safety.restore` — WPE safety candidate; restore must operate on canonical definition history rather than hidden duplicate state.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. The 8 seed records remain candidates until menu-array/hook timing, admin-bar removal/update semantics, multisite/network behavior, broader competitor family coverage, semantic ownership conflicts, rejected-unsafe cases and zero-unresolved review are completed. No runtime implementation is authorized.
