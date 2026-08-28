# WPEssential — Admin Menu Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP47`  
Related: ADR-0037, ADR-0104, KPA, RA, CAC, ASR, ERR, VER, MLC, UI, Multisite/Site Lifecycle, ADR-0014.

## 1. Purpose

Define executable evidence required before Custom Admin Menu Builder can claim safe compatibility with WordPress/core/third-party admin navigation, stable target identity, role/audience presentation, recovery, Multisite isolation or performance.

The non-negotiable invariant is:

**Admin-menu presentation is not authorization. Rename, reorder, hide, move, group, add-link or audience filtering never grants or revokes the owning screen's WordPress capability/resource Policy. Direct-screen authorization remains authoritative.**

## 2. Accepted runtime architecture

`WordPress/core/plugin menu registration → context-specific discovered menu registry → normalized stable target descriptors → published WPE transformation Definition → capability/recovery/audience validation → deterministic presentation plan → wp-admin menu output → diagnostics`

WPE does not treat copied raw `$menu`/`$submenu` arrays as durable authority. Runtime discovery is reconciled against stable target descriptors and versioned rules.

## 3. Runtime certification profile

Every future certification records:
- WordPress/PHP versions;
- Site Admin / Network Admin / User Admin contexts supported;
- active core/plugin/theme/admin-menu providers and versions;
- menu hook priorities and relevant `custom_menu_order` / `menu_order` behavior;
- WPE Admin Menu Definition/compiler version;
- KPA/RA/Policy versions;
- CAC/ASR/UI profile where used;
- MLC/VER/import profile;
- single-site/Multisite topology and Site Lifecycle state;
- locale/RTL/accessibility profile;
- diagnostics/Audit/ERR profile.

Certification is profile-specific. A plugin or WordPress update that changes menu registration identity/order/capability can invalidate support evidence.

# 4. Original canonical fixtures — preserved

### AM-01 — Site Admin basic discovery
Core + WPE + third-party menu tree normalized correctly.

### AM-02 — Network Admin discovery
Network tree is separate from site tree.

### AM-03 — User Admin unsupported/supported distinction
No accidental rule leakage.

### AM-04 — Rename core/third-party menu
Only intended presentation changes.

### AM-05 — Reorder with `custom_menu_order`
WPE composes with WordPress ordering correctly.

### AM-06 — Unmentioned menu items
They remain available and preserve relative behavior according to WordPress semantics.

### AM-07 — Multiple ordering plugins
Detect/diagnose overwritten-by-later-hook/conflict rather than claim guaranteed order.

### AM-08 — Late registered plugin menu
Discovered/applied or diagnosed according to chosen hook profile.

### AM-09 — Missing target
Rule ignored/degraded; no nearest-match mutation.

### AM-10 — Ambiguous slug
Block/diagnose.

### AM-11 — Parent slug changed after plugin update
No accidental move/hide of unrelated item.

### AM-12 — Hide menu item
Sidebar item disappears for target audience, but direct URL capability behavior stays unchanged.

### AM-13 — Unauthorized actor direct URL
Still denied by owning screen; menu visibility irrelevant.

### AM-14 — Authorized actor hidden link direct URL
If original screen still allowed, hiding must not falsely claim it is disabled.

### AM-15 — Add WPE page
Explicit capability required; unauthorized direct URL denied.

### AM-16 — External link
HTTPS/scheme validation, safe target/rel; no callback authority.

### AM-17 — Malicious javascript/data URL
Rejected.

### AM-18 — Move incompatible third-party screen
Marked unsupported/degraded instead of forced array surgery.

### AM-19 — Role-specific transformation
Only presentation for intended role/context; authorization unchanged.

### AM-20 — User-specific preference
Cannot reveal Policy-denied item.

### AM-21 — Self-lockout/recovery path
Rule set attempting to hide all WPE recovery navigation from all recovery principals is blocked/warned according to invariant.

### AM-22 — Safe mode constant/recovery mode
Custom transformations skipped; original WordPress/plugin navigation restored without auth bypass.

### AM-23 — Corrupt WPE rule store
Fail-open to original navigation and diagnostics.

### AM-24 — Invalid capability condition
Fails safe, no unauthorized link exposure.

### AM-25 — Pro expiry
Safe deployed transformation follows ADR-0007; editing may lock without removing navigation/data.

### AM-26 — Import with missing target
Imported rule remains disabled/deferred.

### AM-27 — Import ambiguous target
Requires manual resolution.

### AM-28 — Site/Network import mismatch
Cannot bind site rule to network page silently.

### AM-29 — Nested/native depth
WPE does not fabricate unsupported multi-level core sidebar nesting.

### AM-30 — WPE parent invariant
Modules remain under canonical WPE parent unless accepted transformation preserves recovery/IA constraints.

### AM-31 — Third-party capability changes
Menu transformation does not rewrite owning plugin capability requirement.

### AM-32 — Role Manager change while menu rules active
Presentation recomputes from current effective authorization/audience without stale privilege inference.

### AM-33 — 100/500/1000 discovered entries synthetic scale
Transformation remains bounded and deterministic.

### AM-34 — Every-admin-request overhead
Measure server time/memory/no front-end asset load.

### AM-35 — Builder assets
React/editor assets load only on WPE menu-builder screen, not globally across wp-admin.

### AM-36 — Direct navigation bookmarked before rule change
Authorization remains correct; renamed/moved presentation does not create unsafe redirects.

### AM-37 — Plugin deactivation/reactivation
Target missing/deferred then rebinds only to same stable identity semantics.

### AM-38 — Site deletion/Multisite lifecycle
Scoped rule cleanup/retention does not affect other sites/network.

### AM-39 — Same slug in Site and Network Admin
Scope disambiguation prevents cross-context rule application.

### AM-40 — Competing WPE rules same specificity
Explicit priority + deterministic UUID tie-break and diagnostics match paper contract.

# 5. Definition, publication and stable target identity fixtures

### AM-41 — Draft Menu Definition not executable
Draft transformation Definition never changes live wp-admin navigation.

### AM-42 — Published revision pinning
Live transformation resolves one complete published revision and its compiled plan.

### AM-43 — Atomic-ish revision changeover
Concurrent admin requests see old or new complete plan, not partial mixed rules.

### AM-44 — Stable rule UUID
Rule identity survives label/order edits and is distinct from target menu identity.

### AM-45 — Stable target descriptor
Target descriptor includes context/parent/slug/owner hints sufficient to avoid cross-context accidental match.

### AM-46 — Raw numeric menu position is not durable identity
Changing third-party position does not retarget WPE rule to another item.

### AM-47 — Display label is not durable identity
Localized/renamed labels do not cause nearest-label target binding.

### AM-48 — Callback URL/query normalization
Equivalent supported admin-page URLs normalize deterministically without treating arbitrary query string as stable identity.

### AM-49 — Duplicate target descriptors
Ambiguous discovered candidates block/degrade rather than arbitrary winner.

### AM-50 — Owner/plugin identity available
When provider identity is known it participates in diagnostics/rebinding, not capability authority.

### AM-51 — Owner/plugin identity missing
Unknown owner remains explicit and cannot be silently treated as WPE-owned safe target.

### AM-52 — Parent identity migration
Supported parent change requires explicit target re-resolution and does not hijack unrelated submenu.

### AM-53 — Rule schema compatible migration
Supported old rule schema migrates under VER without changing hide/reorder semantics silently.

### AM-54 — Unsupported future rule schema
Unknown newer schema does not execute permissive/best-effort transformations.

### AM-55 — Rule dependency missing
Missing capability/audience/target/provider dependency degrades selected rule only and never grants screen access.

### AM-56 — Definition delete with live deployment
Delete/archive behavior follows MLC; stale compiled transformations cannot remain hidden indefinitely without diagnostics.

# 6. Runtime discovery, hooks, ordering and third-party conflict fixtures

### AM-57 — Early core registrations
Core entries registered before WPE discovery are captured exactly once.

### AM-58 — Standard plugin registration
Plugin top/submenu entries at ordinary priority are discovered with correct parent/slug/capability metadata.

### AM-59 — Very late plugin registration
Late entry is either transformed in certified hook profile or reported missed/uncertified; no false claim.

### AM-60 — Plugin mutates entry after WPE
Later label/position/capability mutation is detected/diagnosed where observable and not represented as guaranteed WPE final state.

### AM-61 — Plugin removes entry after WPE
WPE does not recreate/authorize removed third-party page accidentally.

### AM-62 — Plugin replaces callback same slug
Stable slug with changed callback/owner/capability triggers drift warning/review.

### AM-63 — Duplicate slug different parents
Parent/context disambiguation preserves intended submenu target.

### AM-64 — Duplicate-looking Site/Network slugs
Context identity prevents cross-application.

### AM-65 — `custom_menu_order` disabled
WPE ordering behavior matches actual WordPress semantics and does not assume custom order is active.

### AM-66 — `custom_menu_order` enabled externally
WPE composes deterministically or reports conflict; does not overwrite unrelated plugin order blindly.

### AM-67 — Multiple `menu_order` filters
Final observed ordering and conflict source are diagnosable; support claim is version/profile scoped.

### AM-68 — Position collision
Multiple items with same/near numeric position retain deterministic relative order without data loss.

### AM-69 — Separator discovery
Native separators remain presentation-only and are not treated as capability targets.

### AM-70 — Hidden core menu via another plugin
WPE does not infer authorization loss from absence in discovered presentation.

### AM-71 — Dynamic menu registration by current user
Discovery differences across principals are handled without caching one user's tree globally.

### AM-72 — Context-dependent registration
Screen/network/site/user-admin-specific entries remain scoped to matching runtime context.

# 7. Authorization, audience, Role/Policy and recovery fixtures

### AM-73 — Original page capability preserved
Rename/reorder/move/hide never alters registered owning capability.

### AM-74 — Resource-aware screen Policy preserved
WPE presentation cannot bypass resource/object Policy used by owning screen/route.

### AM-75 — Added WPE page capability callback
New WPE page registers explicit KPA capability/Policy and direct URL deny works independently from menu display.

### AM-76 — Added link to protected WPE route
Link visibility follows audience/Policy, but destination still reauthorizes server-side.

### AM-77 — Audience role condition
Role match controls presentation only and uses current RA truth.

### AM-78 — Audience capability condition
Capability condition is evaluated from current WordPress authority; cached old allow is invalidated under CAC/RA.

### AM-79 — Audience user condition
User-specific preference/transformation cannot expose a page the user cannot access.

### AM-80 — Audience Membership condition
Membership-based menu visibility does not grant route/content entitlement and revocation recomputes presentation.

### AM-81 — Audience CLG result
Conditional logic `true` only affects presentation; it never grants capability.

### AM-82 — Capability revoke mid-session
Next admin request recomputes tree; stale menu can disappear while bookmarked direct URL remains correctly denied.

### AM-83 — Capability grant mid-session
Menu may become visible according to cache correctness; grant is sourced from WordPress authority, not WPE menu state.

### AM-84 — Last recovery navigation hidden
Published plan that removes all visible WPE/native recovery paths for viable recovery principals is blocked/warned according to accepted recovery invariant.

### AM-85 — Native direct recovery still authoritative
Even with WPE menu hidden/corrupt, authorized user can reach native repair URL/screens where WordPress permits.

### AM-86 — Safe mode does not authenticate
Recovery mode restores presentation only for already authenticated/authorized principal.

### AM-87 — Safe mode network context
Site-level recovery cannot mutate/disable network-owned menu policy without network authority.

### AM-88 — Recovery after bad imported rules
Imported corrupt/conflicting transformation can be disabled through native/CLI/recovery path without anonymous bypass.

# 8. Transformation semantics, links and added pages fixtures

### AM-89 — Rename top-level label only
Menu label changes while page title/screen heading remain owner-defined unless explicit supported adapter changes them separately.

### AM-90 — Rename submenu label only
Submenu presentation changes do not alter route/callback identity.

### AM-91 — Reorder top-level
Ordering moves intended item only; unmentioned items preserve stable relative behavior.

### AM-92 — Reorder submenu
Ordering remains within compatible parent and does not cross parent implicitly.

### AM-93 — Hide top-level with visible children
Behavior is explicit; WPE does not create inaccessible orphan navigation or infer child auth.

### AM-94 — Hide submenu only
Parent remains intact; direct child screen authorization unchanged.

### AM-95 — Move compatible submenu
Move succeeds only when destination parent/context supports owning page semantics.

### AM-96 — Move across Site/Network contexts
Rejected; presentation move cannot change runtime admin context.

### AM-97 — Group WPE items
Grouping preserves stable child targets and recovery entry.

### AM-98 — Separator/group collapse
Empty group after audience pruning is removed without leaking hidden child metadata.

### AM-99 — Internal wp-admin link validation
Generated internal link uses trusted admin URL/context and cannot be host/query poisoned.

### AM-100 — Frontend link validation
Frontend link is escaped/canonicalized and does not inherit admin callback authority.

### AM-101 — External HTTPS link allow
Allowed explicit external URL uses safe escaping and target/rel policy.

### AM-102 — Scheme-relative/encoded javascript link
Rejected after canonicalization, including Unicode/control-character variants.

### AM-103 — Added WPE page callback ownership
Only registered WPE renderer/controller can back page; user configuration cannot choose arbitrary PHP callback/class/function.

### AM-104 — Added page error
Renderer failure shows scoped ERR UX and native admin remains usable; no fatal loop.

# 9. Cache, assets, UI and browser-state fixtures

### AM-105 — CAC discovered registry key
Any cached normalized registry is partitioned by admin context, site/network, relevant provider/menu-generation inputs.

### AM-106 — CAC transformation plan key
Compiled plan includes Definition revision/audience dependencies and cannot cross sites/principals when personalized.

### AM-107 — Plugin activation invalidation
New menu provider invalidates/rebuilds registry/plan before support claim.

### AM-108 — Plugin deactivation invalidation
Removed targets become missing/deferred rather than stale clickable links.

### AM-109 — RA revoke invalidation
Capability/audience caches recompute so presentation does not preserve stale privileged item indefinitely.

### AM-110 — Membership revoke invalidation
Membership-targeted item disappears under accepted generation semantics without changing destination authorization.

### AM-111 — Cache backend unavailable
Correctness falls back to runtime discovery/recompute; stale cache never becomes authorization.

### AM-112 — Principal-specific cache isolation
One admin's customized tree never serves another user with different audience/permissions.

### AM-113 — ASR builder screen assets
Admin Menu editor assets load only on intended WPE route/screen.

### AM-114 — No runtime React dependency for ordinary menu transform
Every admin request does not bootstrap full editor/React app merely to apply server-side transformation.

### AM-115 — Duplicate asset/runtime conflict
Editor coexists with WP/admin plugin assets without injecting competing React/global runtime under certified profile.

### AM-116 — Builder asset failure
Editor degrades, but live native menu and authorization remain usable.

### AM-117 — Browser collapsed menu state
WordPress/user UI collapse preferences do not alter transformation identity/authorization.

### AM-118 — Screen options/help tabs
Menu transformation does not remove or grant unrelated native screen options/help authorization.

### AM-119 — Admin color scheme/locale
Presentation stays readable and identity remains unchanged across user locale/color preferences.

### AM-120 — RTL keyboard navigation
Transformed menu preserves usable DOM/tab/ARIA behavior in RTL without duplicated actionable items.

# 10. Multisite, admin contexts, lifecycle and portability fixtures

### AM-121 — Durable site rule ownership
Site menu Definition stores explicit site scope; `switch_to_blog()` is not authority.

### AM-122 — Network rule ownership
Network Admin transformations require network authority and explicit network scope.

### AM-123 — User Admin isolation
If supported, User Admin has separate registry/rules and cannot inherit site/network mutations accidentally.

### AM-124 — Network floor/recovery item
Network may enforce mandatory recovery/navigation item that site rule cannot hide where declared.

### AM-125 — Site stricter presentation
Site can add/hide local presentation within allowed rules without editing network Definition.

### AM-126 — New-site provisioning
New site receives only documented default/network menu configuration, not another site's personalized rules.

### AM-127 — Site clone
Clone remaps site-owned rule IDs/targets as needed and revalidates plugin/menu availability; user-specific cache/preferences not copied as authority.

### AM-128 — Domain/path change
Admin URL generation revalidates environment while stable target identity remains context-based.

### AM-129 — Site archive/deactivate
Lifecycle state affects available admin context according to WordPress/LC; stale cached links cannot target wrong site.

### AM-130 — Site deletion
Site-owned rules/cache/config cleanup does not delete network/other-site rules.

### AM-131 — Restore
Restored rule Definitions/provider inventory/cache require revalidation; stale plugin target binding is not assumed healthy.

### AM-132 — Import rule package
Import revalidates context, target descriptor, provider/version, capability/audience dependency and conflict before publish.

### AM-133 — Import numeric IDs
Numeric menu positions/site IDs are not portable stable target authority.

### AM-134 — Cross-version import
Supported schema migrates under VER; unsupported version stays disabled/degraded.

### AM-135 — MLC module disable
Disabling Admin Menu module removes only WPE presentation transformations and preserves native menu/authorization.

### AM-136 — Pro expiry/re-enable
Safe deployed transformation follows entitlement contract; re-enable/upgrade revalidates targets/dependencies before editor healthy status.

# 11. Privacy, Audit, ERR, diagnostics and recovery fixtures

### AM-137 — Rule Audit safe diff
Configuration change records actor/scope/rule IDs/transformation class/result, not passwords/tokens/private page data.

### AM-138 — Audience privacy
User/role/Membership targeting metadata is visible only to authorized editors and minimized in export/logs.

### AM-139 — Dynamic label privacy
DVR-derived label cannot expose private entity/user data to a principal not authorized to see it.

### AM-140 — Stable ERR target-missing code
Missing, ambiguous, context-mismatch, capability-denied, provider-drift and conflict states are distinct machine categories.

### AM-141 — Conflict diagnostics
Shows which rules/providers/hook phases conflict without dumping arbitrary callbacks/secrets.

### AM-142 — Runtime exception
Transformation failure fails back to safe/native menu where possible and cannot fatal all wp-admin navigation.

### AM-143 — Corrupt compiled plan
Plan checksum/schema failure discards/degrades WPE transformation rather than executing partial garbage.

### AM-144 — Support bundle
Includes safe context/provider/target/rule/conflict/cache information with preview/redaction.

### AM-145 — Recovery status notice
Authorized admin sees when safe mode/degraded native menu is active; notice itself does not expose bypass secret.

### AM-146 — Recovery mode exit
Returning to normal mode revalidates Definition/provider inventory and cannot silently reactivate known-corrupt plan.

### AM-147 — Privacy export
Personal menu preferences/targeting are exported only where applicable under PDL; global configuration remains separate.

### AM-148 — Privacy erase
Erasing user-specific preferences does not delete shared/global/network menu Definitions or WordPress capabilities.

### AM-149 — Log amplification
Repeated missing/conflicting target on every admin request is rate/sampled/bounded and does not create log DoS.

### AM-150 — Error localization
Localized text is separate from stable machine code/target identity and does not affect matching.

### AM-151 — Third-party unsupported notice
Unsupported target/provider behavior is truthfully marked unverified rather than silently claimed compatible.

### AM-152 — Native admin recovery invariant
Even with WPE rule store/editor unavailable, WordPress login/admin recovery remains possible for a legitimate authorized principal.

# 12. Performance, compatibility and fault-injection fixtures

### AM-153 — Zero-rule overhead
WPE enabled with no applicable transformations has bounded request cost.

### AM-154 — 10/100/1000 rule compile
Definition compile/match time/memory remain bounded and deterministic.

### AM-155 — 100/500/1000 discovered entries
Registry normalization/lookup scales without quadratic hidden target search.

### AM-156 — Many submenu parents
Large parent/child tree transformations remain bounded and preserve stable relative order.

### AM-157 — Personalized audience scale
Role/cap/user/Membership conditions are batched/cached safely and do not trigger per-item remote/DB N+1.

### AM-158 — Every-admin-request DB budget
Ordinary transformed wp-admin request uses bounded local reads and no remote service dependency.

### AM-159 — Persistent cache profile
CAC backend preserves context/site/principal/revision isolation under supported drop-in.

### AM-160 — No persistent cache profile
Correctness remains with request-local/DB state and performance reported honestly.

### AM-161 — WordPress core version matrix
Supported WP versions verify admin menu hook/order/context behavior instead of assuming static globals forever.

### AM-162 — WooCommerce-like large plugin menu
Representative large third-party suite passes discovery/rename/reorder/hide without capability mutation.

### AM-163 — Security plugin menu
Representative security/recovery-sensitive plugin target is not hidden/moved incompatibly without explicit support evidence.

### AM-164 — Plugin registration priority matrix
Early/normal/late priority fixtures document support boundaries.

### AM-165 — Plugin slug migration
Version update changing slug/parent produces missing/drift state rather than wrong-target transformation.

### AM-166 — Concurrent Definition publish
Parallel editors use version/precondition semantics and cannot create mixed live plan.

### AM-167 — Cache stale fault
Injected stale normalized registry cannot cause authorization change or wrong target; generation/version checks recover.

### AM-168 — Corrupt rule fault
Malformed stored rule degrades one/all transformation safely according to compile boundary; native menu remains recoverable.

### AM-169 — Missing plugin fault
Provider disappears between compile and request; stale link/target is suppressed/degraded without fatal.

### AM-170 — Late capability drift fault
Provider changes capability after WPE planning; direct screen remains native authority and diagnostics invalidate assumption.

### AM-171 — 100/1k/10k-site metadata
Site-scoped rule storage/cache identities remain isolated and network lookup bounded.

### AM-172 — Network admin large menu
Large network plugin set remains deterministic and site rules never apply.

### AM-173 — Accessibility regression suite
Keyboard/focus/ARIA/contrast/readability of transformed/editor navigation passes certified UI baseline.

### AM-174 — Localization regression suite
Long translations/RTL/menu labels preserve target identity and usable layout.

### AM-175 — Recovery fault injection
Corrupt Definition/cache/provider conflict always leaves a documented native/CLI/safe-mode path without auth bypass.

### AM-176 — End-to-end Admin Menu safety profile
Representative Site/Network/user-role/plugin-conflict/import/recovery scenarios show zero screen-authorization mutation, zero cross-context target application, zero unsafe link execution and truthful degraded/recovery behavior.

# 13. Pass / stop-the-line gates

Certification fails if:
- hiding/reordering/moving changes actual screen capability/resource authorization;
- a WPE added page/link creates unauthorized callback/action access;
- ambiguous/missing/changed third-party target silently binds wrong screen;
- unsafe `javascript:`, `data:`, encoded or control-character URLs are allowed;
- Site rules mutate Network/User Admin tree or another site's rules;
- stale personalized cache exposes privileged navigation metadata indefinitely or crosses users/sites;
- corrupt rules/provider conflicts make wp-admin unrecoverable without auth bypass;
- recovery mode authenticates/mints privilege;
- WPE claims guaranteed final ordering despite unsupported later-hook overwrite evidence.

# 14. Required future evidence report

Include:
- WP/context/plugin/hook/profile versions;
- AM-01…AM-176 pass/fail/NA;
- discovered normalized registry + stable target evidence;
- authorization/direct-screen parity tests;
- conflict/late-hook/provider-drift results;
- safe-mode/recovery evidence;
- CAC/ASR/UI/browser-state results;
- Multisite/Site Lifecycle/import/version evidence;
- privacy/Audit/ERR/support diagnostics;
- 0/10/100/1000-rule, 1000-entry and 10k-site performance measurements;
- unsupported menu patterns/providers.

# 15. Current state

**AM fixtures executed: 0/176.**  
Admin Menu runtime/provider compatibility certifications: **0**.

No admin-menu hook/filter/transformation, capability change, cache mutation, import, safe-mode runtime, Multisite operation, browser test or performance benchmark has executed.

# 16. Development gate

Execution requires explicit owner consent under ADR-0014. This protocol is planning/evidence only.