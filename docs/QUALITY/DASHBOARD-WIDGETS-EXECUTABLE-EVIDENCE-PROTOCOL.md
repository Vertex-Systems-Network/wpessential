# WPEssential — Dashboard Widgets Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0051, ADR-0103, Component Blueprint, Query, DSR, DVR, CLG, CAC, ASR, Safe HTTP, RLT, PDL, ERR, VER, MLC, Multisite, ADR-0014.

## 1. Purpose

Define the evidence required before WPEssential can claim Dashboard Widgets are safe, performant, accessible and compatible with WordPress Site/Network Dashboard behavior.

Canonical runtime chain:

`Widget Definition/revision → compiled descriptor → current principal/scope → server visibility Policy → authorized source resolution → trusted renderer → scoped assets/cache/refresh → WordPress Dashboard adapter`

No Dashboard hook, widget registration, remote request, cache write, job, provider call or browser execution is authorized by this document.

## 2. Non-negotiable invariants

1. Widget visibility is presentation, not authorization.
2. Policy-denied data must not be fetched merely to be hidden client-side.
3. Remote HTML/JS is never trusted admin-origin content by default.
4. Provider/Vault secrets never enter browser bootstrap, HTML, URLs, logs, support bundles or generic cache.
5. Site, Network and optional User Admin Dashboard contexts are distinct.
6. Cache identity includes all security/scope/revision/source generations that affect output.
7. A cache hit, successful refresh, Job success or renderer success never upgrades source/provider certification.
8. One widget failure must not fatal the whole Dashboard.
9. Pro expiry/module disable/deactivation obey accepted lifecycle contracts and cannot silently destroy definitions/data.
10. No paper/static result in this protocol is a runtime certification.

## 3. WordPress adapter surfaces

Future certification distinguishes:
- Site Dashboard lifecycle;
- Network Dashboard lifecycle;
- User Admin only if explicitly supported;
- context/priority/order semantics;
- per-user metabox/layout/hidden state;
- screen options;
- native/core/third-party widget coexistence;
- multisite network aggregate behavior.

## 4. Content-source profiles

- `DW-S1` Component Blueprint — preferred structured renderer.
- `DW-S2` Query/Listing summary — bounded Query + batching + truthful counts.
- `DW-S3` registered server-rendered block — certified render context required.
- `DW-S4` registered shortcode — advanced, bounded, side-effect-safe profile only.
- `DW-S5` structured rich message/banner — sanitized schema.
- `DW-S6` remote structured data — Safe HTTP/Connection + schema normalization.
- `DW-S7` trusted iframe — advanced/high-risk exact-origin sandbox profile.

## 5. Refresh/cache profiles

- `DW-R0` request render.
- `DW-R1` bounded server cache with explicit security/scope/source generations.
- `DW-R2` authorized async refresh endpoint/Ability.
- `DW-R3` Job-produced snapshot with timestamp/status provenance.

Dismiss state and layout state are distinct, per-user by default, revision-aware and never an authorization primitive.

## 6. Fixed fixture matrix

### A. Original canonical fixtures — preserved

- **DW-01** Site Dashboard registration.
- **DW-02** Network Dashboard registration.
- **DW-03** Unsupported/supported User Admin distinction.
- **DW-04** Context/priority mapping.
- **DW-05** Two WPE widgets stable order.
- **DW-06** Core/third-party widget coexistence.
- **DW-07** Per-user hidden/layout state isolation.
- **DW-08** Dismiss-until-revision semantics.
- **DW-09** Capability-denied widget not fetched/rendered.
- **DW-10** Source-field denial occurs before disclosure.
- **DW-11** Blueprint XSS corpus.
- **DW-12** Rich HTML XSS corpus.
- **DW-13** Remote HTML remains untrusted data/rejected.
- **DW-14** Remote schema mismatch safe degradation.
- **DW-15** Remote timeout/5xx bounded behavior.
- **DW-16** SSRF/private-target redirect blocked by Safe HTTP.
- **DW-17** Provider credential leakage scan.
- **DW-18** Shortcode retains owning capability semantics.
- **DW-19** Unsafe/side-effect shortcode blocked/degraded.
- **DW-20** Missing server-rendered block safe degradation.
- **DW-21** Query summary scale/boundedness.
- **DW-22** Relation-heavy summary batching.
- **DW-23** Cache cross-user attack.
- **DW-24** Cache cross-site attack.
- **DW-25** Access revoked while cache warm.
- **DW-26** Async forged target-site input cannot widen scope.
- **DW-27** Async CSRF/current authorization.
- **DW-28** Job snapshot stale/failed truth.
- **DW-29** Iframe unregistered origin rejected.
- **DW-30** Iframe sandbox escape/navigation prevented.
- **DW-31** CSP/frame refusal degrades safely.
- **DW-32** Assets only on intended Dashboard context.
- **DW-33** One renderer failure isolated.
- **DW-34** Missing Definition dependency diagnosed safely.
- **DW-35** Pro expiry preserves safe deployed runtime.
- **DW-36** Network aggregate widget requires explicit authority and bounded fan-out.

### B. Definition, compilation and lifecycle

- **DW-37** Draft Definition never renders as published runtime.
- **DW-38** Published revision is pinned for one request/render.
- **DW-39** Concurrent publish conflict is explicit.
- **DW-40** Widget UUID remains identity across label/title changes.
- **DW-41** Deleted/archived Definition stops future registration without deleting unrelated user layout state blindly.
- **DW-42** Definition schema-version unknown/future state fails/degrades safely.
- **DW-43** Definition migrator chain preserves semantic identity.
- **DW-44** Missing module dependency yields bounded degraded widget.
- **DW-45** Dependency restored re-enables only after current compatibility/Policy checks.
- **DW-46** Module disable stops module-owned execution while preserving owned data per lifecycle contract.
- **DW-47** Module re-enable revalidates descriptor/cache/source generations.
- **DW-48** Plugin deactivation removes WPE runtime hooks without leaving hidden server execution.
- **DW-49** Pro expiry does not silently expose premium data/actions.
- **DW-50** Free↔Pro version skew enters compatible/degraded state rather than fatal.
- **DW-51** Import duplicate UUID conflict is reviewed/remapped, not silently overwritten.
- **DW-52** Clone/transfer changes site scope without retaining stale source/cache authority.

### C. Principal, capability and Policy

- **DW-53** Anonymous principal never receives authenticated Dashboard data.
- **DW-54** Site administrator lacks Network-only widget authority unless core/network Policy permits.
- **DW-55** Network Super Admin semantics are read from current WordPress authority, not cached role names.
- **DW-56** Object/resource Policy denial blocks source fetch.
- **DW-57** Field-level Policy denial blocks derived labels/counts/facets.
- **DW-58** Role capability revoke invalidates presentation/source access.
- **DW-59** User-specific capability override is respected.
- **DW-60** Membership revoke removes protected widget/content according to access-generation semantics.
- **DW-61** Membership pause/grace states follow Plan policy, not UI heuristics.
- **DW-62** Team membership removal invalidates team-scoped widget access.
- **DW-63** Conditional-logic `true` never grants missing Policy.
- **DW-64** DVR successful resolution never grants source access.
- **DW-65** Hidden layout state does not prove authorization.
- **DW-66** Direct async endpoint remains independently authorized.
- **DW-67** Preview/simulate-as-user cannot mint target-user authority.
- **DW-68** Recent-auth-required action widget reauthorizes action separately from render.
- **DW-69** Destructive action requires its own Ability/Policy/confirmation contract.
- **DW-70** Stale principal/session cache cannot preserve revoked privileged source output.

### D. Data Source, Query, values and actions

- **DW-71** DSR readable source does not imply writable source.
- **DW-72** Unsupported DSR query capability fails explicitly.
- **DW-73** Query AST definition/revision pinned to render.
- **DW-74** Query parameters are typed/bounded, not raw SQL fragments.
- **DW-75** Query sort/filter on protected field cannot leak value through result shape/count.
- **DW-76** Query total/count visibility obeys authorization.
- **DW-77** Cursor/pagination state cannot be forged to bypass scope.
- **DW-78** Remote Query provider is separately certified.
- **DW-79** Relation traversal reauthorizes endpoints/fields.
- **DW-80** Dynamic value uses canonical typed value before formatting/escaping.
- **DW-81** Missing dynamic value has explicit missing/null/default semantics.
- **DW-82** Trusted markup requires explicit trusted provider/context.
- **DW-83** URL dynamic value rejects unsafe scheme.
- **DW-84** Media reference uses authorized media projection.
- **DW-85** Settings value resolves correct site/network inheritance provenance.
- **DW-86** Vault reference exposes status only, never secret plaintext.
- **DW-87** User Profile value follows UP protected-field boundaries.
- **DW-88** Membership source does not expose provider/billing secrets.
- **DW-89** Action button invokes registered typed Ability only.
- **DW-90** Ability input schema rejects forged target/scope fields.
- **DW-91** Action result/error uses shared safe error envelope.
- **DW-92** Duplicate action submit respects operation idempotency where required.

### E. Rendering, Blueprint and output trust

- **DW-93** Component Blueprint compile determinism for same revision/context.
- **DW-94** Missing Blueprint slot/component degrades locally.
- **DW-95** Blueprint binding cannot access undeclared source.
- **DW-96** HTML-text escaping corpus.
- **DW-97** HTML-attribute escaping corpus.
- **DW-98** URL escaping/normalization corpus.
- **DW-99** JSON/bootstrap escaping prevents script-breakout.
- **DW-100** SVG/media output follows approved sanitization/profile.
- **DW-101** Rich-message allowed tags/attributes are bounded.
- **DW-102** Third-party shortcode output sanitization/trust class is explicit.
- **DW-103** Server block output trust class is explicit.
- **DW-104** Remote structured data cannot choose renderer/class/function.
- **DW-105** Remote strings that look like admin notices remain data.
- **DW-106** Translation/localization does not introduce unescaped markup.
- **DW-107** RTL layout retains action semantics and focus order.
- **DW-108** Error state does not leak stack trace/path/query/provider payload.

### F. Cache, refresh and concurrency

- **DW-109** Cache key includes widget revision.
- **DW-110** Cache key includes site/network scope.
- **DW-111** Cache key includes relevant principal/audience generation.
- **DW-112** Cache key includes source/query/settings generations.
- **DW-113** Locale-dependent output partitions cache.
- **DW-114** Cache backend outage follows declared fallback without authorization fail-open.
- **DW-115** Stampede protection does not serve wrong principal/site output.
- **DW-116** Stale-while-revalidate is forbidden/partitioned for revocation-sensitive data unless proven safe.
- **DW-117** Manual refresh invalidates only intended widget/source partition.
- **DW-118** Definition publish invalidates old descriptor/render cache.
- **DW-119** Source mutation invalidates/versions dependent widget cache.
- **DW-120** Role/Membership revoke invalidates security-sensitive cache generation.
- **DW-121** Two concurrent refreshes do not corrupt snapshot state.
- **DW-122** Old refresh finishing after newer refresh cannot overwrite newer snapshot silently.
- **DW-123** Duplicate Job delivery cannot duplicate side effects.
- **DW-124** Job lease expiry does not imply side effect absence.
- **DW-125** Failed refresh keeps last-success timestamp/status distinct from new failure.
- **DW-126** Cancelled/disabled widget stops future refresh scheduling.

### G. Remote data, HTTP and iframe

- **DW-127** Remote host allowlist/scheme policy enforced server-side.
- **DW-128** DNS/private-address rebinding/redirect policy follows Safe HTTP evidence.
- **DW-129** Remote response byte/time limits enforced.
- **DW-130** Remote JSON depth/count limits bounded.
- **DW-131** Provider unknown outcome is not reported as success.
- **DW-132** Provider credential refresh remains Vault/Connection-owned.
- **DW-133** Remote cache cannot cross Connection/account/site scope.
- **DW-134** Remote data privacy classification controls retention/logging.
- **DW-135** Iframe exact origin is immutable/revision-pinned.
- **DW-136** Iframe sandbox permissions are minimal and reviewed.
- **DW-137** Iframe postMessage origin/source/schema validated.
- **DW-138** Iframe cannot receive provider secret through URL/query/bootstrap.
- **DW-139** Iframe top-navigation/popups follow declared sandbox/profile.
- **DW-140** Iframe deactivation/expiry removes active embedding safely.

### H. WordPress UI coexistence, assets and accessibility

- **DW-141** Native screen options continue to work with WPE widgets.
- **DW-142** User metabox order does not overwrite global Definition order.
- **DW-143** Third-party widget ID collision is deterministic and diagnosed.
- **DW-144** Third-party late widget registration does not corrupt WPE registry.
- **DW-145** Core widget removal by another plugin is treated as external drift.
- **DW-146** WPE widget assets use registered ASR handles/dependencies.
- **DW-147** Duplicate React/ReactDOM/JSX runtime is not bundled/enqueued.
- **DW-148** Route/screen-scoped assets do not load on unrelated wp-admin pages.
- **DW-149** Missing optional asset degrades widget without weakening Policy.
- **DW-150** Keyboard focus enters/leaves widget controls predictably.
- **DW-151** Labels/headings/control names are accessible.
- **DW-152** Live refresh announces meaningful state without excessive noise.
- **DW-153** Color/contrast does not become sole status channel.
- **DW-154** Responsive/mobile Dashboard layout remains usable.

### I. Multisite, lifecycle, privacy and observability

- **DW-155** Same widget UUID on different sites remains scope-safe.
- **DW-156** Network aggregate explicitly enumerates authorized sites only.
- **DW-157** Large network aggregate uses bounded fan-out/Job snapshot strategy.
- **DW-158** Site creation provisioning does not copy stale runtime/cache state.
- **DW-159** Site clone requires scope/revision/cache revalidation.
- **DW-160** Site transfer/domain change does not affect ownership authority.
- **DW-161** Site archive/deactivation stops scheduled refresh where required.
- **DW-162** Site deletion cleans site-owned widget runtime without deleting network/global definitions accidentally.
- **DW-163** Privacy exporter includes only WPE-owned eligible per-user widget state.
- **DW-164** Privacy eraser does not delete shared Definition/audit/legal state blindly.
- **DW-165** Support bundle redacts secrets/private remote payloads.
- **DW-166** Audit records publish/high-risk action/remote failures with safe metadata.
- **DW-167** Correlation IDs link widget refresh/Job/provider attempts without becoming authority.
- **DW-168** Error taxonomy distinguishes source denied/unavailable/stale/invalid/timeout.

### J. Scale, performance and adversarial regression

- **DW-169** 1/10/50/100 visible widget server-render profile.
- **DW-170** 100/1k definitions with small visible subset avoid O(all) hot-path work where not required.
- **DW-171** 10k/100k/1M source-row summary remains bounded by Query/Job profile.
- **DW-172** 100/1k/10k-site network profile avoids wrong-site data and uncontrolled fan-out.
- **DW-173** Remote slow-provider mix cannot monopolize complete Dashboard request.
- **DW-174** Cache cold-start and mass-invalidation profile avoids correctness compromise.
- **DW-175** Malicious mixed corpus (XSS/SSRF/IDOR/cache/scope/action) yields zero protected disclosure/mutation.
- **DW-176** Full regression matrix records runtime/profile versions and refuses generic certification beyond tested profile.

## 7. Independent certification classes

Future evidence must record separately:
- `DW-A` WordPress Dashboard adapter/context compatibility;
- `DW-P` Policy/authorization/data-disclosure safety;
- `DW-S` source-class certification (`S1…S7` separately);
- `DW-R` cache/refresh profile certification (`R0…R3` separately);
- `DW-X` output/XSS/iframe/remote trust safety;
- `DW-U` UI/accessibility/asset coexistence;
- `DW-M` Multisite/lifecycle isolation;
- `DW-Q` performance/scale.

Passing one class never auto-certifies another.

## 8. Stop-the-line / pass gates

Certification fails if any tested profile permits:
- arbitrary remote HTML/JS execution in admin origin;
- provider/Vault secret exposure;
- Policy-denied data fetched or disclosed;
- cross-user/site/network cache leakage;
- direct async/action IDOR;
- unsafe iframe origin/sandbox/postMessage behavior;
- one failed widget fataling the Dashboard;
- source/shortcode/block bypass of owning authority;
- stale revocation-sensitive cached allow beyond declared correctness semantics;
- site admin access to network-only aggregate/private data;
- paper success being reported as provider/runtime certification.

## 9. Required future evidence report

Include:
- exact WordPress/PHP/database/admin-context/profile versions;
- DW-01…DW-176 pass/fail/NA;
- source and refresh certification classes;
- XSS/SSRF/IDOR/cache/iframe/adversarial results;
- asset/accessibility/browser findings;
- Multisite/site-lifecycle evidence;
- performance/query/remote/cache measurements;
- limitations and uncertified integrations.

## 10. Current state

**DW fixtures documented: 176.**  
**DW fixtures executed: 0/176.**  
Dashboard Widget runtime certifications: **0**.

No WordPress Dashboard hook, widget registration, render, remote fetch, shortcode/block execution, iframe, cache mutation, Job, asset enqueue, browser action, Multisite action or benchmark has run.

## 11. Development gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger. `continue`/planning approval is not implementation consent.
