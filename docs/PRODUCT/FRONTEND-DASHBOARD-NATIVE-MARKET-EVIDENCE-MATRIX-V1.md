# Frontend Dashboard — Native & Market Evidence Matrix V1

Surface: **13 / Frontend Dashboard**  
Issue: **#496**  
Research anchor: **`main @ 42303a97938f1a80f9c1c9088fa70d40ba359279`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress `add_rewrite_endpoint()` | https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/ | typed endpoint identity/query-var routing and endpoint collision surface |
| WordPress authentication redirect | https://developer.wordpress.org/reference/functions/auth_redirect/ | authenticated frontend/admin access handoff without creating a private authentication engine |
| WordPress roles & capabilities | https://developer.wordpress.org/apis/security/user-roles-and-capabilities/ | server-side capability authorization and least-privilege boundary |
| WP User Frontend — Dashboard | https://wedevs.com/docs/wp-user-frontend-pro/settings/dashboard/ | frontend user dashboard, edit/delete own content, pagination, profile summary, unauthenticated state |
| WP User Frontend — My Account | https://wedevs.com/docs/wp-user-frontend-pro/settings/my-account/ | account page, dashboard/posts/edit-profile endpoint navigation |
| Frontend Dashboard plugin | https://wordpress.org/plugins/frontend-dashboard/ | custom login/register/reset redirects, role-based wp-admin restriction, role-specific dashboard menus, user/post fields |

## Native decisions added in this pass

- A frontend dashboard route/endpoint is a routing concern, not a second resource owner. Endpoint payloads must resolve to canonical Profile, Listings, Forms, Membership, Documents or other owner contracts.
- WordPress rewrite endpoints create query variables and route matches; WPE must therefore model route/endpoint collision diagnostics before any runtime registration lane.
- Authentication redirects may protect entry into the shell, but authorization for each endpoint/action still requires server-side capabilities/Policy/Ability checks.
- Role/capability checks must be evaluated at the target operation. Hiding a dashboard menu item or redirecting wp-admin is presentation/access UX, not a permission grant or revocation.
- Dashboard-owned mutation candidates are rejected unless they route through the canonical resource owner and its revision/authorization contract.

## Seed disposition evidence

- `dashboard.identity.route` — native rewrite endpoint evidence plus market dashboards support a selected host route/account shell.
- `dashboard.access.policy` — login/role/membership/capability constraints are valid inputs, but final authorization remains canonical Policy/Ability-owned.
- `dashboard.navigation.tree` — account/dashboard endpoint menus are established market behavior.
- `dashboard.endpoint.type` — endpoint types should be registered/typed WPE references rather than arbitrary callback/router code.
- `dashboard.content.ownership` — own-content edit/delete is established behavior; actual post/user/document mutation stays with canonical resource owners.
- `dashboard.presentation.layout` — valid presentation concern; responsive styling must not become a duplicate global theme engine.
- `dashboard.integration.references` — should reference Profiles, Forms, Listings, Membership and Documents contracts instead of duplicating those modules.
- `dashboard.accessibility.navigation` — keyboard/focus/landmark requirements are mandatory WPE quality constraints rather than optional visual features.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Template selection/render handoff, endpoint collision/flush lifecycle, login/reset ownership, market competitor breadth, multisite behavior, accessibility evidence, ownership conflicts and zero-unresolved review remain open. No frontend runtime implementation is authorized.
