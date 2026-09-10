# Admin Menu — Native & Market Evidence Matrix V1

Surface: **11 / Admin Menu**  
Issue: **#494**  
Research anchor: **`main @ fad3b2f37bd44a01d77438e71877d7990b4bfe2c`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress `add_menu_page()` | https://developer.wordpress.org/reference/functions/add_menu_page/ | target identity, title/icon/order, capability-gated menu entry; page callback must independently re-check capability |
| WordPress `remove_menu_page()` | https://developer.wordpress.org/reference/functions/remove_menu_page/ | hide/remove semantics for existing top-level menu items; does not by itself define authorization |
| Admin Menu Editor | https://wordpress.org/plugins/admin-menu-editor/ | rename, reorder, URL/icon, capability/role visibility, submenu movement, custom menu items, multisite/global editing |
| WP Adminify Admin Menu Editor | https://wpadminify.com/docs/adminify/admin-menu/admin-menu-editor | rename/reorder, role/user visibility, custom menu items and submenu management |

## Seed disposition evidence

- `admin-menu.transform.target` — supported by native menu slug/menu arrays plus market target-selection behavior.
- `admin-menu.transform.presentation` — supported by native title/icon/position semantics and market rename/reorder capabilities.
- `admin-menu.visibility.policy` — capability/role/user presentation rules are valid candidates, but UI hiding **must never replace Policy/Ability authorization**.
- `admin-menu.custom-item` — valid market-parity candidate; destination and callback/execution must be registered/allowlisted rather than arbitrary executable input.
- `admin-menu.profile.assignment` — market evidence supports reusable role/user menu configurations; exact WPE ownership/storage remains to be normalized.
- `admin-menu.admin-bar.transform` — related but separate WordPress Admin Bar APIs require a dedicated native audit before adoption.
- `admin-menu.preview.actor` — WPE diagnostic candidate only; target-user data and effective capabilities must be bounded/redacted.
- `admin-menu.safety.restore` — WPE safety candidate; restore must operate on canonical definition history rather than hidden duplicate state.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. The 8 seed records remain candidates until the full native inventory, Admin Bar APIs, multisite behavior, competitor family coverage, semantic ownership conflicts, rejected-unsafe cases and zero-unresolved review are completed. No runtime implementation is authorized.