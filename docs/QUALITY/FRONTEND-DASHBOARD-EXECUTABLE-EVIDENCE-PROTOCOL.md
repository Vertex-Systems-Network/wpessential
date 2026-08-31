# WPEssential — Frontend Dashboard Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP46`  
Related: ADR-0031, ADR-0035, ADR-0108, CBP, DSR, QRY, DVR, CLG, KPA, RA, UP, Membership, CAC, ASR, ERR, VER, MLC, MSI/LC, ADR-0014.

## 1. Purpose

Define executable evidence required before WPEssential can claim Frontend Dashboard routing, authorization, navigation, rendering, data/action components, cache behavior, browser navigation, accessibility, lifecycle or Multisite support.

The security invariant is non-negotiable:

**route/menu visibility, client-side state, component presence, cache state, prefetch success and public shell rendering never authorize access. Every protected request/action resolves server-side through trusted route identity + current principal + target resource Policy before protected data or mutation.**

## 2. Runtime certification profile

A future certified Dashboard profile records:
- WordPress/PHP/database versions;
- permalink/rewrite/server routing profile;
- single-site/Multisite topology and subdirectory/subdomain/domain mapping state;
- active theme/template-routing conditions;
- Dashboard Definition/compiler/runtime versions;
- KPA/Policy/RA/UP/Membership versions;
- DSR/QRY/DVR/CLG/CBP/ASR versions;
- CAC/object/full-page/CDN cache layers;
- REST/Ability/Form/Workflow/component adapters used;
- frontend build/browser/runtime profile;
- locale/RTL/accessibility test profile;
- VER/MLC/PDL/ERR/Audit profile.

Certification is profile-specific; theme, permalink, cache/CDN, builder/plugin or route-version changes can invalidate evidence.

# 3. Original canonical fixtures — preserved

### FD-01 — Published route basic match
Published static route resolves through compiled Dashboard descriptor and intended component.

### FD-02 — Unknown route
Unknown path returns Dashboard-aware 404 and never falls through to arbitrary callback/component.

### FD-03 — Duplicate route publish conflict
Duplicate/colliding route patterns are rejected or deterministically resolved before runtime.

### FD-04 — Typed route parameter validation
Integer/UUID/slug/enum rules reject malformed values before resource access.

### FD-05 — Encoded/path-normalization edge cases
Encoded slashes/traversal-like segments/duplicates cannot bypass route identity or Policy.

### FD-06 — Direct protected URL IDOR
Principal lacking access cannot retrieve another resource merely by changing route parameter.

### FD-07 — Hidden navigation is not authorization
Hiding menu item does not alter direct route authorization.

### FD-08 — Unauthenticated protected route
Configured challenge/deny behavior exposes no protected component data in HTML/preload/title/count/cache.

### FD-09 — Authenticated-but-denied route
Returns explicit deny/upgrade/conceal behavior without login redirect loops.

### FD-10 — Safe intended-return login flow
Return destination is validated local Dashboard route; external/open redirects rejected.

### FD-11 — Role/capability change mid-session
Revocation takes effect according to accepted generation/session semantics without stale privileged output.

### FD-12 — Membership/entitlement revoke
Protected route/navigation/count/cache stops exposing revoked content within correctness window.

### FD-13 — Public route
Explicit public route renders without login and does not inherit private shell/cache data.

### FD-14 — Private route noindex
Private/authenticated route emits noindex and remains absent from public sitemap/route manifests where applicable.

### FD-15 — Public indexing opt-in
Only explicitly public/reviewed routes become indexable.

### FD-16 — Navigation depth limit
Maximum supported depth enforced at Definition/publish time.

### FD-17 — Navigation authorization pruning
Unavailable nodes omitted without leaking protected counts/labels/child metadata.

### FD-18 — Badge/counter batching
Navigation counters use bounded batched queries; no per-node N+1 under reference graph.

### FD-19 — Breadcrumb/title resolver authorization
Dynamic titles/breadcrumbs do not expose inaccessible entity labels before Policy.

### FD-20 — Static Component Blueprint render
Static component SSR uses deterministic descriptor revision and scoped assets.

### FD-21 — Dynamic Listing component
Authorized Query + batched hydration + Blueprint SSR preserve protected totals/cursors.

### FD-22 — Form/action component
Read and mutation authorization independent; forged client payload cannot invoke unavailable Ability.

### FD-23 — CRUD list/view/edit separation
List visibility does not imply edit/delete authority; each action evaluates target Policy.

### FD-24 — User Profile security action boundary
Ordinary profile editing cannot mutate credentials/roles/protected identity without dedicated security workflow.

### FD-25 — Missing component dependency
Scoped degraded output/diagnostics, no fatal or Policy bypass.

### FD-26 — Builder plugin disabled
Builder-backed route degrades safely without arbitrary unserialize/render or global fatal.

### FD-27 — Server/client navigation parity
Full refresh and enhanced client navigation produce same route identity/authorization.

### FD-28 — Browser back/forward/history
History navigation does not resurrect protected representation after permission/revision changes beyond accepted cache semantics.

### FD-29 — Prefetch
Prefetch requests are authorized like normal requests and never preload inaccessible private data.

### FD-30 — Principal-specific cache isolation
Different principals never receive each other's protected output/counters/serialized state.

### FD-31 — Revision cache invalidation
Publishing Dashboard/route/component revision invalidates or versions affected cache entries.

### FD-32 — Revocation-sensitive cache
Access/Policy generation changes prevent stale privileged response reuse where fail-closed access required.

### FD-33 — Locale cache partition
Localized output cannot cross-serve wrong locale when locale affects representation.

### FD-34 — Object/full-page cache compatibility
Certified cache layer respects private/public classification and vary/bypass requirements.

### FD-35 — Asset route scoping
Dashboard shell assets load only on Dashboard routes.

### FD-36 — Component asset scoping
Only active component dependencies enqueue; no duplicate conflicting registration.

### FD-37 — Missing/failed asset
Optional component asset failure degrades component without disabling authorization.

### FD-38 — Accessibility/navigation keyboard baseline
Reference shell/nav/modal/menu interactions pass keyboard/focus/label checks.

### FD-39 — Mobile navigation
Responsive navigation remains reachable, ordered and authorization-consistent.

### FD-40 — RTL/localized shell
RTL/localized labels/layout do not break route identity, keyboard order or critical action semantics.

### FD-41 — Plain permalink compatibility
Plain-permalink profile resolves routes without unsafe query-var collisions.

### FD-42 — Pretty permalink compatibility
Rewrite rules coexist with normal posts/pages and do not hijack unrelated URLs.

### FD-43 — Route collision with existing page/CPT endpoint
Collision policy explicit, diagnosed and deterministic.

### FD-44 — Multisite same Dashboard key
Same key on two sites remains site-scoped; data/cache/navigation do not cross.

### FD-45 — Network policy floor
Child site cannot weaken network-enforced restriction where declared.

### FD-46 — Preview-as-user simulation
Clearly labelled simulation does not mint target credentials; destructive actions reauthorize real actor.

### FD-47 — Pro expiry/degraded runtime
Safe deployed runtime follows ADR-0007; editing can lock without unsafe runtime corruption.

### FD-48 — Large route/navigation graph performance
Reference maximum graph compiles/resolves within bounded budgets without hidden per-route DB loop.

# 4. Dashboard Definition, route compiler and version fixtures

### FD-49 — Draft Dashboard not public
Draft Dashboard/route/component does not become public runtime route.

### FD-50 — Published Dashboard revision pinning
Runtime resolves intended published Dashboard revision and compiled descriptor generation.

### FD-51 — Route stable identity
Route identity survives label/navigation-order changes without stale duplicate route.

### FD-52 — Route slug/path migration
Changing public path has explicit redirect/deprecation/cache behavior; old path does not silently expose stale private component.

### FD-53 — Parameter schema evolution
Compatible route-parameter change is versioned; incompatible change requires migration/new route semantics.

### FD-54 — Unsupported descriptor version
Unknown newer compiled descriptor fails/degrades safely under VER instead of executing best-effort.

### FD-55 — Missing route dependency
Missing Policy/Component/Query/Data Source blocks/degrades route; no fail-open.

### FD-56 — Route graph cycle
Navigation/parent/dependency cycle is detected before publish/compile.

### FD-57 — Route precedence
Static/dynamic/wildcard patterns resolve deterministic specificity; generic catch-all cannot shadow protected exact route unexpectedly.

### FD-58 — Optional trailing slash
Configured trailing-slash policy remains canonical and cannot split cache/authorization identity.

### FD-59 — Case/Unicode path semantics
Supported server/WP path case and Unicode normalization are explicit and collision-safe.

### FD-60 — Query-string route state
Query parameters never change route authorization identity unless declared typed route/filter input.

### FD-61 — Fragment non-authority
Browser fragment state cannot grant server route/data/action access.

### FD-62 — Route publish while requests active
Readers observe old or new complete descriptor, not partial mixed graph.

### FD-63 — Route delete with inbound links/dependencies
Impact preview identifies navigation/components/links; stale compiled route removed/invalidation explicit.

### FD-64 — Definition import
Imported routes/components revalidate path collisions, IDs, policies, dependencies and target scope before publish.

# 5. Request, authentication, session and redirect fixtures

### FD-65 — Trusted host/scheme
Host/proto behind proxy uses certified server/Protector identity; spoofed headers cannot alter intended-return/security behavior.

### FD-66 — Request method classification
GET/HEAD/POST/etc. are explicit; route GET visibility cannot authorize mutation method.

### FD-67 — Anonymous shell preload
Public shell/bootstrap contains only public safe metadata; no private route graph/labels/counters.

### FD-68 — Login challenge CSRF/state
Login-return state is integrity-protected/validated according to auth profile and cannot be forged into external redirect.

### FD-69 — Login after access revoke
Successful authentication does not bypass current route Membership/Policy denial.

### FD-70 — Session expires during client navigation
Next protected fetch/action receives truthful auth failure and private state is cleared/hidden appropriately.

### FD-71 — Password/session revoke
UP/WordPress security changes invalidate/deny subsequent protected Dashboard actions according to certified semantics.

### FD-72 — Application Password boundary
External REST auth does not automatically create browser Dashboard session or route visibility.

### FD-73 — Role/capability revoke cache
RA committed revoke propagates through CAC and current Policy; client shell cannot keep privileged mutation usable.

### FD-74 — Membership expiration clock boundary
Entitlement expiration checked server-side; stale open browser cannot preserve access.

### FD-75 — Object ownership change
Route to resource reauthorizes current owner/access; cached old owner state cannot grant access.

### FD-76 — Intended-return nested encoding
Double-encoding/userinfo/scheme-relative tricks cannot create open redirect.

### FD-77 — Login redirect loop detection
Protected/login/home route configuration cannot create infinite loop.

### FD-78 — Logout route
Logout follows WordPress nonce/session semantics and safe local return path.

### FD-79 — 403 vs 404 concealment
Concealment policy does not reveal protected existence through shell/preload/counters beyond accepted profile.

### FD-80 — Rate/abuse on auth-sensitive actions
Where configured, shared RLT applies without becoming route authorization or using spoofable client identity.

# 6. Component, data, Query and action fixtures

### FD-81 — Component Blueprint revision pinning
Route pins/resolve compatible published CBP revision rather than editor Draft.

### FD-82 — Component binding authorization
DVR/DSR source resolution applies current source/field Policy before rendering.

### FD-83 — Component output escaping
HTML/attribute/URL/JSON contexts escape according to renderer; trusted markup is explicit.

### FD-84 — Query parameter binding
Route/user/filter inputs bind only typed Query parameters; no raw SQL/order/source identifiers.

### FD-85 — Query row authorization
Query result candidate set cannot expose rows the principal may not view under resource Policy.

### FD-86 — Protected aggregate/count
Counts/facets/totals do not leak hidden resources through aggregate side channel outside explicit policy.

### FD-87 — Cursor/page token isolation
Pagination token cannot move principal/site into another result set.

### FD-88 — CRUD create
Create Ability validates schema/Policy and does not infer write from Data Source read capability.

### FD-89 — CRUD update IDOR
Target ID is reauthorized and protected fields remain owner-specific.

### FD-90 — CRUD delete
Delete requires dedicated capability/resource Policy and recovery/confirmation semantics as domain requires.

### FD-91 — Bulk action bounded target set
Bulk selection cannot include unseen/forged IDs and reauthorizes each target or certified bounded set.

### FD-92 — Form submission
FM server validation/authorization/idempotency remain authoritative; visible Dashboard form does not bypass them.

### FD-93 — Workflow trigger
Dashboard action invokes typed Workflow/Ability with actor/scope and does not run arbitrary PHP/code.

### FD-94 — Settings component
Settings read/write uses typed Settings service and field/scope Policy; no raw option access.

### FD-95 — User Profile component
UP dedicated identity/security-action boundaries remain intact.

### FD-96 — Membership component
Plan/enrollment/billing actions preserve MBR/provider authority; route visibility never grants commercial mutation.

# 7. Cache, client navigation, assets and browser state fixtures

### FD-97 — CAC route descriptor key
Compiled route cache keys include Dashboard/revision/dependency generation.

### FD-98 — CAC protected representation key
Principal/resource/site/Membership/Policy dimensions prevent protected response sharing.

### FD-99 — Public-to-private transition
Publishing route from public to protected invalidates public/full-page cache before protected claim.

### FD-100 — Private-to-public transition
Making route public requires explicit review and removes principal-only fragments/secrets from representation.

### FD-101 — Membership revoke generation
Revocation invalidates route/nav/count/component caches through CAC without relying on TTL only.

### FD-102 — Resource update generation
Changed entity/field/relation invalidates dependent component representation according to declared dependency graph.

### FD-103 — Cache backend unavailable
Correctness/auth remain server-authoritative; safe no-cache fallback rather than stale privileged allow.

### FD-104 — CDN full-page cache
Private routes are bypassed/vary-protected under certified profile; WPE does not claim control of unknown CDN config.

### FD-105 — Service worker/browser cache
If present, private response caching/logout/revocation behavior is tested; stale offline cache limitation explicit.

### FD-106 — Client router stale descriptor
SPA/enhanced navigation detects revision/auth changes and does not trust stale client route policy.

### FD-107 — Prefetch cancellation/revoke
Prefetched protected data is not later shown after access revoke without revalidation.

### FD-108 — Multiple tabs
One tab's logout/revoke/edit cannot let another tab perform stale privileged action beyond certified session semantics.

### FD-109 — ASR shell asset generation
Build/manifest/hash changes invalidate asset references; stale JS/CSS does not silently execute incompatible route contract.

### FD-110 — Module asset unload
Disabled module/component assets disappear from unrelated Dashboard pages/routes.

### FD-111 — Duplicate library/version conflict
Builder/theme/component assets cannot inject competing React/runtime/global library that corrupts WPE shell under certified profile.

### FD-112 — CSP/security-header compatibility
Frontend assets/components operate under certified header profile without requiring unsafe broad CSP exceptions by default.

# 8. Navigation, SEO, accessibility and theme compatibility fixtures

### FD-113 — Navigation Definition Draft
Draft menu/nav changes do not affect live shell.

### FD-114 — Active-route state
Current item determination uses canonical route identity and cannot reveal hidden protected parent metadata.

### FD-115 — External navigation URL safety
External links are explicit/sanitized and do not become intended-return/auth redirects.

### FD-116 — Dynamic nav label escaping
DVR-provided labels are escaped and authorization-aware.

### FD-117 — Badge count authorization
Counters are based only on authorized rows/aggregates and cache dimensions.

### FD-118 — Breadcrumb ancestor deny
Denied ancestor data is not exposed merely because child route is accessible.

### FD-119 — Page title/meta leakage
Private entity title/description not emitted before route/resource Policy.

### FD-120 — Sitemap exclusion
Private/non-indexable routes never appear in WPE-generated public sitemap.

### FD-121 — Canonical URL
Public route canonical URL uses trusted host/scheme/path and cannot be host-header poisoned.

### FD-122 — Theme header/footer integration
Dashboard layout composition does not duplicate/omit theme shells unpredictably for supported profile.

### FD-123 — Full-width/blank template profile
Template choice remains presentation; it cannot bypass WordPress auth/Policy or required accessibility landmarks.

### FD-124 — Keyboard focus after client navigation
Focus moves predictably to page heading/main region and skip/navigation semantics remain usable.

### FD-125 — Modal/destructive confirmation focus
Focus trap/return/labels work and confirmation does not rely on visual-only state.

### FD-126 — Error accessibility
Denied/validation/loading/error states expose meaningful semantics without protected data leakage.

### FD-127 — Responsive table/list actions
Mobile collapsed actions preserve same authorization and labels; hidden desktop control is not duplicated as unauthorized action.

### FD-128 — RTL route/navigation regression
RTL changes layout only, not path/key/action identity or keyboard sequence correctness.

# 9. Multisite, lifecycle, portability and module-state fixtures

### FD-129 — Durable site ownership
Dashboard/route/nav definitions store explicit site scope; current blog context never becomes authority.

### FD-130 — Network Dashboard policy
Network floor/restriction requires network authority and merges deterministically with site definitions.

### FD-131 — Site cannot weaken network floor
Import/UI/API cannot expose network-disabled component/route feature.

### FD-132 — Same route path across sites
Domain/subdirectory routing resolves correct site and never cross-serves cache/data.

### FD-133 — New-site provisioning
Site receives documented default/network Dashboard configuration only; no unrelated site's private routes/cache/session data.

### FD-134 — Site clone
Definitions/components remap stable identities and environment-specific references; cached auth/session facts are not cloned.

### FD-135 — Domain mapping change
Canonical URL/rewrite/cache keys revalidate after host change; old host cannot route to wrong site data.

### FD-136 — Site archive/deactivate
Protected Dashboard behavior follows lifecycle state; stale cached page cannot remain usable contrary to policy.

### FD-137 — Site deletion
Definitions/value/cache/assets cleanup follows LC without deleting network/shared components incorrectly.

### FD-138 — Restore
Restored route definitions/cache/session-related state revalidate; copied active auth/cache facts do not become current authority.

### FD-139 — Import package
Dashboard import revalidates route collisions, policies, dependencies, builder adapters, site scope and version compatibility.

### FD-140 — Cross-version Dashboard Definition
Supported old schema migrates explicitly; unsupported version degrades/blocks rather than arbitrary rendering.

### FD-141 — Module disable
MLC disable removes owned routes/components/assets as declared while preserving data/config by default and preventing orphan insecure route.

### FD-142 — Dependency disable
Missing Query/CBP/ASR/Membership/UP/RA dependency degrades affected route/component without fail-open.

### FD-143 — Pro expiry
Safe deployed runtime follows entitlement contract; protected access remains protected even if editing locks.

### FD-144 — Re-enable/upgrade
Compatible return revalidates descriptor/cache/adapter versions before declaring route healthy.

# 10. Privacy, errors, audit and security diagnostics fixtures

### FD-145 — PDL page data classification
Bootstrap/preload/component data fields carry privacy class and public/private exposure semantics.

### FD-146 — Browser state secret exclusion
No Vault secrets/passwords/reset/session/Application Password tokens appear in HTML/JS bootstrap/local storage.

### FD-147 — Error redaction
Public/admin errors exclude SQL, filesystem paths, secrets, another site's/user's identifying data beyond permitted message.

### FD-148 — Stable ERR route categories
Not found/unauthenticated/forbidden/concealed/conflict/dependency-degraded/action-failed remain distinct machine states.

### FD-149 — Action partial failure
Multi-target/Workflow/provider action reports partial/unknown truth and UI does not claim all-success.

### FD-150 — Audit sensitive action
Mutation/navigation config/preview-as-user high-risk actions record safe actor/target/scope/result/correlation metadata.

### FD-151 — Preview-as-user audit
Simulation actor and simulated principal remain distinct; no audit impersonation ambiguity.

### FD-152 — Support diagnostics
Shows route graph/revision/dependency/cache/asset/theme/permalink status without protected values/secrets.

### FD-153 — Support bundle route inventory
Private route names/metadata are disclosed only to authorized support viewer and redacted by policy.

### FD-154 — Broken Dashboard recovery
Native WordPress admin/login remains reachable according to recovery architecture; Dashboard failure cannot become auth bypass/lockout.

### FD-155 — Open redirect fuzz
Route/login/logout/action return parameters reject scheme-relative, encoded, Unicode and nested redirect tricks.

### FD-156 — XSS fuzz in dynamic labels/content
Route params/DVR values/errors/entity labels cannot create stored/reflected DOM/SSR XSS in certified renderer.

### FD-157 — CSRF mutation
Browser mutations require certified nonce/CSRF contract; GET/link visibility cannot perform destructive action.

### FD-158 — Clickjacking/header profile
Sensitive Dashboard actions use certified frame/header policy where applicable without claiming headers alone are authorization.

### FD-159 — RLT abuse-sensitive endpoints
Login/action/form endpoints can compose RLT, but RLT allow/deny stays independent from Policy.

### FD-160 — Privacy erase impact
User erasure/revocation updates Dashboard-visible personal data/caches according to PDL without deleting global identity incorrectly.

# 11. Performance, scale and regression fixtures

### FD-161 — Single static route baseline
Cold/warm route resolution, Policy, SSR, assets, queries and memory measured.

### FD-162 — 100/1000 route graph
Compile/match/navigation generation remain bounded; no O(all routes) DB load per ordinary request beyond accepted in-memory descriptor cost.

### FD-163 — Deep navigation graph
Maximum depth/fan-out obeys limits and rendering/access pruning stays bounded.

### FD-164 — 100/1000 navigation counters
Batch Query strategy avoids N+1 and preserves authorization.

### FD-165 — 100k/1M Query-backed rows
Listing route keeps bounded pagination/count/hydration and no private aggregate leak.

### FD-166 — Many components page
SSR/data/asset orchestration remains bounded; duplicate dependencies coalesce safely.

### FD-167 — Persistent cache profile
CAC keys/generations preserve principal/site/revision isolation under certified backend.

### FD-168 — No persistent cache profile
Correctness remains with DB/request-local caches and performance reported honestly.

### FD-169 — Concurrent revocation/load
High traffic while RA/MBR/resource access changes produces no stale privileged response outside correctness window.

### FD-170 — Concurrent publish/load
Old/new complete route descriptors serve consistently; no partial graph fatal or auth bypass.

### FD-171 — 100/1k/10k-site network
Route Definition/cache/asset identities remain site-safe and network policy lookup bounded.

### FD-172 — Theme/builder compatibility matrix
Certified theme/builder versions pass critical route/render/action flows; unknown versions become unverified.

### FD-173 — Browser compatibility matrix
Supported browsers pass full refresh/client navigation/history/forms/focus/private-cache flows.

### FD-174 — Slow network/client race
Delayed old response cannot overwrite newly denied/newer revision UI state without revalidation/version checks.

### FD-175 — Fault injection
Missing cache, dependency exception, Query failure, asset 404 and server error remain fail-safe for authorization with truthful degraded UX.

### FD-176 — End-to-end Dashboard security profile
Representative public/private/member/admin/profile/CRUD/listing/Multisite routes show zero direct-route IDOR, zero cross-principal/site cache leakage, zero mutation-policy bypass and truthful route/dependency/revocation behavior.

# 12. Pass / stop-the-line gates

Certification fails if:
- direct-route IDOR or client-side-only authorization exists;
- protected data leaks through HTML/preload/title/count/SEO/cache/prefetch;
- privileged response is reused across principal/site;
- intended-return/open redirect or route-normalization bypass exists;
- visible/listable data grants unauthorized mutation Ability;
- missing dependency/module/version causes authorization fail-open;
- cache/CDN/service-worker state preserves revoked protected access outside CAC contract;
- Site route/cache/data crosses another site or weakens network floor;
- secrets/security tokens enter browser state/log/support output;
- broken Dashboard prevents native recovery or requires auth bypass.

# 13. Required future evidence report

Include:
- exact WP/theme/permalink/MSI/cache/browser/builder/runtime profile;
- FD-01…FD-176 pass/fail/NA;
- route compile/collision/normalization evidence;
- unauth/auth/RA/MBR/UP revocation and IDOR tests;
- DSR/QRY/DVR/CLG/CBP component/action evidence;
- CAC/CDN/browser cache/prefetch/history evidence;
- ASR/build/theme/SEO/accessibility/mobile/RTL results;
- Multisite/lifecycle/import/version/dependency results;
- privacy/redaction/Audit/recovery security results;
- 1/100/1000-route, 1000-counter, 1M-row, many-component and 10k-site measurements;
- unsupported/degraded profiles.

# 14. Current state

**FD fixtures executed: 0/176.**  
Frontend Dashboard runtime/theme/builder/browser certifications: **0**.

No rewrite rule, route hook, frontend render, Policy/action test, asset build, browser test, cache mutation, Multisite operation or WordPress runtime benchmark has executed.

# 15. Development gate

Execution requires explicit owner consent under ADR-0014. This protocol is planning/evidence only.