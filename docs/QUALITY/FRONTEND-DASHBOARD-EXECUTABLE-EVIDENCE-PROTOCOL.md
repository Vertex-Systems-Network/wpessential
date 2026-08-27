# WPEssential — Frontend Dashboard Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0031, ADR-0035, `docs/ARCHITECTURE/FRONTEND-DASHBOARD-ROUTE-RUNTIME-MODEL.md`, Policy, Component Blueprint, Membership, Query, Asset Registry, ADR-0014.

## 1. Purpose

Define the executable evidence required before WPEssential can claim Frontend Dashboard Builder routing, authorization, navigation, rendering, caching and builder-backed components are production-ready for a certified WordPress environment.

The security invariant is non-negotiable:

**route/menu visibility, client-side state and component presence never authorize access; every direct protected request must resolve server-side through route resolution + Policy before protected content or actions are exposed.**

## 2. Runtime profile

A future certified Dashboard profile records at minimum:
- WordPress/PHP/database versions;
- permalink mode;
- single-site or Multisite topology;
- subdirectory/subdomain/path context;
- active theme/template-routing conditions;
- WPE Dashboard descriptor/compiler version;
- Policy/Query/Component Blueprint versions;
- builder adapter identities/versions used by fixtures;
- object/full-page cache layers relevant to routing/output;
- reverse proxy/CDN behavior where present;
- locale/RTL state;
- frontend asset build/runtime profile.

Certification is scoped to a recorded profile, not a generic “works on WordPress” claim.

## 3. Evidence fixture matrix

### FD-01 — Published route basic match
A published static route resolves through the compiled Dashboard descriptor and renders the intended component.

### FD-02 — Unknown route
Unknown path returns Dashboard-aware 404 behavior and never falls through to an arbitrary callback/component.

### FD-03 — Duplicate route publish conflict
Duplicate/colliding route patterns are rejected or deterministically resolved at publish time; no ambiguous runtime match.

### FD-04 — Typed route parameter validation
Integer/UUID/slug/enum parameter rules reject malformed values before resource access.

### FD-05 — Encoded/path-normalization edge cases
Encoded slashes, traversal-like segments, duplicate separators and normalization variants cannot bypass route identity or Policy.

### FD-06 — Direct protected URL IDOR
A principal lacking access cannot retrieve another principal/resource merely by changing a route parameter.

### FD-07 — Hidden navigation is not authorization
Removing/hiding a menu item does not alter direct route authorization; Policy remains authoritative.

### FD-08 — Unauthenticated protected route
Produces the configured login challenge/403 behavior without disclosing protected component data in HTML, preload state, titles, counters or caches.

### FD-09 — Authenticated-but-denied route
Returns deny/upgrade/conceal behavior according to explicit Policy and does not enter login redirect loops.

### FD-10 — Safe intended-return login flow
Return destination is a validated local Dashboard route; external/open redirects and forged return state are rejected.

### FD-11 — Role/capability change mid-session
Revocation becomes effective according to accepted access-generation/session semantics without stale privileged Dashboard output being served.

### FD-12 — Membership/entitlement revoke
Protected route, navigation badge/count and cached representation cease exposing revoked content within the defined correctness window.

### FD-13 — Public route
Explicitly public Dashboard route can render without login and does not accidentally inherit private-route cache or shell data.

### FD-14 — Private route noindex
Private/authenticated route emits noindex behavior and remains absent from public sitemap/route manifests where applicable.

### FD-15 — Public indexing opt-in
Only explicitly public/reviewed routes become indexable; private route metadata cannot leak through generated SEO surfaces.

### FD-16 — Navigation depth limit
Maximum supported navigation depth is enforced at definition/publish time with deterministic error UX.

### FD-17 — Navigation authorization pruning
Unavailable routes/nodes are omitted without leaking protected entity counts, labels or child metadata.

### FD-18 — Badge/counter batching
Navigation counters use bounded batched queries; no per-node/per-row N+1 behavior under the reference large-navigation fixture.

### FD-19 — Breadcrumb/title resolver authorization
Dynamic titles/breadcrumbs do not fetch or expose inaccessible entity labels before Policy.

### FD-20 — Static Component Blueprint render
Reference static component renders server-side with deterministic descriptor revision and scoped assets.

### FD-21 — Dynamic Listing component
Authorized Query + batched hydration + Blueprint SSR semantics match ADR-0099; protected totals/cursors are not leaked.

### FD-22 — Form/action component
Read and mutation authorization are independent; forged client payload cannot invoke an Ability unavailable to the principal.

### FD-23 — CRUD list/view/edit separation
List visibility does not imply edit/delete authority; each row/action evaluates the target resource Policy.

### FD-24 — User Profile security action boundary
Ordinary profile editing cannot mutate credentials/roles/protected identity fields without the dedicated security workflow.

### FD-25 — Missing component dependency
One missing/unpublished component produces scoped degraded output and diagnostics rather than fataling the site or bypassing Policy.

### FD-26 — Builder plugin disabled
Builder-backed route degrades safely; no arbitrary unserialize/render attempt and no global fatal.

### FD-27 — Server/client navigation parity
Full refresh and enhanced client navigation produce the same route identity and authorization result.

### FD-28 — Browser back/forward/history
History navigation does not resurrect a protected representation after permission/revision changes beyond the accepted cache/session semantics.

### FD-29 — Prefetch
Client prefetch requests are authorized like normal requests and never preload private data for inaccessible routes.

### FD-30 — Principal-specific cache isolation
Two authenticated principals with different permissions never receive each other’s protected rendered output, counters or serialized state.

### FD-31 — Revision cache invalidation
Publishing a Dashboard/route/component revision invalidates or versions affected cache entries deterministically.

### FD-32 — Revocation-sensitive cache
Access-generation/Policy-generation changes prevent stale privileged response reuse where fail-closed access is required.

### FD-33 — Locale cache partition
Localized route/component output cannot cross-serve wrong locale when locale affects representation.

### FD-34 — Object/full-page cache compatibility
Certified caching layer respects private/public classification and bypass/vary requirements for protected Dashboard routes.

### FD-35 — Asset route scoping
Dashboard shell assets load only on Dashboard routes; inactive module/builder assets are absent.

### FD-36 — Component asset scoping
Only active component dependencies are enqueued; no duplicate conflicting dependency registration under multiple components.

### FD-37 — Missing/failed asset
A failed optional component asset degrades that component; it does not disable authorization or corrupt unrelated site pages.

### FD-38 — Accessibility/navigation keyboard baseline
Reference shell/nav/modal/menu interactions pass keyboard/focus/label checks under the certified UI profile.

### FD-39 — Mobile navigation
Responsive navigation remains reachable, ordered and authorization-consistent without duplicated hidden actionable controls.

### FD-40 — RTL/localized shell
RTL/localized labels/layout do not break route identity, keyboard order or critical action semantics.

### FD-41 — Plain permalink compatibility
Certified plain-permalink profile resolves routes without unsafe query-var collisions.

### FD-42 — Pretty permalink compatibility
Rewrite rules coexist with normal WordPress posts/pages and do not hijack unrelated URLs.

### FD-43 — Route collision with existing page/CPT endpoint
Collision policy is explicit, diagnosed and deterministic; WPE does not silently shadow existing content.

### FD-44 — Multisite same Dashboard key
Same Dashboard/route key on two sites remains site-scoped; data, cache and navigation do not cross sites.

### FD-45 — Network policy floor
Child site cannot weaken a network-enforced authorization/feature restriction where the product declares a network floor.

### FD-46 — Preview-as-user simulation
Simulation is clearly labelled, does not mint target-user credentials/session, and destructive actions remain blocked or reauthorized as the real actor.

### FD-47 — Pro expiry/degraded runtime
Previously deployed safe Dashboard runtime follows ADR-0007; editing can lock while required safe frontend behavior remains truthful and non-destructive.

### FD-48 — Large route/navigation graph performance
Reference maximum graph is compiled/resolved within accepted budgets; request cost is bounded and no hidden per-route database loop appears.

## 4. Security pass gates

A certified Dashboard profile fails if any fixture demonstrates:
- direct-route IDOR;
- client-side-only authorization;
- protected content in public/preload/cache surfaces;
- privileged response reuse across principals/sites;
- unsafe intended-return/open redirect;
- route normalization bypass;
- mutation Ability bypass;
- hidden navigation treated as access control;
- private title/count/SEO leakage;
- missing dependency causing authorization fail-open.

## 5. Performance evidence

Capture for representative public/private routes:
- descriptor compile time and output size;
- route-match time;
- Policy evaluation time;
- DB/query count;
- navigation/counter query count;
- component SSR time;
- asset count/bytes;
- cache hit/miss behavior;
- peak memory;
- large graph behavior.

Performance optimization cannot weaken authorization or cache partitioning.

## 6. Required future evidence report

Include:
- runtime profile;
- FD-01…FD-48 pass/fail;
- route collision/permalink results;
- IDOR and cache-isolation evidence;
- direct-refresh/client-navigation parity;
- Multisite isolation results;
- asset/query budgets;
- accessibility/mobile/RTL findings;
- unsupported/degraded builder/component cases.

## 7. Current state

**FD fixtures executed: 0/48.**

No rewrite rule, route hook, frontend render, policy test, asset build, browser test, cache mutation or WordPress runtime operation has been executed by this planning work.

## 8. Development gate

Execution requires explicit owner consent under ADR-0014.