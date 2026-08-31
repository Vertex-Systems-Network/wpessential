# WPEssential — Frontend Dashboard Route & Component Runtime Model

Status: **Phase 0 paper architecture / no implementation authorized**  
Related: Dashboard/Profile/Roles exhaustive spec, Policy, Listings, Forms, Membership, Notifications, Chat, Builder adapters.

## 1. Purpose

Frontend Dashboard Builder is a definition-driven application shell over WordPress data/actions. It must not become a second unsafe PHP router or a client-side-only authorization layer.

Architecture separates:

1. Dashboard Definition;
2. Published Route Graph;
3. Compiled Runtime Descriptor;
4. Server Route Resolution;
5. Policy/authorization decision;
6. Component Renderer;
7. optional client enhancement/navigation state.

## 2. Dashboard Definition

Definition Repository owns editable/published configuration:
- identity/key;
- base path;
- audience assignment;
- shell/layout;
- navigation graph;
- routes;
- content/component references;
- access Policies;
- styling/design tokens;
- SEO/indexing policy;
- dependencies.

Runtime session/user data never lives in Definition Repository.

## 3. Publish-time compile

Published Dashboard revision compiles to immutable runtime descriptor containing:
- Dashboard UUID/revision;
- base path;
- normalized route trie/table;
- route IDs/slugs/typed path parameters;
- content/component descriptors;
- Policy references;
- navigation graph;
- shell regions;
- noindex/index policy;
- asset dependency list;
- compatibility/dependency health;
- descriptor schema/version;
- fingerprint.

Invalid duplicate/cyclic/unreachable route definitions cannot publish silently.

## 4. Route identity

Every route has stable UUID independent of URL slug.

Route path changes are navigation/URL changes but do not change internal identity.

Route stores:
- stable UUID;
- relative path pattern;
- typed path parameters;
- title/breadcrumb resolver;
- component source;
- shell override optional;
- access Policy;
- auth requirement;
- navigation node optional;
- caching classification;
- SEO/noindex;
- status.

## 5. Maximum navigation depth

Hard data-model maximum remains 5 levels.

Navigation visibility is not authorization. A hidden menu item does not make its route inaccessible.

Every direct request independently resolves route + Policy.

## 6. Server route resolution

Candidate request flow:

1. WordPress request enters registered Dashboard base path;
2. resolve Dashboard descriptor;
3. normalize/decode path safely;
4. match exact/typed route;
5. validate typed route parameters;
6. resolve authentication state;
7. run outer WordPress/WPE capability/resource Policy;
8. run Membership/Entitlement conditions where route requires;
9. resolve content/component descriptor;
10. render authorized response/shell;
11. attach only assets declared by active components.

Unknown route returns Dashboard-aware 404, not fallback to arbitrary callback.

## 7. Route parameters

Allowed parameter types candidate:
- integer ID;
- UUID;
- sanitized slug;
- enum;
- registered typed resolver.

A parameter is only an identifier candidate. It never authorizes access.

Example: `/projects/123/edit` still requires `edit` Policy on project 123.

## 8. Intended-return/login redirects

When unauthenticated user is redirected to login, intended route is stored/bound as a local Dashboard route reference or validated local URL.

Use WordPress safe redirect semantics (`wp_validate_redirect`/`wp_safe_redirect` where implementation fits).

Rules:
- external arbitrary return URLs rejected;
- one-time/auth transaction binding where needed;
- redirect loops detected;
- denied authenticated user gets access-denied, not login loop.

## 9. Component Blueprint

Canonical Dashboard content references a **Component Blueprint**, not arbitrary PHP/JS code.

Blueprint types may include:
- WPE static/content component;
- Dynamic Listing;
- Form;
- CRUD screen;
- User Profile;
- Membership account;
- Notifications;
- Chat;
- Gutenberg/server-rendered block/template adapter;
- Elementor/Bricks/WPBakery/Visual Composer certified template adapter;
- registered SDK component.

Each descriptor declares:
- component type/version;
- inputs/context schema;
- Policy requirements;
- data dependencies;
- assets;
- cacheability;
- SSR/client requirements;
- fallback/degraded behavior.

## 10. Builder adapters

Third-party builder templates remain owned by their builder/plugin.

WPE stores stable reference + adapter metadata, not proprietary serialized content as canonical WPE format.

Adapter certification determines:
- editor availability;
- frontend render support;
- dynamic data context;
- assets;
- compatibility version range;
- error fallback.

Missing builder does not cause arbitrary unserialize/render attempts.

## 11. CRUD component

CRUD descriptor binds:
- Data Source;
- list Query;
- view renderer;
- create/update Forms or abilities;
- delete/archive ability;
- ownership/resource Policy;
- actions/menu.

Row-level authorization occurs for every mutation/read where required.

Dashboard membership/access does not imply all records in its Data Source are accessible.

## 12. Navigation runtime

Navigation compiles from nodes but final visible tree is resolved for current principal/context.

Node visibility may depend on:
- route accessibility;
- capability;
- Membership/Entitlement;
- feature/module availability;
- registered condition.

Counter/badge data must be authorized and batched.

If destination becomes inaccessible, do not show a count that leaks protected data.

## 13. Shell and layout

Shell regions are renderer descriptors:
- header;
- primary navigation/sidebar;
- secondary region;
- main;
- footer;
- mobile navigation.

Design tokens own styling. Standard product does not embed unrestricted per-dashboard CSS/JS editors.

## 14. Authentication boundary

WPE Dashboard uses WordPress authentication/session by default.

It may embed:
- login;
- logout;
- lost password;
- profile/security components;
- external SSO adapters.

It does not create a parallel password/session database for normal users.

## 15. Route/access result

Authorization returns structured result:
- allow/deny;
- authentication required?;
- missing Policy/Entitlement category;
- safe public reason key;
- intended response type;
- audit/correlation reference where relevant.

Response options:
- render;
- login challenge;
- 403;
- Membership upgrade CTA if configured;
- custom safe access template;
- 404 concealment only under explicit Policy.

No client-side hiding as primary security.

## 16. Caching

Dashboard output can be:
- public cacheable;
- authenticated shared only when representation truly identical and safe;
- principal-specific;
- non-cacheable.

Cache identity for protected/personal routes must account for:
- principal/access generation;
- Dashboard revision;
- route/component revision;
- Membership/Policy generation where relevant;
- locale;
- Query/filter context.

Never cache a privileged user's response and serve it to another user.

## 17. Client-side navigation

React/client navigation is enhancement, not security/router truth.

Browser may:
- navigate without full reload;
- show loading/skeleton;
- preserve safe UI state;
- prefetch public/authorized metadata where policy permits.

Direct URL refresh must produce same authorization result server-side.

## 18. SEO/indexing

Authenticated/private Dashboards default noindex and excluded from public sitemaps.

Public Dashboard routes may opt into indexing after explicit content/access review.

Route metadata must not leak private titles/entities through sitemap or public manifest.

## 19. Asset isolation

Only load:
- base Dashboard shell assets on Dashboard routes;
- component-specific bundles for active components;
- builder adapter assets only if rendered;
- no global wp-admin/frontend enqueue for inactive modules.

Asset descriptor participates in compiled route/component runtime.

## 20. Failure/degraded states

- Dashboard definition missing/unpublished → normal site unaffected;
- route component missing → scoped error/fallback;
- builder plugin disabled → adapter degraded message, no fatal;
- Policy service unavailable/corrupt → protected route fails safe;
- Query/provider failure → component error, not global access bypass;
- Pro expiry → deployed safe runtime follows ADR-0007; editing becomes restricted/read-only;
- navigation dependency missing → node diagnostics and safe omission if configured.

## 21. Preview/simulation

Preview-as-user/role is diagnostic simulation, not impersonation.

It must:
- label simulation mode;
- avoid issuing privileged user session as target;
- evaluate policies using explicit simulation context;
- prevent destructive component actions or require separate real authorization.

## 22. Observability

Useful safe diagnostics:
- route match;
- Dashboard/revision;
- Policy result category;
- component descriptor;
- render duration;
- query/job errors;
- missing dependency;
- correlation ID.

Do not log rendered personal content by default.

## 23. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- WordPress rewrite/router approach comparison;
- permalink/plain-permalink compatibility;
- multisite/subdirectory/subdomain cases;
- route collision fixtures;
- safe login return handling;
- direct-route IDOR tests;
- component asset scoping;
- cache isolation tests;
- Gutenberg/Elementor/Bricks/WPBakery/VC adapter fixtures;
- accessibility/mobile navigation;
- noindex/sitemap behavior;
- performance with large navigation graphs.

## Paper recommendation

Accept the architecture principle:

**Dashboard Definition → Compiled Route/Component Descriptor → Server Route Resolution → Policy → Component Renderer**, with client-side navigation only as enhancement.