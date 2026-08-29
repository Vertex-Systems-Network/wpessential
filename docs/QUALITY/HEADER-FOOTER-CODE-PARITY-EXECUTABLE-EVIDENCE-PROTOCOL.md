# WPEssential — Header/Footer Code Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `HFC-001…HFC-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- HFC parity extends Safe Script/Tag; it is not a second code-injection engine.
- Browser-side snippets only; PHP/eval/arbitrary SQL/shell/server-code import/execution is prohibited.
- Placement/visibility conditions do not authorize protected data/resource access.
- Consent/CSP/SRI/environment controls from STM remain authoritative and cannot be weakened by compatibility import.
- Theme/builder placement capability is adapter/version evidence; unsupported hooks/locations remain degraded/unsupported.
- Migration imports definitions as Draft/review-required where risk policy says so; imported code is never silently activated.

## Exact fixtures

### Group 1 — source/import provenance
- `HFC-001` Import source records plugin/source family, version, export format, item identity and import timestamp/provenance.
- `HFC-002` Unknown/unsupported source format fails typed before creating snippets.
- `HFC-003` Source item stable ID maps to WPE draft snippet identity and prevents duplicate import replay.
- `HFC-004` Same source ID with changed content creates conflict/revision rather than silent overwrite.
- `HFC-005` Imported head/body/footer placement metadata is normalized into STM typed locations.
- `HFC-006` Source conditions not safely representable remain unresolved/manual instead of guessed.
- `HFC-007` Import package is parsed with bounded size/depth and no executable server code evaluation.
- `HFC-008` Import audit records actor/source/item/content fingerprint without plaintext secrets.
- `HFC-009` Source ownership/license metadata is preserved where available but does not imply compliance.
- `HFC-010` AI/MCP may map/import as Draft but cannot activate imported snippets automatically.
- `HFC-011` Import schema/source-version drift requires explicit adapter update, not best-effort unsafe coercion.

### Group 2 — browser snippet mapping
- `HFC-012` External JavaScript source maps to STM External JavaScript type with validated HTTPS/origin/attributes.
- `HFC-013` Inline JavaScript maps only to privileged STM browser-code type and remains Draft/review-required as policy requires.
- `HFC-014` CSS maps to typed CSS snippet with scope/validation metadata.
- `HFC-015` HTML fragment maps to sanitized/trusted profile according source/risk and scripts inside are extracted/rejected appropriately.
- `HFC-016` Meta tag source maps to typed Meta builder rather than raw markup where representable.
- `HFC-017` Link/preload/preconnect source maps to typed Link builder with validation/conflict checks.
- `HFC-018` JSON-LD source parses valid JSON and rejects JavaScript expressions/PHP interpolation.
- `HFC-019` iframe/widget source maps only through safe iframe profile and allowed origin policy.
- `HFC-020` Unknown/raw browser snippet type remains review-required and cannot be mislabelled safe.
- `HFC-021` Secret-looking source tokens are detected/redacted/reviewed; Vault secrets are never interpolated into frontend automatically.
- `HFC-022` Mapping never creates PHP/server snippet even when competitor source supported it.

### Group 3 — latest-N-posts
- `HFC-023` Latest-N-posts context preset validates positive bounded N and explicit post type/status/query scope.
- `HFC-024` N=0/negative/oversized value is rejected or normalized per documented limit, never unbounded query.
- `HFC-025` Latest-N condition evaluates current resource membership in query result without changing query/source data.
- `HFC-026` Draft/private posts are excluded unless context/profile and Policy explicitly include them.
- `HFC-027` Sticky/custom ordering interaction is explicit rather than assumed by “latest”.
- `HFC-028` Multilingual/provider query semantics are delegated to Query/adapter and remain versioned.
- `HFC-029` Cache key includes site/query/profile/revision and cannot bleed latest-N decision across sites.
- `HFC-030` Condition false means snippet not emitted; it does not revoke destination/resource access.
- `HFC-031` Query failure/unknown context resolves false/degraded according profile and never emits globally by accident.
- `HFC-032` Preview shows sample matching resources without exposing protected posts to unauthorized caller.
- `HFC-033` AI/MCP cannot broaden N/post-status scope beyond draft/approval Policy.

### Group 4 — taxonomy presets
- `HFC-034` Category/tag/taxonomy preset stores typed taxonomy + selected term IDs/slugs under current site ownership.
- `HFC-035` Unknown taxonomy/term is rejected/unresolved rather than silently matching all content.
- `HFC-036` Include descendants option is explicit and uses taxonomy owner semantics.
- `HFC-037` Any/all term matching semantics are explicit and deterministic.
- `HFC-038` Term rename preserves stable ID mapping where applicable and does not break condition solely by label change.
- `HFC-039` Deleted term invalidates condition dependency and reports degraded/unresolved state.
- `HFC-040` Private/protected taxonomy metadata is not exposed through preview/diagnostics without Policy.
- `HFC-041` Cross-site term numeric ID cannot match current-site condition.
- `HFC-042` Builder/CPT context using taxonomy condition composes canonical Query/Condition engine.
- `HFC-043` Condition remains presentation/emission logic only, not authorization.
- `HFC-044` AI/MCP can draft taxonomy preset but cannot publish snippet across terms/sites without approval.

### Group 5 — selected CPT instances
- `HFC-045` Selected CPT-instance preset stores typed post type + canonical object refs and validates current-site ownership.
- `HFC-046` Object ID from another post type/site is rejected even if numeric ID exists.
- `HFC-047` Deleted/unpublished selected object becomes unresolved according condition lifecycle rather than matching replacement by title.
- `HFC-048` Bulk selected instances enforce bounded count and per-object visibility during preview.
- `HFC-049` Include/exclude list precedence is explicit and conflict-detected.
- `HFC-050` Revision/cache invalidates when selected-set definition changes.
- `HFC-051` Direct URL request to selected object remains governed by owning resource Policy, not snippet condition.
- `HFC-052` Export/import remaps object identities explicitly; source numeric IDs are never assumed portable.
- `HFC-053` Woo product/order contexts are handled only through Woo adapter and never raw CPT assumptions for commerce truth.
- `HFC-054` Preview redacts protected selected resource details for unauthorized operator.
- `HFC-055` AI/MCP cannot use selected-instance condition to extract hidden post/order data.

### Group 6 — desktop/mobile preset
- `HFC-056` Coarse desktop/mobile visibility preset declares client-side/media/server signal source and limitations explicitly.
- `HFC-057` Device preset is presentation optimization, not user/device identity or authorization.
- `HFC-058` Responsive CSS/media implementation does not duplicate/expose snippet content in a hidden DOM mode when non-execution is required.
- `HFC-059` Server-side coarse device inference, if used, records uncertainty and cache-vary requirements.
- `HFC-060` Unknown device defaults to declared safe behavior rather than broad execution silently.
- `HFC-061` Full-page cache varies correctly when server-side device condition changes emitted code.
- `HFC-062` Consent/CSP requirements apply regardless of device condition.
- `HFC-063` Mobile preset does not allow different secret/token interpolation.
- `HFC-064` Accessibility/reduced-motion/user preference can be considered through supported condition facts without fingerprinting.
- `HFC-065` Preview simulates coarse breakpoint without claiming exact real-device behavior.
- `HFC-066` AI/MCP cannot target sensitive demographic/device fingerprint data outside approved coarse profile.

### Group 7 — shortcode/block placement
- `HFC-067` Manual shortcode/token placement resolves exact registered snippet key and safe renderer profile.
- `HFC-068` Unknown snippet key renders safe empty/error placeholder and does not evaluate arbitrary shortcode/PHP.
- `HFC-069` Block placement stores snippet reference/config through registered Gutenberg/WPE block schema.
- `HFC-070` Block editor preview does not execute external/high-risk script unless explicit sandbox/profile permits safe preview.
- `HFC-071` Frontend manual placement still evaluates consent/CSP/environment/conditions at render.
- `HFC-072` Snippet archive/pause causes manual placements to emit nothing/safe placeholder without deleting content block/shortcode.
- `HFC-073` Duplicate manual placements obey once-per-page/occurrence semantics explicitly.
- `HFC-074` Protected dynamic tokens inside manual placement reauthorize source data and cannot use snippet as exfiltration path.
- `HFC-075` Shortcode/block output is context-escaped and does not permit PHP tags/server callbacks.
- `HFC-076` Builder adapters can consume manual placement token only through certified widget/component integration.
- `HFC-077` AI/MCP may insert draft block/snippet reference only within authorized content editing scope and cannot auto-publish high-risk code.

### Group 8 — metadata
- `HFC-078` Snippet list records created-by, last-edited-by, created/updated timestamps and revision separately from execution status.
- `HFC-079` Actor identity is resolved from authenticated principal, never trusted client-supplied name/user ID.
- `HFC-080` Imported original author metadata is provenance only and not treated as current authenticated actor.
- `HFC-081` Content fingerprint changes when executable/markup content changes, not merely list metadata.
- `HFC-082` Risk class/type/placement/condition changes are revisioned and auditable.
- `HFC-083` Secret/private metadata is redacted from ordinary list/export.
- `HFC-084` Metadata edit permission can be narrower/broader only as declared and never grants publish privilege.
- `HFC-085` Timestamp uses canonical server/timezone semantics and is not legal/audit timestamp authority beyond recorded app time.
- `HFC-086` Deleted actor account preserves historical attribution identifier/name snapshot according privacy policy without reassigning action.
- `HFC-087` Multisite actor/site attribution is explicit.
- `HFC-088` AI/MCP attribution is supplemental and does not replace authenticated principal/approval identity.

### Group 9 — classic theme
- `HFC-089` Head placement uses supported classic-theme/Core hook and reports missing/nonconforming hook when theme omits it.
- `HFC-090` After-opening-body uses supported `wp_body_open` where available and has explicit unsupported fallback policy.
- `HFC-091` Footer placement uses supported footer hook and detects theme omission/degraded state.
- `HFC-092` Before/after-content placement integrates through registered content filter/context and avoids duplicate insertion in excerpts/feeds where excluded.
- `HFC-093` Theme-specific custom hook requires registered Hook Adapter, not arbitrary hook-name textbox execution.
- `HFC-094` Placement ordering/priority composes STM dependency graph and remains deterministic.
- `HFC-095` Theme switch invalidates compatibility diagnostics/cache and does not assume old hooks still exist.
- `HFC-096` Classic theme placement does not modify theme files/templates directly.
- `HFC-097` Consent/CSP/environment checks remain active regardless of classic hook path.
- `HFC-098` Missing placement hook produces diagnostic, not silent move to unsafe global location.
- `HFC-099` AI/MCP cannot create arbitrary PHP hook callbacks to make unsupported theme placement work.

### Group 10 — block theme
- `HFC-100` Block-theme head/footer/browser locations use supported Core render/enqueue surfaces rather than editing template files blindly.
- `HFC-101` Before/after-content compatibility accounts for block templates/query loops and avoids duplicate injection into repeated inner content unintentionally.
- `HFC-102` Site Editor preview can represent placement without executing high-risk external code by default.
- `HFC-103` Template/part condition uses registered route/template facts and remains presentation-only.
- `HFC-104` Block template change invalidates occurrence/placement diagnostics.
- `HFC-105` Multiple query loops are distinguished from main singular content when placement profile says main content only.
- `HFC-106` Block theme unsupported slot remains degraded rather than falling back to raw file modification.
- `HFC-107` Generated markup remains valid around block serialization and does not corrupt block comments.
- `HFC-108` Theme.json/style system remains separate from generic CSS snippet unless explicit CSS snippet type is chosen.
- `HFC-109` Full-site-editing cache/pre-render paths do not bypass consent/CSP/environment evaluation.
- `HFC-110` AI/MCP cannot edit PHP/theme source to force block-theme placement through HFC.

### Group 11 — builder compatibility
- `HFC-111` Builder adapter capability/version is detected before exposing placement location.
- `HFC-112` Unsupported builder/version reports unavailable location and does not inject via undocumented internals.
- `HFC-113` Before/after-content builder placement maps to documented builder hook/component where certified.
- `HFC-114` Builder preview/editor context can suppress high-risk code to avoid duplicate tracking/editor breakage.
- `HFC-115` Frontend builder output evaluates same snippet conditions/consent/CSP as non-builder path.
- `HFC-116` Builder template/global part occurrence is distinguished from page-specific instance where possible.
- `HFC-117` Builder cache/regeneration invalidation is adapter-owned and scoped after snippet placement change.
- `HFC-118` Builder-owned script/tag system conflict is detected to prevent duplicate analytics/tag output.
- `HFC-119` Snippet removal does not modify builder content unless explicit manual placement token cleanup is separately approved.
- `HFC-120` Multisite builder adapter state remains site-owned.
- `HFC-121` AI/MCP cannot use builder adapter as hidden admin/server code injection route.

### Group 12 — occurrence diagnostics
- `HFC-122` Diagnostics record route/context, resolved placement, snippet revision and emitted/not-emitted reason.
- `HFC-123` Condition false, paused, environment mismatch, consent blocked and CSP conflict are distinct reasons.
- `HFC-124` Manual shortcode/block occurrence index records source resource/location without copying protected full content unnecessarily.
- `HFC-125` Theme/builder hook absence is reported as compatibility degradation.
- `HFC-126` Duplicate snippet/library occurrence is detected by stable snippet/URL/fingerprint where possible.
- `HFC-127` Occurrence diagnostics do not claim browser network execution success unless telemetry explicitly observed it.
- `HFC-128` External script load failure is separate from server-side emission success.
- `HFC-129` Protected route/source details are redacted for unauthorized operators.
- `HFC-130` Diagnostic cache invalidates on snippet/theme/builder/content revision changes.
- `HFC-131` Network summary aggregates counts without raw cross-site page URLs by default.
- `HFC-132` AI/MCP diagnostics cannot reveal private route/content/token values beyond caller Policy.

### Group 13 — consent/CSP/SRI/environment
- `HFC-133` Imported analytics/marketing snippet is assigned explicit consent category and remains blocked until required signal.
- `HFC-134` Compatibility import cannot default unknown marketing script to strictly-necessary silently.
- `HFC-135` Enforced CSP conflict blocks/reports snippet; HFC never weakens CSP automatically.
- `HFC-136` External script origin is validated/allowlisted and SRI/crossorigin/referrerpolicy attributes preserved where configured.
- `HFC-137` SRI mismatch would block browser execution according browser semantics and is not auto-removed to “fix” loading.
- `HFC-138` CSP nonce/hash integration delegates to STM/Protector owner and does not persist reusable nonce as secret.
- `HFC-139` Production/staging/development environment scope is explicit and cloned snippets are review-required where bindings change.
- `HFC-140` Production-only analytics cannot execute on staging merely because imported source lacked environment semantics.
- `HFC-141` Consent withdrawal stops future emission/execution where technically possible and no impossible deletion of vendor data is claimed.
- `HFC-142` Provider/vendor privacy/compliance remains external fact; presence of consent category does not certify legal compliance.
- `HFC-143` AI/MCP cannot reclassify consent, weaken CSP/SRI or broaden environment scope outside approval.

### Group 14 — PHP/server-code rejection
- `HFC-144` Import detects PHP tags/server snippet type and rejects/quarantines item rather than creating executable snippet.
- `HFC-145` Obfuscated/base64-encoded source is not decoded/executed as PHP; risk review remains browser-content classification only.
- `HFC-146` Arbitrary SQL/shell/server callback fields from competitor export are rejected.
- `HFC-147` Source “universal snippet” that may contain PHP remains unresolved/rejected until browser-safe content is extracted manually.
- `HFC-148` No “unsafe mode” flag can re-enable PHP/eval in HFC/STM.
- `HFC-149` Custom hook name cannot become server callback/code execution surface.
- `HFC-150` Shortcode import cannot register arbitrary PHP shortcode callback; only registered safe snippet placement token is supported.
- `HFC-151` JavaScript containing strings resembling PHP is treated as JS syntax/content, not executed server-side.
- `HFC-152` Rejected server-code item export/report redacts secrets but preserves source provenance/fingerprint for migration audit.
- `HFC-153` Extension requirement is redirected to typed Extension SDK/plugin plan under separate development consent.
- `HFC-154` AI/MCP cannot translate/repackage PHP into hidden eval/server execution within STM/HFC.

### Group 15 — Multisite/import/coexistence
- `HFC-155` Snippets/import are site-scoped by default; same source ID/key on two sites cannot collide.
- `HFC-156` Network library/template can distribute Draft definitions with explicit target sites and no shared secret reveal.
- `HFC-157` Network-enforced security floor can prohibit inline JS/unapproved origins without granting site admin override.
- `HFC-158` Site clone changes environment/provider/production bindings to review-required state.
- `HFC-159` Coexistence detector reports WPCode/header-footer/theme/builder/analytics duplicate output candidates.
- `HFC-160` Migration does not disable competitor plugin/source automatically; verify parity before optional later disable.
- `HFC-161` Duplicate external URL/snippet fingerprint conflict is surfaced with ownership/preference options.
- `HFC-162` Network emergency pause remains network-authority operation and does not imply deleting site definitions.
- `HFC-163` Import into site cannot create network-enforced snippet through forged scope.
- `HFC-164` Shared full-page cache varies correctly for site/audience/consent/environment emission state.
- `HFC-165` AI/MCP site principal cannot publish/import network-wide snippets or disable coexistence owners.

### Group 16 — security/cache/performance golden
- `HFC-166` Golden simple head analytics import maps to Draft external/inline browser snippet with consent/environment/CSP review.
- `HFC-167` Golden latest-N/taxonomy/CPT preset scenario emits only in exact selected contexts without authorization effects.
- `HFC-168` Golden shortcode/block placement scenario honors pause/consent/CSP and does not execute arbitrary shortcode/PHP.
- `HFC-169` Golden classic/block/builder compatibility scenario reports unsupported hooks rather than editing theme/server code.
- `HFC-170` Golden duplicate/coexistence scenario detects competing tag output and requires explicit ownership resolution.
- `HFC-171` Golden PHP/server-code migration scenario rejects item and routes requirement to Extension Plan.
- `HFC-172` Golden CSP/SRI scenario refuses silent security weakening even when snippet would otherwise fail.
- `HFC-173` Golden cache scenario proves snippet output does not bleed across site/consent/environment/private audience contexts.
- `HFC-174` Golden Multisite scenario proves site/network import, enforcement and emergency-pause authority isolation.
- `HFC-175` Golden performance scenario later measures duplicate scripts/render impact/cache behavior with declared browser/server profile; currently NOT EXECUTED.
- `HFC-176` Golden adversarial AI/MCP scenario cannot import/execute PHP, reveal secrets, bypass consent/CSP, publish high-risk code or expand network scope outside Policy.

## Execution gate

This document specifies evidence only. **HFC executed remains 0/176.** No snippet import/activation, browser code emission, provider call, theme/builder runtime, test, benchmark or AI/MCP execution is authorized by this protocol.