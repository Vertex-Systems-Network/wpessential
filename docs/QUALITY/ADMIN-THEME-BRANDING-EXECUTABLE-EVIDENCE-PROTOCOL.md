# WPEssential — Admin Theme, Branding & Experience Exact Executable-Evidence Protocol

Status: **Planning-only exact protocol / NOT EXECUTED**  
Date: 2026-08-29  
Work package: **WP114**  
Namespace: **ATM-001…ATM-176**

## Contract

This protocol expands the fixed ATM groups into exact individual fixtures for Surface 49. It is a presentation/experience protocol only; authentication, authorization, recovery and WordPress admin semantics remain owned by their canonical systems.

Truth boundaries: branding/theme assignment ≠ authentication/authorization; hiding admin UI ≠ revoking access; environment color ≠ sole safety signal; fallback CSS ≠ proof of compatibility; preview ≠ publish; imported theme ≠ trusted/active theme.

Execution status: **176/176 documented; 0/176 executed; runtime certification 0.**

## Group 1 — Theme definition/version profile — ATM-001…011
- **ATM-001** — Theme definition stores stable key/UUID, name, Draft/Active/Archived state, owner scope and immutable revision identity.
- **ATM-002** — Duplicate stable key import cannot silently overwrite an existing theme; create/map/replace choice is explicit.
- **ATM-003** — Base theme/preset inheritance resolves deterministically and cycle detection blocks recursive theme chains.
- **ATM-004** — Variant type light/dark/high-contrast/custom is metadata plus token profile, not a guarantee of accessibility.
- **ATM-005** — WordPress compatibility profile is pinned per published revision and unsupported controls are marked rather than silently ignored.
- **ATM-006** — Site/Network Template/Network Enforced scope is server-resolved; request-supplied scope cannot elevate authority.
- **ATM-007** — Fallback theme reference is validated and cannot create an inheritance loop or cross-network leak.
- **ATM-008** — Archived theme remains available for history/rollback but cannot be newly assigned unless restored through governed action.
- **ATM-009** — Theme source/author metadata is provenance only and grants no trust or execution privilege.
- **ATM-010** — Publishing a new revision invalidates generated artifacts/assignment caches tied to prior fingerprint.
- **ATM-011** — Theme deletion/retirement checks active assignments, fallback dependencies and revision history before destructive action.

## Group 2 — Native `wp_admin_css_color()` registration — ATM-012…022
- **ATM-012** — When native scheme registration is supported, WPE emits a stable unique scheme key without colliding with Core/third-party schemes.
- **ATM-013** — Native scheme display name/palette corresponds to published theme revision and cannot point to stale artifact.
- **ATM-014** — Generated CSS artifact URL is versioned/fingerprinted and no secret/query credential appears in it.
- **ATM-015** — Native scheme palette swatches are validated colors and malformed values block registration rather than breaking profile UI.
- **ATM-016** — Icon color mapping uses supported native format and degrades explicitly when unavailable.
- **ATM-017** — RTL native artifact is selected only for RTL context and missing RTL artifact reports fallback/degraded state.
- **ATM-018** — User profile native scheme selection coexists with WPE user-selectable mode without duplicate/ghost options.
- **ATM-019** — WPE enforced assignment overrides native user preference only according to explicit precedence and explains the effective source.
- **ATM-020** — Core default scheme remains available/recoverable unless explicit policy hides WPE options only; WPE cannot remove native recovery path by accident.
- **ATM-021** — Native registration failure leaves theme definition intact but marks runtime compatibility degraded; no false “active” claim.
- **ATM-022** — WordPress version/API removal causes capability detection to disable native registration path instead of calling unsupported API blindly.

## Group 3 — WordPress 7.1 `wp-theme` token integration — ATM-023…033
- **ATM-023** — Capability detection confirms semantic `wp-theme` token support before token-backed controls are marked effective.
- **ATM-024** — Supported color tokens map one-to-one to declared semantic token keys and unknown token names are rejected/ignored with diagnostics.
- **ATM-025** — Border radius/roundness tokens apply only when current Core exposes them and fallback ownership is explicit otherwise.
- **ATM-026** — Interactive pointer/cursor token is used only if Core contract supports it; arbitrary cursor CSS is not inferred as token support.
- **ATM-027** — Component-facing token changes cannot target undocumented private Core internals under the token profile.
- **ATM-028** — Future token group introduced by newer WordPress is detected/versioned before editor exposes it.
- **ATM-029** — Core-owned/no-override token is visibly locked and publish does not fabricate an override.
- **ATM-030** — Unsupported token value blocks or degrades with typed validation rather than generating invalid theme data.
- **ATM-031** — Token inheritance from base theme preserves explicit override vs inherited distinction across revisions.
- **ATM-032** — Token preview uses same semantic mapping contract as final runtime or labels approximation explicitly.
- **ATM-033** — WordPress downgrade from token-capable to non-capable profile switches to declared fallback path without treating prior token support as current truth.

## Group 4 — Legacy/fallback generated CSS — ATM-034…044
- **ATM-034** — Fallback CSS is generated only for controls not expressible by stable current Core tokens/APIs.
- **ATM-035** — Generated selectors are scoped to certified WordPress compatibility profile; undocumented wildcard selectors are prohibited.
- **ATM-036** — CSS value encoding rejects control characters, broken declarations and unsafe URL constructs.
- **ATM-037** — Theme artifact fingerprint includes theme revision, WP compatibility profile and RTL mode where applicable.
- **ATM-038** — Stale artifact fingerprint mismatch triggers safe regeneration/degraded notice rather than serving old styling silently.
- **ATM-039** — Missing generated CSS leaves wp-admin usable with Core fallback; it cannot white-screen admin.
- **ATM-040** — Generated CSS never hides login/recovery/security controls as an authorization mechanism.
- **ATM-041** — `!important` use, if any certified gap requires it, is bounded/documented and conflict diagnostics identify its ownership.
- **ATM-042** — Fallback artifact size has explicit budget and duplicate declarations are minimized without semantic change.
- **ATM-043** — Admin, login and frontend admin-bar styles remain separated so fallback CSS cannot leak globally.
- **ATM-044** — WordPress DOM/selector drift that invalidates certified selector profile disables affected rule group instead of guessing replacements.

## Group 5 — Palette/icon/state mapping — ATM-045…055
- **ATM-045** — Canvas/surface/panel/border tokens resolve valid colors and preserve inherited vs explicit state.
- **ATM-046** — Primary/secondary accent tokens do not automatically override destructive/status semantics without dedicated mappings.
- **ATM-047** — Text primary/secondary/muted mappings are contrast-audited against effective backgrounds.
- **ATM-048** — Link normal/hover/focus tokens preserve visible focus and do not rely solely on color when policy requires additional indicator.
- **ATM-049** — Menu background/text/current/hover states remain distinguishable and current item is not identified only by color.
- **ATM-050** — Toolbar/admin-bar state tokens preserve readable network/site/environment controls.
- **ATM-051** — Button primary/secondary/destructive mapping keeps destructive actions semantically distinct and readable.
- **ATM-052** — Input border/background/text/focus state preserves validation/error visibility and browser autofill usability.
- **ATM-053** — Notice info/success/warning/error mappings retain semantic icon/text cues beyond color.
- **ATM-054** — Disabled state remains visibly and programmatically distinct without reducing text below readability floor.
- **ATM-055** — Icon base/focus/current colors remain legible across light/dark/high-contrast variants and SVG/currentColor contexts.

## Group 6 — Typography/geometry/density — ATM-056…066
- **ATM-056** — Registered UI font preset resolves local/approved asset source and remote font URL is rejected without Asset/Privacy policy.
- **ATM-057** — Missing font falls back to declared system stack without blocking admin usability.
- **ATM-058** — Base font-size scale is bounded and cannot shrink critical controls below accessibility policy floor.
- **ATM-059** — Heading/body/label weight mapping uses available font weights or deterministic fallback without invisible text.
- **ATM-060** — Line-height scale preserves text readability and control clipping constraints.
- **ATM-061** — Compact/comfortable density changes only supported geometry tokens/selectors and does not collapse interactive target sizes below policy.
- **ATM-062** — Radius scale applies to certified components without altering semantic focus outline geometry unexpectedly.
- **ATM-063** — Control-height density preserves form labels/error messages and keyboard hit areas.
- **ATM-064** — Table-row/menu-item density supports long labels/multiline/translated text without overlap.
- **ATM-065** — Modal/drawer density does not clip close/action controls at zoom/mobile widths.
- **ATM-066** — Geometry controls unavailable in detected WP profile are disabled/labelled unsupported rather than generating unstable selector hacks.

## Group 7 — Accessibility/contrast/focus — ATM-067…077
- **ATM-067** — Theme audit computes required foreground/background contrast pairs for normal text and reports failures by token source.
- **ATM-068** — Large-text contrast threshold uses declared accessibility policy and does not classify arbitrary font as large without actual size/weight criteria.
- **ATM-069** — Focus ring remains visible on links/buttons/inputs/menu items across all effective backgrounds.
- **ATM-070** — Destructive/status states retain text/icon/non-color indicators so color-vision deficiency does not remove meaning.
- **ATM-071** — High-contrast variant or OS mode does not hide content due to forced-color incompatibility in certified profiles.
- **ATM-072** — Reduced-motion preference disables or reduces decorative transitions introduced by theme where applicable.
- **ATM-073** — Keyboard tab order is unchanged by visual theming; CSS cannot make focused control inaccessible/offscreen.
- **ATM-074** — 200%/400% zoom fixture preserves critical admin actions without horizontal clipping beyond documented Core constraints.
- **ATM-075** — Publish policy can warn/block critical contrast failures according to configured governance and records result.
- **ATM-076** — Network-enforced visual floor cannot override an approved accessibility user variant when policy says accessibility preference wins.
- **ATM-077** — Accessibility regression pack covers Dashboard, list tables, editor shell, Users, Settings, Network Admin and login screen.

## Group 8 — User/role/site assignment precedence — ATM-078…088
- **ATM-078** — Explicit user assignment resolves only after higher-priority environment/network/site enforced rules according to declared precedence.
- **ATM-079** — Role default applies only when user lacks a higher-priority explicit/enforced assignment and multi-role conflict has deterministic resolution.
- **ATM-080** — Site default applies when no user/role/enforced assignment matches and never changes authorization.
- **ATM-081** — User-selectable mode persists allowed preference without escaping network/site enforcement floor.
- **ATM-082** — `default unless user chose another` respects existing valid user preference and does not reset it on every login.
- **ATM-083** — Role/site enforced mode ignores client profile choice only for presentation while underlying user capabilities remain unchanged.
- **ATM-084** — Assignment explanation returns effective theme plus every matched/overridden source and revision.
- **ATM-085** — Deleted/archived assigned theme falls back through declared fallback chain instead of leaving broken admin CSS.
- **ATM-086** — User role change invalidates role-default theme cache and recalculates presentation without role privilege side effect.
- **ATM-087** — Full-page/object cache keys include relevant assignment/theme revision and cannot serve another user’s theme-specific private UI data.
- **ATM-088** — AI may draft assignment Plan but broad user/role reassignment requires governed apply Ability; theme assignment never grants role/capability.

## Group 9 — Network assignment/enforcement — ATM-089…099
- **ATM-089** — Network theme library template can be instantiated to selected site without sharing site-private branding refs accidentally.
- **ATM-090** — Network Enforced assignment requires Network Admin/Super Admin Policy and cannot be created by site admin.
- **ATM-091** — Site admin cannot edit network-enforced definition but may view effective values allowed by Policy.
- **ATM-092** — Network enforced floor vs site/user accessibility variant precedence follows explicit policy and is explainable.
- **ATM-093** — Network Admin interface may use distinct theme from child sites without site assignment leaking upward.
- **ATM-094** — Network assignment Plan lists exact sites/users/roles affected and dry-run counts before broad apply.
- **ATM-095** — Partial network rollout reports per-site success/degraded/conflict and does not claim all-success.
- **ATM-096** — Site deletion removes site assignment refs through lifecycle while preserving reusable network theme definitions.
- **ATM-097** — New-site bootstrap uses current approved network template revision and does not copy old user preferences by default.
- **ATM-098** — Network cache keys include site/network identity to prevent same theme key collision across sites.
- **ATM-099** — Cross-site preview/compare reauthorizes target site context and cannot reveal private branding/assets without Policy.

## Group 10 — Environment identity — ATM-100…110
- **ATM-100** — Environment class resolves from WordPress environment type/config/explicit override with provenance and mismatch diagnostic.
- **ATM-101** — Production/staging/development/local/custom labels are presentation safety cues and never affect authorization automatically.
- **ATM-102** — Environment cue uses text/icon/badge in addition to color; color alone fails evidence.
- **ATM-103** — Admin-bar accent/label remains visible in compact/mobile states and does not obscure critical controls.
- **ATM-104** — Production destructive-action warning enhancement is additive UI safety, not replacement for capability/reauth/confirmation.
- **ATM-105** — Environment cue visibility by selected roles does not hide actual environment truth from security/admin diagnostics.
- **ATM-106** — Explicit environment override requires privileged change and audit, with warning when conflicting with WordPress environment type.
- **ATM-107** — Staging clone to production does not blindly copy staging environment label/override; environment identity is re-resolved/quarantined.
- **ATM-108** — Browser favicon/site-icon environment cue uses approved asset mechanism and cannot leak private asset token.
- **ATM-109** — Environment-specific theme override preserves user accessibility settings where policy requires.
- **ATM-110** — Cache/CDN/browser cache cannot keep stale environment cue across deployment after environment identity revision change.

## Group 11 — Branding/admin bar — ATM-111…121
- **ATM-111** — Organization/product name branding is sanitized text and cannot inject markup/script into admin chrome.
- **ATM-112** — Admin/compact logo references resolve through Asset/Media registry with public/private suitability checks.
- **ATM-113** — Footer text/link validates URL/scheme and does not create open redirect or unsafe javascript/data scheme.
- **ATM-114** — Help/support link is presentation only and cannot bypass support/account authorization.
- **ATM-115** — White-label mode cannot remove legally/operationally required attribution or security/recovery information beyond supported policy.
- **ATM-116** — Brand colors map through theme tokens rather than unconstrained CSS injection.
- **ATM-117** — Admin-bar node visibility delegates to Admin Menu/Admin Bar presentation adapter and destination access remains independently authorized.
- **ATM-118** — Frontend admin-bar theme does not leak wp-admin-only CSS/branding data to logged-out visitors.
- **ATM-119** — Responsive/mobile admin bar preserves site/network/account/recovery navigation usability.
- **ATM-120** — Missing branding asset falls back to safe text/Core identity rather than broken/blank login/admin navigation.
- **ATM-121** — Branding revision change invalidates cached assets/UI and audit records actor/scope without storing binary payloads.

## Group 12 — Login presentation — ATM-122…132
- **ATM-122** — Login logo/background/panel/input/button tokens alter presentation only; native WordPress authentication/session remains authoritative.
- **ATM-123** — Login background image URL uses approved asset and cannot fetch arbitrary remote private-network resource.
- **ATM-124** — Heading/help/footer/privacy/terms content is sanitized and safe links are scheme/host validated.
- **ATM-125** — Login theme does not reveal whether username/email exists through conditional branding or error detail.
- **ATM-126** — Lost-password/reset form remains visible/usable under every active login theme.
- **ATM-127** — CAPTCHA/SSO/2FA/provider widgets from certified adapters remain operable and conflicts are diagnosed rather than hidden by CSS.
- **ATM-128** — High-contrast/focus/keyboard requirements apply to login screen before publish.
- **ATM-129** — Mobile/zoom preview catches clipped password fields, submit, reset and privacy links.
- **ATM-130** — Hiding default branding, where supported, does not remove required security/help semantics or impersonate another service deceptively.
- **ATM-131** — Login CSS/asset failure falls back to Core login usability; authentication must not become unavailable because branding artifact fails.
- **ATM-132** — Login presentation cache is public-safe and contains no user-specific account or auth state.

## Group 13 — Preview/revision/rollback — ATM-133…143
- **ATM-133** — Preview is no-write by default and cannot change live user/native scheme preference until explicit Publish/Assign.
- **ATM-134** — Dashboard preview renders theme tokens/profile with approximation labels where exact runtime shell is unavailable.
- **ATM-135** — List table/editor/Users/Settings/Network/login/mobile preview contexts use pinned theme revision and WP profile.
- **ATM-136** — Compare Core vs theme shows token/style differences without claiming accessibility/performance pass before checks.
- **ATM-137** — Revision publish stores before/after token diff, actor, scope, WP profile and generated artifact fingerprint.
- **ATM-138** — Rollback restores prior theme definition revision but does not claim WordPress/plugin/version rollback.
- **ATM-139** — Rollback preflight detects current assignment/dependency drift and reports impact before apply.
- **ATM-140** — Concurrent theme edits use revision/precondition; stale publish cannot overwrite newer revision silently.
- **ATM-141** — Failed artifact generation leaves prior active revision intact or explicit degraded fallback; no half-published theme.
- **ATM-142** — Preview of role/user assignment does not impersonate that user’s live authenticated session or expose private data.
- **ATM-143** — Emergency fallback to Core theme is available through governed recovery path even when custom theme CSS is broken.

## Group 14 — Import/export/lifecycle — ATM-144…154
- **ATM-144** — Export contains theme tokens/profile/version/fallback metadata and optional assignments, but no credentials/secrets.
- **ATM-145** — Portable asset refs are exported as references/provenance; private signed URLs/tokens are never embedded.
- **ATM-146** — Import validates package/schema/version before creating Draft theme.
- **ATM-147** — Create-new import gets new local identity while preserving source provenance.
- **ATM-148** — Merge-compatible token groups reports conflicts and never silently overrides unsupported groups.
- **ATM-149** — Replace requires diff/impact preview and cannot overwrite network-enforced theme under site authority.
- **ATM-150** — Imported theme remains Draft/unassigned until validation/publish according to risk policy.
- **ATM-151** — Plugin/module disable leaves WordPress admin usable via Core/native scheme and preserves WPE definitions for re-enable.
- **ATM-152** — Uninstall respects configured data-retention contract and must remove generated artifacts without deleting unrelated native schemes.
- **ATM-153** — Site clone copies theme definitions only per profile and re-evaluates environment identity/user preferences.
- **ATM-154** — Version migration preserves revision provenance and reports token/profile lossiness rather than silently reinterpreting old theme.

## Group 15 — Third-party admin/style/editor conflicts — ATM-155…165
- **ATM-155** — Diagnostics detect third-party admin CSS/theme plugin presence by supported signals and label ownership confidence.
- **ATM-156** — `!important` collision is reported with affected token/control; WPE does not automatically escalate specificity indefinitely.
- **ATM-157** — Site Editor/iframe/editor-shell isolation differences are detected and unsupported theming regions are labelled.
- **ATM-158** — Plugin custom React/admin pages outside Core token adoption are reported partial/third-party-owned rather than falsely themed.
- **ATM-159** — Native user color scheme plus WPE enforced theme conflict resolves through explicit assignment precedence.
- **ATM-160** — Admin menu/toolbar transformation plugin conflict cannot cause hidden destination to become unauthorized/authorized.
- **ATM-161** — Browser extension/forced-colors interference is classified as environment/client effect where distinguishable, not WPE success/failure by guess.
- **ATM-162** — Remote font/admin asset failure produces fallback and diagnostics without blocking core admin actions.
- **ATM-163** — Cache/minifier/CDN rewriting generated CSS is detected where possible and fingerprint mismatch is surfaced.
- **ATM-164** — Third-party login branding coexistence identifies duplicate/override ownership and does not inject both blindly.
- **ATM-165** — Conflict safe mode can disable WPE compatibility CSS while leaving definitions intact for diagnosis/recovery.

## Group 16 — Performance/RTL/browser/WP-version regression — ATM-166…176
- **ATM-166** — Large theme library evidence profile defines list/filter/search/pagination budgets without preclaiming measured performance.
- **ATM-167** — Assignment evaluation for large user/role/site sets defines bounded cache/query behavior and invalidation keys.
- **ATM-168** — Generated CSS/token output has size/request-count budgets and avoids per-admin-request regeneration.
- **ATM-169** — RTL golden fixture verifies menus, toolbar, forms, notices and login layout under native/fallback profiles.
- **ATM-170** — Browser matrix fixture covers supported evergreen browsers, forced colors, reduced motion and zoom according to compatibility plan.
- **ATM-171** — WordPress minimum/current/target version fixture confirms token/native/fallback capability detection paths separately.
- **ATM-172** — Upgrade to newer Core token profile does not keep obsolete fallback override active when Core becomes owner.
- **ATM-173** — Downgrade/rollback to older WP profile disables unsupported tokens and retains Core-usable fallback without silent corruption.
- **ATM-174** — Admin load under object-cache/artifact-store failure remains usable and fails to Core/default rather than blanking UI.
- **ATM-175** — AI adversarial fixture asks to hide security/recovery controls or use theme to grant admin access; expected result rejects authorization misuse and preserves critical controls.
- **ATM-176** — Golden end-to-end fixture covers create theme → validate accessibility/profile → preview → publish → assign by precedence → environment/branding/login → rollback, with presentation and authorization truths kept separate.

## Completion truth

`ATM-001…ATM-176` are **176/176 documented and 0/176 executed**. No runtime WordPress theme/token/native-scheme/browser/accessibility certification is implied.