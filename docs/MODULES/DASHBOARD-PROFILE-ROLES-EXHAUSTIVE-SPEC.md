# WPEssential — Frontend Dashboard, User Profile & Roles/Capabilities Exhaustive Specification

Status: **Phase 0 planning only — no development consent**

Modules:
- Dashboard Builder — Pro
- User Profile Builder — Pro
- Role & Capability Manager — Pro

Membership remains governed by its dedicated deep specification and ADR-0013.

## Research baseline

Reviewed current WordPress user/profile hooks, role/capability APIs, `WP_User` multi-role behavior, `current_user_can()`/`map_meta_cap()`, user creation/update hooks and Application Password behavior.

Core principle:

**Role is a capability bundle, not the universal business identity/access object.**

---

# Part A — Frontend Dashboard Builder

# 1. Dashboard definitions list

Columns:

- Name
- Key
- Audience
- Home route
- Pages/routes count
- Navigation depth
- Status
- Used by
- Health
- Updated
- Actions

Actions:

- Edit
- Preview as role/user simulation
- Duplicate
- Publish/Disable
- Dependencies
- Revisions
- Export
- Delete definition

---

# 2. Dashboard editor sections

1. General
2. Audience & Access
3. Shell/Layout
4. Navigation
5. Routes/Pages
6. Header/Footer/Sidebars
7. Authentication
8. Profile/Account Integration
9. Notifications/Chat
10. Error/Empty States
11. Styling/Responsive
12. SEO/Indexing
13. Performance
14. Preview
15. Dependencies/Revisions

---

# 3. Dashboard identity

- name
- UUID
- machine key
- description
- status Draft default
- base frontend path/route prefix

Route prefix:

- unique among WPE dashboards/protected frontend applications
- validated URL slug/path
- changing published base route is URL/integration-impacting
- redirect/migration plan required if externally linked.

---

# 4. Audience targeting

Dashboard can target:

- all authenticated users
- role(s)
- capability
- Membership Plan/Entitlement
- Query/segment
- specific users
- registered Policy condition

Resolution when multiple dashboards match:

1. explicit user assignment
2. explicit entitlement/policy-specific assignment
3. role/capability priority rule
4. default authenticated dashboard

Priority must be deterministic and explainable.

Audience selection does not replace route-level authorization.

---

# 5. Guest/authenticated behavior

Dashboard access modes:

- authenticated only — default
- mixed guest/auth routes
- public dashboard/application only when explicitly configured

Unauthenticated behavior:

- redirect to login preserving safe intended route
- render login component/page
- render access message

Open redirect prevention required when preserving destination.

---

# 6. Shell layout

Regions:

- header
- primary sidebar/navigation
- secondary optional sidebar
- main content
- footer
- mobile navigation drawer

Shell presets:

- Sidebar
- Top navigation
- Hybrid
- Minimal/account

Each region can use:

- WPE component/template
- Gutenberg content/template
- selected builder template adapter
- registered renderer

Shell is responsive and keyboard navigable.

---

# 7. Navigation tree

Hard data-model maximum: **5 levels**.

UX guidance:

- recommended maximum 2 levels
- warn at 3+
- mobile design must not expose unusable deep nesting

Node options:

- label
- UUID/key
- icon
- destination route/external/internal link
- parent
- order
- visibility Policy
- badge/count source
- active-match rule
- open behavior
- description/tooltip
- mobile visibility

A navigation node may be grouping-only with no destination if accessible expansion semantics are correct.

---

# 8. Navigation badge/counter

Sources:

- static
- Query count
- unread notifications
- unread chat
- form/task count
- registered resolver

Performance:

- batch counters
- cache
- avoid query per menu item

Badge permission follows destination/source Policy.

---

# 9. Route definition

Each route:

- UUID
- slug/path
- title
- page/component source
- layout/shell override
- access Policy
- login requirement
- breadcrumb
- navigation node optional
- SEO/noindex
- cache policy
- status

Route path parameters are typed and allowlisted.

No arbitrary PHP router callback.

---

# 10. Page content sources

- WPE component/template
- Dynamic Listing
- Form
- CRUD screen
- User Profile
- Membership account component
- Notifications
- Chat
- Gutenberg block content
- Elementor template adapter
- Bricks template adapter
- WPBakery/Visual Composer template adapter
- shortcode renderer
- registered component

Third-party builder output is invoked through supported APIs; WPE does not copy proprietary internal formats as canonical dashboard data.

---

# 11. Route guard

Server evaluates:

- authentication
- capability
- resource Policy
- Membership/Entitlement
- route parameter entity access
- optional condition

Denied behavior:

- 403/access page
- login redirect only when unauthenticated
- upgrade/membership CTA when policy explicitly supports
- custom safe denied template

Never render protected data then hide it with frontend JS.

---

# 12. CRUD route component

Binds:

- Data Source/entity
- list Query
- create Form
- edit Form
- view template
- delete Ability

Options:

- actions allowed
- ownership filter
- page size
- empty state
- confirmation

All CRUD uses Data Source authorization; route membership does not grant universal row access.

---

# 13. Breadcrumbs

Sources:

- route hierarchy
- navigation hierarchy
- dynamic entity label

Options:

- home/dashboard root label
- separator presentation
- current item visible

Structured semantic nav/list markup.

---

# 14. Header user menu

Potential items:

- avatar/display name
- profile
- account
- membership
- notifications
- logout
- custom route

Each item Policy-aware.

Logout uses WordPress nonce-aware logout flow, not raw `/wp-login.php?action=logout` string without generated nonce.

---

# 15. Login component integration

Uses WordPress authentication flow.

Options:

- username/email field label
- password
- remember me
- forgot password link
- registration link when enabled
- social/SSO through adapters later
- redirect destination policy

Credentials never pass through arbitrary WPE logs/analytics.

---

# 16. Dashboard styling

- design tokens
- max content width
- sidebar width
- header height
- spacing scale
- typography tokens
- surface/border/radius tokens
- light/dark only if product-wide design system supports

No dashboard-specific unrestricted CSS editor as standard feature.

---

# 17. SEO/indexing

Authenticated account dashboard default:

- noindex candidate/default
- protected routes not exposed in public sitemap

Public dashboards/pages can override through SEO adapter.

No sensitive route/title metadata leaked in sitemap by default.

---

# 18. Dashboard diagnostics/tests

Diagnostics:

- duplicate route
- navigation destination missing
- route inaccessible to every audience
- recursive navigation
- depth >5 blocked
- content source missing
- builder adapter unsupported
- Query counter N+1
- redirect loop

Tests:

- audience priority
- guest/login intended route
- direct protected URL
- deep route parameter IDOR
- mobile navigation
- keyboard/focus
- builder template failure
- noindex/protected sitemap behavior
- asset scoping

---

# Part B — User Profile Builder

# 19. Profile definitions list

Profile definition types:

- Admin self profile additions
- Admin edit-user additions
- New-user admin form additions
- Frontend Account Profile
- Frontend Public Profile
- Dashboard profile component

Columns:

- Name
- Context
- Audience
- Fields
- Public/private
- Status
- Used by
- Health

---

# 20. Profile resolution

A user may match multiple templates.

Priority:

1. explicit user profile assignment
2. Membership/segment-specific
3. role-specific by configured priority
4. general default

Multiple WordPress roles require deterministic profile resolution; never assume first array role is business priority.

---

# 21. Profile page structure

- tabs
- sections
- field groups
- Listings
- Forms
- actions
- partials

Tab options:

- label
- key
- icon
- Policy
- order
- URL/deep link

Section options:

- title
- description
- layout columns
- conditional visibility

---

# 22. Core user fields inventory

WPE distinguishes WordPress core user account fields from custom fields.

Potential core profile bindings:

- user_login — generally immutable after creation in normal WPE UI
- user_email
- first_name
- last_name
- nickname
- display_name
- user_url
- description/bio
- locale
- admin color — admin context only if exposed
- show admin bar frontend

Password is an **action**, not a readable/bindable field.

User activation/reset keys, password hash and session internals are never dynamic profile fields.

---

# 23. Custom user fields

Uses Custom Fields schema with user-meta/custom storage adapter.

Each field still has:

- edit Policy
- view Policy
- privacy classification
- public profile exposure
- export/privacy behavior

Admin ability to edit user does not imply every sensitive custom field should be publicly renderable.

---

# 24. Admin profile integration

Current WordPress contexts require distinct handling:

- own profile display (`show_user_profile` family)
- editing another user (`edit_user_profile` family)
- own profile save (`personal_options_update`)
- another user save (`edit_user_profile_update`)
- new user form/user creation context
- multisite variants where applicable

WPE adapter abstracts these hooks while preserving `edit_user` object capability checks.

---

# 25. Frontend profile edit

Flow:

1. authenticate
2. resolve target user (normally current user)
3. authorize every field/action
4. render current values
5. nonce/CSRF
6. validate
7. update approved fields
8. audit sensitive changes
9. display success/errors

Editing another user frontend is disabled unless explicit administrative use case/policy is configured.

---

# 26. Email change

Email is security/account-sensitive.

Options:

- allow self-change
- require current password/re-auth candidate
- verification of new email depending WordPress/product flow
- notify old/new address

Exact workflow follows current WordPress secure behavior and must be evidence-tested before implementation.

Do not reduce security to make generic Field Form saving easier.

---

# 27. Password change

Dedicated component/action.

Options:

- current password required according to chosen secure flow
- new password
- confirmation
- strength guidance
- logout other sessions candidate
- notification email

Never expose current password value, hash, or log submitted passwords.

Forgot/reset password delegates WordPress reset-key flow.

---

# 28. Avatar

Modes:

- WordPress/Gravatar default
- local media/avatar adapter if approved
- custom field image

Options:

- upload permission
- file/dimension validation
- fallback
- public exposure

Do not overwrite unrelated media attachment on avatar removal unless explicitly requested.

---

# 29. Public profile

Options:

- enabled
- route/slug strategy
- username/nicename/custom stable slug source
- visible fields
- noindex/index
- author archive integration
- avatar
- Listings (posts/projects/etc.)
- contact links

Privacy:

- email hidden by default
- phone/address personal data hidden unless explicitly public
- user ID enumeration concerns considered in URLs/API

---

# 30. Profile field visibility levels

Preset policies:

- only user + administrators
- logged-in users
- selected roles/memberships
- public
- custom Policy

Presentation label “Private/Public” cannot replace server policy.

---

# 31. Application Passwords

WordPress Application Passwords are separate revocable integration credentials, stored hashed and shown only at creation.

WPE may:

- link to/embed supported core management surface
- show safe metadata if authorized
- offer create/revoke action through WordPress APIs if product requires

WPE must not:

- display hashed credentials
- store plaintext Application Passwords
- treat them as browser-login passwords
- expose them through generic Custom Fields/dynamic tokens

---

# 32. Session/security controls

Potential account security components:

- log out current session
- log out other sessions through WordPress APIs
- Application Password management
- password reset/change
- 2FA/SSO adapter status only when external plugin/provider supplies supported API

Do not invent a weak parallel authentication system.

---

# 33. Profile diagnostics/tests

Diagnostics:

- template priority collision
- field exposed publicly despite sensitive classification
- core field mapping invalid
- missing field group
- public route collision

Tests:

- own admin profile
- edit another user permission
- new user
- frontend self edit
- field-level denied edit tampering
- public/private fields
- email/password action security
- multiple-role template resolution
- multisite user context
- Application Password secrecy

---

# Part C — Role & Capability Manager

# 34. Roles list

Columns:

- Display name
- Role key
- Core/custom/external/WPE source heuristic
- Users count
- Capabilities allowed count
- Explicit denied count
- Administrator-equivalent warning
- Default role marker
- Health
- Actions

Actions:

- Inspect
- Edit capabilities
- Clone
- Compare
- View users
- Export
- Delete custom role — guarded

Core role deletion/editing allowed only under explicit advanced safeguards; do not encourage removing Administrator/Subscriber/etc. casually.

---

# 35. Role identity

Create role:

- display name
- machine key
- clone-from optional
- capabilities

Role key immutable after creation in normal UI because users/integrations store it.

Rename changes display name only unless explicit migration flow exists.

---

# 36. Capability registry

WPE inventories capabilities from:

- WordPress core-known capabilities
- registered roles
- user individual caps
- CPT/taxonomy generated caps
- WPE modules
- WooCommerce/integrations
- registered extension descriptors

Capability metadata when known:

- key
- label
- group/source
- primitive/meta classification
- object arguments required
- risk level
- description

Unknown custom capabilities remain visible; WPE does not delete them as “invalid”.

---

# 37. Capability matrix

Per role value:

- allow true
- explicit deny false
- absent/inherit none

UI must distinguish false from absent because WordPress roles/user capability arrays can contain explicit boolean denial.

Filters:

- group/source
- allowed/denied/absent
- risk
- search

Bulk actions:

- allow selected
- deny selected
- remove explicit entry/inherit

---

# 38. Primitive vs meta capabilities

Meta capability examples such as `edit_post`, `edit_user`, `edit_post_meta` are evaluated with object arguments through `map_meta_cap()`.

UI rule:

- do not treat meta capability as simple assignable role boolean when it is not meaningful that way;
- show mapped primitive requirements in simulator/explain tool;
- capability simulation accepts target object ID/context when required.

---

# 39. User multiple roles

WordPress user roles are multiple-capable.

User management operations:

- add role
- remove role
- replace all roles/set one role — explicitly destructive to existing role assignments

UI must not implement “set role” when user intended “add role”.

For each operation show diff.

---

# 40. Individual user capabilities

Advanced feature.

A user may have explicit capabilities in addition to roles, including explicit deny.

UI:

- role-derived capabilities read-only summary
- individual overrides separate
- allow/deny/remove override

Warning: individual caps are easy to forget and complicate audits; use sparingly.

---

# 41. Effective capability explain

Given:

- user
- capability
- optional object/context args

Show:

- assigned roles
- role capability entries
- individual overrides
- meta-cap mapping result
- multisite Super Admin effect
- final allow/deny

This is diagnostic/simulation, not impersonation.

---

# 42. Role cloning

Options:

- source role
- new key/name
- copy true capabilities
- copy explicit denies

Does not assign users automatically.

---

# 43. Role compare/diff

Compare 2+ roles:

- common allows
- only A/B
- explicit denies
- WPE/module capability groups
- high-risk difference

Exportable diagnostic report.

---

# 44. Role deletion

Before deletion:

- user count
- list/sample affected users
- default role check
- integrations/dependencies
- Membership optional role-sync references
- Dashboard/Menu/Profile audience dependencies

Required remap strategy when users assigned:

- replace role with selected role
- remove this role only, preserving other roles
- abort

For users whose only role is deleted, require explicit replacement/no-role handling.

Important WordPress nuance: role definition removal itself does not safely clean every stale per-user assignment. WPE remaps/unassigns users **before** removing definition.

---

# 45. Default registration role

WPE may inspect/configure site default role only under appropriate capability/settings integration.

Changing default affects future users, not existing users.

Deleting current default role requires selecting replacement first.

---

# 46. Administrator-equivalent risk

Risk classifier identifies capability combinations that effectively grant broad administration, e.g. plugin/theme/user/settings management classes.

Do not assume role named `administrator` is the only privileged role.

Warnings use effective capabilities, not name.

---

# 47. Anti-lockout

Before capability/role mutations affecting current actor:

- calculate post-change effective access
- preserve ability to manage roles/WPE recovery unless intentionally confirmed via privileged path
- prevent removing last viable site administrator in ordinary UI
- multisite Super Admin semantics handled separately

Recovery mechanism documented.

---

# 48. Multisite

Roles are site-context-specific; Super Admin is network-level special authority.

Controls explicitly choose:

- current site
- selected site(s) — future bulk network operation
- network/Super Admin management only under dedicated high-risk flow

Do not present Super Admin as ordinary role checkbox.

---

# 49. Test-as-role / simulation

Preferred initial feature: **permission simulator**, not session impersonation.

Simulate:

- menu visibility
- Dashboard/Profile matching
- WPE Policies
- selected `current_user_can`-style capability checks where safe

Actual user switching/impersonation introduces audit/security concerns and is separate future feature.

---

# 50. Role import/export

Exports:

- role key/name
- capability true/false entries
- source metadata

Import conflict:

- create
- merge
- replace with diff
- skip

Replacing existing role requires user-impact preview.

No user assignments included unless Data Import/Export explicitly includes them.

---

# 51. Role revisions/backup

Role mutations create WPE audit/snapshot entries:

- before
- after
- actor
- reason

Rollback:

- preview current drift since snapshot
- restore capability set only after impact check

Do not overwrite roles changed by another plugin silently.

---

# 52. Role tests

- create role empty/from clone
- allow/deny/absent capability
- meta-cap explain with object
- user add/remove multiple roles
- set/replace warns/removes prior roles only deliberately
- individual user deny overrides
- delete role with users remapped first
- default role deletion blocked/replaced
- current admin lockout prevention
- administrator-equivalent custom role warning
- multisite site role vs Super Admin
- unknown external capability preserved
- import/merge/replace diff

---

# 53. Cross-module differentiators

1. Frontend Dashboard routes use same Policy engine as APIs/actions.
2. Profile template resolution handles multiple roles and Membership separately.
3. Core account security fields/actions are not treated like generic meta.
4. Capability explain understands meta→primitive mapping.
5. Role deletion is a user migration, not only removing an option entry.
6. Menu/Profile/Dashboard visibility can reference roles while authorization remains capability/policy-based.
7. Membership role-sync cannot become source of truth for membership.

---

# 54. Open decisions before implementation

Dashboard:
- canonical frontend router/rewrite strategy;
- SSR vs client navigation boundaries;
- route cache architecture;
- builder-template rendering contracts/version matrix.

Profile:
- secure self-service email-change workflow at supported WordPress floor;
- local-avatar strategy;
- account session-management surface;
- public-profile slug uniqueness/migration.

Roles:
- capability registry source metadata accuracy;
- effective-capability risk classifier;
- role snapshot/rollback storage;
- multisite bulk role operations.

Global:
- explicit user development consent;
- accepted compatibility/UI/build/Definition/CI/Secrets/Free-Pro architecture.

**Development authorization remains NO.**
