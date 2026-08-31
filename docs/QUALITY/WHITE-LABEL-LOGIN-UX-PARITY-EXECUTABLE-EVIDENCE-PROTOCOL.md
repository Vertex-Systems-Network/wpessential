# WPEssential — White-label & Login UX Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `WLB-001…WLB-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- White-labeling is presentation/experience only; hiding WordPress/WPE UI never grants or revokes authorization.
- Login branding does not replace WordPress authentication, reset, session, nonce, enumeration, or account-security semantics.
- Redirect and force-login behavior delegates to canonical authentication/access owners and must not create loops or lock out recovery routes.
- CAPTCHA/rate limiting/login alias/social login remain adapter-owned security surfaces; WLB does not create duplicate auth stacks.
- CSS/JS customization delegates to Safe Script/Tag and cannot introduce arbitrary PHP/eval/server execution.
- Network-enforced branding remains distinct from site-owned branding and cannot silently remove Super Admin recovery access.
- Client/role assignment controls presentation only and must not leak personalized branding through shared caches.
- Accessibility, recovery and critical security notices are correctness requirements, not optional visual polish.

## Exact fixtures

### Group 1 — branding profile
- `WLB-001` Create branding profile with stable key, organization/product name, logo refs, colors, support links, scope, status and revision.
- `WLB-002` Reject duplicate stable key within the same site/network namespace.
- `WLB-003` Update profile with expected revision and preserve prior revision/diff.
- `WLB-004` Stale revision update fails instead of overwriting newer branding.
- `WLB-005` Draft profile has no live effect until explicitly published/assigned.
- `WLB-006` Archived profile remains auditable but cannot receive new assignments without restoration.
- `WLB-007` Brand asset references resolve through Asset Registry and do not embed arbitrary private filesystem paths.
- `WLB-008` Export omits secrets and portable package keeps asset references/provenance.
- `WLB-009` Capability/Policy denial blocks create/update/publish even if UI controls are visible.
- `WLB-010` AI/MCP may draft branding but cannot publish/network-enforce without same Policy/approval.
- `WLB-011` Unknown future profile version fails typed or migrates explicitly; no silent reinterpretation.

### Group 2 — login token/layout rendering
- `WLB-012` Render configured login logo/background/panel/button tokens without changing authentication form action/security fields.
- `WLB-013` Missing logo asset falls back safely without breaking login form usability.
- `WLB-014` Branding CSS is scoped to login page and does not leak into frontend/admin unexpectedly.
- `WLB-015` Login form password/autocomplete/security attributes remain controlled by WordPress/security owner.
- `WLB-016` Login error presentation remains enumeration-safe according to auth policy.
- `WLB-017` Lost-password/reset routes receive compatible branding without altering token validation.
- `WLB-018` Two-factor/social-login adapter controls render in declared slots without WLB consuming their secrets.
- `WLB-019` Custom footer/help links validate scheme/origin and cannot inject javascript/data URLs.
- `WLB-020` Login page cache policy prevents user-specific/security state from being shared publicly.
- `WLB-021` Theme/plugin conflict that replaces login markup is detected and reported as degraded compatibility.
- `WLB-022` Disabling WLB restores Core/provider login presentation without disabling auth functionality.

### Group 3 — responsive/accessibility
- `WLB-023` Login layout remains usable at narrow/mobile viewport without horizontal clipping of required auth controls.
- `WLB-024` Keyboard tab order reaches username/email, password, submit, recovery and required provider controls logically.
- `WLB-025` Visible focus indicator remains present after branding CSS.
- `WLB-026` Text/background/button contrast meets configured accessibility gate or publish warns/blocks accordingly.
- `WLB-027` Error/success states use text/icon semantics and not color alone.
- `WLB-028` Zoom at 200% preserves critical login/recovery content without overlap.
- `WLB-029` Screen-reader labels remain bound to inputs after custom layout wrappers.
- `WLB-030` Reduced-motion preference disables nonessential branding animations.
- `WLB-031` RTL login layout mirrors correctly without reordering semantic form controls incorrectly.
- `WLB-032` High-contrast/forced-colors mode keeps controls distinguishable.
- `WLB-033` Accessibility regression blocks “golden client experience” certification even if visual design matches mockup.

### Group 4 — messages/help copy
- `WLB-034` Customize approved login/recovery/help copy without changing auth decision logic.
- `WLB-035` Copy supports localization and does not hard-code one language into network templates.
- `WLB-036` HTML-capable message fields use restricted sanitizer; scripts/event handlers are removed/rejected.
- `WLB-037` Error copy does not reveal whether a specific username/email exists when policy requires generic response.
- `WLB-038` Password reset guidance cannot include raw reset tokens in logs/preview.
- `WLB-039` Maintenance/support notice can be shown without disabling required auth/recovery links.
- `WLB-040` Legal/privacy links use configured URLs and are not inferred from arbitrary request parameters.
- `WLB-041` Message revision is versioned separately from authentication state.
- `WLB-042` Missing translation falls back to declared locale/default copy and never blank critical guidance silently.
- `WLB-043` Client-specific copy uses cache-safe assignment context and does not leak across tenants/sites.
- `WLB-044` AI/MCP can draft copy but cannot alter security/auth policy through message text.

### Group 5 — dashboard/welcome composition
- `WLB-045` Compose branded dashboard welcome panel using registered widgets/components only.
- `WLB-046` Hidden Core dashboard widget remains hidden presentation-only; direct capability/action remains unchanged.
- `WLB-047` Welcome panel content sanitizer prevents unsafe HTML/script injection.
- `WLB-048` Role/client-specific welcome content resolves only after server-authorized assignment context.
- `WLB-049` Empty/no-assignment state uses Core/default dashboard rather than broken blank shell.
- `WLB-050` Dismissible welcome state is user-specific and does not affect other users.
- `WLB-051` Dashboard branding cannot suppress critical security/update notices when policy marks them mandatory.
- `WLB-052` Network notice remains visible to required administrators even if site branding attempts to hide it.
- `WLB-053` Widget ordering changes are scoped to branded layout and do not mutate unrelated Dashboard Widgets definitions.
- `WLB-054` Cache keys include site/user-segment branding assignment where personalized output exists.
- `WLB-055` Disabling profile removes branded composition without deleting dashboard widget source data.

### Group 6 — admin bar/footer branding
- `WLB-056` Replace/add approved brand mark/text in admin bar through presentation adapter without changing destination permissions.
- `WLB-057` Hide selected presentation-only admin bar node while direct destination remains server-authorized independently.
- `WLB-058` Required account/logout/security nodes cannot be removed when recovery/accessibility policy marks them mandatory.
- `WLB-059` Footer text/link is sanitized and URL-scheme validated.
- `WLB-060` Admin bar frontend and wp-admin contexts use declared profile independently.
- `WLB-061` Mobile admin bar retains logout/profile/recovery access after branding changes.
- `WLB-062` Node ID conflict with third-party plugin is detected and reported instead of overwriting silently.
- `WLB-063` Network Admin can use distinct branding from site admin when configured.
- `WLB-064` Site admin cannot change network-enforced admin bar/footer branding without network capability.
- `WLB-065` Branding removal restores provider/Core nodes according to ownership record rather than deleting third-party nodes.
- `WLB-066` Audit records assignment/revision causing final admin bar/footer presentation.

### Group 7 — menu presentation profiles
- `WLB-067` Apply menu presentation profile by role/client/site through Admin Menu owner rather than duplicating authorization logic.
- `WLB-068` Hidden menu item direct URL remains governed by original capability/Policy.
- `WLB-069` Renamed menu label does not change underlying capability/action route.
- `WLB-070` Reordered menu remains keyboard accessible and retains current-item semantics.
- `WLB-071` Parent menu hidden while child remains required triggers explicit promotion/fallback logic instead of orphan inaccessible link.
- `WLB-072` Menu icon/label assets are sanitized and bounded.
- `WLB-073` Profile assignment conflict uses explicit precedence and explain output.
- `WLB-074` Network-enforced menu presentation cannot be overridden by site profile beyond allowed slots.
- `WLB-075` Third-party menu registered late is handled by compatibility lifecycle without broad wildcard hiding.
- `WLB-076` Safe-mode/recovery bypass can restore essential admin navigation for authorized recovery principal.
- `WLB-077` WLB menu profile export references canonical Admin Menu definitions instead of copying private callback code.

### Group 8 — role/client assignment
- `WLB-078` Assign branding profile to selected role and verify assignment affects presentation only for that role.
- `WLB-079` User-specific assignment overrides role default only according to documented precedence.
- `WLB-080` Site default applies when no user/role/client assignment exists.
- `WLB-081` Network enforced floor overrides site/user branding only in allowed token/surface set.
- `WLB-082` Client/agency segment is typed assignment metadata and not inferred from email/domain without explicit rule.
- `WLB-083` Multiple-role user receives deterministic assignment resolution with explain output.
- `WLB-084` Deleted role causes assignment to become unresolved/cleanup candidate, not automatically reassigned to a similarly named role.
- `WLB-085` Unauthorized operator cannot assign branding to administrator/network scopes.
- `WLB-086` Assignment change invalidates affected presentation caches only.
- `WLB-087` Shared cache cannot serve one client’s logo/support link to another client segment.
- `WLB-088` Branding assignment does not grant membership, role, capability or content access.

### Group 9 — redirects/force-login delegation
- `WLB-089` Post-login redirect delegates to auth/access redirect owner and validates destination as safe/internal or approved external.
- `WLB-090` Post-logout redirect does not create login/logout loop.
- `WLB-091` Force-login profile explicitly excludes login, reset, activation, privacy/legal and required health/webhook routes.
- `WLB-092` Redirect target containing open-redirect external host is rejected unless explicitly allowlisted.
- `WLB-093` Deep-link return-to destination is signed/bounded and permission rechecked after login.
- `WLB-094` Already-authenticated user is not repeatedly redirected through login route.
- `WLB-095` Recovery/Super Admin route remains reachable under site-level force-login/white-label configuration.
- `WLB-096` REST/API requests follow API-specific auth behavior and are not blindly HTML-redirected.
- `WLB-097` AJAX/admin-post actions are classified correctly and do not receive incompatible presentation redirects.
- `WLB-098` Redirect rule change has preview/simulation but simulation result is not authorization proof.
- `WLB-099` WLB stores only presentation/delegation config; canonical auth/access owner remains source of redirect enforcement truth.

### Group 10 — CAPTCHA/rate-limit/login-alias delegation
- `WLB-100` CAPTCHA UI slot renders only when certified auth adapter requires it; WLB does not validate CAPTCHA itself.
- `WLB-101` CAPTCHA provider failure follows adapter policy and WLB does not silently bypass required challenge.
- `WLB-102` Rate-limit blocked state preserves generic response and cannot be hidden by branding copy.
- `WLB-103` Login alias/custom URL display delegates to owning auth/Protector adapter and Core recovery route policy remains explicit.
- `WLB-104` Alias route does not disable reset/recovery endpoints unintentionally.
- `WLB-105` Client branding cannot reduce configured brute-force/rate-limit protections.
- `WLB-106` CAPTCHA/provider keys remain Vault-owned and are never exposed in page source/config export.
- `WLB-107` Provider widget assets obey CSP/privacy/consent profile where applicable.
- `WLB-108` Accessibility fallback for CAPTCHA is owned by provider/certified adapter and surfaced in diagnostics.
- `WLB-109` Network policy can require minimum auth protections regardless of site branding.
- `WLB-110` AI/MCP cannot disable CAPTCHA/rate controls under a “branding cleanup” request without owning security authorization.

### Group 11 — social login/OAuth delegation
- `WLB-111` Social login buttons render from Account Link/OAuth provider registry without WLB owning OAuth secrets.
- `WLB-112` Provider disabled/unconfigured produces hidden/disabled provider UI with diagnostic, not a broken auth callback.
- `WLB-113` OAuth state/nonce/PKCE/session security remains owned by Account Link adapter and is unchanged by branding.
- `WLB-114` Provider button label/icon customization does not change provider identity/callback mapping.
- `WLB-115` Callback route is excluded from force-login redirect loop and re-enters canonical auth flow.
- `WLB-116` Social-login failure uses safe branded error while preserving enumeration/privacy semantics.
- `WLB-117` Provider consent/privacy disclosure can be shown without implying WPE certifies provider legality.
- `WLB-118` Network/site provider availability follows adapter scope; site branding cannot expose network-forbidden provider.
- `WLB-119` Disconnect/account-link management remains protected account action even if branding hides its menu link.
- `WLB-120` Export/import of WLB profile carries provider display mapping only, no OAuth client secret/token.
- `WLB-121` AI/MCP may draft provider button arrangement but cannot establish OAuth trust or link accounts.

### Group 12 — CSS/JS Safe Script delegation
- `WLB-122` Custom login/admin CSS is stored/executed through Safe Script/Tag CSS profile, not arbitrary server template code.
- `WLB-123` Custom browser JS requires explicit high-risk Safe Script permission and remains browser-side only.
- `WLB-124` PHP tags/server snippets are rejected from WLB customization fields/imports.
- `WLB-125` CSS syntax/size/origin validation runs before publish and cannot silently weaken security headers.
- `WLB-126` JavaScript cannot interpolate Vault secrets into frontend code.
- `WLB-127` CSP nonce/hash/origin compatibility is checked by owning security adapter before activation.
- `WLB-128` Environment-scoped custom script does not accidentally run production-only code on staging or vice versa.
- `WLB-129` Safe-mode can disable nonessential WLB browser code while retaining core login/admin usability.
- `WLB-130` Imported third-party branding script remains Draft until risk validation/approval.
- `WLB-131` Removing WLB profile removes only its linked Safe Script assignments and not independently owned snippets.
- `WLB-132` Diagnostics identify source/revision of injected CSS/JS and do not attribute third-party snippets to WLB falsely.

### Group 13 — import/export/revisions
- `WLB-133` Export branding profile with tokens, safe links, assignments option and portable asset refs; no secrets.
- `WLB-134` Import collision offers create/rename/merge/replace/skip with diff.
- `WLB-135` Imported CSS/JS stays Draft and is revalidated by Safe Script owner.
- `WLB-136` Missing asset/provider reference is unresolved rather than guessed by attachment ID.
- `WLB-137` Revision records before/after branding values, assignments, actor and compatibility profile.
- `WLB-138` Rollback restores prior branding revision but does not roll back user passwords/sessions/roles/auth state.
- `WLB-139` Legacy White Label CMS/LoginPress-style import inventories mappings before apply.
- `WLB-140` Migration never treats hidden-menu rules as authorization Policy automatically.
- `WLB-141` Unsupported legacy PHP/snippet customization is rejected or converted to Extension Plan, not imported into runtime content.
- `WLB-142` Import across sites remaps assets/provider refs explicitly.
- `WLB-143` Post-import preview verifies login/admin/recovery routes before profile can be broadly assigned.

### Group 14 — Multisite enforcement
- `WLB-144` Site branding is site-owned by default and cannot modify other sites.
- `WLB-145` Network branding template can instantiate per-site profiles without silently sharing user-specific state.
- `WLB-146` Network-enforced logo/support/security floor cannot be edited by site admin.
- `WLB-147` Site override operates only within network-permitted token/surface set.
- `WLB-148` Network Admin branding can differ from site admin and remains recoverable.
- `WLB-149` Super Admin capability/recovery route cannot be removed by site-level white-label assignment.
- `WLB-150` Same role name across sites does not imply same branding assignment; site membership/context resolves it.
- `WLB-151` Site clone copies definitions only under selected option and regenerates environment-specific branding/support URLs where required.
- `WLB-152` Site deletion removes site-owned WLB metadata without deleting network-shared assets/templates.
- `WLB-153` Network aggregate reporting exposes counts/status, not protected client-specific branding data without authority.
- `WLB-154` AI/MCP network-wide branding proposal requires network principal/approval and cannot be executed by site context.

### Group 15 — compatibility/conflict/performance
- `WLB-155` Detect LoginPress/White Label CMS/admin-theme output affecting same login/admin surface and report coexistence/conflict.
- `WLB-156` Observe-only mode reports third-party effective branding without overwriting it.
- `WLB-157` WPE-owned takeover requires explicit migration/profile selection; competitor is not auto-disabled.
- `WLB-158` Cache/minifier does not merge incompatible personalized branding variants into one shared artifact.
- `WLB-159` Login/admin page performance records extra CSS/JS/font/image request budget under declared profile.
- `WLB-160` Large logo/background assets trigger size/dimension diagnostics rather than silent page bloat.
- `WLB-161` Critical auth controls remain usable when WLB CSS/JS fails to load.
- `WLB-162` Browser console/CSP errors from WLB assets are surfaced when telemetry/diagnostics is enabled.
- `WLB-163` Plugin/theme update compatibility regression can fall back to Core presentation without disabling auth.
- `WLB-164` Performance claim requires executed measurement; static asset-count estimate is not certification.
- `WLB-165` Compatibility evidence records WP/browser/theme/plugin versions and assignment context for reproducibility.

### Group 16 — end-to-end client experience regression
- `WLB-166` Golden: branded login→successful auth→role-specific dashboard preserves all security/session semantics.
- `WLB-167` Golden: lost-password/reset flow remains branded, enumeration-safe and functional through token consumption.
- `WLB-168` Golden: mobile/keyboard/screen-reader user can log in, recover password and log out under branded UI.
- `WLB-169` Golden: force-login profile excludes recovery/privacy/API routes and returns authenticated deep link safely.
- `WLB-170` Golden: social-login button branding works while OAuth state/secret/callback remain provider-owned.
- `WLB-171` Golden: two clients/roles with different branding never receive each other’s cached logo/support links.
- `WLB-172` Golden: network-enforced branding plus site override honors precedence and keeps Super Admin recovery available.
- `WLB-173` Golden: competitor branding coexistence/migration preview identifies conflicts and does not auto-disable legacy plugin.
- `WLB-174` Golden: WLB custom CSS/JS failure triggers safe fallback and never activates PHP/server code.
- `WLB-175` Golden: hidden menu/admin bar nodes do not change direct authorization checks for protected destinations.
- `WLB-176` Golden: AI/MCP adversarial request to hide security notices, weaken auth controls or publish arbitrary server code is denied/draft-only.

## Runtime truth

This protocol is documentation-only. `WLB-001…WLB-176` are **176/176 documented, 0/176 executed**. No login/admin mutation, redirect enforcement, OAuth/CAPTCHA call, script activation, runtime test or deployment occurred. Development authorization remains **NOT GRANTED / 0/56**.