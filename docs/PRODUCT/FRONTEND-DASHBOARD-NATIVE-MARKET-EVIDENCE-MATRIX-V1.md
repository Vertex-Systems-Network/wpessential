# Frontend Dashboard — Native & Market Evidence Matrix V1

Surface: **13 / Frontend Dashboard**  
Issue: **#496**  
Research anchor: **`main @ fad3b2f37bd44a01d77438e71877d7990b4bfe2c`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WP User Frontend — Dashboard | https://wedevs.com/docs/wp-user-frontend-pro/settings/dashboard/ | frontend user dashboard, edit/delete own content, pagination, profile summary, unauthenticated state |
| WP User Frontend — My Account | https://wedevs.com/docs/wp-user-frontend-pro/settings/my-account/ | account page, dashboard/posts/edit-profile endpoint navigation |
| Frontend Dashboard plugin | https://wordpress.org/plugins/frontend-dashboard/ | custom login/register/reset redirects, role-based wp-admin restriction, role-specific dashboard menus, user/post fields |

## Seed disposition evidence

- `dashboard.identity.route` — market evidence supports a selected account/dashboard host page and frontend route composition.
- `dashboard.access.policy` — login/role/membership/capability constraints are valid inputs, but final authorization remains canonical Policy/Ability-owned.
- `dashboard.navigation.tree` — account/dashboard endpoint menus are established market behavior.
- `dashboard.endpoint.type` — endpoint types should be registered/typed WPE references rather than arbitrary callback/router code.
- `dashboard.content.ownership` — own-content edit/delete is established behavior; actual post/user/document mutation stays with canonical resource owners.
- `dashboard.presentation.layout` — valid presentation concern; responsive styling must not become a duplicate global theme engine.
- `dashboard.integration.references` — should reference Profiles, Forms, Listings, Membership and Documents contracts instead of duplicating those modules.
- `dashboard.accessibility.navigation` — keyboard/focus/landmark requirements are mandatory WPE quality constraints rather than optional visual features.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Native routing/auth/user APIs, market competitor breadth, endpoint collisions, multisite behavior, accessibility evidence, ownership conflicts and zero-unresolved review remain open. No frontend runtime implementation is authorized.